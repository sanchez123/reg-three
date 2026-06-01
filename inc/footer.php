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
// Table pagination/filtering script - only run if the table controls exist on the page
(function(){
  const countryFilter = document.getElementById('countryFilter');
  if (!countryFilter) return; // nothing to do on pages without these controls

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

      const countryMatch = countryFilter.value === 'all' || country === countryFilter.value;
      const educationMatch = (educationFilter && educationFilter.value === 'all') || (educationFilter ? education === educationFilter.value : true);

      return countryMatch && educationMatch;
    });
  }

  function displayTable(){
    const filteredRows = getFilteredRows();
    const limit = parseInt(entriesPerPage.value || 10);
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / limit));

    if(currentPage > totalPages) currentPage = 1;

    rows.forEach(row => row.style.display = 'none');

    const start = (currentPage - 1) * limit;
    const end = start + limit;
    filteredRows.slice(start, end).forEach(row => row.style.display = '');

    if (pageInfo) pageInfo.innerText = `Page ${currentPage} of ${totalPages || 1}`;
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages || totalPages === 0;
  }

  if (countryFilter) countryFilter.addEventListener('change', () => { currentPage = 1; displayTable(); });
  if (educationFilter) educationFilter.addEventListener('change', () => { currentPage = 1; displayTable(); });
  if (entriesPerPage) entriesPerPage.addEventListener('change', () => { currentPage = 1; displayTable(); });
  if (prevBtn) prevBtn.addEventListener('click', () => { currentPage--; displayTable(); });
  if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; displayTable(); });

  displayTable();
})();
</script>


<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<script>
(function(){
  // Expose a global helper so other scripts and AJAX responses can show system messages
  function showSystemMessage(type, message, options = {}){
    // Log for debugging (helps when page reloads quickly)
    if(type === 'success') console.log('System message (success):', message);
    else console.error('System message (error):', message);

    const cfg = {
      icon: type === 'success' ? 'success' : 'error',
      title: type === 'success' ? 'Success' : 'Error',
      html: message,
      showCloseButton: true,
      showConfirmButton: !!(options.confirm !== false),
      timer: type === 'success' ? (options.timer || 3000) : undefined,
      timerProgressBar: type === 'success'
    };

    Swal.fire(cfg);
  }

  // Make callable from anywhere
  window.showSystemMessage = showSystemMessage;

  // Read flash messages from PHP session (safe JSON encoding)
  const flashSuccess = <?php echo isset($_SESSION['flash_success']) ? json_encode($_SESSION['flash_success']) : 'null'; ?>;
  const flashError = <?php echo isset($_SESSION['flash_error']) ? json_encode($_SESSION['flash_error']) : 'null'; ?>;

  if (flashSuccess){
    showSystemMessage('success', flashSuccess, {timer:3000, confirm:false});
    <?php unset($_SESSION['flash_success']); ?>
  }

  if (flashError){
    showSystemMessage('error', flashError, {confirm:true});
    <?php unset($_SESSION['flash_error']); ?>
  }

})();
</script>

<!-- Admin UI JS -->
<script src="assets/js/admin-ui.js"></script>
</body>
</html>