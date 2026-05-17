<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$db = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    if (($_POST['action'] ?? '') === 'delete') {
        $db->prepare('DELETE FROM faqs WHERE id = ?')->execute([(int)$_POST['id']]);
    } else {
        $db->prepare('INSERT INTO faqs (question, answer, sort_order, is_active) VALUES (?,?,?,?)')
            ->execute([trim($_POST['question']), trim($_POST['answer']), (int)$_POST['sort_order'], isset($_POST['is_active']) ? 1 : 0]);
    }
    flash('success', 'FAQ updated.');
    redirect(panel_url('faq.php'));
}

$faqs = $db->query('SELECT * FROM faqs ORDER BY sort_order')->fetchAll();
$adminTitle = 'FAQ';
require __DIR__ . '/../includes/admin-header.php';
?>

<div class="row g-4">
<div class="col-md-4">
<div class="card p-3">
<h2 class="h6 fw-bold">Add FAQ</h2>
<form method="post"><?= csrf_field() ?>
<div class="mb-2"><input name="question" class="form-control" placeholder="Question" required></div>
<div class="mb-2"><textarea name="answer" class="form-control" rows="3" placeholder="Answer" required></textarea></div>
<div class="mb-2"><input type="number" name="sort_order" class="form-control" value="0" placeholder="Sort order"></div>
<div class="form-check mb-2"><input type="checkbox" name="is_active" class="form-check-input" checked id="fa"><label for="fa">Active</label></div>
<button class="btn btn-primary w-100">Add</button>
</form>
</div>
</div>
<div class="col-md-8">
<?php foreach ($faqs as $f): ?>
<div class="card mb-2 p-3">
<strong><?= e($f['question']) ?></strong>
<p class="small text-muted mb-2"><?= nl2br(e($f['answer'])) ?></p>
<form method="post" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $f['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
</div>
<?php endforeach; ?>
</div>
</div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
