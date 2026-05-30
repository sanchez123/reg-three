<?php
ob_start();  // START OUTPUT BUFFERING - Add this FIRST!

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

require_role('super');

if (!isset($_GET['id'])) {
    header('Location: manage-admin.php');
    exit();
}

$admin_id = intval($_GET['id']);
$errors = [];

$stmt = $conn->prepare("SELECT id, username, email, full_name, phone, role, status, profile_photo FROM admin_users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        header('Location: manage-admin.php');
        exit();
    }
    $admin = $res->fetch_assoc();
    $stmt->close();
} else {
    die('Database error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['super','admin']) ? $_POST['role'] : 'admin';
    $status = in_array($_POST['status'] ?? 'active', ['active','inactive']) ? $_POST['status'] : 'active';

    if (empty($full_name) || empty($email)) {
        $errors[] = 'Name and email required';
    } elseif (!validate_email($email)) {
        $errors[] = 'Invalid email';
    }

    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
    if ($stmt) {
        $stmt->bind_param("si", $email, $admin_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email already used by another admin';
        }
        $stmt->close();
    }

    $profile_photo = $admin['profile_photo'];
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $uploaded = upload_file($_FILES['profile_photo'], 'uploads/admins/');
        if ($uploaded) {
            if ($admin['profile_photo'] && file_exists(__DIR__ . '/../' . $admin['profile_photo'])) {
                @unlink(__DIR__ . '/../' . $admin['profile_photo']);
            }
            $profile_photo = $uploaded;
        } else {
            $errors[] = 'Profile photo upload failed';
        }
    }

    if (empty($errors)) {
        $new_password = $_POST['password'] ?? '';
        $hashed = $admin['password'] ?? null;
        if (!empty($new_password)) {
            $hashed = hash_password($new_password);
        }

        if (!empty($new_password)) {
            $stmt = $conn->prepare("UPDATE admin_users SET full_name=?, email=?, phone=?, role=?, profile_photo=?, status=?, password=? WHERE id=?");
            $stmt->bind_param("sssssssi", $full_name, $email, $phone, $role, $profile_photo, $status, $hashed, $admin_id);
        } else {
            $stmt = $conn->prepare("UPDATE admin_users SET full_name=?, email=?, phone=?, role=?, profile_photo=?, status=? WHERE id=?");
            $stmt->bind_param("ssssssi", $full_name, $email, $phone, $role, $profile_photo, $status, $admin_id);
        }

        if ($stmt->execute()) {
            log_audit($_SESSION['admin_id'], 'UPDATE', 'admin_users', $admin_id, ['action'=>'Admin updated']);
            $_SESSION['flash_success'] = 'Admin updated successfully';
            ob_end_clean();  // Clear the buffer before redirecting
            header('Location: manage-admin.php');
            exit();
        } else {
            $errors[] = 'Error updating admin: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>
<?php include 'header.php'; ?>

<style>
.form-container-admin { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.08); max-width: 900px; }
.page-title { font-size: 28px; color: #083a9c; margin-bottom: 10px; font-weight: 700; }
.alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid; }
.alert-error { background: #fee2e2; color: #991b1b; border-color: #dc2626; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.form-row.full { grid-template-columns: 1fr; }
.form-group { display: flex; flex-direction: column; }
.form-group label { font-weight: 600; color: #333; margin-bottom: 8px; font-size: 15px; }
.form-group input, .form-group select { padding: 12px 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 15px; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
.current-photo { margin-top: 10px; }
.current-photo img { max-width: 120px; border-radius: 8px; }
.form-buttons { display: flex; gap: 15px; justify-content: center; margin-top: 30px; }
.btn { padding: 14px 40px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary { background: linear-gradient(135deg, #083a9c 0%, #2563eb 100%); color: white; }
.btn-secondary { background: #f3f4f6; color: #333; border: 2px solid #ddd; }
</style>

<div class="form-container-admin">
    <h1 class="page-title"><i class="fas fa-edit" style="margin-right:10px;"></i>Edit Admin</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin-left:20px;">
                <?php foreach($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required value="<?php echo htmlspecialchars($admin['full_name']); ?>">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" disabled value="<?php echo htmlspecialchars($admin['username']); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($admin['email']); ?>">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="password">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="admin" <?php echo $admin['role']==='admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="super" <?php echo $admin['role']==='super' ? 'selected' : ''; ?>>Super Admin</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*">
                <?php if (!empty($admin['profile_photo']) && file_exists(__DIR__ . '/../' . $admin['profile_photo'])): ?>
                    <div class="current-photo">
                        <img src="<?php echo '../' . htmlspecialchars($admin['profile_photo']); ?>" alt="Profile Photo">
                        <div><a href="<?php echo '../' . htmlspecialchars($admin['profile_photo']); ?>" download class="btn btn-secondary" style="padding:6px 10px; font-size:12px; margin-top:8px;">Download</a></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?php echo $admin['status']==='active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $admin['status']==='inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            <a href="manage-admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>