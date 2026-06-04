<?php
// All PHP logic BEFORE any HTML output to allow redirects
require_once '../inc/session-config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';
require_once '../inc/constants.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$errors   = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'first_name'     => sanitize_input($_POST['first_name']     ?? ''),
        'mothers_name'   => sanitize_input($_POST['mothers_name']   ?? ''),
        'gender'         => sanitize_input($_POST['gender']         ?? ''),
        'date_of_birth'  => sanitize_input($_POST['date_of_birth']  ?? ''),
        'place_of_birth' => sanitize_input($_POST['place_of_birth'] ?? ''),
        'government_id'  => sanitize_input($_POST['government_id']  ?? ''),
        'education'      => sanitize_input($_POST['education']      ?? ''),
        'occupation'     => sanitize_input($_POST['occupation']     ?? ''),
        'country'        => sanitize_input($_POST['country']        ?? ''),
        'state'          => sanitize_input($_POST['state']          ?? ''),
        'district'       => sanitize_input($_POST['district']       ?? ''),
        'email'          => sanitize_input($_POST['email']          ?? ''),
        'phone'          => sanitize_input($_POST['phone']          ?? ''),
    ];

    // Validation
    if (empty($form_data['first_name']))   $errors[] = 'First name is required';
    if (empty($form_data['mothers_name'])) $errors[] = "Mother's name is required";

    if (empty($form_data['gender']) || !in_array($form_data['gender'], ['Male', 'Female'])) {
        $errors[] = 'Valid gender selection is required';
    }

    if (empty($form_data['date_of_birth']) || !strtotime($form_data['date_of_birth'])) {
        $errors[] = 'Valid date of birth is required';
    } else {
        $dob   = new DateTime($form_data['date_of_birth']);
        $today = new DateTime();
        $age   = $today->diff($dob)->y;
        if ($age < 18) $errors[] = 'Candidate must be at least 18 years old';
    }

    if (empty($form_data['place_of_birth'])) $errors[] = 'Place of birth is required';

    if (empty($form_data['education']) || !in_array($form_data['education'], EDUCATION_LEVELS)) {
        $errors[] = 'Valid education level is required';
    }

    if (empty($form_data['occupation'])) $errors[] = 'Occupation is required';
    if (empty($form_data['country']))    $errors[] = 'Country is required';
    if (empty($form_data['state']))      $errors[] = 'State/Region is required';
    if (empty($form_data['district']))   $errors[] = 'District is required';

    if (empty($form_data['email'])) {
        $errors[] = 'Email is required';
    } elseif (!validate_email($form_data['email'])) {
        $errors[] = 'Invalid email address';
    }

    if (empty($form_data['phone'])) {
        $errors[] = 'Phone number is required';
    } elseif (!validate_phone($form_data['phone'])) {
        $errors[] = 'Invalid phone number format';
    }

    // Duplicate email check
    if (!empty($form_data['email'])) {
        $email_check = $conn->prepare("SELECT id FROM candidates WHERE email = ?");
        if ($email_check) {
            $email_check->bind_param("s", $form_data['email']);
            $email_check->execute();
            if ($email_check->get_result()->num_rows > 0) $errors[] = 'Email already registered';
            $email_check->close();
        }
    }

    // Duplicate phone check
    if (!empty($form_data['phone'])) {
        $phone_check = $conn->prepare("SELECT id FROM candidates WHERE phone = ?");
        if ($phone_check) {
            $phone_check->bind_param("s", $form_data['phone']);
            $phone_check->execute();
            if ($phone_check->get_result()->num_rows > 0) $errors[] = 'Phone number already registered';
            $phone_check->close();
        }
    }

    // Photo upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $new_photo = upload_file($_FILES['photo'], 'uploads/candidates/');
        if ($new_photo) {
            $photo_path = $new_photo;
        } else {
            $errors[] = "Failed to upload photo. Please ensure it's a valid image file (JPEG, PNG, GIF) under 5MB.";
        }
    }

    // Save and redirect — no HTML output yet
    if (empty($errors)) {
        $status = 'approved'; // Admin-added candidates are always approved

        $insert = $conn->prepare(
            "INSERT INTO candidates (first_name, mothers_name, gender, date_of_birth, place_of_birth,
             government_id, education, occupation, country, state, district, email, phone, photo_path, status, added_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($insert) {
            $insert->bind_param(
                "sssssssssssssssi",
                $form_data['first_name'],
                $form_data['mothers_name'],
                $form_data['gender'],
                $form_data['date_of_birth'],
                $form_data['place_of_birth'],
                $form_data['government_id'],
                $form_data['education'],
                $form_data['occupation'],
                $form_data['country'],
                $form_data['state'],
                $form_data['district'],
                $form_data['email'],
                $form_data['phone'],
                $photo_path,
                $status,
                $admin_id
            );

            if ($insert->execute()) {
                log_audit($admin_id, 'CREATE', 'candidates', $insert->insert_id, ['name' => $form_data['first_name'] . ' ' . $form_data['mothers_name']]);
                $_SESSION['flash_success'] = 'Candidate added successfully!';
                $insert->close();
                header('Location: candidates-list.php');
                exit();
            } else {
                $errors[] = 'Error adding candidate. Please try again.';
            }
            $insert->close();
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }
}

// Only output HTML after all possible redirects
include 'header.php';
?>

<style>
    .form-container-admin { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.08); max-width: 900px; }
    .page-title { font-size: 28px; color: #083a9c; margin-bottom: 10px; font-weight: 700; }
    .page-subtitle { color: #666; margin-bottom: 30px; font-size: 15px; }
    .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid; display: none; }
    .alert.show { display: block; animation: slideIn 0.3s ease-out; }
    @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .alert-error { background: #fee2e2; color: #991b1b; border-color: #dc2626; }
    .alert ul { margin-left: 20px; }
    .alert li { margin-bottom: 5px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .form-row.full { grid-template-columns: 1fr; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { font-weight: 600; color: #333; margin-bottom: 8px; font-size: 15px; }
    .form-group label .required { color: #dc2626; margin-left: 3px; }
    .form-group input, .form-group select { padding: 12px 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 15px; font-family: Arial, sans-serif; transition: 0.3s ease; }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .form-buttons { display: flex; gap: 15px; justify-content: center; margin-top: 30px; }
    .btn { padding: 14px 40px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary { background: linear-gradient(135deg, #083a9c 0%, #2563eb 100%); color: white; box-shadow: 0 6px 18px rgba(8,58,156,0.25); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(8,58,156,0.35); }
    .btn-secondary { background: #f3f4f6; color: #333; border: 2px solid #ddd; }
    .btn-secondary:hover { background: #e5e7eb; }
    @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } .form-buttons { flex-direction: column; } .btn { width: 100%; justify-content: center; } }
</style>

<div class="form-container-admin">
    <h1 class="page-title"><i class="fas fa-user-plus" style="margin-right: 10px;"></i>Add Candidate</h1>
    <p class="page-subtitle">Register a new election candidate</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error show">
            <i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i>Please fix the following errors:
            <ul style="margin-top: 10px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-row">
            <div class="form-group">
                <label>First Name <span class="required">*</span></label>
                <input type="text" name="first_name" required value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Mother's Name <span class="required">*</span></label>
                <input type="text" name="mothers_name" required value="<?php echo htmlspecialchars($form_data['mothers_name'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Gender <span class="required">*</span></label>
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male"   <?php echo (($form_data['gender'] ?? '') === 'Male')   ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (($form_data['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date of Birth <span class="required">*</span></label>
                <input type="date" name="date_of_birth" required value="<?php echo htmlspecialchars($form_data['date_of_birth'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Place of Birth <span class="required">*</span></label>
                <input type="text" name="place_of_birth" required value="<?php echo htmlspecialchars($form_data['place_of_birth'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Government ID / Passport</label>
                <input type="text" name="government_id" value="<?php echo htmlspecialchars($form_data['government_id'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Education Level <span class="required">*</span></label>
                <select name="education" required>
                    <option value="">Select Education Level</option>
                    <?php foreach (EDUCATION_LEVELS as $level): ?>
                        <option value="<?php echo htmlspecialchars($level); ?>" <?php echo (($form_data['education'] ?? '') === $level) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($level); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Occupation <span class="required">*</span></label>
                <input type="text" name="occupation" required value="<?php echo htmlspecialchars($form_data['occupation'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Country <span class="required">*</span></label>
                <select name="country" required>
                    <option value="">Select Country</option>
                    <?php foreach (AFRICAN_COUNTRIES as $country): ?>
                        <option value="<?php echo htmlspecialchars($country); ?>" <?php echo (($form_data['country'] ?? '') === $country) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($country); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>State/Region <span class="required">*</span></label>
                <input type="text" name="state" required value="<?php echo htmlspecialchars($form_data['state'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>District <span class="required">*</span></label>
                <input type="text" name="district" required value="<?php echo htmlspecialchars($form_data['district'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Phone Number <span class="required">*</span></label>
                <input type="tel" name="phone" required value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Photo (Optional)</label>
                <input type="file" name="photo" accept="image/*">
            </div>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>Add Candidate</button>
            <a href="candidates-list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i>Cancel</a>
        </div>

    </form>
</div>

<?php include 'footer.php'; ?>
