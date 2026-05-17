<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$db = Database::connect();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $db->prepare('INSERT INTO support_tickets (user_id, name, email, subject, message) VALUES (?,?,?,?,?)')
        ->execute([
            $user['id'],
            $user['name'],
            $user['email'],
            trim($_POST['subject'] ?? ''),
            trim($_POST['message'] ?? ''),
        ]);
    flash('success', 'Support ticket submitted.');
    redirect(base_url('ticket.php'));
}

$tickets = $db->prepare('SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC');
$tickets->execute([$user['id']]);
$tickets = $tickets->fetchAll();

$pageTitle = 'Support';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-3 py-lg-4">
    <h1 class="h3 fw-bold mb-4">Support Tickets</h1>
    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <form method="post" class="card border-0 shadow-sm p-4">
                <?= csrf_field() ?>
                <h2 class="h6 fw-bold mb-3">New Ticket</h2>
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-touch w-100">Submit Ticket</button>
            </form>
        </div>
        <div class="col-lg-7">
            <h2 class="h6 fw-bold mb-3">Your Tickets</h2>
            <?php if (empty($tickets)): ?>
            <p class="text-muted">No tickets yet.</p>
            <?php else: ?>
            <?php foreach ($tickets as $t): ?>
            <div class="card border-0 shadow-sm mb-3 p-3">
                <div class="d-flex justify-content-between mb-2">
                    <strong><?= e($t['subject']) ?></strong>
                    <span class="badge bg-info"><?= e($t['status']) ?></span>
                </div>
                <p class="small text-muted"><?= date('M j, Y g:i A', strtotime($t['created_at'])) ?></p>
                <p><?= nl2br(e($t['message'])) ?></p>
                <?php if ($t['admin_reply']): ?>
                <div class="bg-light rounded p-2 mt-2 small">
                    <strong>Support reply:</strong><br><?= nl2br(e($t['admin_reply'])) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
