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

</body>
</html>
