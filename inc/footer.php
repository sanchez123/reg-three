<style>

.fa-instagram{
    background: #fd5949 !important;
}
</style>

    <div class="clear"></div>
    <div id="social-media">
      <a href="https://www.facebook.com/profile.php?id=100043574748918" target="_blank"> <i class="fa fa-facebook"> </i> </a> 
      <a href="https://twitter.com/XisbigaTiir" target="_blank"> <i class="fa fa-twitter"> </i> </a> 
      <a href="https://www.youtube.com/channel/UC6LhjIgWjvcI6d1GMytGAgw" target="_blank"> <i class="fa fa-youtube"> </i> </a> 
      <a href="https://www.instagram.com/xisbigatiir" target="_blank"> <i class="fa fa-instagram"> </i> </a> 
      <a href="" target="_blank"> <i class="fa fa-rss"> </i> </a> 
    </div>
    <div class="clear"></div>
    <div id="footer">
      <div id="copyright">Copyright &copy; 2017 - 2026 Xisbiga Midnimada iyo Cadaaladda All Rights Reserved. </div>
      <div id="credits">Site Designed &amp; Developed by <a href="http://ileys.so" target="_blank" class="tooltip" title="<b>Ileys Inc.</b> - Horn of Africa's Largest Web Solution Provider">Ileys Inc</a></div>
      <div class="clear"></div>
    </div>
    
  </div>
  <script> $('audio,video').mediaelementplayer({audioWidth: 435,audioHeight: 30,}); 
</script> 


<script>

  const countryFilter = document.getElementById('countryFilter');
  const educationFilter = document.getElementById('educationFilter');
  const entriesPerPage = document.getElementById('entriesPerPage');

  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const pageInfo = document.getElementById('pageInfo');

  const rows = Array.from(document.querySelectorAll('#tableBody tr'));

  let currentPage = 1;

  function getFilteredRows(){

    return rows.filter(row => {

      const country = row.dataset.country;
      const education = row.dataset.education;

      const countryMatch =
        countryFilter.value === 'all' ||
        country === countryFilter.value;

      const educationMatch =
        educationFilter.value === 'all' ||
        education === educationFilter.value;

      return countryMatch && educationMatch;

    });

  }

  function displayTable(){

    const filteredRows = getFilteredRows();

    const limit = parseInt(entriesPerPage.value);

    const totalPages = Math.ceil(filteredRows.length / limit);

    if(currentPage > totalPages){
      currentPage = 1;
    }

    rows.forEach(row => {
      row.style.display = 'none';
    });

    const start = (currentPage - 1) * limit;
    const end = start + limit;

    filteredRows.slice(start, end).forEach(row => {
      row.style.display = '';
    });

    pageInfo.innerText =
      `Page ${currentPage} of ${totalPages || 1}`;

    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled =
      currentPage === totalPages ||
      totalPages === 0;

  }

  countryFilter.addEventListener('change', () => {
    currentPage = 1;
    displayTable();
  });

  educationFilter.addEventListener('change', () => {
    currentPage = 1;
    displayTable();
  });

  entriesPerPage.addEventListener('change', () => {
    currentPage = 1;
    displayTable();
  });

  prevBtn.addEventListener('click', () => {
    currentPage--;
    displayTable();
  });

  nextBtn.addEventListener('click', () => {
    currentPage++;
    displayTable();
  });

  displayTable();

</script>


<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
Swal.fire({icon: 'success', title: 'Success', text: '<?php echo addslashes($_SESSION['flash_success']); ?>', timer: 3000});
<?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
Swal.fire({icon: 'error', title: 'Error', text: '<?php echo addslashes($_SESSION['flash_error']); ?>'});
<?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
</script>

<!-- Admin UI JS -->
<script src="assets/js/admin-ui.js"></script>
</body>
</html>