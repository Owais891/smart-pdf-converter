<?php
/**
 * Copy this file to social_auth.local.php and add OAuth credentials.
 * Never commit social_auth.local.php or share its client secrets.
 */
return [
    'google' => [
        'client_id' => 'YOUR_GOOGLE_OAUTH_CLIENT_ID',
        'client_secret' => 'YOUR_GOOGLE_OAUTH_CLIENT_SECRET',
    ],
    'facebook' => [
        'client_id' => 'YOUR_META_APP_ID',
        'client_secret' => 'YOUR_META_APP_SECRET',
        'graph_version' => 'v24.0',
    ],
];
