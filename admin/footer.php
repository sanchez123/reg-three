            </div>
        </div>
    </div>

    <!-- SweetAlert2 for admin pages -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
    <script>
    (function(){
      function showSystemMessage(type, message, options = {}){
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

<!-- flatpickr for admin date inputs -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
;(function(){
  if (typeof flatpickr === 'undefined') return;
  function initFlatpickrOnDateInputsAdmin(){
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

  document.addEventListener('DOMContentLoaded', initFlatpickrOnDateInputsAdmin);
  window.addEventListener('load', initFlatpickrOnDateInputsAdmin);
  if (window.MutationObserver){ var mo=new MutationObserver(function(){ initFlatpickrOnDateInputsAdmin(); }); mo.observe(document.body,{childList:true,subtree:true}); setTimeout(function(){ mo.disconnect(); }, 5000);} else { setTimeout(initFlatpickrOnDateInputsAdmin,500); setTimeout(initFlatpickrOnDateInputsAdmin,1500); }
})();
</script>

</body>
</html>
