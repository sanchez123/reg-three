document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const countrySelect = document.querySelector('select[name="country"]');
    const statusSelect = document.querySelector('select[name="status"]');
    const form = document.querySelector('.filters-container');

    // Real-time filtering
    function applyFilters() {
        if (form) {
            form.submit();
        }
    }

    // Add event listeners for real-time filtering
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(this.filterTimeout);
            this.filterTimeout = setTimeout(applyFilters, 500);
        });
    }

    if (countrySelect) {
        countrySelect.addEventListener('change', applyFilters);
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', applyFilters);
    }

    // Handle reset button
    const resetBtn = document.querySelector('.reset-filter');
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Clear all filters
            if (searchInput) searchInput.value = '';
            if (countrySelect) countrySelect.value = 'all';
            if (statusSelect) statusSelect.value = 'all';

            // Redirect to clean page
            window.location.href = window.location.pathname;
        });
    }
});

