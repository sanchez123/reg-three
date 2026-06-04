document.addEventListener('DOMContentLoaded', function() {
    const countryFilter = document.getElementById('countryFilter');
    const educationFilter = document.getElementById('educationFilter');
    const entriesPerPage = document.getElementById('entriesPerPage');
    const tableBody = document.getElementById('tableBody');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const pageInfo = document.getElementById('pageInfo');

    let currentPage = 1;
    let allRows = [];
    let filteredRows = [];
    let rowsPerPage = 10;

    // Store all original rows with their data
    function initializeRows() {
        allRows = Array.from(tableBody.querySelectorAll('tr')).map(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            return {
                html: row.innerHTML,
                cellsData: cells.map(td => td.textContent.trim())
            };
        });
    }

    // Apply filters
    function applyFilters() {
        const countryValue = countryFilter.value;
        const educationValue = educationFilter.value;

        filteredRows = allRows.filter(row => {
            if (row.cellsData.length === 0) return false; // Skip rows with no cells

            // Columns: 0=Name, 1=Gender, 2=Place of Birth, 3=Education, 4=Occupation, 5=Country, 6=State, 7=District
            const educationCell = row.cellsData[3] || '';
            const countryCell = row.cellsData[5] || '';

            const educationMatch = educationValue === 'all' || educationCell === educationValue;
            const countryMatch = countryValue === 'all' || countryCell === countryValue;

            return countryMatch && educationMatch;
        });

        currentPage = 1;
        updateTableDisplay();
    }

    // Update table display
    function updateTableDisplay() {
        // Clear the table body
        tableBody.innerHTML = '';

        if (filteredRows.length === 0) {
            // Show empty message
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="8" style="text-align:center; padding: 20px;">No records found</td>';
            tableBody.appendChild(tr);
            pageInfo.textContent = 'Page 0 of 0';
            prevBtn.disabled = true;
            nextBtn.disabled = true;
            return;
        }

        // Calculate pagination
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        const paginated = filteredRows.slice(startIndex, endIndex);

        // Add rows to table
        paginated.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = row.html;
            tableBody.appendChild(tr);
        });

        // Update pagination info
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;

        // Disable/enable pagination buttons
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
    }

    // Event listeners for filters
    if (countryFilter) {
        countryFilter.addEventListener('change', applyFilters);
    }

    if (educationFilter) {
        educationFilter.addEventListener('change', applyFilters);
    }

    // Entries per page
    if (entriesPerPage) {
        entriesPerPage.addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            updateTableDisplay();
        });
    }

    // Pagination buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                updateTableDisplay();
                window.scrollTo(0, 0);
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));
            if (currentPage < totalPages) {
                currentPage++;
                updateTableDisplay();
                window.scrollTo(0, 0);
            }
        });
    }

    // Initialize
    initializeRows();
    applyFilters();
});

