<?php
// All PHP logic BEFORE any HTML output to allow redirects
require_once '../inc/session-config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $candidate_id = intval($_GET['id']);

    $verify = $conn->prepare("SELECT photo_path FROM candidates WHERE id = ?");
    if ($verify) {
        $verify->bind_param("i", $candidate_id);
        $verify->execute();
        $result = $verify->get_result();

        if ($result->num_rows > 0) {
            $candidate = $result->fetch_assoc();

            // Delete photo if exists
            if ($candidate['photo_path'] && file_exists('../' . $candidate['photo_path'])) {
                unlink('../' . $candidate['photo_path']);
            }

            // Delete candidate
            $delete_query = $conn->prepare("DELETE FROM candidates WHERE id = ?");
            if ($delete_query) {
                $delete_query->bind_param("i", $candidate_id);
                if ($delete_query->execute()) {
                    log_audit($admin_id, 'DELETE', 'candidates', $candidate_id, ['action' => 'Candidate deleted']);
                    $_SESSION['flash_success'] = 'Candidate deleted successfully!';
                }
                $delete_query->close();
            }
        }
        $verify->close();
    }

    header("Location: candidates-list.php");
    exit();
}

// Pagination
$page     = intval($_GET['page']     ?? 1);
if ($page < 1) $page = 1;
$per_page = intval($_GET['per_page'] ?? 10);

// Filtering
$country_filter = isset($_GET['country']) && $_GET['country'] !== 'all' ? sanitize_input($_GET['country']) : '';
$status_filter  = isset($_GET['status'])  && $_GET['status']  !== 'all' ? sanitize_input($_GET['status'])  : '';
$search         = isset($_GET['search'])  ? sanitize_input($_GET['search']) : '';

// Build WHERE clause
$where_conditions = ["1=1"];
if (!empty($country_filter)) {
    $where_conditions[] = "country = '" . $conn->real_escape_string($country_filter) . "'";
}
if (!empty($status_filter)) {
    $where_conditions[] = "status = '" . $conn->real_escape_string($status_filter) . "'";
}
if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $where_conditions[] = "(first_name LIKE '%$search_term%' OR phone LIKE '%$search_term%' OR email LIKE '%$search_term%')";
}
$where_clause = implode(" AND ", $where_conditions);

// Total count
$count_result = $conn->query("SELECT COUNT(*) as total FROM candidates WHERE $where_clause");
$total        = $count_result->fetch_assoc()['total'];
$total_pages  = ceil($total / $per_page);
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// Fetch candidates
$result     = $conn->query("SELECT * FROM candidates WHERE $where_clause ORDER BY registration_date DESC LIMIT $offset, $per_page");
$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[] = $row;
}

// Unique countries for filter
$countries_result = $conn->query("SELECT DISTINCT country FROM candidates ORDER BY country");
$countries        = [];
while ($row = $countries_result->fetch_assoc()) {
    $countries[] = $row['country'];
}

// Only output HTML after all possible redirects
include 'header.php';
?>

<style>
    .list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
    .page-title { font-size: 28px; color: #083a9c; font-weight: 700; }
    .add-btn { background: linear-gradient(135deg, #083a9c 0%, #2563eb 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.3s ease; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 18px rgba(8,58,156,0.25); }
    .add-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(8,58,156,0.35); }
    .filters-container { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .filters-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    .filter-group { display: flex; flex-direction: column; }
    .filter-group label { font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px; }
    .filter-group input, .filter-group select { padding: 10px 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; transition: 0.3s ease; }
    .filter-group input:focus, .filter-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .filter-buttons { display: flex; gap: 10px; align-items: flex-end; margin-top: 15px; }
    .apply-filter { padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s ease; flex: 1; }
    .apply-filter:hover { background: #1d4ed8; }
    .reset-filter { padding: 10px 20px; background: #f3f4f6; color: #333; border: 2px solid #ddd; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .reset-filter:hover { background: #e5e7eb; }
    .table-container { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead { background: linear-gradient(to right, #2563eb, #1d4ed8); color: white; }
    thead th { padding: 16px; text-align: left; white-space: nowrap; font-size: 14px; font-weight: 600; }
    tbody tr { border-bottom: 1px solid #e2e8f0; transition: 0.3s ease; }
    tbody tr:hover { background: #f8fafc; }
    tbody td { padding: 16px; font-size: 14px; color: #334155; }
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .status-approved { background: #dcfce7; color: #166534; }
    .status-pending  { background: #fef3c7; color: #92400e; }
    .status-rejected { background: #fee2e2; color: #991b1b; }
    .action-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
    .action-btn { padding: 8px 12px; border: none; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.3s ease; display: inline-flex; align-items: center; gap: 5px; }
    .edit-btn   { background: #2563eb; color: white; }
    .edit-btn:hover   { background: #1d4ed8; }
    .delete-btn { background: #dc2626; color: white; }
    .delete-btn:hover { background: #b91c1c; }
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-state i { font-size: 64px; color: #cbd5e1; margin-bottom: 20px; display: block; }
    .empty-state h3 { color: #475569; margin-bottom: 10px; }
    .empty-state p  { color: #94a3b8; margin-bottom: 20px; }
    .pagination-container { padding: 20px; background: white; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap; }
    .pagination-info { color: #475569; font-size: 14px; }
    .pagination-links { display: flex; gap: 5px; }
    .page-link { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; text-decoration: none; color: #2563eb; font-weight: 600; transition: 0.3s ease; }
    .page-link:hover { background: #f0f4ff; }
    .page-link.active { background: #2563eb; color: white; border-color: #2563eb; }
    @media (max-width: 768px) {
        .list-header { flex-direction: column; align-items: stretch; }
        .add-btn { justify-content: center; }
        .filters-row { grid-template-columns: 1fr; }
        .filter-buttons { flex-direction: column; }
        table { min-width: 900px; }
        .action-buttons { flex-direction: column; }
    }
</style>

<div class="list-header">
    <h1 class="page-title"><i class="fas fa-file-powerpoint" style="margin-right: 10px;"></i>Manage Candidates</h1>
    <a href="add-candidate.php" class="add-btn">
        <i class="fas fa-plus"></i>Add New Candidate
    </a>
</div>

<!-- FILTERS -->
<form method="GET" class="filters-container">
    <div class="filters-row">
        <div class="filter-group">
            <label>Search (Name / Phone / Email)</label>
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="filter-group">
            <label>Filter by Country</label>
            <select name="country">
                <option value="all">All Countries</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo htmlspecialchars($country); ?>" <?php echo $country_filter === $country ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($country); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Filter by Status</label>
            <select name="status">
                <option value="all">All Status</option>
                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="pending"  <?php echo $status_filter === 'pending'  ? 'selected' : ''; ?>>Pending</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Entries Per Page</label>
            <select name="per_page">
                <option value="10" <?php echo $per_page === 10 ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo $per_page === 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo $per_page === 50 ? 'selected' : ''; ?>>50</option>
            </select>
        </div>
    </div>
    <div class="filter-buttons">
        <button type="submit" class="apply-filter"><i class="fas fa-search"></i> Apply Filters</button>
        <a href="candidates-list.php" class="reset-filter"><i class="fas fa-redo"></i> Reset</a>
    </div>
</form>

<!-- TABLE -->
<div class="table-container">
    <?php if (empty($candidates)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Candidates Found</h3>
            <p>There are no candidates matching your search criteria.</p>
            <a href="add-candidate.php" class="add-btn" style="display:inline-flex;">
                <i class="fas fa-plus"></i>Add Your First Candidate
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Country</th>
                        <th>Education</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = $offset + 1; foreach ($candidates as $candidate): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><strong><?php echo htmlspecialchars($candidate['first_name'] . ' ' . $candidate['mothers_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($candidate['phone']); ?></td>
                            <td><?php echo htmlspecialchars($candidate['email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($candidate['country']); ?></td>
                            <td><?php echo htmlspecialchars($candidate['education']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($candidate['status']); ?>">
                                    <?php echo ucfirst($candidate['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($candidate['registration_date'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit-candidate.php?id=<?php echo $candidate['id']; ?>" class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i>Edit
                                    </a>
                                    <a href="?action=delete&id=<?php echo $candidate['id']; ?>"
                                       class="action-btn delete-btn"
                                       onclick="return confirm('Are you sure you want to delete this candidate?');">
                                        <i class="fas fa-trash"></i>Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($total_pages > 1):
            $qs = (!empty($search)         ? '&search='  . urlencode($search)         : '')
                . (!empty($country_filter) ? '&country=' . urlencode($country_filter) : '')
                . (!empty($status_filter)  ? '&status='  . urlencode($status_filter)  : '');
        ?>
            <div class="pagination-container">
                <span class="pagination-info">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total); ?> of <?php echo $total; ?> candidates
                </span>
                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo $qs; ?>" class="page-link">« First</a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $qs; ?>" class="page-link">‹ Prev</a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $qs; ?>"
                           class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $qs; ?>" class="page-link">Next ›</a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo $qs; ?>" class="page-link">Last »</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
