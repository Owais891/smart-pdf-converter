<?php
/**
 * Google and Facebook OAuth sign-in callback.
 */
require_once __DIR__ . '/../includes/functions.php';

$localConfig = __DIR__ . '/../config/social_auth.local.php';
$socialConfig = is_file($localConfig) ? require $localConfig : [];
if (!is_array($socialConfig)) {
    $socialConfig = [];
}

function social_auth_fail(string $message): void
{
    flash('error', $message);
    redirect(APP_URL . '/auth/login.php');
}

function social_auth_http(string $url, string $method = 'GET', array $params = [], string $accessToken = ''): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP cURL is required for social sign-in.');
    }

    $headers = ['Accept: application/json'];
    if ($accessToken !== '') {
        $headers[] = 'Authorization: Bearer ' . $accessToken;
    }

    if ($method === 'GET' && $params) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params, '', '&', PHP_QUERY_RFC3986));
    }

    $body = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($body === false) {
        throw new RuntimeException('Could not connect to the sign-in provider: ' . $error);
    }

    $data = json_decode($body, true);
    if ($status < 200 || $status >= 300 || !is_array($data)) {
        throw new RuntimeException('The sign-in provider rejected the request.');
    }

    return $data;
}

function social_auth_complete(string $provider, string $subject, string $email, string $name): int
{
    $email = mb_strtolower(trim($email));
    $name = trim($name);
    if (!is_valid_email($email) || $subject === '') {
        throw new RuntimeException('The provider did not return a usable email address and account ID.');
    }
    if ($name === '') {
        $name = strstr($email, '@', true) ?: 'Smart PDF user';
    }
    $name = mb_substr($name, 0, 100);

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'SELECT u.id, u.is_active FROM social_accounts s
             JOIN users u ON u.id = s.user_id
             WHERE s.provider = ? AND s.provider_user_id = ? LIMIT 1'
        );
        $stmt->execute([$provider, $subject]);
        $user = $stmt->fetch();

        if ($user) {
            if ((int)$user['is_active'] !== 1) {
                throw new RuntimeException('Your account has been disabled. Please contact support.');
            }
            $userId = (int)$user['id'];
        } else {
            $stmt = $pdo->prepare('SELECT id, is_active FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                if ((int)$user['is_active'] !== 1) {
                    throw new RuntimeException('Your account has been disabled. Please contact support.');
                }
                $userId = (int)$user['id'];
            } else {
                $password = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    'INSERT INTO users (full_name, email, password, role, is_active)
                     VALUES (?, ?, ?, "user", 1)'
                );
                $stmt->execute([$name, $email, $password]);
                $userId = (int)$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO user_settings (user_id, theme, language) VALUES (?, "light", "en")')
                    ->execute([$userId]);
            }

            $pdo->prepare(
                'INSERT INTO social_accounts (user_id, provider, provider_user_id) VALUES (?, ?, ?)'
            )->execute([$userId, $provider, $subject]);
        }

        $pdo->commit();
        return $userId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

$isCallback = isset($_GET['code']) || isset($_GET['error']) || isset($_GET['state']);
if (!$isCallback) {
    $provider = (string)($_GET['provider'] ?? '');
    if (!in_array($provider, ['google', 'facebook'], true)) {
        social_auth_fail('Choose Google or Facebook to sign in.');
    }

    $settings = $socialConfig[$provider] ?? [];
    if (empty($settings['client_id']) || empty($settings['client_secret'])) {
        social_auth_fail(ucfirst($provider) . ' sign-in is not configured yet. Add its OAuth credentials in config/social_auth.local.php.');
    }

    $state = bin2hex(random_bytes(32));
    $_SESSION['social_oauth'] = [
        'provider' => $provider,
        'state' => $state,
        'created_at' => time(),
    ];
    $redirectUri = APP_URL . '/auth/social_login.php';

    if ($provider === 'google') {
        $authorizeUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
        $params = [
            'client_id' => $settings['client_id'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ];
    } else {
        $version = $settings['graph_version'] ?? 'v22.0';
        $authorizeUrl = 'https://www.facebook.com/' . rawurlencode($version) . '/dialog/oauth';
        $params = [
            'client_id' => $settings['client_id'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email,public_profile',
            'state' => $state,
        ];
    }

    redirect($authorizeUrl . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986));
}

$oauth = $_SESSION['social_oauth'] ?? null;
unset($_SESSION['social_oauth']);
if (!is_array($oauth) || empty($oauth['provider']) || empty($oauth['state']) ||
    (int)($oauth['created_at'] ?? 0) > time() ||
    time() - (int)($oauth['created_at'] ?? 0) > 600 ||
    !isset($_GET['state']) || !hash_equals((string)$oauth['state'], (string)$_GET['state'])) {
    social_auth_fail('The sign-in request expired or could not be verified. Please try again.');
}

$provider = (string)$oauth['provider'];
if (isset($_GET['error'])) {
    social_auth_fail('Sign-in was cancelled or declined. You can try again.');
}
if (empty($_GET['code'])) {
    social_auth_fail('The provider did not complete sign-in. Please try again.');
}

$settings = $socialConfig[$provider] ?? [];
if (empty($settings['client_id']) || empty($settings['client_secret'])) {
    social_auth_fail(ucfirst($provider) . ' sign-in is not configured yet.');
}

try {
    $redirectUri = APP_URL . '/auth/social_login.php';
    if ($provider === 'google') {
        $tokens = social_auth_http('https://oauth2.googleapis.com/token', 'POST', [
            'code' => (string)$_GET['code'],
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);
        if (empty($tokens['access_token'])) {
            throw new RuntimeException('Google did not return an access token.');
        }
        $profile = social_auth_http(
            'https://openidconnect.googleapis.com/v1/userinfo',
            'GET',
            [],
            (string)$tokens['access_token']
        );
        if (empty($profile['email_verified'])) {
            throw new RuntimeException('Verify your email address with Google before using it to sign in.');
        }
        $subject = (string)($profile['sub'] ?? '');
    } else {
        $version = $settings['graph_version'] ?? 'v22.0';
        $graphBase = 'https://graph.facebook.com/' . rawurlencode($version);
        $tokens = social_auth_http($graphBase . '/oauth/access_token', 'GET', [
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
            'redirect_uri' => $redirectUri,
            'code' => (string)$_GET['code'],
        ]);
        if (empty($tokens['access_token'])) {
            throw new RuntimeException('Facebook did not return an access token.');
        }
        $profile = social_auth_http(
            $graphBase . '/me',
            'GET',
            ['fields' => 'id,name,email'],
            (string)$tokens['access_token']
        );
        $subject = (string)($profile['id'] ?? '');
    }

    $userId = social_auth_complete(
        $provider,
        $subject,
        (string)($profile['email'] ?? ''),
        (string)($profile['name'] ?? '')
    );
    login_user($userId);
    flash('success', 'You are signed in with ' . ucfirst($provider) . '.');

    $destination = $_SESSION['redirect_after_login'] ?? (APP_URL . '/dashboard/index.php');
    unset($_SESSION['redirect_after_login']);
    $appUrl = parse_url(APP_URL);
    $target = parse_url((string)$destination);
    $appPath = rtrim($appUrl['path'] ?? '', '/');
    $targetPath = $target['path'] ?? '';
    $isLocalPath = isset($destination[0]) && $destination[0] === '/' &&
        strpos($destination, '//') !== 0 &&
        ($appPath === '' || $targetPath === $appPath || strpos($targetPath, $appPath . '/') === 0);
    $isSameOrigin = isset($target['host'], $appUrl['host']) &&
        strtolower($target['host']) === strtolower($appUrl['host']) &&
        ($target['scheme'] ?? '') === ($appUrl['scheme'] ?? '') &&
        ($target['port'] ?? null) === ($appUrl['port'] ?? null) &&
        ($appPath === '' || $targetPath === $appPath || strpos($targetPath, $appPath . '/') === 0);
    if (!$isLocalPath && !$isSameOrigin) {
        $destination = APP_URL . '/dashboard/index.php';
    }
    redirect($destination);
} catch (Throwable $e) {
    error_log('Social sign-in error (' . $provider . '): ' . $e->getMessage());
    social_auth_fail($e instanceof RuntimeException
        ? $e->getMessage()
        : 'Could not complete social sign-in. Please try again.');
}
