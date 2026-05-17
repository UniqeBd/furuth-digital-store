<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$db = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $id = (int)$_POST['id'];
    $db->prepare('UPDATE support_tickets SET status = ?, admin_reply = ? WHERE id = ?')
        ->execute([$_POST['status'], trim($_POST['admin_reply'] ?? ''), $id]);
    flash('success', 'Ticket updated.');
    redirect(panel_url('support.php'));
}

$tickets = $db->query('SELECT * FROM support_tickets ORDER BY created_at DESC')->fetchAll();
$messages = $db->query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 20')->fetchAll();

$adminTitle = 'Support';
require __DIR__ . '/../includes/admin-header.php';
?>

<div class="row g-4">
<div class="col-lg-7">
<h2 class="h6 fw-bold">Tickets</h2>
<?php foreach ($tickets as $t): ?>
<div class="card mb-3 p-3">
<div class="d-flex justify-content-between"><strong><?= e($t['subject']) ?></strong><span class="badge bg-info"><?= e($t['status']) ?></span></div>
<p class="small text-muted mb-1"><?= e($t['name']) ?> &lt;<?= e($t['email']) ?>&gt; — <?= date('M j, Y', strtotime($t['created_at'])) ?></p>
<p><?= nl2br(e($t['message'])) ?></p>
<?php if ($t['admin_reply']): ?><p class="bg-light p-2 rounded small"><strong>Reply:</strong> <?= nl2br(e($t['admin_reply'])) ?></p><?php endif; ?>
<form method="post" class="mt-2"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>">
<textarea name="admin_reply" class="form-control mb-2" rows="2" placeholder="Reply…"></textarea>
<select name="status" class="form-select mb-2"><option value="open">open</option><option value="in_progress">in progress</option><option value="closed">closed</option></select>
<button class="btn btn-sm btn-primary">Update</button>
</form>
</div>
<?php endforeach; ?>
<?php if (!$tickets): ?><p class="text-muted">No tickets yet.</p><?php endif; ?>
</div>
<div class="col-lg-5">
<h2 class="h6 fw-bold">Contact Messages</h2>
<?php foreach ($messages as $m): ?>
<div class="card mb-2 p-2 small">
<strong><?= e($m['subject']) ?></strong> — <?= e($m['name']) ?><br>
<?= nl2br(e($m['message'])) ?>
</div>
<?php endforeach; ?>
</div>
</div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
