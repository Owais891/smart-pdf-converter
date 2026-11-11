<?php
/**
 * Smart PDF - Admin: Contact Messages
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'mark_read' && $id) {
        try {
            db()->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
            flash('success', 'Marked as read.');
        } catch (Throwable $e) { flash('error', 'Could not update.'); }
        redirect(APP_URL . '/admin/messages.php');
    }
    if ($action === 'delete' && $id) {
        try {
            db()->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
            flash('success', 'Message deleted.');
        } catch (Throwable $e) { flash('error', 'Could not delete.'); }
        redirect(APP_URL . '/admin/messages.php');
    }
}

$active = 'admin';
$pageTitle = 'Contact Messages';
require __DIR__ . '/../includes/dashboard_header.php';

$rows = [];
try {
    $rows = db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
} catch (Throwable $e) {}
?>

<div class="dash-page-title">Contact Messages</div>

<div class="dash-card">
    <div class="dash-card-header"><h5>Total Messages: <?= count($rows) ?></h5></div>

    <?php if (empty($rows)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h5>No messages yet</h5>
            <p>Messages submitted via the Contact Us form will appear here.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>From</th><th>Subject</th><th>Message</th>
                        <th>Status</th><th>Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td>
                                <strong><?= e($r['name']) ?></strong><br>
                                <a href="mailto:<?= e($r['email']) ?>" class="small text-muted"><?= e($r['email']) ?></a>
                            </td>
                            <td><?= e($r['subject']) ?></td>
                            <td style="max-width:300px"><?= e(mb_substr($r['message'], 0, 120)) ?><?= mb_strlen($r['message']) > 120 ? '...' : '' ?></td>
                            <td>
                                <?php if ((int)$r['is_read'] === 1): ?>
                                    <span class="status-pill status-expired">Read</span>
                                <?php else: ?>
                                    <span class="status-pill status-pending">Unread</span>
                                <?php endif; ?>
                            </td>
                            <td><?= time_ago($r['created_at']) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#msgModal<?= (int)$r['id'] ?>" title="View">
                                        <i class="fas fa-eye text-primary"></i>
                                    </button>
                                    <?php if ((int)$r['is_read'] === 0): ?>
                                    <form method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-icon" title="Mark read">
                                            <i class="fas fa-check text-success"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete message?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-icon text-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modals -->
        <?php foreach ($rows as $r): ?>
        <?php
            $replySubject = 'Re: ' . $r['subject'];
            $replyUrl = 'https://mail.google.com/mail/?view=cm&fs=1'
                . '&to=' . rawurlencode($r['email'])
                . '&su=' . rawurlencode($replySubject);
            $mailtoUrl = 'mailto:' . rawurlencode($r['email'])
                . '?subject=' . rawurlencode($replySubject);
        ?>
        <div class="modal fade" id="msgModal<?= (int)$r['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= e($r['subject']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">
                            From <strong><?= e($r['name']) ?></strong>
                            (<?= e($r['email']) ?>) on <?= date('M j, Y g:i a', strtotime($r['created_at'])) ?>
                        </p>
                        <hr>
                        <p style="white-space:pre-wrap"><?= e($r['message']) ?></p>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= e($replyUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                            <i class="fas fa-reply me-1"></i> Reply
                        </a>
                        <a href="<?= e($mailtoUrl) ?>" class="btn btn-outline-secondary" title="Open your default email app">
                            <i class="fas fa-envelope me-1"></i> Email app
                        </a>
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
