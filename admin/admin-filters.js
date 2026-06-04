document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.filters-container');
    if (!form) return;

    const searchInput = form.querySelector('input[name="search"]');
    const countrySelect = form.querySelector('select[name="country"]');
    const educationSelect = form.querySelector('select[name="education"]');
    const statusSelect = form.querySelector('select[name="status"]');
    const resetBtn = document.querySelector('.reset-filter');
    let pendingFetch = null;

    function setLoading(active) {
        const table = document.querySelector('.table-container');
        if (!table) return;
        table.style.opacity = active ? '0.6' : '';
    }

    async function fetchAndUpdate() {
        if (!form) return;
        const params = new URLSearchParams(new FormData(form));
        const url = window.location.pathname + '?' + params.toString();

        try { history.replaceState(null, '', url); } catch (e) {}

        setLoading(true);
        try {
            if (pendingFetch && typeof pendingFetch.cancel === 'function') pendingFetch.cancel();
            const controller = new AbortController();
            pendingFetch = controller;

            const resp = await fetch(url, { signal: controller.signal, credentials: 'same-origin' });
            if (!resp.ok) { setLoading(false); return; }
            const text = await resp.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newTable = doc.querySelector('.table-container');
            const curTable = document.querySelector('.table-container');
            if (newTable && curTable) {
                curTable.innerHTML = newTable.innerHTML;
                // Smooth scroll the updated table into view
                try {
                    curTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (err) {
                    // fallback
                    window.scrollTo({ top: curTable.offsetTop, behavior: 'smooth' });
                }
            }
            setLoading(false);
        } catch (err) {
            if (err.name !== 'AbortError') console.error('Filter fetch error', err);
            setLoading(false);
        } finally { pendingFetch = null; }
    }

    // Apply Filters should be explicit: submit triggers fetch
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchAndUpdate();
    });

    // Prevent Enter from submitting the form (force using Apply Filters button)
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });
    }

    // Do NOT auto-apply on select changes or input; user must click Apply Filters
    // Intercept reset link so clearing filters is also AJAX (no full reload)
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            if (countrySelect) countrySelect.value = 'all';
            if (educationSelect) educationSelect.value = 'all';
            if (statusSelect) statusSelect.value = 'all';

            // Remove query params from URL
            try {
                history.replaceState(null, '', window.location.pathname);
            } catch (err) {}

            // Fetch and update via AJAX
            fetchAndUpdate();
        });
    }

    // AJAX pagination: intercept clicks on pagination links inside the table container
    document.addEventListener('click', function(e) {
        const anchor = e.target.closest('a');
        if (!anchor) return;
        const tableContainer = document.querySelector('.table-container');
        if (!tableContainer) return;
        if (!tableContainer.contains(anchor)) return; // only handle links inside table area

        // Determine if link is a pagination/navigation link (has page= in href or links to same pathname)
        try {
            const href = anchor.getAttribute('href');
            if (!href || href.startsWith('javascript:') || href === '#') return;
            const url = new URL(href, window.location.origin);
            const isSamePath = url.pathname === window.location.pathname;
            const hasPageParam = url.searchParams.has('page');

            if (isSamePath && (hasPageParam || url.search)) {
                e.preventDefault();
                // Merge current form values with parameters from the clicked link
                const formParams = new URLSearchParams(new FormData(form));
                // Override with link params
                for (const [k, v] of url.searchParams.entries()) {
                    formParams.set(k, v);
                }
                // Apply form values back to form fields so state matches URL
                for (const [k, v] of formParams.entries()) {
                    const field = form.querySelector('[name="' + k + '"]');
                    if (field) {
                        field.value = v;
                    }
                }

                // Fetch using the merged params
                try { history.replaceState(null, '', window.location.pathname + '?' + formParams.toString()); } catch (err) {}
                setLoading(true);
                fetch(window.location.pathname + '?' + formParams.toString(), { credentials: 'same-origin' })
                    .then(r => r.text())
                        .then(text => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(text, 'text/html');
                                const newTable = doc.querySelector('.table-container');
                                const curTable = document.querySelector('.table-container');
                                if (newTable && curTable) {
                                    curTable.innerHTML = newTable.innerHTML;
                                    try {
                                        curTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    } catch (err) {
                                        window.scrollTo({ top: curTable.offsetTop, behavior: 'smooth' });
                                    }
                                }
                            })
                    .catch(err => console.error('Pagination fetch error', err))
                    .finally(() => setLoading(false));
            }
        } catch (err) {
            // ignore malformed URLs
        }
    });
});

