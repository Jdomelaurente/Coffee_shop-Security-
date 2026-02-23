<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
require_once 'log_functions.php';

// Avatar initials
$name_parts = explode(' ', $_SESSION['user_name'] ?? 'Admin');
$initials = '';
foreach ($name_parts as $p) { $initials .= strtoupper(substr($p, 0, 1)); }
$initials = substr($initials, 0, 2);

// Logs – filter params
$filter_module = $_GET['module'] ?? '';
$filter_date   = $_GET['date']   ?? '';
$page_num      = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit         = 20;
$offset        = ($page_num - 1) * $limit;

$filters = [];
if ($filter_module) $filters['module'] = $filter_module;
if ($filter_date)   { $filters['date_from'] = $filter_date . ' 00:00:00'; $filters['date_to'] = $filter_date . ' 23:59:59'; }

$logs_data   = getActivityLogs($filters, $limit, $offset);
$logs        = $logs_data['data']  ?? [];
$total_logs  = (int)($logs_data['total'] ?? 0);
$total_pages = $total_logs > 0 ? (int)ceil($total_logs / $limit) : 0;

// Role display helper
function roleDisplay($role) {
    $map = ['admin'=>['Admin','badge-admin'], 'user'=>['User','badge-user'], 'staff'=>['User','badge-user'],
            'supervisor'=>['Supervisor','badge-supervisor'], 'manager'=>['Manager','badge-inventory'], 'system'=>['System','badge-admin']];
    $r = strtolower($role ?? '');
    return $map[$r] ?? [ucfirst($role ?: 'Unknown'), 'badge-admin'];
}

// Module badge helper
function moduleBadge($mod) {
    $map = ['Authentication'=>'badge-admin','Inventory'=>'badge-inventory','Sales'=>'badge-transaction','User Mgmt'=>'badge-user'];
    return $map[$mod] ?? 'badge-admin';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – Coffee Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<!-- ======== SIDEBAR BACKDROP (mobile) ======== -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeMobileSidebar()"></div>

<!-- ======== SIDEBAR ======== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-mug-hot"></i>
        <span class="logo-text">Brew<span>ADMIN</span></span>
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
            <i class="fas fa-users-cog"></i><span class="nav-text">Staff Management</span>
        </div>
        <div class="nav-link" onclick="navigateTo('reports',this)" id="nl-reports">
            <i class="fas fa-chart-bar"></i><span class="nav-text">Reports</span>
        </div>

        <div class="nav-label">System</div>
        <div class="nav-link" onclick="navigateTo('transactions',this)" id="nl-transactions">
            <i class="fas fa-credit-card"></i><span class="nav-text">Transactions</span>
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

<!-- ======== MAIN CONTENT ======== -->
<div class="main-content" id="mainContent">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="mobile-menu-btn" onclick="openMobileSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="page-breadcrumb">
                <i class="fas fa-mug-hot" style="color:var(--brown-light)"></i>
                <span>Coffee Shop</span>
                <i class="fas fa-chevron-right" style="font-size:1rem"></i>
                <span class="current" id="pageTitle">Overview</span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="topbar-date">
                <i class="fas fa-calendar-alt"></i>
                <?php echo date('F j, Y'); ?>
            </div>
            <div class="user-chip">
                <div class="user-avatar"><?php echo $initials; ?></div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></div>
                    <div class="user-role-lbl">Administrator</div>
                </div>
            </div>
            <button class="logout-btn" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </header>

    <!-- CONTENT AREA -->
    <div class="content-area">

        <!-- ===== OVERVIEW ===== -->
        <div id="overview-section" class="section-view active">
            <div class="page-hdr">
                <div>
                    <div class="page-hdr-title">Management Dashboard</div>
                    <div class="page-hdr-sub">Welcome back, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Admin')[0]); ?>! Here's what's happening today.</div>
                </div>
                <button class="btn btn-primary" onclick="navigateTo('reports', document.getElementById('nl-reports'))">
                    <i class="fas fa-chart-line"></i> View Reports
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
                        <div class="stat-label">Transactions Today</div>
                        <div class="stat-change neutral"><i class="fas fa-clock"></i> Peak: 9:00 AM</div>
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
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;flex-wrap:wrap;" id="overviewCols">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-bolt"></i> Quick Actions</div>
                    </div>
                    <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <button class="btn btn-primary" style="justify-content:center;" onclick="navigateTo('inventory',document.getElementById('nl-inventory'))">
                            <i class="fas fa-boxes"></i> Inventory
                        </button>
                        <button class="btn btn-primary" style="justify-content:center; background-color: var(--brown-dark);" onclick="openModal('addUserModal')">
                            <i class="fas fa-user-plus"></i> Add Staff
                        </button>
                        <button class="btn btn-ghost" style="justify-content:center;" onclick="navigateTo('user',document.getElementById('nl-user'))">
                            <i class="fas fa-users"></i> Users
                        </button>
                        <button class="btn btn-ghost" style="justify-content:center;" onclick="navigateTo('logs',document.getElementById('nl-logs'))">
                            <i class="fas fa-history"></i> View Logs
                        </button>
                        <button class="btn btn-success" style="justify-content:center;" onclick="navigateTo('reports',document.getElementById('nl-reports'))">
                            <i class="fas fa-chart-pie"></i> Reports
                        </button>
                    </div>
                </div>

                <!-- Recent activity feed -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-stream"></i> Recent Activity</div>
                        <button class="btn btn-ghost btn-sm" onclick="navigateTo('logs',document.getElementById('nl-logs'))">View All</button>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent = getActivityLogs([], 5, 0);
                        if (!empty($recent['data'])):
                            foreach ($recent['data'] as $ra):
                                $dot_cls = str_contains(strtolower($ra['action'] ?? ''), 'login') ? 'success'
                                         : (str_contains(strtolower($ra['action'] ?? ''), 'fail') ? 'danger' : '');
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
                        <div style="text-align:center;color:var(--muted);padding:3rem;font-size:1.4rem;">No recent activity</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== INVENTORY ===== -->
        <div id="inventory-section" class="section-view">
            <div class="page-hdr">
                <div>
                    <div class="page-hdr-title">Inventory Management</div>
                    <div class="page-hdr-sub">Monitor and manage your stock levels</div>
                </div>
                <button class="btn btn-primary" onclick="openModal('addInventoryModal')">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-boxes"></i> Stock Items</div>
                    <div style="display:flex;gap:.8rem;flex-wrap:wrap;">
                        <span class="badge badge-danger">2 Low Stock</span>
                        <span class="badge badge-danger" style="background:#FFEBEE;">1 Critical</span>
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Arabica Coffee Beans</strong></td>
                                <td>Coffee</td>
                                <td>45</td>
                                <td>kg</td>
                                <td><span class="status-pill normal">Normal</span></td>
                                <td><button class="btn btn-ghost btn-sm"><i class="fas fa-edit"></i> Edit</button></td>
                            </tr>
                            <tr>
                                <td><strong>Whole Milk</strong></td>
                                <td>Dairy</td>
                                <td>8</td>
                                <td>L</td>
                                <td><span class="status-pill low">Low Stock</span></td>
                                <td><button class="btn btn-ghost btn-sm"><i class="fas fa-edit"></i> Edit</button></td>
                            </tr>
                            <tr>
                                <td><strong>Sugar</strong></td>
                                <td>Supplies</td>
                                <td>2</td>
                                <td>kg</td>
                                <td><span class="status-pill critical">Critical</span></td>
                                <td><button class="btn btn-ghost btn-sm"><i class="fas fa-edit"></i> Edit</button></td>
                            </tr>
                            <tr>
                                <td><strong>Paper Cups (12oz)</strong></td>
                                <td>Packaging</td>
                                <td>500</td>
                                <td>pcs</td>
                                <td><span class="status-pill normal">Normal</span></td>
                                <td><button class="btn btn-ghost btn-sm"><i class="fas fa-edit"></i> Edit</button></td>
                            </tr>
                            <tr>
                                <td><strong>Espresso Machine Oil</strong></td>
                                <td>Maintenance</td>
                                <td>0.5</td>
                                <td>L</td>
                                <td><span class="status-pill low">Low Stock</span></td>
                                <td><button class="btn btn-ghost btn-sm"><i class="fas fa-edit"></i> Edit</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===== USER MANAGEMENT ===== -->
        <div id="user-section" class="section-view">
            <div class="page-hdr">
                <div>
                    <div class="page-hdr-title">Staff Management</div>
                    <div class="page-hdr-sub">Manage staff accounts, roles, and system access</div>
                </div>
                <button class="btn btn-primary" onclick="openModal('addUserModal')">
                    <i class="fas fa-user-plus"></i> Add New Staff
                </button>
            </div>

            <div class="user-grid">
                <?php
                try {
                    $stmt = $conn->prepare("SELECT first_name, last_name, role, created_at FROM users WHERE role != 'user' ORDER BY role, last_name");
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($users)) {
                        echo '<div style="text-align:center;color:var(--muted);padding:3rem;font-size:1.4rem;grid-column:1/-1;">No users found</div>';
                    } else {
                        foreach ($users as $u):
                            $fullname = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                            if (empty($fullname)) $fullname = "Unknown User";
                            
                            $av = '';
                            foreach (explode(' ', $fullname) as $np) {
                                if (!empty($np)) $av .= strtoupper(substr($np,0,1));
                            }
                            $av = substr($av, 0, 2);
                            if (empty($av)) $av = '??';

                            $role = strtolower($u['role'] ?? 'user');
                            [$role_display, $badge_cls] = roleDisplay($role);
                            $joined = isset($u['created_at']) ? date('M Y', strtotime($u['created_at'])) : 'Unknown';
                ?>
                <div class="user-card">
                    <div class="user-card-avatar"><?php echo $av; ?></div>
                    <div class="user-card-info">
                        <div class="user-card-name"><?php echo htmlspecialchars($fullname); ?></div>
                        <div class="user-card-meta">
                            <span class="badge <?php echo $badge_cls; ?>"><?php echo $role_display; ?></span>
                            &nbsp;Joined <?php echo $joined; ?>
                        </div>
                    </div>
                    <div class="user-card-actions">
                        <button class="btn btn-ghost btn-sm" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm" title="Deactivate"><i class="fas fa-user-slash"></i></button>
                    </div>
                </div>
                <?php 
                        endforeach;
                    }
                } catch (Exception $e) {
                    echo '<div style="text-align:center;color:var(--muted);padding:3rem;font-size:1.4rem;grid-column:1/-1;">Error loading users</div>';
                }
                ?>
            </div>
        </div>

        <!-- ===== REPORTS ===== -->
        <div id="reports-section" class="section-view">
            <div class="page-hdr">
                <div>
                    <div class="page-hdr-title">Business Reports</div>
                    <div class="page-hdr-sub">Analytics and performance overview</div>
                </div>
                <button class="btn btn-primary"><i class="fas fa-download"></i> Export PDF</button>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;" id="reportsCols">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-chart-line"></i> Daily Revenue Trend</div>
                    </div>
                    <div class="card-body">
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-line"></i>
                            <span>Chart visualization coming soon</span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-chart-pie"></i> Top Selling Products</div>
                    </div>
                    <div class="card-body">
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-pie"></i>
                            <span>Chart visualization coming soon</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" style="margin-top:0;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-chart-bar"></i> Staff Performance</div>
                </div>
                <div class="card-body">
                    <div class="chart-placeholder" style="height:180px;">
                        <i class="fas fa-chart-bar"></i>
                        <span>Chart visualization coming soon</span>
                    </div>
                </div>
            </div>
            <!-- ===== PENDING APPROVALS ===== -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-clock"></i> Pending User Approvals</div>
                </div>
                <div class="table-container">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>ID Number</th>
                                <th>Contact</th>
                                <th>Registered At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $conn->prepare("SELECT id, first_name, last_name, email, id_number, contact, created_at FROM users WHERE role = 'user' AND status = 'pending' ORDER BY created_at DESC");
                                $stmt->execute();
                                $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (empty($pending)) {
                                    echo '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">No pending approvals found.</td></tr>';
                                } else {
                                    foreach ($pending as $p):
                                        $pname = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                                        echo "<tr>
                                            <td><strong>" . htmlspecialchars($pname) . "</strong></td>
                                            <td>" . htmlspecialchars($p['email']) . "</td>
                                            <td>" . htmlspecialchars($p['id_number']) . "</td>
                                            <td>" . htmlspecialchars($p['contact']) . "</td>
                                            <td>" . date('M j, Y H:i', strtotime($p['created_at'])) . "</td>
                                            <td>
                                                <div style='display:flex;gap:0.5rem;'>
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
        </div>

        <!-- ===== TRANSACTIONS ===== -->
        <div id="transactions-section" class="section-view">
            <div class="page-hdr">
                <div>
                    <div class="page-hdr-title">Transactions</div>
                    <div class="page-hdr-sub">View all sales and payment records</div>
                </div>
            </div>
            <div class="coming-soon">
                <i class="fas fa-credit-card"></i>
                <h3>Transactions Module</h3>
                <p>This module is under development and will be available soon.</p>
            </div>
        </div>

        <!-- ===== ACTIVITY LOGS ===== -->
        <div id="logs-section" class="section-view">
            <div class="page-hdr">
                <div>
                    <div class="page-hdr-title">Activity Logs</div>
                    <div class="page-hdr-sub">View all system activities and user actions</div>
                </div>
                <button class="btn btn-primary" onclick="exportLogs()">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </div>

            <!-- Filter row -->
            <div class="filter-row">
                <select id="moduleFilter" class="filter-control" onchange="filterLogs()">
                    <option value="">All Modules</option>
                    <?php foreach (['Authentication','Inventory','User Mgmt','Sales','Reports','System'] as $mod): ?>
                    <option value="<?php echo $mod; ?>" <?php echo $filter_module === $mod ? 'selected' : ''; ?>><?php echo $mod; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" id="dateFilter" class="filter-control" value="<?php echo htmlspecialchars($filter_date); ?>" onchange="filterLogs()">
                <?php if ($filter_module || $filter_date): ?>
                <button class="btn btn-ghost btn-sm" onclick="clearFilters()"><i class="fas fa-times"></i> Clear</button>
                <?php endif; ?>
                <span style="font-size:1.3rem;color:var(--muted);margin-left:auto;"><?php echo $total_logs; ?> record<?php echo $total_logs !== 1 ? 's' : ''; ?></span>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;padding:4rem;color:var(--muted);">
                                    <div style="font-size:3rem;margin-bottom:1rem;"><i class="fas fa-inbox"></i></div>
                                    No logs found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log):
                                $details = isset($log['details']) ? json_decode($log['details'], true) : [];
                                [$role_display, $role_class] = roleDisplay($log['user_role'] ?? '');
                                $mod_class = moduleBadge($log['module'] ?? '');
                            ?>
                            <tr>
                                <td style="white-space:nowrap; font-size:1.2rem;">
                                    <strong><?php echo isset($log['created_at']) ? date('Y-m-d', strtotime($log['created_at'])) : 'N/A'; ?></strong><br>
                                    <span style="color:var(--muted)"><?php echo isset($log['created_at']) ? date('h:i A', strtotime($log['created_at'])) : ''; ?></span>
                                </td>
                                <td>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($log['user_name'] ?? 'N/A'); ?></div>
                                    <div style="font-size:1.15rem;color:var(--muted);"><?php echo htmlspecialchars($log['user_id'] ?? ''); ?></div>
                                </td>
                                <td><span class="badge <?php echo $role_class; ?>"><?php echo $role_display; ?></span></td>
                                <td><?php echo htmlspecialchars($log['action'] ?? 'N/A'); ?></td>
                                <td><span class="badge <?php echo $mod_class; ?>"><?php echo htmlspecialchars($log['module'] ?? 'N/A'); ?></span></td>
                                <td>
                                    <?php if (!empty($details)): ?>
                                    <button class="details-btn" onclick='showDetails(<?php echo json_encode($details); ?>)'>
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <?php else: ?>
                                    <span style="color:var(--muted)">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page_num > 1): ?>
                <button class="page-btn" onclick="goToPage(<?php echo $page_num - 1; ?>)"><i class="fas fa-chevron-left"></i></button>
                <?php endif; ?>
                <?php for ($i = max(1,$page_num-2); $i <= min($total_pages,$page_num+2); $i++): ?>
                <button class="page-btn <?php echo $i === $page_num ? 'active' : ''; ?>" onclick="goToPage(<?php echo $i; ?>)"><?php echo $i; ?></button>
                <?php endfor; ?>
                <?php if ($page_num < $total_pages): ?>
                <button class="page-btn" onclick="goToPage(<?php echo $page_num + 1; ?>)"><i class="fas fa-chevron-right"></i></button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== SETTINGS ===== -->
        <div id="settings-section" class="section-view">
            <div class="page-hdr">
                <div>
                    <div class="page-hdr-title">System Settings</div>
                    <div class="page-hdr-sub">Configure your shop settings</div>
                </div>
            </div>
            <div class="coming-soon">
                <i class="fas fa-cog"></i>
                <h3>Settings Module</h3>
                <p>This module is under development and will be available soon.</p>
            </div>
        </div>

    </div><!-- /content-area -->
</div><!-- /main-content -->

<!-- ===== MODALS ===== -->

<!-- Details Modal -->
<div class="modal-overlay" id="detailsModal">
    <div class="modal-box" style="max-width:440px;">
        <div class="modal-head">
            <h3><i class="fas fa-info-circle"></i> Activity Details</h3>
            <button class="modal-close" onclick="closeModal('detailsModal')">✕</button>
        </div>
        <div class="modal-body">
            <table class="detail-table" id="detailsTable"></table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-head">
            <h3><i class="fas fa-user-plus"></i> Add New Staff Account</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">✕</button>
        </div>
        <div class="modal-body">
            <form id="addStaffForm" onsubmit="submitAddStaff(event)">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.2rem;">
                    <div class="form-group">
                        <label>ID Number</label>
                        <input type="text" name="id_number" class="form-control" placeholder="0000-0000" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="staff@example.com" required>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.2rem;">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middleName" class="form-control">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr; gap: 1.2rem;">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.2rem;">
                    <div class="form-group">
                        <label>Sex</label>
                        <select name="sex" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contact</label>
                        <input type="text" name="contact" class="form-control" placeholder="09xxxxxxxxx" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" class="form-control" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal-overlay" id="addInventoryModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-boxes"></i> Add Inventory Item</h3>
            <button class="modal-close" onclick="closeModal('addInventoryModal')">✕</button>
        </div>
        <div class="modal-body">
            <form onsubmit="return false;">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" class="form-control" placeholder="Enter product name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select class="form-control" required>
                        <option value="">Select Category</option>
                        <option>Coffee</option>
                        <option>Dairy</option>
                        <option>Supplies</option>
                        <option>Packaging</option>
                        <option>Maintenance</option>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" class="form-control" placeholder="0" required>
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <select class="form-control" required>
                            <option value="">Unit</option>
                            <option>kg</option>
                            <option>L</option>
                            <option>pcs</option>
                            <option>boxes</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('addInventoryModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== JS ===== -->
<script>
// ---- SIDEBAR ----
let sidebarCollapsed = false;

function toggleSidebar() {
    sidebarCollapsed = !sidebarCollapsed;
    const sidebar = document.getElementById('sidebar');
    const main    = document.getElementById('mainContent');
    const icon    = document.getElementById('toggleIcon');

    sidebar.classList.toggle('collapsed', sidebarCollapsed);
    main.classList.toggle('expanded', sidebarCollapsed);
    icon.className = sidebarCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
}

function openMobileSidebar() {
    document.getElementById('sidebar').classList.add('mobile-open');
    document.getElementById('sidebarBackdrop').classList.add('show');
}

function closeMobileSidebar() {
    document.getElementById('sidebar').classList.remove('mobile-open');
    document.getElementById('sidebarBackdrop').classList.remove('show');
}

// ---- NAVIGATION ----
const pageNames = {
    overview:'Overview', inventory:'Inventory Management', user:'User Management',
    reports:'Reports', transactions:'Transactions', logs:'Activity Logs', settings:'Settings'
};

function navigateTo(page, linkEl) {
    // Hide all sections
    document.querySelectorAll('.section-view').forEach(s => s.classList.remove('active'));
    // Show target
    const sec = document.getElementById(page + '-section');
    if (sec) sec.classList.add('active');

    // Update nav links
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    if (linkEl) linkEl.classList.add('active');
    else {
        const nl = document.getElementById('nl-' + page);
        if (nl) nl.classList.add('active');
    }

    // Update breadcrumb
    document.getElementById('pageTitle').textContent = pageNames[page] || page;

    // Mobile — close sidebar
    closeMobileSidebar();

    // Update URL without refreshing
    const url = new URL(window.location.href);
    // Remove existing section flags
    Object.keys(pageNames).forEach(p => url.searchParams.delete(p));
    // Add new section flag (except for overview which is default)
    if (page !== 'overview') {
        url.searchParams.set(page, '');
    }
    window.history.pushState({page: page}, '', url.toString());
}

// ---- MODALS ----
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Close on backdrop click
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

// ---- LOG DETAIL ----
function showDetails(details) {
    const roleMap = { staff:'User', user:'User', admin:'Admin', supervisor:'Supervisor', manager:'Manager' };
    const roleKeys = ['role', 'user_role'];

    let rows = '';
    for (const [k, v] of Object.entries(details)) {
        let display = v;
        if (roleKeys.includes(k) && roleMap[String(v).toLowerCase()]) display = roleMap[String(v).toLowerCase()];
        rows += `<tr><td>${k}</td><td>${display}</td></tr>`;
    }
    document.getElementById('detailsTable').innerHTML = rows;
    openModal('detailsModal');
}

// ---- LOG FILTERS ----
function filterLogs() {
    const module = document.getElementById('moduleFilter').value;
    const date   = document.getElementById('dateFilter').value;
    let url = window.location.pathname + '?logs';
    const p = new URLSearchParams();
    if (module) p.append('module', module);
    if (date)   p.append('date', date);
    if (p.toString()) url += '&' + p.toString();
    window.location.href = url;
}

function clearFilters() {
    window.location.href = window.location.pathname + '?logs';
}

function goToPage(pg) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', pg);
    // Ensure the section flag (e.g., 'logs') is present
    const activeSection = document.querySelector('.section-view.active').id.replace('-section', '');
    if (activeSection !== 'overview' && !url.searchParams.has(activeSection)) {
        url.searchParams.set(activeSection, '');
    }
    window.location.href = url.toString();
}

function exportLogs() {
    const module = document.getElementById('moduleFilter').value;
    const date   = document.getElementById('dateFilter').value;
    let url = 'export_logs.php';
    const p = [];
    if (module) p.push('module=' + encodeURIComponent(module));
    if (date)   p.push('date='   + encodeURIComponent(date));
    if (p.length) url += '?' + p.join('&');
    window.location.href = url;
}

// ---- LOGOUT ----
function confirmLogout() {
    if (confirm('Are you sure you want to logout from the Admin Panel?')) {
        window.location.href = 'log-out.php';
    }
}

// ---- ADD STAFF ----
function submitAddStaff(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('add_staff.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload(); // Refresh to show new user
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}

// ---- APPROVALS ----
function processApproval(userId, action) {
    const confirmMsg = action === 'approve' ? 'Approve this user account?' : 'Reject and delete this registration?';
    if (!confirm(confirmMsg)) return;

    const formData = new FormData();
    formData.append('id', userId);
    formData.append('action', action);

    fetch('approve_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}

// ---- INIT ----
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    let found = false;
    for (const [key] of params) {
        if (pageNames[key]) {
            navigateTo(key, null);
            found = true;
            break;
        }
    }
    if (!found) navigateTo('overview', null);
});

// Handle browser back/forward
window.onpopstate = function(event) {
    if (event.state && event.state.page) {
        navigateTo(event.state.page, null);
    }
};
</script>
</body>
</html>