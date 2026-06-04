<?php include("inc/header.php") ?>
<?php
require_once 'config.php';

$candidates = [];
$stmt = $conn->prepare("SELECT first_name, gender, place_of_birth, education, occupation, country, state, district FROM candidates WHERE status = 'approved' ORDER BY registration_date DESC");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $candidates[] = $row;
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
      <h1><span>TIIR  Party Candidates</span></h1>
      <div class="body">
        <img src="assets/registration-banner.jpg" style="width: 100%;">
       <!-- View All Registered Candidates -->
       
</div>
</div>


<div class="post">
  <h1><span>Registered Candidates</span></h1>
  <div class="body">
       <div class="table-container">

  <!-- HEADER -->
  <div class="table-header">
    <p>Manage and filter registered candidates</p>
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
          <option value="Somalia">Somalia</option>
          <option value="Ethiopia">Ethiopia</option>
        </select>
      </div>

       <!-- EDUCATION FILTER -->
       <div class="filter-group">
         <label>Filter by Education</label>

         <select id="educationFilter">
           <option value="all">All Levels</option>
           <option value="Primary School">Primary School</option>
           <option value="Secondary School">Secondary School</option>
           <option value="Diploma">Diploma</option>
           <option value="Degree">Degree</option>
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
          <th>Name</th>
                    <th>Gender</th>
                    <th>Place of Birth</th>
                    <th>Education</th>
                    <th>Occupation</th>
                    <th>Country</th>
                    <th>State</th>
                    <th>District</th>
   
        </tr>
      </thead>

      <tbody id="tableBody">

        <!-- SAMPLE ROWS -->

        <?php if (count($candidates) > 0): ?>
            <?php $counter = 1; foreach ($candidates as $candidate): ?>
                <tr>
                    <td><?php echo htmlspecialchars($candidate['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['place_of_birth']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['education']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['occupation']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['country']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['state']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['district']); ?></td>
                </tr>
                <?php $counter++; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center; padding: 20px;">No approved candidates found</td>
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

<script src="assets/js/table-filters.js"></script>
<?php include("inc/footer.php"); ?>