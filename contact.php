<?php
require_once __DIR__ . '/includes/init.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $db = Database::connect();
    $db->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)')->execute([
        trim($_POST['name'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['subject'] ?? ''),
        trim($_POST['message'] ?? ''),
    ]);
    $success = true;
}

$pageTitle = 'Contact';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-3 py-lg-4">
    <h1 class="h3 fw-bold mb-4">Contact Us</h1>
    <div class="row g-4">
        <div class="col-lg-6">
            <?php if ($success): ?>
            <div class="alert alert-success">Thank you! We'll get back to you soon.</div>
            <?php else: ?>
            <form method="post" class="card border-0 shadow-sm p-4">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-touch">Send Message</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h2 class="h5 fw-bold mb-3">Get in Touch</h2>
                <p class="text-muted">Questions about products, orders, or licensing? We're here to help.</p>
                <p><i class="fas fa-envelope text-primary me-2"></i> support@furuthdigital.com</p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
