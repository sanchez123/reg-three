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
    // Table rendering/pagination handled on pages that need it.
    // Original implementation removed to avoid duplicate/embedded datepicker logic.
    return;
  }
</script>

<!-- SweetAlert2 + shared showSystemMessage for front-end pages (matches admin) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<script>
  (function(){
    function showSystemMessage(type, message, options = {}){
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
    window.showSystemMessage = showSystemMessage;

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

<!-- flatpickr for front-end date inputs (match admin behavior) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
;(function(){
  if (typeof flatpickr === 'undefined') return;
  function initFlatpickrOnDateInputs(){
    var inputs = Array.from(document.querySelectorAll('input[type="date"]'));
    if (!inputs.length) return;
    var currentYear = new Date().getFullYear();
    inputs.forEach(function(inp){
      if (inp.dataset.fpInitialized) return;
      inp.dataset.fpInitialized = '1';
      var name = inp.getAttribute('name');
      var val = inp.value;
      try { inp.setAttribute('type','text'); } catch(e) {}
      var hidden = document.createElement('input'); hidden.type='hidden'; if (name) hidden.name = name; inp.removeAttribute('name'); inp.parentNode.insertBefore(hidden, inp.nextSibling);
      var opts = {
        altInput: true,
        altFormat: 'd/m/Y',
        dateFormat: 'Y-m-d',
        clickOpens: true,
        allowInput: false,
        onChange: function(selectedDates, dateStr, instance){
          if (selectedDates && selectedDates.length){
            var d = selectedDates[0];
            hidden.value = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
          } else {
            hidden.value = '';
          }
        },
        onReady: function(selectedDates, dateStr, instance){
          var vis = instance.altInput || inp;
          vis.addEventListener('focus', function(){ instance.open(); });
          vis.addEventListener('click', function(){ instance.open(); });
        }
      };
      var nm=(name||'').toLowerCase(); if (nm.indexOf('dob')!==-1||nm.indexOf('date_of_birth')!==-1) opts.maxDate=new Date();
      if (/^\d{4}-\d{2}-\d{2}$/.test(val)) opts.defaultDate=val; else if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)){ var p=val.split('/'); opts.defaultDate = p[2]+'-'+p[1]+'-'+p[0]; }
      flatpickr(inp, opts);
    });
  }

  document.addEventListener('DOMContentLoaded', initFlatpickrOnDateInputs);
  window.addEventListener('load', initFlatpickrOnDateInputs);
  if (window.MutationObserver){ var mo=new MutationObserver(function(){ initFlatpickrOnDateInputs(); }); mo.observe(document.body,{childList:true,subtree:true}); setTimeout(function(){ mo.disconnect(); }, 5000);} else { setTimeout(initFlatpickrOnDateInputs,500); setTimeout(initFlatpickrOnDateInputs,1500); }
})();
</script>

<script src="assets/js/admin-ui.js"></script>
</body>
</html>