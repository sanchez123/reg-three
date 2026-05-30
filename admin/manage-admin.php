<?php
ob_start();  // START OUTPUT BUFFERING - Add this FIRST!

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

require_role('super');

// Delete/deactivate handled via POST - DO THIS BEFORE INCLUDING HEADER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['admin_id'])) {
    $aid = intval($_POST['admin_id']);

    // Prevent deleting self
    if ($aid === intval($_SESSION['admin_id'])) {
        $_SESSION['flash_error'] = 'You cannot delete your own account';
        ob_end_clean();  // Clear buffer before redirect
        header('Location: manage-admin.php');
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $aid);
        if ($stmt->execute()) {
            log_audit($_SESSION['admin_id'], 'DELETE', 'admin_users', $aid, ['action'=>'Admin deleted']);
            $_SESSION['flash_success'] = 'Admin deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Error deleting admin';
        }
        $stmt->close();
    }
    ob_end_clean();  // Clear buffer before redirect
    header('Location: manage-admin.php');
    exit();
}

$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$per_page = intval($_GET['per_page'] ?? 10);

// Fetch admins
$offset = ($page - 1) * $per_page;
$stmt = $conn->prepare("SELECT id, username, email, full_name, role, phone, status, created_at FROM admin_users ORDER BY created_at DESC LIMIT ?, ?");
if ($stmt) {
    $stmt->bind_param("ii", $offset, $per_page);
    $stmt->execute();
    $res = $stmt->get_result();
    $admins = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $admins = [];
}

// Count total admins
$count_res = $conn->query("SELECT COUNT(*) as cnt FROM admin_users");
$total = $count_res ? $count_res->fetch_assoc()['cnt'] : 0;
$total_pages = ceil($total / $per_page);
?>
<?php include 'header.php'; ?>

<style>
.page-title { font-size: 28px; color: #083a9c; font-weight: 700; margin-bottom: 10px; }
.list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 15px; flex-wrap: wrap; }
.add-btn { background: linear-gradient(135deg,#083a9c,#2563eb); color: #fff; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:8px; }
.table-container { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.action-btn { padding:8px 12px; border-radius:6px; text-decoration:none; font-size:13px; font-weight:600; cursor:pointer; border:none; }
.edit-btn { background:#2563eb; color:#fff; }
.delete-btn { background:#dc2626; color:#fff; }
.empty-state { text-align:center; padding:60px 20px; }
.empty-state i { font-size:64px; color:#cbd5e1; margin-bottom:20px; }
</style>

<div class="list-header">
    <h1 class="page-title"><i class="fas fa-users-cog" style="margin-right:10px;"></i>Manage Admins</h1>
    <a href="add-admin.php" class="add-btn"><i class="fas fa-user-plus"></i> Add Admin</a>
</div>

<div class="table-container">
    <?php if (empty($admins)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Admin Users Found</h3>
        </div>
    <?php else: ?>
        <div class="table-responsive" style="overflow-x:auto;">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background:linear-gradient(to right,#2563eb,#1d4ed8); color:#fff;">
                        <th style="padding:12px; text-align:left;">#</th>
                        <th style="padding:12px; text-align:left;">Full Name</th>
                        <th style="padding:12px; text-align:left;">Username</th>
                        <th style="padding:12px; text-align:left;">Email</th>
                        <th style="padding:12px; text-align:left;">Phone</th>
                        <th style="padding:12px; text-align:left;">Role</th>
                        <th style="padding:12px; text-align:left;">Status</th>
                        <th style="padding:12px; text-align:left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $c = $offset + 1; foreach ($admins as $a): ?>
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:12px;"><?php echo $c++; ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($a['full_name']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($a['username']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($a['email']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($a['phone'] ?? '-'); ?></td>
                            <td style="padding:12px;"><span style="background:#e0e7ff; color:#1e40af; padding:4px 8px; border-radius:4px; font-size:12px;"><?php echo htmlspecialchars($a['role']); ?></span></td>
                            <td style="padding:12px;"><span style="background:#dcfce7; color:#166534; padding:4px 8px; border-radius:4px; font-size:12px;"><?php echo htmlspecialchars($a['status']); ?></span></td>
                            <td style="padding:12px;">
                                <a href="edit-admin.php?id=<?php echo $a['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <?php if (intval($a['id']) !== intval($_SESSION['admin_id'])): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this admin?');">
                                    <input type="hidden" name="admin_id" value="<?php echo $a['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="action-btn delete-btn">Delete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div style="padding-top:12px; text-align:center;">
                <?php for ($i=1;$i<=$total_pages;$i++): ?>
                    <a href="?page=<?php echo $i; ?>" style="padding:6px 10px; border:1px solid #ddd; margin-right:6px; text-decoration:none; border-radius:4px; <?php echo $i===$page ? 'background:#2563eb; color:#fff;' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>