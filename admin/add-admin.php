<?php include 'header.php'; ?>
<?php
require_role('super');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['super','admin']) ? $_POST['role'] : 'admin';
    $status = in_array($_POST['status'] ?? 'active', ['active','inactive']) ? $_POST['status'] : 'active';

    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $errors[] = 'Please fill required fields';
    } elseif (!validate_email($email)) {
        $errors[] = 'Invalid email';
    }

    // Unique checks
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Username or email already exists';
        }
        $stmt->close();
    }

    // Handle profile photo
    $profile_photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $uploaded = upload_file($_FILES['profile_photo'], 'uploads/admins/');
        if ($uploaded) {
            $profile_photo = $uploaded;
        } else {
            $errors[] = 'Profile photo upload failed';
        }
    }

    if (empty($errors)) {
        $hash = hash_password($password);
        $stmt = $conn->prepare("INSERT INTO admin_users (username, email, password, full_name, phone, role, profile_photo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssss", $username, $email, $hash, $full_name, $phone, $role, $profile_photo, $status);
            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                log_audit($_SESSION['admin_id'], 'CREATE', 'admin_users', $new_id, ['username'=>$username]);
                $_SESSION['flash_success'] = 'Admin created successfully';
                header('Location: manage-admin.php'); exit();
            } else {
                $errors[] = 'Database error creating admin';
            }
            $stmt->close();
        } else {
            $errors[] = 'Database prepare error: ' . $conn->error;
        }
    }
}
?>

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
.form-buttons { display: flex; gap: 15px; justify-content: center; margin-top: 30px; }
.btn { padding: 14px 40px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary { background: linear-gradient(135deg, #083a9c 0%, #2563eb 100%); color: white; }
.btn-secondary { background: #f3f4f6; color: #333; border: 2px solid #ddd; }
@media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } .form-buttons { flex-direction: column; } .btn { width: 100%; } }
</style>

<div class="form-container-admin">
    <h1 class="page-title"><i class="fas fa-user-plus" style="margin-right:10px;"></i>Add Admin</h1>

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
                <label>Full Name <span style="color:#dc2626;">*</span></label>
                <input type="text" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Username <span style="color:#dc2626;">*</span></label>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email <span style="color:#dc2626;">*</span></label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Password <span style="color:#dc2626;">*</span></label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="admin">Admin</option>
                    <option value="super">Super Admin</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Admin</button>
            <a href="manage-admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>