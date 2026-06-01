<?php include 'header.php'; ?>

<?php
// Fetch updated stats using prepared statements
$stats = [
    'total_members' => 0,
    'members_pending' => 0,
    'members_approved' => 0,
    'total_candidates' => 0,
    'candidates_pending' => 0,
    'candidates_approved' => 0,
    'total_admins' => 0
];

$error_msg = '';

// Members total
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM members");
if (!$stmt) {
    error_log("Query error: " . $conn->error);
    $error_msg = "Database error occurred";
} else {
    $stmt->execute();
    $r = $stmt->get_result();
    $stats['total_members'] = $r->fetch_assoc()['cnt'];
    $stmt->close();
}

// Members pending
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM members WHERE status = 'pending'");
if ($stmt) { $stmt->execute(); $r = $stmt->get_result(); $stats['members_pending'] = $r->fetch_assoc()['cnt']; $stmt->close(); }

// Members approved
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM members WHERE status = 'approved'");
if ($stmt) { $stmt->execute(); $r = $stmt->get_result(); $stats['members_approved'] = $r->fetch_assoc()['cnt']; $stmt->close(); }

// Candidates total
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM candidates");
if ($stmt) { $stmt->execute(); $r = $stmt->get_result(); $stats['total_candidates'] = $r->fetch_assoc()['cnt']; $stmt->close(); }

// Candidates pending
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM candidates WHERE status = 'pending'");
if ($stmt) { $stmt->execute(); $r = $stmt->get_result(); $stats['candidates_pending'] = $r->fetch_assoc()['cnt']; $stmt->close(); }

// Candidates approved
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM candidates WHERE status = 'approved'");
if ($stmt) { $stmt->execute(); $r = $stmt->get_result(); $stats['candidates_approved'] = $r->fetch_assoc()['cnt']; $stmt->close(); }

// Admins total (single-admin system or schema without status column)
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM admin_users");
if ($stmt) { $stmt->execute(); $r = $stmt->get_result(); $stats['total_admins'] = $r->fetch_assoc()['cnt']; $stmt->close(); }
?>

<!-- STATS GRID -->
<div class="stats-grid">
    <div class="stat-card members">
        <div class="stat-card-icon"><i class="fas fa-users"></i></div>
        <div class="stat-card-label">Total Members</div>
        <div class="stat-card-value"><?php echo htmlspecialchars($stats['total_members']); ?></div>
    </div>

    <div class="stat-card pending">
        <div class="stat-card-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-card-label">Members Pending</div>
        <div class="stat-card-value"><?php echo htmlspecialchars($stats['members_pending']); ?></div>
    </div>

    <div class="stat-card approved">
        <div class="stat-card-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-card-label">Members Approved</div>
        <div class="stat-card-value"><?php echo htmlspecialchars($stats['members_approved']); ?></div>
    </div>

    <div class="stat-card candidates">
        <div class="stat-card-icon"><i class="fas fa-file-powerpoint"></i></div>
        <div class="stat-card-label">Total Candidates</div>
        <div class="stat-card-value"><?php echo htmlspecialchars($stats['total_candidates']); ?></div>
    </div>

    <div class="stat-card pending">
        <div class="stat-card-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-card-label">Candidates Pending</div>
        <div class="stat-card-value"><?php echo htmlspecialchars($stats['candidates_pending']); ?></div>
    </div>

    <div class="stat-card approved">
        <div class="stat-card-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-card-label">Candidates Approved</div>
        <div class="stat-card-value"><?php echo htmlspecialchars($stats['candidates_approved']); ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon"><i class="fas fa-user-shield"></i></div>
        <div class="stat-card-label">Total Admins</div>
        <div class="stat-card-value"><?php echo htmlspecialchars($stats['total_admins']); ?></div>
    </div>
</div>

<!-- QUICK ACTIONS -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
        <h3 style="color: #083a9c; margin-bottom: 15px; font-size: 18px;">
            <i class="fas fa-user-plus" style="margin-right: 10px;"></i>Quick Actions
        </h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="add-member.php" style="display: inline-block; background: linear-gradient(135deg, #083a9c 0%, #2563eb 100%); color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(8, 58, 156, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <i class="fas fa-plus"></i> Add New Member
            </a>
            <a href="members-list.php" style="display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(37, 99, 235, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <i class="fas fa-list"></i> View All Members
            </a>
            <a href="add-candidate.php" style="display: inline-block; background: linear-gradient(135deg, #d73322 0%, #c52811 100%); color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(215, 51, 34, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <i class="fas fa-plus"></i> Add New Candidate
            </a>
            <a href="manage-admin.php" style="display: inline-block; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(99, 102, 241, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <i class="fas fa-user-edit"></i> Edit My Profile
            </a>
        </div>
    </div>

    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
        <h3 style="color: #083a9c; margin-bottom: 15px; font-size: 18px;">
            <i class="fas fa-info-circle" style="margin-right: 10px;"></i>System Info
        </h3>
        <div style="font-size: 14px; color: #666;">
            <p style="margin-bottom: 8px;"><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p style="margin-bottom: 8px;"><strong>MySQL Server:</strong> <?php echo mysqli_get_server_info($conn); ?></p>
            <p style="margin-bottom: 8px;"><strong>Current Date:</strong> <?php echo date('F d, Y H:i A'); ?></p>
        </div>
    </div>

    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
        <h3 style="color: #083a9c; margin-bottom: 15px; font-size: 18px;">
            <i class="fas fa-chart-bar" style="margin-right: 10px;"></i>Statistics
        </h3>
        <div style="font-size: 14px; color: #666;">
            <p style="margin-bottom: 8px;"><strong>Avg Members/Month:</strong> <?php echo $stats['total_members'] > 0 ? ceil($stats['total_members'] / 12) : 0; ?></p>
            <p style="margin-bottom: 8px;"><strong>Approval Rate:</strong> <?php echo $stats['total_members'] > 0 ? round(($stats['members_approved'] / $stats['total_members']) * 100, 1) : 0; ?>%</p>
            <p style="margin-bottom: 8px;"><strong>Pending Rate:</strong> <?php echo $stats['total_members'] > 0 ? round(($stats['members_pending'] / $stats['total_members']) * 100, 1) : 0; ?>%</p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>