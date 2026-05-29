<?php
session_start();
if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'superadmin') {
    header("Location: index.php");
    exit();
}
require_once 'includes/db.php';
require_once 'includes/log_functions.php';
$isSuperadmin = true;

// Avatar initials
$name_parts = explode(' ', $_SESSION['user_name'] ?? 'Admin');
$initials = '';
foreach ($name_parts as $p) { if(!empty($p)) $initials .= strtoupper(substr($p, 0, 1)); }
$initials = substr($initials, 0, 2);

// Logs – filter params
$filter_module = $_GET['module'] ?? '';
$filter_date   = $_GET['date']   ?? '';
$filter_search = $_GET['search'] ?? '';
$page_num      = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit         = 10;
$offset        = ($page_num - 1) * $limit;

$filters = [];
if ($filter_module) $filters['module'] = $filter_module;
if ($filter_search) $filters['search'] = $filter_search;
if ($filter_date)   { $filters['date_from'] = $filter_date . ' 00:00:00'; $filters['date_to'] = $filter_date . ' 23:59:59'; }

$logs_data   = getActivityLogs($filters, $limit, $offset);
$logs        = $logs_data['data']  ?? [];
$total_logs  = (int)($logs_data['total'] ?? 0);
$total_pages = $total_logs > 0 ? (int)ceil($total_logs / $limit) : 0;

// Role display helper
function roleDisplay($role) {
    $map = [
        'superadmin' => ['Superadmin', 'badge-admin'],
        'admin' => ['Admin', 'badge-admin'],
        'user' => ['User', 'badge-user'],
        'system' => ['System', 'badge-admin']
    ];
    $r = strtolower($role ?? '');
    return $map[$r] ?? [ucfirst($role ?: 'Unknown'), 'badge-admin'];
}

// Module badge helper is now defined in includes/log_functions.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalinga Coffee | Superadmin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        .role-selection-grid {
            display: grid;
            gap: 1.2rem;
            margin-top: 0.5rem;
        }
        .role-card {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            padding: 1.2rem;
            background: #fff;
            border: 2px solid #eee;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .role-card:hover {
            border-color: var(--primary-brown);
            background: rgba(108, 78, 49, 0.02);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .role-card.selected {
            border-color: var(--primary-brown);
            background: rgba(108, 78, 49, 0.05);
        }
        .role-icon {
            width: 48px;
            height: 48px;
            background: #f5f5f5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--muted);
            transition: all 0.3s;
        }
        .role-card:hover .role-icon, .role-card.selected .role-icon {
            background: var(--primary-brown);
            color: #fff;
        }
        .role-info {
            flex: 1;
        }
        .role-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--brown-dark);
            margin-bottom: 0.2rem;
        }
        .role-desc {
            font-size: 0.85rem;
            color: var(--muted);
            line-height: 1.4;
        }
        .role-check {
            font-size: 1.4rem;
            color: var(--primary-brown);
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s;
        }
        .role-card.selected .role-check {
            opacity: 1;
            transform: scale(1);
        }

        /* Color Coding for Roles */
        .role-card[data-role="superadmin"].selected { border-color: #e74c3c; background: rgba(231, 76, 60, 0.05); }
        .role-card[data-role="superadmin"]:hover .role-icon, .role-card[data-role="superadmin"].selected .role-icon { background: #e74c3c; }
        .role-card[data-role="superadmin"].selected .role-check { color: #e74c3c; }        
        
        /* Premium Pagination Styles - Exact Match to Design */
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 2.5rem;
            margin-top: 2rem;
            padding: 1.2rem 1rem;
            background: transparent;
            flex-wrap: wrap;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .pagination-rows {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            font-size: 1.4rem;
            color: #666;
            font-weight: 500;
        }
        .pagination-rows select {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-family: inherit;
            font-weight: 600;
            color: #333;
            background: #fff;
            cursor: pointer;
            outline: none;
            transition: all 0.2s;
        }
        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .pag-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1px solid #eee;
            background: #fff;
            color: #666;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .pag-btn:hover:not(:disabled) {
            background: #f5f5f5;
            color: #333;
            border-color: #ccc;
        }
        .pag-btn.active {
            background: var(--primary-brown);
            color: #fff;
            border-color: var(--primary-brown);
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(108, 78, 49, 0.3);
        }
        .logs-table-container {
            min-height: 400px;
            position: relative;
        }
    </style>
</head>
<body>

<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeMobileSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-seedling"></i>
        <div class="logo-text">KALINGA COFFEE <br><span>MASANG KAPE</span></div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Dashboard</div>
        <div class="nav-link active" onclick="navigateTo('overview',this)" id="nl-overview">
            <i class="fas fa-home"></i><span class="nav-text">Overview</span>
        </div>

        <div class="nav-label">Management</div>
        <div class="nav-link" onclick="navigateTo('inventory',this)" id="nl-inventory">
            <i class="fas fa-boxes"></i><span class="nav-text">Inventory</span>
        </div>
        <div class="nav-link" onclick="navigateTo('user',this)" id="nl-user">
            <i class="fas fa-users"></i><span class="nav-text">Users</span>
        </div>
        <div class="nav-link" onclick="navigateTo('user-approval',this)" id="nl-user-approval">
            <i class="fas fa-user-check"></i><span class="nav-text">User Approvals</span>
        </div>
        <div class="nav-link" onclick="navigateTo('reports',this)" id="nl-reports">
            <i class="fas fa-chart-bar"></i><span class="nav-text">Reports</span>
        </div>

        <div class="nav-label">System</div>
        <div class="nav-link" onclick="navigateTo('transactions',this)" id="nl-transactions">
            <i class="fas fa-credit-card"></i><span class="nav-text">Sales</span>
        </div>
        <div class="nav-link" onclick="navigateTo('logs',this)" id="nl-logs">
            <i class="fas fa-history"></i><span class="nav-text">Activity Logs</span>
        </div>
        <div class="nav-link" onclick="navigateTo('settings',this)" id="nl-settings">
            <i class="fas fa-cog"></i><span class="nav-text">Settings</span>
        </div>
    </nav>

    <div class="sidebar-footer">
        <button class="sidebar-toggle-btn" onclick="toggleSidebar()" id="toggleBtn">
            <i class="fas fa-chevron-left" id="toggleIcon"></i>
            <span class="nav-text">Collapse</span>
        </button>
    </div>
</aside>

<div class="main-content" id="mainContent">
    <header class="topbar">
        <div class="topbar-left">
            <button class="mobile-menu-btn" onclick="openMobileSidebar()"><i class="fas fa-bars"></i></button>
            <div class="page-breadcrumb">
                <i class="fas fa-mug-hot"></i><span>Coffee Shop</span><i class="fas fa-chevron-right"></i><span class="current" id="pageTitle">Overview</span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="topbar-date"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y'); ?></div>
            <div class="user-chip">
                <div class="user-avatar"><?php echo $initials; ?></div>
                <div><div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></div><div class="user-role-lbl">Superadmin</div></div>
            </div>
            <button class="logout-btn" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </header>

    <div class="content-area">
        <div id="overview-section" class="section-view active">
            <div class="page-hdr">
                <div>
                    <div class="page-hdr-title">Superadmin Control Panel</div>
                    <div class="page-hdr-sub">Welcome back, Superadmin! Comprehensive system overview and administrative tools.</div>
                </div>
                <button class="btn btn-primary" onclick="navigateTo('reports', document.getElementById('nl-reports'))">
                    <i class="fas fa-chart-line"></i> Global Reports
                </button>
            </div>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon brown"><i class="fas fa-peso-sign"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">₱12,450</div>
                        <div class="stat-label">Daily Revenue</div>
                        <div class="stat-change up"><i class="fas fa-arrow-up"></i> 8.2% vs yesterday</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info"><i class="fas fa-receipt"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">142</div>
                        <div class="stat-label">Total Transactions</div>
                        <div class="stat-change neutral"><i class="fas fa-clock"></i> Peak: 10:00 AM</div>
                    </div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-icon danger"><i class="fas fa-box-open"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">3</div>
                        <div class="stat-label">Low Stock Items</div>
                        <div class="stat-change down"><i class="fas fa-exclamation-triangle"></i> Action required</div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">12</div>
                        <div class="stat-label">Staff on Duty</div>
                        <div class="stat-change up"><i class="fas fa-check-circle"></i> 0 Absences</div>
                    </div>
                </div>
            </div>

            <!-- Two-column: Quick shortcuts + Recent logs -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;flex-wrap:wrap; margin-top:2rem;" id="overviewCols">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-bolt"></i> Global Quick Actions</div>
                    </div>
                    <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <button class="btn btn-primary" style="justify-content:center;" onclick="navigateTo('inventory',document.getElementById('nl-inventory'))">
                            <i class="fas fa-boxes"></i> Inventory
                        </button>
                        <button class="btn btn-ghost" style="justify-content:center; color: var(--primary-brown); border-color: var(--primary-brown);" onclick="openModal('addUserModal')">
                            <i class="fas fa-user-plus"></i> Add User
                        </button>
                        <button class="btn btn-ghost" style="justify-content:center;" onclick="navigateTo('user',document.getElementById('nl-user'))">
                            <i class="fas fa-users"></i> Users
                        </button>
                        <button class="btn btn-ghost" style="justify-content:center;" onclick="navigateTo('user-approval',document.getElementById('nl-user-approval'))">
                            <i class="fas fa-user-check"></i> Approvals
                        </button>
                        <button class="btn btn-ghost" style="justify-content:center;" onclick="navigateTo('logs',document.getElementById('nl-logs'))">
                            <i class="fas fa-history"></i> Global Logs
                        </button>
                        <button class="btn btn-success" style="justify-content:center;" onclick="navigateTo('reports',document.getElementById('nl-reports'))">
                            <i class="fas fa-chart-pie"></i> Reports
                        </button>
                    </div>
                </div>

                <!-- Recent activity feed -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-stream"></i> Recent Global Activity</div>
                        <button class="btn btn-ghost btn-sm" onclick="navigateTo('logs',document.getElementById('nl-logs'))">View All</button>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent = getActivityLogs([], 5, 0);
                        if (!empty($recent['data'])):
                            foreach ($recent['data'] as $ra):
                                $act_low = strtolower($ra['action'] ?? '');
                                $dot_cls = (str_contains($act_low, 'log') && str_contains($act_low, 'in')) ? 'success'
                                         : ((str_contains($act_low, 'log') && str_contains($act_low, 'out')) ? 'warning'
                                         : (str_contains($act_low, 'fail') || str_contains($act_low, 'error') ? 'danger' : ''));
                        ?>
                        <div class="activity-item">
                            <div class="activity-dot <?php echo $dot_cls; ?>"></div>
                            <div class="activity-content">
                                <div class="activity-action"><?php echo htmlspecialchars($ra['action'] ?? 'N/A'); ?></div>
                                <div class="activity-meta">
                                    <?php echo htmlspecialchars($ra['user_name'] ?? 'N/A'); ?> •
                                    <?php echo isset($ra['created_at']) ? date('h:i A', strtotime($ra['created_at'])) : ''; ?>
                                </div>
                            </div>
                            <span class="badge <?php echo moduleBadge($ra['module'] ?? ''); ?>"><?php echo htmlspecialchars($ra['module'] ?? ''); ?></span>
                        </div>
                        <?php endforeach; else: ?>
                        <div style="text-align:center;color:var(--muted);padding:3rem;font-size:1.1rem;">No recent global activity</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="inventory-section" class="section-view">
             <div class="page-hdr"><div><div class="page-hdr-title">Inventory Management</div></div></div>
             <div class="card"><div class="table-wrap"><table class="data-table"><thead><tr><th>Product</th><th>Quantity</th><th>Action</th></tr></thead><tbody><tr><td>Arabica Beans</td><td>45kg</td><td><button class="btn btn-ghost btn-sm">Edit</button></td></tr></tbody></table></div></div>
        </div>

        <!-- ===== USERS ===== -->
        <div id="user-section" class="section-view">
            <div class="page-hdr">
                <div><div class="page-hdr-title">Registered Users</div><div class="page-hdr-sub">Manage approved user accounts and system access</div></div>
                <button class="btn btn-primary" onclick="openModal('addUserModal')"><i class="fas fa-user-plus"></i> Add New User</button>
            </div>

            <!-- Role Privileges Summary -->
            <div class="card" style="margin-bottom: 2rem; border-left: 5px solid var(--primary-brown);">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-shield-halved"></i> Role Privileges Reference</div>
                </div>
                <div class="card-body" style="padding-top: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                        <!-- User / Customer -->
                        <div style="background: rgba(52, 152, 219, 0.05); padding: 1.6rem; border-radius: 12px; border: 1px solid rgba(0,0,0,0.05);">
                            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.2rem;">
                                <span class="badge badge-user" style="font-size:1.3rem; padding:0.6rem 1.2rem;">User</span>
                                <i class="fas fa-user-tag" style="color:var(--info); font-size:1.6rem;"></i>
                            </div>
                            <ul style="list-style:none; padding:0; display:grid; gap:0.8rem; font-size:1.25rem; color:var(--muted);">
                                <li><i class="fas fa-check-circle" style="color:var(--accent-green); margin-right:0.6rem;"></i> View Coffee Menu</li>
                                <li><i class="fas fa-check-circle" style="color:var(--accent-green); margin-right:0.6rem;"></i> Place POS Orders</li>
                                <li><i class="fas fa-check-circle" style="color:var(--accent-green); margin-right:0.6rem;"></i> Manage Personal Profile</li>
                            </ul>
                        </div>
                        
                        <!-- Admin -->
                        <div style="background: rgba(108, 78, 49, 0.05); padding: 1.6rem; border-radius: 12px; border: 1px solid rgba(0,0,0,0.05);">
                            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.2rem;">
                                <span class="badge badge-admin" style="font-size:1.3rem; padding:0.6rem 1.2rem; background:rgba(62,31,0,0.2);">Admin</span>
                                <i class="fas fa-user-shield" style="color:var(--primary-brown); font-size:1.6rem;"></i>
                            </div>
                            <div style="margin-bottom: 0.8rem; font-weight: 600; color: var(--primary-brown); font-size: 1.1rem; border-bottom: 1px dashed rgba(108, 78, 49, 0.2); padding-bottom: 0.4rem;">Configurable Privileges:</div>
                            <ul style="list-style:none; padding:0; display:grid; gap:0.8rem; font-size:1.2rem; color:var(--muted);">
                                <li><i class="fas fa-key" style="color:var(--primary-light); margin-right:0.6rem;"></i> <strong>Add New Users</strong></li>
                                <li><i class="fas fa-key" style="color:var(--primary-light); margin-right:0.6rem;"></i> <strong>Block Users</strong></li>
                                <li><i class="fas fa-key" style="color:var(--primary-light); margin-right:0.6rem;"></i> <strong>Change User Roles</strong></li>
                                <li><i class="fas fa-key" style="color:var(--primary-light); margin-right:0.6rem;"></i> <strong>View Registered Users Data</strong></li>
                                <li><i class="fas fa-circle-plus" style="color:var(--primary-light); margin-right:0.6rem;"></i> Manage Inventory & Sales Reports</li>
                                <li><i class="fas fa-circle-plus" style="color:var(--primary-light); margin-right:0.6rem;"></i> View Activity Logs</li>
                                <li style="font-size:1.1rem; color:var(--danger); font-style:italic; margin-top:0.4rem;">*All sensitive actions require Superadmin Approval</li>
                            </ul>
                        </div>

                        <!-- Superadmin -->
                        <div style="background: rgba(192, 57, 43, 0.05); padding: 1.6rem; border-radius: 12px; border: 1px solid rgba(192, 57, 43, 0.1);">
                            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.2rem;">
                                <span class="badge badge-danger" style="font-size:1.3rem; padding:0.6rem 1.2rem;">Superadmin</span>
                                <i class="fas fa-crown" style="color:var(--danger); font-size:1.6rem;"></i>
                            </div>
                            <ul style="list-style:none; padding:0; display:grid; gap:0.8rem; font-size:1.25rem; color:var(--muted);">
                                <li><i class="fas fa-bolt" style="color:var(--warning); margin-right:0.6rem;"></i> <strong>Ultimate System Control</strong></li>
                                <li><i class="fas fa-bolt" style="color:var(--warning); margin-right:0.6rem;"></i> Approve/Reject New Signups</li>
                                <li><i class="fas fa-bolt" style="color:var(--warning); margin-right:0.6rem;"></i> Block/Delete Any User/Admin</li>
                                <li><i class="fas fa-bolt" style="color:var(--warning); margin-right:0.6rem;"></i> Modify System Configuration</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-users"></i> Registered & Approved Users</div>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>Role</th>
                                <th>Joined Date</th>
                                <th>Last Login</th>
                                <th>Last Logout</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $conn->prepare("SELECT id, first_name, last_name, role, status, created_at, last_login, last_logout FROM users WHERE status IN ('approved', 'blocked', 'deactivated') AND role IN ('user', 'admin', 'superadmin') ORDER BY role, last_name");
                                $stmt->execute();
                                $currentActorId = (int)($_SESSION['user_id'] ?? 0);
                                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (empty($users)) {
                                    echo '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">No approved users found.</td></tr>';
                                } else {
                                    foreach ($users as $u):
                                        $fullname = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                                        $role = strtolower($u['role'] ?? 'user');
                                        [$role_display, $badge_cls] = roleDisplay($role);
                                        $joined = isset($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : 'Unknown';
                                        $last_login  = isset($u['last_login'])  ? date('M d, Y H:i', strtotime($u['last_login']))  : '<span style="color:var(--muted)">Never</span>';
                                        $last_logout = isset($u['last_logout']) ? date('M d, Y H:i', strtotime($u['last_logout'])) : '<span style="color:var(--muted)">Never</span>';
                                        $isSelf = ((int)$u['id'] === $currentActorId);
                            ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:1rem;">
                                        <div class="user-chip-sm" style="width:32px;height:32px;background:var(--brown-light);color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:600;">
                                            <?php echo strtoupper(substr($u['first_name']??'?',0,1).substr($u['last_name']??'?',0,1)); ?>
                                        </div>
                                        <strong><?php echo htmlspecialchars($fullname); ?> <?php if($isSelf) echo '<span style="color:var(--muted); font-weight:normal; font-style:italic;">(You)</span>'; ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge_cls; ?>"><?php echo $role_display; ?></span>
                                    <?php if ($u['status'] === 'blocked'): ?>
                                    <span class="badge badge-danger" style="margin-left:4px;">BLOCKED</span>
                                    <?php elseif ($u['status'] === 'deactivated'): ?>
                                    <span class="badge" style="margin-left:4px; font-weight:700; background:rgba(122,106,90,0.15); color:var(--muted); border:1px solid rgba(122,106,90,0.3);">DEACTIVATED (SOFT DELETE)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $joined; ?></td>
                                <td><?php echo $last_login; ?></td>
                                <td><?php echo $last_logout; ?></td>
                                <td style="text-align:right;">
                                    <?php if (!$isSelf): ?>
                                    <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                                        <?php if ($u['status'] === 'blocked'): ?>
                                        <button class="btn btn-success btn-sm" title="Unblock User" onclick="requestStatusUpdate(<?php echo $u['id']; ?>, 'unblock_user')"><i class="fas fa-unlock"></i></button>
                                        <?php elseif ($u['status'] === 'deactivated'): ?>
                                        <button class="btn btn-success btn-sm" title="Restore Account" onclick="requestStatusUpdate(<?php echo $u['id']; ?>, 'unblock_user')"><i class="fas fa-trash-can-arrow-up"></i> Restore</button>
                                        <?php else: ?>
                                        <button class="btn btn-ghost btn-sm" style="color:var(--danger);" title="Block User" onclick="requestStatusUpdate(<?php echo $u['id']; ?>, 'block_user')"><i class="fas fa-ban"></i></button>
                                        <?php endif; ?>
                                        <?php if ($role === 'admin'): ?>
                                        <button class="btn btn-ghost btn-sm" style="color:var(--primary-brown);" title="Manage Privileges" onclick="openPrivilegesModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($fullname, ENT_QUOTES); ?>')"><i class="fas fa-key"></i></button>
                                        <?php endif; ?>
                                        <button class="btn btn-ghost btn-sm" title="Update Role" onclick="requestRoleUpdate(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($fullname, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($role, ENT_QUOTES); ?>')"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-danger btn-sm" title="Delete User" onclick="requestDeleteUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($fullname, ENT_QUOTES); ?>')"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <?php else: ?>
                                    <span style="font-size:0.85rem; color:var(--muted); font-style:italic;">No Actions Available</span>
                                    <?php endif; ?>
                                </td>

                            </tr>
                            <?php 
                                    endforeach;
                                }
                            } catch (Exception $e) {
                                echo '<tr><td colspan="6" style="text-align:center;padding:1rem;">Error loading users</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===== USERS APPROVAL ===== -->
        <div id="user-approval-section" class="section-view">
            <div class="page-hdr">
                <div><div class="page-hdr-title">User Approvals</div><div class="page-hdr-sub">Review and process registration requests and administrative actions</div></div>
            </div>

            <!-- ===== PENDING USER APPROVALS ===== -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-user-clock"></i> Pending User Approvals</div>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Applicant Name</th>
                                <th>Email</th>
                                <th>ID Number</th>
                                <th>Contact</th>
                                <th>Registered</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $conn->prepare("SELECT id, first_name, last_name, email, id_number, contact, created_at FROM users WHERE role = 'user' AND status = 'pending' ORDER BY created_at DESC");
                                $stmt->execute();
                                $pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (empty($pendingUsers)) {
                                    echo '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">No pending approvals found.</td></tr>';
                                } else {
                                    foreach ($pendingUsers as $p):
                                        $pname = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                                        echo "<tr>
                                            <td><strong>" . htmlspecialchars($pname) . "</strong></td>
                                            <td>" . htmlspecialchars($p['email'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($p['id_number'] ?? '') . "</td>
                                            <td>" . htmlspecialchars($p['contact'] ?? '') . "</td>
                                            <td>" . (isset($p['created_at']) ? date('M j, H:i', strtotime($p['created_at'])) : '-') . "</td>
                                            <td style='text-align:right;'>
                                                <div style='display:flex;gap:0.5rem;justify-content:flex-end;'>
                                                    <button class='btn btn-primary btn-sm' onclick='processApproval(" . $p['id'] . ", \"approve\")'>Approve</button>
                                                    <button class='btn btn-danger btn-sm' onclick='processApproval(" . $p['id'] . ", \"reject\")'>Reject</button>
                                                </div>
                                            </td>
                                        </tr>";
                                    endforeach;
                                }
                            } catch (Exception $e) {
                                echo '<tr><td colspan="6" style="text-align:center;padding:1rem;">Error loading pending users</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== PENDING ADMIN APPROVALS ===== -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-shield-alt"></i> Pending Admin Approvals (Action Confirmations)</div>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Requested</th>
                                <th>Requested By</th>
                                <th>Role</th>
                                <th>Target User</th>
                                <th>Action Type</th>
                                <th style="text-align:right;">Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $conn->prepare("
                                    SELECT pa.*, ru.first_name as rf, ru.last_name as rl, ru.role as rr, tu.first_name as tf, tu.last_name as tl 
                                    FROM pending_actions pa 
                                    JOIN users ru ON ru.id=pa.requested_by 
                                    JOIN users tu ON tu.id=pa.target_user_id 
                                    WHERE pa.status='pending'
                                    ORDER BY pa.created_at DESC
                                ");
                                $stmt->execute();
                                $pas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                if (empty($pas)) {
                                    echo '<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--muted);">No pending admin confirmations.</td></tr>';
                                } else {
                                    foreach ($pas as $pa):
                                        $rName = trim($pa['rf'].' '.$pa['rl']) ?: 'Admin';
                                        $tName = trim($pa['tf'].' '.$pa['tl']) ?: 'User';
                                        $roleInfo = roleDisplay($pa['rr'] ?? 'admin');
                                        $act_map = ['delete_user'=>'Delete', 'update_role'=>'Role Change', 'block_user'=>'Block', 'unblock_user'=>'Unblock'];
                                        $act = $act_map[$pa['action_type']] ?? 'Action';
                                ?>
                                <tr>
                                    <td><?php echo date('M j, H:i', strtotime($pa['created_at'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($rName); ?></strong></td>
                                    <td><span class="badge <?php echo $roleInfo[1]; ?>"><?php echo $roleInfo[0]; ?></span></td>
                                    <td><?php echo htmlspecialchars($tName); ?></td>
                                    <td><span class="badge badge-admin"><?php echo $act; ?></span></td>
                                    <td style="text-align:right;">
                                        <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                                            <button class="btn btn-primary btn-sm" onclick="processPendingAction(<?php echo $pa['id']; ?>, 'approve')">Confirm</button>
                                            <button class="btn btn-danger btn-sm" onclick="processPendingAction(<?php echo $pa['id']; ?>, 'reject')">Cancel</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; }
                            } catch (Exception $e) {
                                echo '<tr><td colspan="6" style="text-align:center;padding:1rem;">Error loading confirmations</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="reports-section" class="section-view">
            <div class="page-hdr"><div><div class="page-hdr-title">System Reports</div></div></div>
            <div class="coming-soon">Reports visualization coming soon.</div>
        </div>

        <div id="transactions-section" class="section-view"><div class="coming-soon">Transactions coming soon.</div></div>

        <div id="logs-section" class="section-view">
            <div class="page-hdr">
                <div>
                    <div class="page-hdr-title">System Activity Logs</div>
                    <div class="page-hdr-sub">Comprehensive audit trail of all system actions</div>
                </div>
                <button class="btn btn-primary" onclick="exportLogs()">Export CSV</button>
            </div>
            <div class="filter-row" style="margin-bottom: 1.5rem; display: grid; grid-template-columns: 300px 160px 160px 160px 160px auto; gap: 1rem; align-items: end; justify-content: start;">
                <div class="search-box" style="margin: 0;">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchLogs" class="filter-control" placeholder="Search actions, users, or usernames..." onkeyup="if(event.key==='Enter') loadLogs(1)">
                </div>
                <div>
                    <label style="font-size: 0.8rem; font-weight: 600; color: var(--muted); margin-bottom: 0.4rem; display: block;">Module</label>
                    <select id="moduleFilter" class="filter-control">
                        <option value="">All Modules</option>
                        <?php foreach (['Authentication','Inventory','User Mgmt','Privileges','Sales'] as $mod): ?>
                        <option value="<?php echo $mod; ?>"><?php echo $mod; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.8rem; font-weight: 600; color: var(--muted); margin-bottom: 0.4rem; display: block;">Actor Role</label>
                    <select id="roleFilter" class="filter-control">
                        <option value="">All Roles</option>
                        <option value="superadmin">Superadmin</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.8rem; font-weight: 600; color: var(--muted); margin-bottom: 0.4rem; display: block;">From</label>
                    <input type="date" id="dateFromFilter" class="filter-control">
                </div>
                <div>
                    <label style="font-size: 0.8rem; font-weight: 600; color: var(--muted); margin-bottom: 0.4rem; display: block;">To</label>
                    <input type="date" id="dateToFilter" class="filter-control">
                </div>
                <input type="hidden" id="actionTypeFilter" value="">
                <input type="hidden" id="limitFilter" value="5">
                <button type="button" class="btn btn-primary" onclick="loadLogs(1)" style="height: 48px;">Apply</button>
            </div>

            <div style="display:flex; gap:0.8rem; margin-bottom:1.5rem; flex-wrap:wrap;">
                <button type="button" class="btn btn-sm btn-ghost" style="border-radius:20px; font-size:0.9rem; padding:0.4rem 1.2rem; background:rgba(141,110,99,0.05);" onclick="document.getElementById('actionTypeFilter').value='login'; loadLogs(1);">
                    <i class="fas fa-sign-in-alt" style="margin-right:0.4rem; color:var(--primary-brown);"></i> Logins
                </button>
                <button type="button" class="btn btn-sm btn-ghost" style="border-radius:20px; font-size:0.9rem; padding:0.4rem 1.2rem; background:rgba(141,110,99,0.05);" onclick="document.getElementById('actionTypeFilter').value='logout'; loadLogs(1);">
                    <i class="fas fa-sign-out-alt" style="margin-right:0.4rem; color:var(--warning);"></i> Logouts
                </button>
                <button type="button" class="btn btn-sm btn-ghost" style="border-radius:20px; font-size:0.9rem; padding:0.4rem 1.2rem; background:rgba(141,110,99,0.05);" onclick="document.getElementById('actionTypeFilter').value='delete'; loadLogs(1);">
                    <i class="fas fa-trash-alt" style="margin-right:0.4rem; color:var(--danger);"></i> Deletions
                </button>
                <button type="button" class="btn btn-sm btn-ghost" style="border-radius:20px; font-size:0.9rem; padding:0.4rem 1.2rem; background:rgba(141,110,99,0.05);" onclick="document.getElementById('actionTypeFilter').value='update'; loadLogs(1);">
                    <i class="fas fa-edit" style="margin-right:0.4rem; color:var(--info);"></i> Updates
                </button>
                <button type="button" class="btn btn-sm btn-ghost" style="border-radius:20px; font-size:0.9rem; padding:0.4rem 1.2rem; background:rgba(141,110,99,0.05);" onclick="document.getElementById('actionTypeFilter').value='block'; loadLogs(1);">
                    <i class="fas fa-ban" style="margin-right:0.4rem; color:var(--danger);"></i> Blocks
                </button>
                <button type="button" class="btn btn-sm btn-ghost" style="border-radius:20px; font-size:0.9rem; padding:0.4rem 1.2rem; background:rgba(141,110,99,0.05);" onclick="document.getElementById('actionTypeFilter').value='privilege'; loadLogs(1);">
                    <i class="fas fa-shield-halved" style="margin-right:0.4rem; color:var(--primary-brown);"></i> Privileges
                </button>
                <button type="button" class="btn btn-sm btn-ghost" style="border-radius:20px; font-size:0.9rem; padding:0.4rem 1.2rem; background:rgba(141,110,99,0.05);" onclick="document.getElementById('moduleFilter').value='Authentication'; loadLogs(1);">
                    <i class="fas fa-shield-alt" style="margin-right:0.4rem; color:var(--primary-brown);"></i> Security
                </button>
                <button type="button" class="btn btn-sm btn-ghost" style="border-radius:20px; font-size:0.9rem; padding:0.4rem 1.2rem; background:var(--primary-brown); color:white;" onclick="document.getElementById('moduleFilter').value=''; document.getElementById('actionTypeFilter').value=''; document.getElementById('searchLogs').value=''; document.getElementById('dateFromFilter').value=''; document.getElementById('dateToFilter').value=''; document.getElementById('roleFilter').value=''; document.getElementById('limitFilter').value='5'; loadLogs(1);">
                    Clear Filters
                </button>
            </div>

            <div class="card logs-table-container">
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Action & Details</th>
                                <th>IP & Device</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--muted);"><i class="fas fa-spinner fa-spin"></i> Loading logs...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pagination" id="logsPagination" style="display:none;"></div>
        </div>

        <div id="settings-section" class="section-view"><div class="coming-soon">Settings coming soon.</div></div>
    </div>
</div>

<!-- MODALS -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-box" style="max-width: 850px; border-radius: 12px; overflow: hidden;">
        <div class="modal-head" style="padding: 1.5rem 2rem;">
            <h3 style="margin:0; font-size: 1.6rem;"><i class="fas fa-user-plus"></i> Add New User</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">✕</button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <form onsubmit="submitAddStaff(event)">
                <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 3rem;">
                    
                    <!-- Left Column: Personal Info -->
                    <div style="border-right: 1px solid rgba(139, 99, 71, 0.1); padding-right: 2rem;">
                        <h4 style="color: var(--primary-brown); border-bottom: 1px solid rgba(108, 78, 49, 0.1); padding-bottom: 0.5rem; margin-bottom: 1.5rem; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.5px;">Personal Details</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group"><label>First Name</label><input type="text" name="firstName" class="form-control" style="padding: 0.7rem 1rem;" placeholder="First name" required></div>
                            <div class="form-group"><label>Last Name</label><input type="text" name="lastName" class="form-control" style="padding: 0.7rem 1rem;" placeholder="Last name" required></div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group"><label>ID Number</label><input type="text" name="id_number" class="form-control" style="padding: 0.7rem 1rem;" placeholder="xxxx-xxxx" required></div>
                            <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" style="padding: 0.7rem 1rem;" placeholder="jdoe123" required></div>
                        </div>
                        <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" style="padding: 0.7rem 1rem;" placeholder="mail@example.com" required></div>
                        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 1rem;">
                            <div class="form-group"><label>Contact</label><input type="text" name="contact" class="form-control" style="padding: 0.7rem 1rem;" placeholder="09xxxxxxxxx" required></div>
                            <div class="form-group">
                                <label>Sex</label>
                                <select name="sex" class="form-control" style="padding: 0.7rem 1rem;" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;"><label>Date of Birth</label><input type="date" name="dob" class="form-control" style="padding: 0.7rem 1rem;" required></div>
                    </div>

                    <!-- Right Column: Account Security -->
                    <div>
                        <h4 style="color: var(--primary-brown); border-bottom: 1px solid rgba(108, 78, 49, 0.1); padding-bottom: 0.5rem; margin-bottom: 1.5rem; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.5px;">Security & Access</h4>
                        <div class="form-group">
                            <label>System Role</label>
                            <select name="role" class="form-control" style="padding: 0.7rem 1rem;">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div style="background: rgba(108, 78, 49, 0.05); padding: 1.5rem; border-radius: 12px; border: 1px dashed var(--primary-brown); margin-top: 2rem;">
                            <div style="color: var(--primary-brown); font-weight: 700; margin-bottom: 0.5rem;">
                                <i class="fas fa-info-circle"></i> Password Setup
                            </div>
                            <p style="font-size: 0.9rem; color: #666; line-height: 1.5; margin: 0;">
                                You don't need to set a password. The user will be invited via email to create their own secure password once they verify their account.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(139, 99, 71, 0.1); justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding-left: 3rem; padding-right: 3rem;">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="detailsModal">
    <div class="modal-box">
        <div class="modal-head"><h3>Action Details</h3><button class="modal-close" onclick="closeModal('detailsModal')">✕</button></div>
        <div class="modal-body"><table class="detail-table" id="detailsTable"></table></div>
    </div>
</div>

<div class="modal-overlay" id="roleModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Change User Role</h3>
            <button class="modal-close" onclick="closeModal('roleModal')">✕</button>
        </div>
        <div class="modal-body">
            <p id="roleModalUser" style="margin-bottom:1.5rem; font-weight:600; color:var(--brown);"></p>
            <input type="hidden" id="roleModalUserId">
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.8rem; font-weight: 600; color: var(--muted);">Select New Designation</label>
                <div class="role-selection-grid">


                    <div class="role-card" data-role="admin" onclick="selectRoleCard(this)">
                        <div class="role-icon"><i class="fas fa-user-shield"></i></div>
                        <div class="role-info">
                            <div class="role-name">Admin</div>
                            <div class="role-desc">Store management, inventory, and staff oversight.</div>
                        </div>
                        <div class="role-check"><i class="fas fa-check-circle"></i></div>
                    </div>

                    <?php if ($isSuperadmin): ?>
                    <div class="role-card" data-role="superadmin" onclick="selectRoleCard(this)">
                        <div class="role-icon"><i class="fas fa-crown"></i></div>
                        <div class="role-info">
                            <div class="role-name">Superadmin</div>
                            <div class="role-desc">Full system authority and administrative control.</div>
                        </div>
                        <div class="role-check"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <?php endif; ?>
                </div>
                <input type="hidden" id="roleModalSelect" value="">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-ghost" onclick="closeModal('roleModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmRoleUpdate()">Update Role</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Confirmation Modal -->
<div class="modal-overlay" id="deleteConfirmModal">
    <div class="modal-box" style="max-width: 450px;">
        <div class="modal-head">
            <h3><i class="fas fa-user-times" style="color:var(--danger);"></i> Confirm Account Deletion</h3>
            <button class="modal-close" onclick="closeModal('deleteConfirmModal')">✕</button>
        </div>
        <div class="modal-body">
            <div style="background: rgba(192, 57, 43, 0.05); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; border-left: 4px solid var(--danger);">
                <p id="deleteModalUser" style="font-weight: 600; color: var(--danger); margin-bottom: 0.3rem;"></p>
                <p style="font-size: 0.9rem; color: var(--muted);">This action is permanent and will remove all user records from the system.</p>
            </div>
            
            <form id="deleteUserForm" onsubmit="submitPermanentDeletion(event)">
                <input type="hidden" id="deleteModalUserId" name="target_user_id">
                <input type="hidden" name="action" value="delete_user">
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Reason for Deletion <span style="color:var(--danger);">*</span></label>
                    <textarea name="deletion_reason" class="form-control" placeholder="Please provide a specific reason for removing this account..." required style="min-height: 100px; resize: none;"></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Supporting Document (Proof) <span style="color:var(--danger);">*</span></label>
                    <div style="position: relative; border: 2px dashed rgba(139, 99, 71, 0.2); border-radius: 8px; padding: 1.5rem; text-align: center; transition: all 0.3s; background: rgba(255,255,255,0.5);" id="dropZone">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--primary-brown); margin-bottom: 0.5rem; display: block;"></i>
                        <span style="font-size: 0.9rem; color: var(--muted);">Click or drag to upload file (PDF, JPG, PNG)</span>
                        <input type="file" name="deletion_doc" required style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" onchange="updateFileName(this)">
                    </div>
                    <div id="fileNameDisplay" style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--primary-brown); font-weight: 600; text-align: center;"></div>
                </div>
                
                <div class="form-actions" style="justify-content: space-between; gap: 1rem;">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('deleteConfirmModal')" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deleteBtn" style="flex: 1.5; background: var(--danger); border-color: var(--danger);">
                        <i class="fas fa-trash-alt"></i> Permanently Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Notification Components -->
<div class="toast-container" id="toastContainer"></div>

<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon"><i class="fas fa-question-circle"></i></div>
        <h4 id="confirmTitle">Are you sure?</h4>
        <p id="confirmMessage">This action cannot be undone.</p>
        <div class="confirm-actions">
            <button class="confirm-btn-no" onclick="handleConfirm(false)">Cancel</button>
            <button class="confirm-btn-yes" id="confirmBtnYes">Confirm</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="privilegesModal">
    <div class="modal-box" style="max-width: 500px;">
        <div class="modal-head">
            <h3><i class="fas fa-shield-halved"></i> Admin Privileges</h3>
            <button class="modal-close" onclick="closeModal('privilegesModal')">✕</button>
        </div>
        <div class="modal-body">
            <p id="privModalUser" style="margin-bottom:1.5rem; font-weight:600; color:var(--brown); font-size: 1.2rem;"></p>
            <input type="hidden" id="privModalAdminId">
            
            <div style="display: grid; gap: 1.2rem; background: rgba(108, 78, 49, 0.05); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(108, 78, 49, 0.1);">
                <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='rgba(108, 78, 49, 0.1)'" onmouseout="this.style.background='transparent'">
                    <input type="checkbox" id="priv_add_user" style="width: 20px; height: 20px; accent-color: var(--primary-brown);">
                    <div>
                        <div style="font-weight: 600; color: var(--primary-brown);">Add Users</div>
                        <div style="font-size: 0.85rem; color: var(--muted);">Allow admin to create new user accounts</div>
                    </div>
                </label>
                
                <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='rgba(108, 78, 49, 0.1)'" onmouseout="this.style.background='transparent'">
                    <input type="checkbox" id="priv_block_user" style="width: 20px; height: 20px; accent-color: var(--primary-brown);">
                    <div>
                        <div style="font-weight: 600; color: var(--primary-brown);">Block Users</div>
                        <div style="font-size: 0.85rem; color: var(--muted);">Allow admin to block or unblock users</div>
                    </div>
                </label>
                

                
                <label style="display: flex; align-items: center; gap: 1rem; cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='rgba(108, 78, 49, 0.1)'" onmouseout="this.style.background='transparent'">
                    <input type="checkbox" id="priv_view_users" style="width: 20px; height: 20px; accent-color: var(--primary-brown);">
                    <div>
                        <div style="font-weight: 600; color: var(--primary-brown);">View User Data</div>
                        <div style="font-size: 0.85rem; color: var(--muted);">Allow admin to access the Registered Users module</div>
                    </div>
                </label>
            </div>

            <div class="form-actions" style="margin-top: 2rem;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('privilegesModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAdminPrivileges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
let sidebarCollapsed = false;
function toggleSidebar() { sidebarCollapsed = !sidebarCollapsed; document.getElementById('sidebar').classList.toggle('collapsed', sidebarCollapsed); document.getElementById('mainContent').classList.toggle('expanded', sidebarCollapsed); }
function openMobileSidebar() { document.getElementById('sidebar').classList.add('mobile-open'); document.getElementById('sidebarBackdrop').classList.add('show'); }
function closeMobileSidebar() { document.getElementById('sidebar').classList.remove('mobile-open'); document.getElementById('sidebarBackdrop').classList.remove('show'); }

const pageNames = { overview:'Overview', inventory:'Inventory', user:'Users', 'user-approval':'Users Approval', reports:'Reports', transactions:'Transactions', logs:'Activity Logs', settings:'Settings' };
function navigateTo(page, linkEl) {
    document.querySelectorAll('.section-view').forEach(s => s.classList.remove('active'));
    document.getElementById(page + '-section').classList.add('active');
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    if (linkEl) linkEl.classList.add('active'); else document.getElementById('nl-'+page).classList.add('active');
    document.getElementById('pageTitle').textContent = pageNames[page];
    closeMobileSidebar();
    if (page === 'logs') loadLogs(1);
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { 
    document.getElementById(id).classList.remove('open'); 
    if(id === 'addUserModal') {
        const reqs = ['length','upper','lower','number','special'];
        reqs.forEach(r => {
            const el = document.getElementById('adm-req-' + r);
            if(el) { el.classList.remove('valid'); el.querySelector('i').className = 'fas fa-circle'; }
        });
    }
}

function toggleGenericPass(id, icon) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}



function validateAdminPassword(pass) {
    const checks = {
        length: pass.length >= 12,
        upper: /[A-Z]/.test(pass),
        lower: /[a-z]/.test(pass),
        number: /[0-9]/.test(pass),
        special: /[^A-Za-z0-9]/.test(pass)
    };

    Object.keys(checks).forEach(id => {
        const el = document.getElementById('adm-req-' + id);
        if (el) {
            if (checks[id]) {
                el.classList.add('valid');
                el.querySelector('i').className = 'fas fa-check-circle';
            } else {
                el.classList.remove('valid');
                el.querySelector('i').className = 'fas fa-circle';
            }
        }
    });

    return Object.values(checks).every(v => v === true);
}

function showLogDetails(log) {
    let details = {};
    try {
        details = (typeof log.details === 'string') ? JSON.parse(log.details) : (log.details || {});
    } catch(e) { details = { error: 'Invalid JSON', raw: log.details }; }
    
    let h = `
        <tr><td>Timestamp</td><td>${new Date(log.created_at).toLocaleString()}</td></tr>
        <tr><td>Operator</td><td><strong>${log.user_name}</strong> (ID: ${log.user_id})</td></tr>
        <tr><td>Role</td><td>${log.user_role.toUpperCase()}</td></tr>
        <tr><td>Action</td><td>${log.action}</td></tr>
        <tr><td>Module</td><td>${log.module}</td></tr>
        <tr><td>IP Address</td><td><code>${log.ip_address || 'N/A'}</code></td></tr>
        <tr><td>User Agent</td><td style="font-size:1.1rem; color:var(--muted);">${log.user_agent}</td></tr>
        <tr><td>Payload</td><td><code>${JSON.stringify(details, null, 2)}</code></td></tr>
    `;
    document.getElementById('detailsTable').innerHTML = h;
    openModal('detailsModal');
}

let _logsCurrentPage = 1;

function loadLogs(page) {
    page = page || _logsCurrentPage;
    _logsCurrentPage = page;

    const mod    = document.getElementById('moduleFilter').value;
    const action = document.getElementById('actionTypeFilter').value;
    const df     = document.getElementById('dateFromFilter').value;
    const dt     = document.getElementById('dateToFilter').value;
    const role   = document.getElementById('roleFilter').value;
    const limit  = document.getElementById('limitFilter').value;
    const search = document.getElementById('searchLogs').value;

    const tbody = document.getElementById('logsTableBody');
    const pag   = document.getElementById('logsPagination');
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--muted);"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>`;
    pag.style.display = 'none';

    const params = new URLSearchParams({ page, module: mod, action_type: action, date_from: df, date_to: dt, role, search, limit });
    fetch('actions/get_logs.php?' + params.toString())
    .then(r => r.json())
    .then(data => {
        const logs = data.logs || [];
        if (!logs.length) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--muted);">No logs found matching your criteria.</td></tr>`;
            pag.style.display = 'none';
            return;
        }

        const roleClassMap = { superadmin:'badge-admin', admin:'badge-admin', user:'badge-user', system:'badge-admin' };
        const roleLabelMap = { superadmin:'Superadmin', admin:'Admin', user:'User', system:'System' };

        tbody.innerHTML = logs.map(log => {
            const roleClass = log.role_class || roleClassMap[log.user_role] || 'badge-admin';
            const roleLabel = log.role_label || roleLabelMap[log.user_role] || log.user_role;
            const initials = log.user_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            
            let summaryHtml = log.summary ? `<div style="font-size:0.85rem; color:#5d4037; margin-top:0.6rem; line-height:1.4; background:rgba(141,110,99,0.08); padding:0.6rem 1rem; border-radius:8px; border-left:3px solid var(--primary-brown);">${log.summary}</div>` : '';
            
            const isCritical = log.act_class === 'badge-danger';
            const rowStyle = isCritical ? 'background-color: rgba(192, 57, 43, 0.02);' : '';

            return `<tr style="cursor:pointer; transition:all 0.2s; ${rowStyle}" onclick="showDetails(${JSON.stringify(log).replace(/"/g, '&quot;')})" onmouseover="this.style.backgroundColor='rgba(108, 78, 49, 0.05)'" onmouseout="this.style.backgroundColor='${isCritical ? 'rgba(192, 57, 43, 0.02)' : 'transparent'}'">
                <td>
                    <div style="font-weight:700; color:var(--primary-brown); font-size:1.1rem;">${log.time_ago}</div>
                    <div style="font-size:0.85rem; color:var(--muted); margin-top:0.2rem;">${log.date_fmt} • ${log.time_fmt}</div>
                </td>
                <td>
                    <div style="display:flex; align-items:center; gap:0.8rem;">
                        <div style="width:36px; height:36px; background:var(--primary-brown); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:700; flex-shrink:0;">${initials}</div>
                        <div>
                            <div style="font-weight:600; font-size:1.1rem;">${log.user_name}</div>
                            <div style="font-size:0.85rem; color:var(--muted);">ID: ${log.user_id || 'N/A'}</div>
                        </div>
                    </div>
                </td>
                <td><span class="badge ${roleClass}" style="padding:0.4rem 0.8rem; font-size:0.85rem;">${roleLabel}</span></td>
                <td>
                    <div style="display:flex; align-items:center; gap:0.6rem;">
                        <span class="badge ${log.act_class}" style="padding:0.4rem 0.8rem; font-size:0.85rem; display:inline-flex; align-items:center; gap:0.4rem;">
                            <i class="fas ${log.act_icon}"></i> ${log.action}
                        </span>
                        <span style="font-size:0.85rem; color:var(--muted); font-weight:500; text-transform:uppercase; letter-spacing:0.5px;">[${log.module}]</span>
                    </div>
                    ${summaryHtml}
                </td>
                <td>
                    <div style="font-size:0.9rem; color:var(--primary-brown); font-weight:600; margin-bottom:0.4rem;"><i class="fas fa-network-wired" style="opacity:0.6;"></i> ${log.ip_address}</div>
                    <div style="display:flex; align-items:center; gap:0.6rem; opacity:0.8;">
                        <i class="${log.ua_icon}" style="font-size:1.2rem; color:var(--primary-brown);"></i>
                        <div>
                            <div style="font-size:0.85rem; font-weight:600;">${log.platform}</div>
                            <div style="font-size:0.75rem; color:var(--muted);">${log.browser}</div>
                        </div>
                    </div>
                </td>
            </tr>`;
        }).join('');

        // Pagination
        const total = data.total_pages || 1;
        const cur   = data.page || page;
        
        if (data.total_pages > 1 || logs.length > 0) {
            pag.style.display = 'flex';
            pag.className = 'pagination-container';
            
            let pagHtml = '';
            
            // Rows per page
            pagHtml += `<div class="pagination-rows">
                            <span>Rows per page:</span>
                            <select onchange="document.getElementById('limitFilter').value = this.value; loadLogs(1)">
                                <option value="5" ${limit == 5 ? 'selected' : ''}>5</option>
                                <option value="10" ${limit == 10 ? 'selected' : ''}>10</option>
                                <option value="25" ${limit == 25 ? 'selected' : ''}>25</option>
                                <option value="50" ${limit == 50 ? 'selected' : ''}>50</option>
                                <option value="100" ${limit == 100 ? 'selected' : ''}>100</option>
                            </select>
                        </div>`;

            pagHtml += `<div class="pagination-controls">`;

            // First page button
            pagHtml += `<button type="button" class="pag-btn" ${cur <= 1 ? 'disabled' : ''} onclick="loadLogs(1)" title="First Page">
                            <i class="fas fa-angle-double-left"></i>
                        </button>`;
            
            // Previous
            pagHtml += `<button type="button" class="pag-btn" ${cur <= 1 ? 'disabled' : ''} onclick="loadLogs(${cur - 1})" title="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </button>`;
            
            // Page Numbers
            let start = Math.max(1, cur - 1);
            let end = Math.min(total, start + 2);
            if (end - start < 2) start = Math.max(1, end - 2);

            if (start > 1) {
                pagHtml += `<button type="button" class="pag-btn" onclick="loadLogs(1)">1</button>`;
                if (start > 2) pagHtml += `<span class="pag-ellipsis">...</span>`;
            }

            for (let i = start; i <= end; i++) {
                const isActive = i === cur;
                pagHtml += `<button type="button" class="pag-btn ${isActive ? 'active' : ''}" onclick="loadLogs(${i})">${i}</button>`;
            }

            if (end < total) {
                if (end < total - 1) pagHtml += `<span class="pag-ellipsis">...</span>`;
                pagHtml += `<button type="button" class="pag-btn" onclick="loadLogs(${total})">${total}</button>`;
            }
            
            // Next
            pagHtml += `<button type="button" class="pag-btn" ${cur >= total ? 'disabled' : ''} onclick="loadLogs(${cur + 1})" title="Next">
                            <i class="fas fa-chevron-right"></i>
                        </button>`;

            // Last page button
            pagHtml += `<button type="button" class="pag-btn" ${cur >= total ? 'disabled' : ''} onclick="loadLogs(${total})" title="Last Page">
                            <i class="fas fa-angle-double-right"></i>
                        </button>`;
            
            pagHtml += `</div>`;
            
            pag.innerHTML = pagHtml;
        } else {
            pag.style.display = 'none';
        }
    })
    .catch(() => {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--danger);">Failed to load logs. Please try again.</td></tr>`;
    });
}

function showDetails(log) {
    const details = log.details || {};
    let rows = `<div style="background:#f9f5f0; padding:1.5rem; border-radius:8px; margin-bottom:1.5rem; border-left:4px solid var(--primary-brown);">
                    <div style="font-size:1.4rem; font-weight:700; color:var(--brown-dark); margin-bottom:0.5rem;">${log.action}</div>
                    <div style="color:var(--muted); font-size:0.9rem;">${log.date_fmt} at ${log.time_fmt} • Module: ${log.module}</div>
                </div>`;
                
    rows += `<h4 style="font-size:1.1rem; margin-bottom:1rem; color:var(--primary-brown); border-bottom:1px solid #ddd; padding-bottom:0.5rem;">Specific Data</h4>`;
    
    if (Object.keys(details).length === 0 || (Object.keys(details).length === 1 && details.timestamp)) {
        rows += `<p style="color:var(--muted); font-style:italic; padding:1rem;">No additional specific data for this action.</p>`;
    } else {
        rows += `<dl style="display:grid; grid-template-columns:140px 1fr; gap:1rem 2rem; padding:0 1rem;">`;
        for (const [k, v] of Object.entries(details)) {
            if (k === 'timestamp') continue;
            rows += `<dt style="font-weight:600; color:var(--muted); text-transform:capitalize;">${k.replace(/_/g, ' ')}</dt>
                     <dd style="color:var(--text); word-break:break-all;">${v}</dd>`;
        }
        rows += `</dl>`;
    }
    
    rows += `<h4 style="font-size:1.1rem; margin:2rem 0 1rem; color:var(--primary-brown); border-bottom:1px solid #ddd; padding-bottom:0.5rem;">System Information</h4>`;
    rows += `<dl style="display:grid; grid-template-columns:140px 1fr; gap:1rem 2rem; padding:0 1rem;">
                <dt style="font-weight:600; color:var(--muted);">User Name</dt><dd>${log.user_name}</dd>
                <dt style="font-weight:600; color:var(--muted);">User ID</dt><dd>${log.user_id || 'N/A'}</dd>
                <dt style="font-weight:600; color:var(--muted);">IP Address</dt><dd><code>${log.ip_address}</code></dd>
                <dt style="font-weight:600; color:var(--muted);">Platform</dt><dd>${log.platform}</dd>
                <dt style="font-weight:600; color:var(--muted);">Browser</dt><dd>${log.browser}</dd>
            </dl>`;

    document.getElementById('detailsTable').innerHTML = rows;
    openModal('detailsModal');
}

function exportLogs() {
    const mod    = document.getElementById('moduleFilter').value;
    const action = document.getElementById('actionFilter').value;
    const date   = document.getElementById('dateFilter').value;
    const search = document.getElementById('searchLogs').value;
    const params = new URLSearchParams({ module: mod, action, date, search });
    window.location.href = 'actions/export_logs.php?' + params.toString();
}


function selectRoleCard(card) {
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('roleModalSelect').value = card.getAttribute('data-role');
}

function requestRoleUpdate(userId, name, old) {
    document.getElementById('roleModalUserId').value = userId;
    document.getElementById('roleModalUser').textContent = `Updating designation for: ${name}`;
    
    // Select the card corresponding to the current role
    document.querySelectorAll('.role-card').forEach(c => {
        c.classList.remove('selected');
        if (c.getAttribute('data-role') === old) {
            c.classList.add('selected');
        }
    });
    document.getElementById('roleModalSelect').value = old;
    
    openModal('roleModal');
}

function confirmRoleUpdate() {
    const userId = document.getElementById('roleModalUserId').value;
    const next = document.getElementById('roleModalSelect').value;
    if (!next) { alert("Please select a role first."); return; }
    
    const fd = new FormData(); 
    fd.append('action','update_role'); 
    fd.append('target_user_id',userId); 
    fd.append('new_role',next);
    
    fetch('actions/manage_user.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(d=>{
        alert(d.message);
        if(d.status==='success') location.reload();
    });
}

// --- Custom Notification System ---
function showToast(msg, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle' };
    const icon = icons[type] || 'fa-info-circle';
    
    toast.innerHTML = `<i class="fas ${icon}"></i><div class="toast-msg">${msg}</div>`;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Override default Alert
window.alert = function(msg) {
    let type = 'success';
    if(msg.toLowerCase().includes('error') || msg.toLowerCase().includes('failed') || msg.toLowerCase().includes('unauthorized')) type = 'error';
    if(msg.toLowerCase().includes('invalid') || msg.toLowerCase().includes('requirements')) type = 'warning';
    showToast(msg, type);
};

let confirmPromiseResolve;
function showConfirm(title, message, icon = 'fa-question-circle') {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    document.querySelector('.confirm-icon i').className = `fas ${icon}`;
    document.getElementById('confirmOverlay').classList.add('show');
    
    return new Promise((resolve) => {
        confirmPromiseResolve = resolve;
        document.getElementById('confirmBtnYes').onclick = () => handleConfirm(true);
    });
}

function handleConfirm(value) {
    document.getElementById('confirmOverlay').classList.remove('show');
    if(confirmPromiseResolve) confirmPromiseResolve(value);
}

// Update existing functions to use async/await for confirmations
function requestDeleteUser(userId, name) {
    document.getElementById('deleteModalUser').textContent = "Deleting: " + name;
    document.getElementById('deleteModalUserId').value = userId;
    document.getElementById('deleteUserForm').reset();
    document.getElementById('fileNameDisplay').textContent = "";
    openModal('deleteConfirmModal');
}

function updateFileName(input) {
    const display = document.getElementById('fileNameDisplay');
    if (input.files && input.files[0]) {
        display.textContent = "Selected: " + input.files[0].name;
    } else {
        display.textContent = "";
    }
}

async function submitPermanentDeletion(e) {
    e.preventDefault();
    const btn = document.getElementById('deleteBtn');
    const originalContent = btn.innerHTML;
    
    if(!await showConfirm('Final Confirmation', 'Are you ABSOLUTELY sure? This cannot be undone and will be recorded with your provided documents.', 'fa-exclamation-triangle')) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    const formData = new FormData(e.target);
    fetch('actions/manage_user.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire('Deleted', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    })
    .catch(err => {
        Swal.fire('Error', 'An unexpected error occurred', 'error');
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
}

async function requestStatusUpdate(userId, action) {
    const isBlock = action === 'block_user';
    if(!await showConfirm(isBlock ? 'Block User' : 'Unblock User', `Are you sure you want to ${isBlock ? 'block' : 'unblock'} this user?`, isBlock ? 'fa-user-slash' : 'fa-user-check')) return;
    const fd = new FormData(); fd.append('action', action); fd.append('target_user_id', userId);
    fetch('actions/manage_user.php', { method:'POST', body:fd })
    .then(r => r.json()).then(d => { alert(d.message); if(d.status==='success') location.reload(); });
}

async function processApproval(id, act) {
    if(!await showConfirm('Confirm Approval', 'Do you want to process this user approval?', 'fa-check-circle')) return;
    const fd = new FormData(); fd.append('id',id); fd.append('action',act);
    fetch('actions/approve_user.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{alert(d.message);if(d.status==='success')location.reload();});
}

async function processPendingAction(id, dec) {
    const isApprove = dec === 'approve';
    if(!await showConfirm(isApprove ? 'Confirm Action' : 'Cancel Action', `Are you sure you want to ${isApprove ? 'confirm' : 'cancel'} this admin request?`, isApprove ? 'fa-shield-alt' : 'fa-times-circle')) return;
    const fd = new FormData(); fd.append('pending_action_id',id); fd.append('decision',dec);
    fetch('actions/approve_pending_action.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{alert(d.message);if(d.status==='success')location.reload();});
}

function submitAddStaff(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    
    // Loading State
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Invitation...';

    fetch('actions/add_staff.php', { method: 'POST', body: new FormData(e.target) })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            // Trigger Confetti
            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#6c4e31', '#FFEAC5', '#a67c52']
            });

            Swal.fire({
                icon: 'success',
                title: 'Successfully Added!',
                text: 'Invitation sent! The user can now set their password via email.',
                timer: 3500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert(d.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert("An error occurred. Please try again.");
    });
}
async function confirmLogout() { 
    if(await showConfirm('Logout', 'Are you sure you want to sign out?', 'fa-sign-out-alt')) window.location.href='actions/log-out.php'; 
}
document.addEventListener('DOMContentLoaded', () => { 
    const params = new URLSearchParams(window.location.search);
    for (const [key] of params) { if (pageNames[key]) { navigateTo(key, null); return; } }
    navigateTo('overview', null); 

    // Add password listener
    const passInp = document.getElementById('admin_new_pass');
    if(passInp) {
        passInp.addEventListener('input', function() {
            validateAdminPassword(this.value);
        });
    }
});

function openPrivilegesModal(adminId, fullName) {
    document.getElementById('privModalUser').textContent = "Set privileges for: " + fullName;
    document.getElementById('privModalAdminId').value = adminId;
    
    // Clear previous
    document.getElementById('priv_add_user').checked = false;
    document.getElementById('priv_block_user').checked = false;

    document.getElementById('priv_view_users').checked = false;
    
    openModal('privilegesModal');
    
    // Fetch current privs
    fetch(`actions/get_privileges.php?admin_id=${adminId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('priv_add_user').checked = data.privileges.can_add_user;
                document.getElementById('priv_block_user').checked = data.privileges.can_block_user;

                document.getElementById('priv_view_users').checked = data.privileges.can_view_users;
            }
        });
}

function saveAdminPrivileges() {
    const adminId = document.getElementById('privModalAdminId').value;
    const formData = new FormData();
    formData.append('admin_id', adminId);
    if (document.getElementById('priv_add_user').checked) formData.append('can_add_user', '1');
    if (document.getElementById('priv_block_user').checked) formData.append('can_block_user', '1');

    if (document.getElementById('priv_view_users').checked) formData.append('can_view_users', '1');
    
    fetch('actions/update_privileges.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', data.message, 'success');
            closeModal('privilegesModal');
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Something went wrong', 'error');
    });
}
</script>
</body>
</html>
