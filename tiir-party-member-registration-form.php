<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$success = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $form_data = [
        'first_name' => sanitize_input($_POST['first-name'] ?? ''),
        'mothers_name' => sanitize_input($_POST['mothers-name'] ?? ''),
        'gender' => sanitize_input($_POST['gender'] ?? ''),
        'dob' => sanitize_input($_POST['dob'] ?? ''),
        'pob' => sanitize_input($_POST['pob'] ?? ''),
        'govt' => sanitize_input($_POST['govtid'] ?? ''),
        'education' => sanitize_input($_POST['education'] ?? ''),
        'occupation' => sanitize_input($_POST['occupation'] ?? ''),
        'country' => sanitize_input($_POST['country'] ?? ''),
        'state' => sanitize_input($_POST['state'] ?? ''),
        'district' => sanitize_input($_POST['district'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'phone' => sanitize_input($_POST['phone'] ?? ''),
        'security_code' => sanitize_input($_POST['security_code'] ?? '')
    ];

    // Validate each field individually
        if (empty($form_data['first_name'])) {
            $errors[] = 'First Name is required';
        }
        if (empty($form_data['mothers_name'])) {
            $errors[] = 'Mother\'s Name is required';
        }
        if (empty($form_data['gender'])) {
            $errors[] = 'Gender is required';
        }
        if (empty($form_data['dob'])) {
            $errors[] = 'Date of Birth is required';
        } else {
            // Check if user is at least 18 years old
            $dob = new DateTime($form_data['dob']);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            if ($age < 18) {
                $errors[] = 'You must be at least 18 years old to register';
            }
        }
        if (empty($form_data['pob'])) {
            $errors[] = 'Place of Birth is required';
        }
        if (empty($form_data['education'])) {
            $errors[] = 'Education Level is required';
        }
        if (empty($form_data['occupation'])) {
            $errors[] = 'Occupation is required';
        }
        if (empty($form_data['country'])) {
            $errors[] = 'Country is required';
        }
        if (empty($form_data['state'])) {
            $errors[] = 'State/Region is required';
        }
        if (empty($form_data['district'])) {
            $errors[] = 'District is required';
        }
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
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Photo is required';
        }
        if (empty($form_data['security_code'])) {
            $errors[] = 'Security code is required';
        } elseif (isset($_SESSION['security_code']) && (int)$_SESSION['security_code'] !== (int)$form_data['security_code']) {
            $errors[] = 'Security code is incorrect';
        }

    // If no errors, process the form
    if (empty($errors)) {
        // Handle photo upload
        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo_path = upload_file($_FILES['photo'], 'uploads/members/');
            if (!$photo_path) {
                $errors[] = 'Photo upload failed';
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO members (first_name, mothers_name, gender, date_of_birth, place_of_birth, government_id, education, occupation, country, state, district, email, phone, photo_path, status, security_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
            if ($stmt) {
                $stmt->bind_param("sssssssssssssss",
                    $form_data['first_name'],
                    $form_data['mothers_name'],
                    $form_data['gender'],
                    $form_data['dob'],
                    $form_data['pob'],
                    $form_data['govt'],
                    $form_data['education'],
                    $form_data['occupation'],
                    $form_data['country'],
                    $form_data['state'],
                    $form_data['district'],
                    $form_data['email'],
                    $form_data['phone'],
                    $photo_path,
                    $form_data['security_code']
                );
                if ($stmt->execute()) {
                    $success = 'Registration successful! Your information has been submitted.';
                    $form_data = [];
                    $_SESSION['security_code'] = rand(10000, 99999);
                } else {
                    $errors[] = 'Database error: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $errors[] = 'Database error: ' . $conn->error;
            }
        }
    }
}

// Generate security code if needed
if (!isset($_SESSION['security_code'])) {
    $_SESSION['security_code'] = rand(10000, 99999);
}
?>
<?php
include("inc/header.php");
include("inc/menu.php");
?>

<div id="content">
  <div id="main" class="inner_main">

    <div class="post">
      <h1><span>TIIR Party Member Registration Form</span></h1>
      <div class="body">
        <img src="assets/registration-banner.jpg" style="width: 100%;">

      <div class="form-container">

        <?php if (!empty($success)): ?>
            <div style="background: #dcfce7; color: #166534; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #16a34a;">
                <i class="fas fa-check-circle" style="margin-right: 10px;"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc2626;">
                <strong>Please fix the following errors:</strong>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="classic-form" method="POST" enctype="multipart/form-data" id="member-form">

          <div class="form-row">
            <label>Magaca oo Afaran: <span>*</span></label>
            <input type="text" name="first-name" value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Magaca Hooyada: <span>*</span></label>
            <input type="text" name="mothers-name" value="<?php echo htmlspecialchars($form_data['mothers_name'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Jinsiga: <span>*</span></label>
            <select name="gender">
              <option value="">----- DOORO JINSIGA -----</option>
              <option value="Male" <?php echo ($form_data['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo ($form_data['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
          </div>

          <div class="form-row">
            <label>Taariikhda Dhalashada: <span>*</span></label>
            <input type="date" name="dob" value="<?php echo htmlspecialchars($form_data['dob'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Goobta Dhalashada: <span>*</span></label>
            <input type="text" name="pob" value="<?php echo htmlspecialchars($form_data['pob'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Aqoonsi / Passport No:</label>
            <input type="text" name="govtid" value="<?php echo htmlspecialchars($form_data['govt'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Heerka Waxbarashada: <span>*</span></label>
            <select name="education">
              <option value="">----- DOORO HEERKA WAXBARASHADA -----</option>
              <option value="Primary School" <?php echo ($form_data['education'] ?? '') === 'Primary School' ? 'selected' : ''; ?>>Primary School</option>
              <option value="Secondary School" <?php echo ($form_data['education'] ?? '') === 'Secondary School' ? 'selected' : ''; ?>>Secondary School</option>
              <option value="Diploma" <?php echo ($form_data['education'] ?? '') === 'Diploma' ? 'selected' : ''; ?>>Diploma</option>
              <option value="Degree" <?php echo ($form_data['education'] ?? '') === 'Degree' ? 'selected' : ''; ?>>Degree</option>
            </select>
          </div>

          <div class="form-row">
            <label>Shaqada: <span>*</span></label>
            <input type="text" name="occupation" value="<?php echo htmlspecialchars($form_data['occupation'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Waddanka: <span>*</span></label>
            <select name="country">
              <option value="">----- DOORO WADDANKA -----</option>
              <option value="Somalia" <?php echo ($form_data['country'] ?? '') === 'Somalia' ? 'selected' : ''; ?>>Somalia</option>
              <option value="Kenya" <?php echo ($form_data['country'] ?? '') === 'Kenya' ? 'selected' : ''; ?>>Kenya</option>
              <option value="Ethiopia" <?php echo ($form_data['country'] ?? '') === 'Ethiopia' ? 'selected' : ''; ?>>Ethiopia</option>
            </select>
          </div>

          <div class="form-row">
            <label>Gobolka: <span>*</span></label>
            <input type="text" name="state" value="<?php echo htmlspecialchars($form_data['state'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Degmada: <span>*</span></label>
            <input type="text" name="district" value="<?php echo htmlspecialchars($form_data['district'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Email-kaaga: <span>*</span></label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Telefoon-kaaga: <span>*</span></label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Sawir: <span>*</span></label>
            <input type="file" name="photo" accept="image/*">
          </div>

          <div class="form-row security-row">
            <label>Security Code: <span>*</span></label>
            <div class="security-wrapper">
              <div class="security-number">
               <?php echo $_SESSION['security_code']; ?>
              </div>
              <input type="text" name="security_code" placeholder="Enter the 5-digit code" value="<?php echo htmlspecialchars($form_data['security_code'] ?? ''); ?>">
            </div>
          </div>

          <div class="button-row">
            <button type="submit" class="dark-btn">
              Gudbi Macluumaadkeyga
            </button>

            <button type="reset" class="dark-btn">
              Masax Foom-ka
            </button>
          </div>

        </form>

      </div>

      </div>

      <div class="clear"></div>

    </div>

  </div>

  <?php include("inc/sidebar.php"); ?>

</div>

<?php include("inc/footer.php"); ?>