<?php
// Configure session for domain compatibility
require_once '../inc/session-config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Admin Dashboard - TIIR Party Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="https://xisbigatiir.so/theme/tiir/images/favicon.ico" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #333;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #083a9c 0%, #0f4ecd 100%);
            color: white;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-logo {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            transition: 0.3s ease;
            font-size: 15px;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            font-weight: 600;
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            margin-left: 260px;
            background: #f8fafc;
        }

        /* TOP BAR */
        .topbar {
            background: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-left h2 {
            font-size: 24px;
            color: #083a9c;
            font-weight: 700;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: #f0f4ff;
            border-radius: 8px;
            color: #083a9c;
            font-weight: 500;
        }

        .admin-profile i {
            font-size: 18px;
        }

        .logout-btn {
            padding: 10px 20px;
            background: #d73322;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #b51808;
        }

        /* CONTENT AREA */
        .admin-content {
            padding: 30px;
        }

        /* DASHBOARD STATS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #2563eb;
            transition: 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card.members {
            border-left-color: #2563eb;
        }

        .stat-card.candidates {
            border-left-color: #d73322;
        }

        .stat-card.pending {
            border-left-color: #fb923c;
        }

        .stat-card.approved {
            border-left-color: #16a34a;
        }

        .stat-card-icon {
            font-size: 32px;
            margin-bottom: 10px;
            color: #2563eb;
        }

        .stat-card.candidates .stat-card-icon {
            color: #d73322;
        }

        .stat-card.pending .stat-card-icon {
            color: #fb923c;
        }

        .stat-card.approved .stat-card-icon {
            color: #16a34a;
        }

        .stat-card-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .stat-card-value {
            font-size: 32px;
            font-weight: 700;
            color: #083a9c;
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
            }

            .admin-content {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -260px;
                height: 100vh;
                z-index: 1000;
                transition: 0.3s ease;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    /* Submenu styles */
    .sidebar-menu .submenu {
        list-style: none;
        margin: 6px 0 12px 0;
        padding-left: 12px;
        display: none;
    }
    .sidebar-menu .has-dropdown .submenu li a {
        padding: 8px 12px;
        font-size: 14px;
        color: rgba(255,255,255,0.85);
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .sidebar-menu .submenu li a:hover {
        background: rgba(255,255,255,0.1);
        color: white;
    }

    /* When active (JS toggles .open) */
    .sidebar-menu .has-dropdown.open .submenu {
        display: block;
    }

    /* Menu toggle arrow rotation */
    .sidebar-menu .has-dropdown.open > a .fa-chevron-down {
        transform: rotate(180deg);
        transition: transform 0.2s ease;
    }

    .sidebar-menu .has-dropdown > a .fa-chevron-down {
        transition: transform 0.2s ease;
    }
    </style>
<script src="../assets/js/admin-ui.js"></script>
</head>
<body>
    <div class="admin-wrapper">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-shield-alt"></i><br>
                Admin Panel
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="has-dropdown">
                    <a href="#" class="menu-toggle">
                        <i class="fas fa-users"></i>
                        <span>Members</span>
                        <i class="fas fa-chevron-down" style="margin-left:auto;"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="members-list.php"><i class="fas fa-list"></i> All Members</a></li>
                        <li><a href="add-member.php"><i class="fas fa-user-plus"></i> Add Member</a></li>
                        <li><a href="members-list.php?status=pending"><i class="fas fa-hourglass-half"></i> Pending Approvals</a></li>
                    </ul>
                </li>

                <li class="has-dropdown">
                    <a href="#" class="menu-toggle">
                        <i class="fas fa-file-powerpoint"></i>
                        <span>Candidates</span>
                        <i class="fas fa-chevron-down" style="margin-left:auto;"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="candidates-list.php"><i class="fas fa-list"></i> All Candidates</a></li>
                        <li><a href="add-candidate.php"><i class="fas fa-user-plus"></i> Add Candidate</a></li>
                        <li><a href="candidates-list.php?status=pending"><i class="fas fa-hourglass-half"></i> Pending Approvals</a></li>
                    </ul>
                </li>

                <li class="has-dropdown">
                    <a href="#" class="menu-toggle">
                        <i class="fas fa-user-shield"></i>
                        <span>Admins</span>
                        <i class="fas fa-chevron-down" style="margin-left:auto;"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="manage-admin.php"><i class="fas fa-users-cog"></i> Manage Admins</a></li>
                        <li><a href="add-admin.php"><i class="fas fa-user-plus"></i> Add Admin</a></li>
                    </ul>
                </li>

                <li>
                    <a href="audit-log.php">
                        <i class="fas fa-history"></i>
                        <span>Audit Log</span>
                    </a>
                </li>

                <li style="margin-top: 30px; border-top: 2px solid rgba(255, 255, 255, 0.2); padding-top: 20px;">
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- TOPBAR -->
            <div class="topbar">
                <div class="topbar-left">
                    <h2>Dashboard</h2>
                </div>
                <div class="topbar-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($admin_name); ?></span>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="admin-content">

