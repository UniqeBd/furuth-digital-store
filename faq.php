<?php
require_once __DIR__ . '/includes/init.php';
$db = Database::connect();
$faqs = $db->query('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order')->fetchAll();

$pageTitle = 'FAQ';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-3 py-lg-4">
    <h1 class="h3 fw-bold mb-4 text-center">Frequently Asked Questions</h1>
    <div class="accordion col-lg-8 mx-auto" id="faqPage">
        <?php foreach ($faqs as $i => $f): ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button <?= $i ? 'collapsed' : '' ?>" type="button" data-mdb-collapse-init data-mdb-target="#faqP<?= $i ?>">
                    <?= e($f['question']) ?>
                </button>
            </h2>
            <div id="faqP<?= $i ?>" class="accordion-collapse collapse <?= $i ? '' : 'show' ?>">
                <div class="accordion-body text-muted"><?= nl2br(e($f['answer'])) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
