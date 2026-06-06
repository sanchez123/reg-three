<?php
require_once 'config.php';
require_once 'inc/constants.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$success = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Detect AJAX submissions
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

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

    // Normalize education value in case it was HTML-encoded by the browser or sanitizers
    if (!empty($form_data['education'])) {
        $form_data['education'] = html_entity_decode($form_data['education'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

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
        if (empty($form_data['govt'])) {
            $errors[] = 'Aqoonsi / Passport No is required';
        }
        if (empty($form_data['education'])) {
            $errors[] = 'Education Level is required';
        } elseif (!in_array($form_data['education'], EDUCATION_LEVELS)) {
            $errors[] = 'Invalid education level selected';
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
        // Duplicate email/phone/government id checks
        if (!empty($form_data['email'])) {
            $email_check = $conn->prepare("SELECT id FROM members WHERE email = ?");
            if ($email_check) {
                $email_check->bind_param("s", $form_data['email']);
                $email_check->execute();
                if ($email_check->get_result()->num_rows > 0) $errors[] = 'Email already registered';
                $email_check->close();
            }
        }

        if (!empty($form_data['phone'])) {
            $phone_check = $conn->prepare("SELECT id FROM members WHERE phone = ?");
            if ($phone_check) {
                $phone_check->bind_param("s", $form_data['phone']);
                $phone_check->execute();
                if ($phone_check->get_result()->num_rows > 0) $errors[] = 'Phone number already registered';
                $phone_check->close();
            }
        }

        if (!empty($form_data['govt'])) {
            $gov_check = $conn->prepare("SELECT id FROM members WHERE government_id = ?");
            if ($gov_check) {
                $gov_check->bind_param("s", $form_data['govt']);
                $gov_check->execute();
                if ($gov_check->get_result()->num_rows > 0) $errors[] = 'Government ID / Passport already registered';
                $gov_check->close();
            }
        }
        // Photo is required for member registration
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Photo is required';
        }

        if (empty($form_data['security_code'])) {
            $errors[] = 'Security code is required';
        } elseif (isset($_SESSION['security_code']) && (int)$_SESSION['security_code'] !== (int)$form_data['security_code']) {
            $errors[] = 'Security code is incorrect';
            // Rotate the security code on incorrect attempts to prevent replay (same as admin)
            $_SESSION['security_code'] = rand(10000, 99999);
        }

    // If no errors, process the form
    if (empty($errors)) {
        // Handle photo upload (we already required the file above)
        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo_path = upload_file($_FILES['photo'], 'uploads/members/');
            if (!$photo_path) {
                $errors[] = 'Photo upload failed';
            }
        } else {
            if (empty($photo_path)) {
                $errors[] = 'Photo is required';
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
                    $success = 'Member Registration Successful! Your information has been submitted.';
                    if (!$is_ajax) {
                        $_SESSION['flash_success'] = $success;
                    }
                    $form_data = [];
                    $_SESSION['security_code'] = rand(10000, 99999);
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => $success,
                            'security_code' => $_SESSION['security_code']
                        ]);
                        exit();
                    }
                } else {
                    $errors[] = 'Database error: ' . $conn->error;
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'errors' => ['Database error: ' . $conn->error]
                        ]);
                        exit();
                    }
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

// If this was an AJAX submission and there are validation errors, return them as JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($is_ajax) && $is_ajax && !empty($errors)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'errors' => array_values($errors),
        // include the current security code so clients can update the displayed code if it was rotated
        'security_code' => isset($_SESSION['security_code']) ? $_SESSION['security_code'] : null,
        // include submitted form values so the client can repopulate fields after validation errors
        'form_data' => [
            'first_name' => $form_data['first_name'] ?? '',
            'mothers_name' => $form_data['mothers_name'] ?? '',
            'gender' => $form_data['gender'] ?? '',
            'dob' => $form_data['dob'] ?? '',
            'pob' => $form_data['pob'] ?? '',
            'govt' => $form_data['govt'] ?? '',
            'education' => $form_data['education'] ?? '',
            'occupation' => $form_data['occupation'] ?? '',
            'country' => $form_data['country'] ?? '',
            'state' => $form_data['state'] ?? '',
            'district' => $form_data['district'] ?? '',
            'email' => $form_data['email'] ?? '',
            'phone' => $form_data['phone'] ?? ''
        ]
    ]);
    exit();
}

// For non-AJAX submissions with validation errors, set a session flash so the front-end footer shows a popup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (empty($is_ajax) || !$is_ajax) && !empty($errors)) {
    $html = '<strong>Please fix the following errors:</strong><ul>';
    foreach ($errors as $e) {
        $html .= '<li>' . htmlspecialchars($e) . '</li>';
    }
    $html .= '</ul>';
    $_SESSION['flash_error'] = $html;
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

        <!-- Success messages are shown via the shared admin SweetAlert2 popup (inc/footer.php)
             to ensure consistent animation and behavior. -->

        <!-- Top-of-page error summary removed. Validation errors are shown in a popup via SweetAlert2. -->

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
            <label>Aqoonsi / Passport No: <span>*</span></label>
            <input type="text" name="govtid" required value="<?php echo htmlspecialchars($form_data['govt'] ?? ''); ?>">
          </div>

          <div class="form-row">
            <label>Heerka Waxbarashada: <span>*</span></label>
            <select name="education">
              <option value="">----- DOORO HEERKA WAXBARASHADA -----</option>
              <?php foreach (EDUCATION_LEVELS as $level): ?>
                <option value="<?php echo htmlspecialchars($level); ?>" <?php echo ($form_data['education'] ?? '') === $level ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($level); ?>
                </option>
              <?php endforeach; ?>
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
              <?php foreach (AFRICAN_COUNTRIES as $country): ?>
                <option value="<?php echo htmlspecialchars($country); ?>" <?php echo ($form_data['country'] ?? '') === $country ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($country); ?>
                </option>
              <?php endforeach; ?>
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
            <input type="file" name="photo" accept="image/*" required>
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

<script>
// AJAX submit for member registration to show admin-style popup without full page reload
(function(){
    const form = document.getElementById('member-form');
    if (!form) return;

    form.addEventListener('submit', function(e){
        e.preventDefault();
        // Respect HTML5 validation (required, type, etc.) before performing AJAX submit
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const fd = new FormData(form);

        fetch(window.location.href, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function(response){
            const ct = (response.headers.get('content-type') || '').toLowerCase();
            if (ct.indexOf('application/json') !== -1) {
                return response.json().then(function(data){
                    if (submitBtn) submitBtn.disabled = false;
                    if (data.success) {
                        if (window.showSystemMessage) {
                            window.showSystemMessage('success', data.message, {timer:3000, confirm:false});
                        } else if (window.Swal) {
                            Swal.fire({icon:'success', title:'Success', html: data.message, timer:3000, showConfirmButton:false});
                        }
                        form.reset();
                        if (data.security_code) {
                            const el = document.querySelector('.security-number');
                            if (el) el.textContent = data.security_code;
                        }
                        const err = document.getElementById('form-errors');
                        if (err) err.style.display = 'none';
                    } else {
                        // Show validation errors in a popup (consistent with admin UI)
                        var listHtml = '<strong>Please fix the following errors:</strong><ul style="text-align:left;margin-left:18px;margin-top:10px;">' + (data.errors || []).map(function(it){ return '<li>'+it+'</li>'; }).join('') + '</ul>';
                        if (data.security_code) {
                            var secEl = document.querySelector('.security-number');
                            if (secEl) secEl.textContent = data.security_code;
                        }
                        if (window.showSystemMessage) {
                            window.showSystemMessage('error', listHtml, {confirm:true});
                        } else if (window.Swal) {
                            Swal.fire({icon:'error', title:'Validation failed', html: listHtml, showConfirmButton:true});
                        }

                        // Repopulate form fields from server-returned form_data (helps preserve fields like DOB)
                        if (data.form_data) {
                            try {
                                Object.keys(data.form_data).forEach(function(k){
                                    var v = data.form_data[k] || '';
                                    var el = form.querySelector('[name="'+k+'"]');
                                    if (el) {
                                        // visible or hidden input with the name (most fields)
                                        el.value = v;
                                        el.dispatchEvent(new Event('input', {bubbles:true}));
                                        el.dispatchEvent(new Event('change', {bubbles:true}));
                                        return;
                                    }

                                    var hidden = form.querySelector('input[type="hidden"][name="'+k+'"]');
                                    if (hidden) {
                                        hidden.value = v;
                                        hidden.dispatchEvent(new Event('input', {bubbles:true}));
                                        hidden.dispatchEvent(new Event('change', {bubbles:true}));

                                        var vis = hidden.previousElementSibling;
                                        if (vis) {
                                            if (k === 'dob' && /^\d{4}-\d{2}-\d{2}$/.test(v)) {
                                                var p = v.split('-');
                                                vis.value = p[2] + '/' + p[1] + '/' + p[0];
                                            } else {
                                                vis.value = v;
                                            }
                                            vis.dispatchEvent(new Event('input', {bubbles:true}));
                                            vis.dispatchEvent(new Event('change', {bubbles:true}));

                                            var instance = vis._flatpickr || hidden._flatpickr || vis._fp || null;
                                            if (!instance) {
                                                var candidates = form.querySelectorAll('input[data-fp-initialized="1"]');
                                                for (var i=0;i<candidates.length;i++) {
                                                    var c = candidates[i];
                                                    if (c.nextElementSibling === hidden || c === vis || c.previousElementSibling === vis) { instance = c._flatpickr || c._fp || null; break; }
                                                }
                                            }
                                            if (instance && k === 'dob' && v) {
                                                try { instance.setDate(v, true); } catch(e) { }
                                            }
                                        }
                                    }
                                });
                            } catch (e) {
                                console.error('Error repopulating form fields:', e);
                            }
                        }
                    }
                    }
                });
            }
            return response.text().then(function(text){
                if (submitBtn) submitBtn.disabled = false;
                if (response.ok) {
                    window.location.reload();
                } else {
                    if (window.showSystemMessage) {
                        window.showSystemMessage('error', text || 'Submission failed. Please try again.', {confirm:true});
                    }
                }
            });
        }).catch(function(err){
            if (submitBtn) submitBtn.disabled = false;
            if (window.showSystemMessage) {
                window.showSystemMessage('error', 'Submission failed. Please try again.', {confirm:true});
            }
            console.error('AJAX submit error:', err);
        });
    });
})();
</script>

<?php include("inc/footer.php"); ?>