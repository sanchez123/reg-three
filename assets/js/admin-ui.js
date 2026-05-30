document.addEventListener('DOMContentLoaded', function() {
    // Dropdown toggles for sidebar
    document.querySelectorAll('.menu-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            var parent = this.parentElement;
            parent.classList.toggle('open');
        });
    });

    // Highlight active link
    var current = location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar-menu a').forEach(function(a) {
        var href = a.getAttribute('href') || '';
        if (href === current || (href && current.startsWith(href.replace('.php','')))) {
            a.classList.add('active');
            var p = a.closest('.has-dropdown');
            if (p) p.classList.add('open');
        }
    });

    // Image preview handler
    document.querySelectorAll('.open-photo').forEach(function(img) {
        img.addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.getAttribute('src') || this.getAttribute('href');
            if (window.Swal) {
                Swal.fire({
                    imageUrl: url,
                    imageAlt: 'Photo',
                    showCloseButton: true,
                    showCancelButton: true,
                    cancelButtonText: 'Close',
                    confirmButtonText: 'Download',
                    confirmButtonColor: '#2563eb'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var link = document.createElement('a');
                        link.href = url;
                        link.download = url.split('/').pop();
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    }
                });
            } else {
                window.open(url, '_blank');
            }
        });
    });
});