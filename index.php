<?php include("inc/header.php") ?>
<?php include("inc/menu.php") ?>

<div id="content">
  <div id="main" class="inner_main">

    

     <div class="post">
      <h1><span>TIIR  Party Registration</span></h1>
      <div class="body">
        <img src="assets/registration-banner.jpg" style="width: 100%;">
       <div class="registration-wrapper">

    <!-- Member Registration -->
    <div class="registration-card">
        <h2>Member Registration</h2>
        <p>
            TIIR Party Shareholders Information Registration Form
        </p>

        <a href="tiir-party-member-registration-form.php" class="register-btn">
            Register Now
        </a>
    </div>

    <!-- Candidate Registration -->
<div class="registration-card">
    <h2>Candidate Registration</h2>
    <p>
        Only used to register candidates when there is election.
    </p>

    <a href="tiir-party-candidate-registration-form.php" class="register-btn">
        Register Now
    </a>
</div>

<!-- View All Registered Members -->
<div class="registration-card">
    <h4>View All Registered Members</h4>
    <p>
        Access and view the complete list of all registered TIIR Party members.
    </p>

    <a href="view-registered-members.php" class="view-btn">
        View Members
    </a>
</div>

<!-- View All Registered Candidates -->
<div class="registration-card">
    <h4>View All Registered Candidates</h4>
    <p>
        Access and view the complete list of all registered election candidates.
    </p>

    <a href="view-registered-candidates.php" class="view-btn">
        View Candidates
    </a>
</div>

<!-- Admin Access -->
<div class="registration-card">
    <h4>Admin Dashboard</h4>
    <p>
        Admin access for managing members and candidates.
    </p>

    <a href="login.php" class="view-btn" style="background: linear-gradient(135deg, #d73322 0%, #b51808 100%);">
        Admin Login
    </a>
</div>
</div>

      </div>


      <div class="clear"></div>


    </div>      

  </div>

  <?php include("inc/sidebar.php"); ?>

</div>


<?php include("inc/footer.php"); ?>