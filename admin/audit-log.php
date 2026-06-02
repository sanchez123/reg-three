<?php
// Handle clear logs request — BEFORE any HTML output
if (isset($_GET['action']) && $_GET['action'] === 'clear_logs') {
    require_once '../inc/session-config.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once '../config.php';

    // Verify admin is logged in
    if (isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];

        // Clear all audit logs
        $clear_query = $conn->prepare("DELETE FROM audit_log");
        if ($clear_query) {
            if ($clear_query->execute()) {
                // Log the clearing action itself
                log_audit($admin_id, 'DELETE', 'audit_log', 0, ['action' => 'All audit logs cleared']);
                $_SESSION['flash_success'] = 'Audit Logs cleared Successfully';
            }
            $clear_query->close();
        }
    }

    // Redirect to avoid re-submission
    header("Location: audit-log.php");
    exit();
}

include 'header.php'; ?>

<?php
// Pagination
$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$per_page = 20;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM audit_log";
$count_result = $conn->query($count_query);
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$offset = ($page - 1) * $per_page;

// Get logs with admin info
$query = "SELECT al.*, au.full_name FROM audit_log al
          LEFT JOIN admin_users au ON al.admin_id = au.id
          ORDER BY al.created_at DESC
          LIMIT $offset, $per_page";
$result = $conn->query($query);
$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}
?>

                <style>
                    .page-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 30px;
                        flex-wrap: wrap;
                        gap: 15px;
                    }

                    .page-title {
                        font-size: 28px;
                        color: #083a9c;
                        font-weight: 700;
                        margin: 0;
                    }

                    .clear-logs-btn {
                        background: linear-gradient(135deg, #d73322 0%, #b91c1c 100%);
                        color: white;
                        padding: 10px 20px;
                        border-radius: 8px;
                        border: none;
                        cursor: pointer;
                        font-weight: 600;
                        transition: 0.3s ease;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        box-shadow: 0 6px 18px rgba(215, 51, 34, 0.25);
                        font-size: 14px;
                    }

                    .clear-logs-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 8px 22px rgba(215, 51, 34, 0.35);
                    }

                    .clear-logs-btn:disabled {
                        opacity: 0.5;
                        cursor: not-allowed;
                    }

                    .table-container {
                        background: white;
                        border-radius: 12px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                        overflow: hidden;
                    }

                    .table-responsive {
                        overflow-x: auto;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }

                    thead {
                        background: linear-gradient(to right, #2563eb, #1d4ed8);
                        color: white;
                    }

                    thead th {
                        padding: 16px;
                        text-align: left;
                        white-space: nowrap;
                        font-size: 14px;
                        font-weight: 600;
                    }

                    tbody tr {
                        border-bottom: 1px solid #e2e8f0;
                        transition: 0.3s ease;
                    }

                    tbody tr:hover {
                        background: #f8fafc;
                    }

                    tbody td {
                        padding: 16px;
                        font-size: 14px;
                        color: #334155;
                    }

                    .action-badge {
                        display: inline-block;
                        padding: 6px 12px;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: 600;
                    }

                    .action-create {
                        background: #dcfce7;
                        color: #166534;
                    }

                    .action-update {
                        background: #dbeafe;
                        color: #1e40af;
                    }

                    .action-delete {
                        background: #fee2e2;
                        color: #991b1b;
                    }

                    .empty-state {
                        text-align: center;
                        padding: 60px 20px;
                    }

                    .empty-state i {
                        font-size: 64px;
                        color: #cbd5e1;
                        margin-bottom: 20px;
                    }

                    .empty-state h3 {
                        color: #475569;
                        margin-bottom: 10px;
                    }

                    .empty-state p {
                        color: #94a3b8;
                    }

                    .pagination-container {
                        padding: 20px;
                        background: white;
                        border-top: 1px solid #e2e8f0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        gap: 15px;
                        flex-wrap: wrap;
                    }

                    .pagination-info {
                        color: #475569;
                        font-size: 14px;
                    }

                    .pagination-links {
                        display: flex;
                        gap: 5px;
                    }

                    .page-link {
                        padding: 8px 12px;
                        border: 1px solid #ddd;
                        border-radius: 6px;
                        text-decoration: none;
                        color: #2563eb;
                        font-weight: 600;
                        transition: 0.3s ease;
                    }

                    .page-link:hover {
                        background: #f0f4ff;
                    }

                    .page-link.active {
                        background: #2563eb;
                        color: white;
                        border-color: #2563eb;
                    }
                </style>

                <div class="page-header">
                    <h1 class="page-title"><i class="fas fa-history" style="margin-right: 10px;"></i>Audit Log</h1>
                    <?php if (!empty($logs)): ?>
                        <button class="clear-logs-btn" onclick="confirmClearLogs()">
                            <i class="fas fa-trash"></i>Clear Logs
                        </button>
                    <?php endif; ?>
                </div>

                <!-- TABLE -->
                <div class="table-container">
                    <?php if (empty($logs)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Audit Logs</h3>
                            <p>There are no audit logs yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Admin</th>
                                        <th>Action</th>
                                        <th>Table</th>
                                        <th>Record ID</th>
                                        <th>Details</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($log['full_name'] ?? 'Unknown'); ?></td>
                                            <td>
                                                <span class="action-badge action-<?php echo strtolower($log['action']); ?>">
                                                    <?php echo htmlspecialchars($log['action']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['table_name'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($log['record_id'] ?? '-'); ?></td>
                                            <td>
                                                <small><?php
                                                    $details = json_decode($log['details'], true);
                                                    if (is_array($details)) {
                                                        echo htmlspecialchars(json_encode($details));
                                                    } else {
                                                        echo htmlspecialchars($log['details'] ?? '-');
                                                    }
                                                ?></small>
                                            </td>
                                            <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINATION -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-container">
                                <span class="pagination-info">
                                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total); ?> of <?php echo $total; ?> logs
                                </span>

                                <div class="pagination-links">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=1" class="page-link">« First</a>
                                        <a href="?page=<?php echo $page - 1; ?>" class="page-link">‹ Prev</a>
                                    <?php endif; ?>

                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($total_pages, $page + 2);
                                    for ($i = $start; $i <= $end; $i++):
                                    ?>
                                        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>" class="page-link">Next ›</a>
                                        <a href="?page=<?php echo $total_pages; ?>" class="page-link">Last »</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <script>
                function confirmClearLogs() {
                    Swal.fire({
                        title: 'Clear Audit Logs?',
                        html: 'This will permanently delete all audit logs. This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d73322',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, Clear Logs',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '?action=clear_logs';
                        }
                    });
                }
                </script>

<?php include 'footer.php'; ?>

