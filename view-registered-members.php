<?php include("inc/header.php") ?>
<?php
require_once 'config.php';

$members = [];
$stmt = $conn->prepare("SELECT first_name, mothers_name, email, phone, photo_path, country, education FROM members WHERE status = 'approved' ORDER BY registration_date DESC");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $members[] = $row;
}
?>
<?php include("inc/menu.php") ?>

<div id="content">
  <div id="main" class="view_main">

    

     <div class="post">
      <style>
        .post h1,.post .body{
          width: unset;
        }
      </style>
      <h1><span>TIIR  Party Members</span></h1>
      <div class="body">
        <img src="assets/registration-banner.jpg" style="width: 100%;">
       <!-- View All Registered Members -->
       
</div>
</div>


<div class="post">
  <h1><span>Registered Members</span></h1>
  <div class="body">
       <div class="table-container">

  <!-- HEADER -->
  <div class="table-header">
    <p>Manage and filter registered members</p>
  </div>

  <!-- FILTERS -->
  <div class="top-controls">

    <div class="filters">

      <!-- COUNTRY FILTER -->
      <div class="filter-group">
        <label>Filter by Country</label>

        <select id="countryFilter">
          <option value="all">All Countries</option>
          <option value="Kenya">Kenya</option>
          <option value="Uganda">Uganda</option>
          <option value="Tanzania">Tanzania</option>
        </select>
      </div>

      <!-- EDUCATION FILTER -->
      <div class="filter-group">
        <label>Filter by Education</label>

        <select id="educationFilter">
          <option value="all">All Levels</option>
          <option value="Bachelor’s Degree">Bachelor’s Degree</option>
          <option value="Master’s Degree">Master’s Degree</option>
          <option value="Diploma">Diploma</option>
        </select>
      </div>

    </div>

    <!-- SHOW ENTRIES -->
    <div class="filter-group">
      <label>Show Entries</label>

      <select id="entriesPerPage">
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="30">30</option>
      </select>
    </div>

  </div>

  <!-- TABLE -->
  <div class="table-responsive">

   <table id="membersTable">

      <thead>
        <tr>
          <th>#</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Gender</th>
          <th>Place of Birth</th>
          <th>Date of Birth</th>
          <th>Government ID</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Education</th>
          <th>Occupation</th>
          <th>Country</th>
          <th>State</th>
          <th>District</th>
   
        </tr>
      </thead>

      <tbody id="tableBody">

        <!-- SAMPLE ROWS -->
<?php if (count($members) > 0): ?>
    <?php $counter = 1; foreach ($members as $member): ?>
        <tr>
            <td><?php echo htmlspecialchars($counter); ?></td>
            <td><?php echo htmlspecialchars($member['first_name']); ?></td>
            <td><?php echo htmlspecialchars($member['mothers_name']); ?></td>
            <td><?php echo htmlspecialchars($member['email']); ?></td>
            <td><?php echo htmlspecialchars($member['phone']); ?></td>
            <td><?php echo htmlspecialchars($member['country']); ?></td>
            <td><?php echo htmlspecialchars($member['education']); ?></td>
        </tr>
        <?php $counter++; ?>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7" style="text-align:center; padding: 20px;">No approved members found</td>
    </tr>
<?php endif; ?>


      </tbody>

    </table>

  </div>

  <!-- PAGINATION -->
  <div class="pagination">

    <button id="prevBtn">Previous</button>

    <span class="page-info" id="pageInfo">
      Page 1
    </span>

    <button id="nextBtn">Next</button>

  </div>

</div>

      </div>


      <div class="clear"></div>


    </div>      

  </div>

  <!-- Include Side Bar Below -->

</div>


<?php include("inc/footer.php"); ?>