<?php
session_start();
require_once 'config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize
    $first_name = sanitize_input($_POST['first-name'] ?? '');
    $mothers_name = sanitize_input($_POST['mothers-name'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');
    $dob = sanitize_input($_POST['dob'] ?? '');
    $pob = sanitize_input($_POST['pob'] ?? '');
    $govt = sanitize_input($_POST['govtid'] ?? '');
    $education = sanitize_input($_POST['education'] ?? '');
    $occupation = sanitize_input($_POST['occupation'] ?? '');
    $country = sanitize_input($_POST['country'] ?? '');
    $state = sanitize_input($_POST['state'] ?? '');
    $district = sanitize_input($_POST['district'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $security_code = sanitize_input($_POST['security_code'] ?? '');

    // validations
    if (empty($first_name) || empty($mothers_name) || empty($gender) || empty($dob) || empty($pob) || empty($education) || empty($occupation) || empty($country) || empty($state) || empty($district) || empty($email) || empty($phone)) {
        $errors[] = 'Fadlan buuxi dhammaan meelaha loo baahanyahay (All required fields must be filled).';
    }
    if (!empty($email) && !validate_email($email)) $errors[] = 'Invalid email';
    if (!validate_phone($phone)) $errors[] = 'Invalid phone';
    if (!validate_email($email)) $errors[] = 'Invalid email address';
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Photo is required';
    }
    // check security code if session exists
    if (isset($_SESSION['security_code']) && $_SESSION['security_code'] !== $security_code) {
        $errors[] = 'Invalid security code';
    }

    // handle photo
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploaded = upload_file($_FILES['photo'], 'uploads/members/');
        if ($uploaded) {
            $photo_path = $uploaded;
        } else {
            $errors[] = 'Photo upload failed';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO members (first_name, mothers_name, gender, date_of_birth, place_of_birth, government_id, education, occupation, country, state, district, email, phone, photo_path, status, security_code, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
        if ($stmt) {
            $added_by = null;
            $stmt->bind_param("sssssssssssssssi", $first_name, $mothers_name, $gender, $dob, $pob, $govt, $education, $occupation, $country, $state, $district, $email, $phone, $photo_path, $security_code, $added_by);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = 'Xogtaada waa la diiwaan geliyey - Member submitted successfully';
                header('Location: tiir-party-member-registration-form.php');
                exit();
            } else {
                $errors[] = 'Database error: ' . $conn->error;
            }
            $stmt->close();
        } else {
            $errors[] = 'Unable to prepare database statement';
        }
    }
}
?>
<?php include("inc/header.php") ?>
<?php include("inc/menu.php") ?>


<div id="content">
  <div id="main" class="inner_main">

    <div class="post">
      <h1><span>TIIR Party Member Registration Form</span></h1>
      <div class="body">
        <img src="assets/registration-banner.jpg" style="width: 100%;">
      
      <div class="form-container">

 <form class="classic-form" method="POST" enctype="multipart/form-data" id="member-form">

  <div class="form-row">
    <label>Magaca oo Afaran: <span>*</span></label>
    <input type="text" name="first-name">
  </div>

  <div class="form-row">
    <label>Magaca Hooyada: <span>*</span></label>
    <input type="text" name="mothers-name">
  </div>

  <div class="form-row">
    <label>Jinsiga: <span>*</span></label>
    <select name="gender">
      <option>----- DOORO JINSIGA -----</option>
      <option>Male</option>
      <option>Female</option>
    </select>
  </div>

  <div class="form-row">
    <label>Taariikhda Dhalashada: <span>*</span></label>
    <input type="date" name="dob">
  </div>

  <div class="form-row">
    <label>Goobta Dhalashada: <span>*</span></label>
    <input type="text" name="pob">
  </div>

  <div class="form-row">
    <label>Aqoonsi / Passport No:</label>
    <input type="text" name="govtid">
  </div>

  <div class="form-row">
    <label>Heerka Waxbarashada: <span>*</span></label>
    <select name="education">
      <option>----- DOORO HEERKA WAXBARASHADA -----</option>
      <option>Primary School</option>
      <option>Secondary School</option>
      <option>Diploma</option>
      <option>Degree</option>
    </select>
  </div>

  <div class="form-row">
    <label>Shaqada: <span>*</span></label>
    <input type="text" name="occupation">
  </div>

  <div class="form-row">
    <label>Waddanka: <span>*</span></label>
    <select name="country">
      <option>----- DOORO WADDANKA -----</option>
      <option>Somalia</option>
      <option>Kenya</option>
      <option>Ethiopia</option>
    </select>
  </div>

  <div class="form-row">
    <label>Gobolka: <span>*</span></label>
    <input type="text" name="state">
  </div>

  <div class="form-row">
    <label>Degmada: <span>*</span></label>
    <input type="text" name="district">
  </div>

  <div class="form-row">
    <label>Email-kaaga: <span>*</span></label>
    <input type="email" name="email">
  </div>

  <div class="form-row">
    <label>Telefoon-kaaga: <span>*</span></label>
    <input type="tel" name="phone">
  </div>

  <div class="form-row">
    <label>Sawir: <span>*</span></label>
    <input type="file" name="photo">
  </div>

  <div class="form-row security-row">
    <label>Security Code: <span>*</span></label>

    <div class="security-wrapper">

      <div class="security-number">
       <?php echo $_SESSION['security_code']; ?>
      </div>

      <input type="text" name="security_code">

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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('member-form').addEventListener('submit', function(e){
    var phone = document.querySelector('input[name="phone"]').value.trim();
    var first = document.querySelector('input[name="first-name"]').value.trim();
    var mothers = document.querySelector('input[name="mothers-name"]').value.trim();
    var email = document.querySelector('input[name="email"]').value.trim();
    var photo = document.querySelector('input[name="photo"]').files.length;

    if (!first || !mothers || !email || !phone || !photo) {
        e.preventDefault();
        Swal.fire({icon:'warning', title:'Fadlan buuxi meelaha loo baahan yahay', text:'Please fill all required fields including email and photo'});
        return false;
    }
});
</script>
<?php include("inc/footer.php"); ?>