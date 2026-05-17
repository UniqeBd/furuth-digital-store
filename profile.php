<?php
require_once __DIR__ . '/includes/init.php';
require_login();
$db = Database::connect();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? 'profile';
    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $db->prepare('SELECT id FROM ' . db_table('registry') . ' WHERE email = ? AND id != ?');
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                flash('error', 'Email already in use.');
            } else {
                $db->prepare('UPDATE ' . db_table('registry') . ' SET name = ?, email = ? WHERE id = ?')->execute([$name, $email, $user['id']]);
                flash('success', 'Profile updated.');
            }
        }
    } elseif ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $stmt = $db->prepare('SELECT password FROM ' . db_table('registry') . ' WHERE id = ?');
        $stmt->execute([$user['id']]);
        $hash = $stmt->fetchColumn();
        if (!password_verify($current, $hash)) {
            flash('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 8 || $new !== $confirm) {
            flash('error', 'New password must be 8+ characters and match confirmation.');
        } else {
            $db->prepare('UPDATE ' . db_table('registry') . ' SET password = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            flash('success', 'Password changed.');
        }
    }
    redirect(base_url('profile.php'));
}

$user = current_user();
$pageTitle = 'Profile';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="container py-3 py-lg-4">
    <h1 class="h3 fw-bold mb-4 d-lg-none">Profile</h1>
    <?php if ($err = flash('error')): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
    <?php if ($ok = flash('success')): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>

    <div class="profile-layout">
        <aside class="profile-sidebar d-none d-lg-block">
            <a href="<?= base_url('profile.php') ?>" class="active"><i class="fas fa-user"></i> Profile</a>
            <a href="<?= base_url('orders.php') ?>"><i class="fas fa-download"></i> Orders</a>
            <a href="<?= base_url('ticket.php') ?>"><i class="fas fa-headset"></i> Support</a>
            <a href="<?= base_url('auth/logout.php') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </aside>
        <div>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3">Account Details</h2>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="profile">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= e($user['name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($user['email']) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-touch">Save Changes</button>
                    </form>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3">Change Password</h2>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="password">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-touch">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
