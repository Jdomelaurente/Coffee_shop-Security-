<?php
session_start();
// Security Check: Only allow 'admin'
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Coffee Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Additional styles for logs section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .filter-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .filter-select, .filter-date {
            padding: 0.8rem 1.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 1rem;
            font-size: 1.4rem;
            background: white;
            color: #333;
            cursor: pointer;
        }
        
        .filter-select:focus, .filter-date:focus {
            outline: none;
            border-color: #4a2c2a;
        }
        
        .admin-table-container {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            overflow-x: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 1.4rem;
        }
        
        .admin-table th {
            text-align: left;
            padding: 1.5rem 1rem;
            background: #f8f9fa;
            color: #2c1810;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        .admin-table td {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
            vertical-align: middle;
        }
        
        .admin-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 1.2rem;
            font-weight: 500;
            display: inline-block;
            text-transform: capitalize;
        }
        
        .badge-admin {
            background: #4a2c2a;
            color: #FFEAC5;
        }
        
        .badge-staff {
            background: #17a2b8;
            color: white;
        }
        
        .badge-inventory {
            background: #28a745;
            color: white;
        }
        
        .badge-transaction {
            background: #ffc107;
            color: #2c1810;
        }
        
        .badge-supervisor {
            background: #6f42c1;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }
        
        .page-btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 0.8rem;
            background: white;
            color: #4a2c2a;
            font-size: 1.4rem;
            font-weight: 500;
            cursor: pointer;
            border: 2px solid #4a2c2a;
            transition: all 0.3s;
        }
        
        .page-btn:hover {
            background: #4a2c2a;
            color: #FFEAC5;
        }
        
        .page-btn.active {
            background: #4a2c2a;
            color: #FFEAC5;
        }
        
        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .details-btn {
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 0.8rem;
            color: #495057;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .details-btn:hover {
            background: #e9ecef;
        }
        
        .modal-body table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .modal-body td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .modal-body td:first-child {
            font-weight: 600;
            color: #4a2c2a;
            width: 40%;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-mug-hot"></i>
            <span class="logo-text">Coffee<span class="admin-badge">ADMIN</span></span>
        </div>

        <nav class="nav-section">
            <div class="section-title">Dashboard</div>
            <ul class="nav-menu">
                <li class="nav-item active" onclick="navigateTo('overview')">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Overview</span>
                </li>
                <li class="nav-item" onclick="navigateTo('inventory')">
                    <i class="fas fa-boxes"></i>
                    <span class="nav-text">Inventory</span>
                </li>
                <li class="nav-item" onclick="navigateTo('staff')">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Staff Mgmt</span>
                </li>
                <li class="nav-item" onclick="navigateTo('reports')">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Reports</span>
                </li>
            </ul>
        </nav>

        <nav class="nav-section">
            <div class="section-title">System</div>
            <ul class="nav-menu">
                <li class="nav-item" onclick="navigateTo('transactions')">
                    <i class="fas fa-credit-card"></i>
                    <span class="nav-text">Transactions</span>
                </li>
                <li class="nav-item" onclick="navigateTo('logs')">
                    <i class="fas fa-history"></i>
                    <span class="nav-text">Activity Logs</span>
                </li>
                <li class="nav-item" onclick="navigateTo('settings')">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
       <!-- Top Bar -->
        <div class="topbar">
            <h2 class="topbar-title" id="pageTitle">Management Dashboard</h2>
            <div class="topbar-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php 
                        // Get first letter of first name and last name for avatar
                        $name_parts = explode(' ', $_SESSION['user_name'] ?? 'Admin');
                        $initials = '';
                        foreach ($name_parts as $part) {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                        echo substr($initials, 0, 2); // Show first 2 initials
                        ?>
                    </div>
                    <div>
                        <div class="user-name"><?php echo $_SESSION['user_name'] ?? 'Admin Panel'; ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">
            <!-- Overview Section -->
            <div id="overview-section" class="section-content">
                <div class="page-header">
                    <h1 class="page-title">Management Dashboard</h1>
                    <p class="page-subtitle">Welcome back, Administrator | <?php echo date('F j, Y'); ?></p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">₱ 12,450</div>
                        <div class="stat-label">Daily Revenue</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i> 8.2% vs yesterday
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">142</div>
                        <div class="stat-label">Total Transactions</div>
                        <div class="stat-change">
                            <i class="fas fa-coffee"></i> Peak hour: 9:00 AM
                        </div>
                    </div>
                    <div class="stat-card urgent">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Low Stock Items</div>
                        <div class="stat-change negative">
                            <i class="fas fa-exclamation-triangle"></i> Action Required
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Staff on Duty</div>
                        <div class="stat-change">
                            <i class="fas fa-user-check"></i> 0 Absences
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Management Section -->
            <div id="staff-section" style="display: none;">
                <h2 class="section-title-main">Staff Management</h2>
                <div class="table-container">
                    <div class="table-header">
                        <h3><i class="fas fa-users"></i> Staff Members</h3>
                        <button class="add-btn" onclick="openAddStaffModal()">
                            <i class="fas fa-plus"></i> Add Staff
                        </button>
                    </div>

                    <div class="queue-item">
                        <div class="queue-order">
                            <div class="queue-number"><i class="fas fa-user"></i></div>
                            <div class="queue-details">
                                <h3>Sarah Anderson</h3>
                                <p>Role: Staff • Joined: Jan 2026 • Status: Active</p>
                            </div>
                        </div>
                        <div class="queue-actions">
                            <button class="queue-btn secondary" onclick="editStaff('sarah')">Edit</button>
                            <button class="queue-btn danger" onclick="deactivateStaff('sarah')">Deactivate</button>
                        </div>
                    </div>

                    <div class="queue-item">
                        <div class="queue-order">
                            <div class="queue-number"><i class="fas fa-user"></i></div>
                            <div class="queue-details">
                                <h3>James Martinez</h3>
                                <p>Role: Staff • Joined: Feb 2026 • Status: Active</p>
                            </div>
                        </div>
                        <div class="queue-actions">
                            <button class="queue-btn secondary" onclick="editStaff('james')">Edit</button>
                            <button class="queue-btn danger" onclick="deactivateStaff('james')">Deactivate</button>
                        </div>
                    </div>

                    <div class="queue-item">
                        <div class="queue-order">
                            <div class="queue-number"><i class="fas fa-user"></i></div>
                            <div class="queue-details">
                                <h3>Emily Parks</h3>
                                <p>Role: Supervisor • Joined: Dec 2025 • Status: Active</p>
                            </div>
                        </div>
                        <div class="queue-actions">
                            <button class="queue-btn secondary" onclick="editStaff('emily')">Edit</button>
                            <button class="queue-btn danger" onclick="deactivateStaff('emily')">Deactivate</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Section -->
            <div id="inventory-section" style="display: none;">
                <h2 class="section-title-main">Inventory Management</h2>
                <div style="margin-bottom: 2rem;">
                    <button class="add-btn" style="width: auto;" onclick="openAddInventoryModal()">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>

                <table class="inventory-table">
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
                            <td>Arabica Coffee Beans</td>
                            <td>Coffee</td>
                            <td>45 kg</td>
                            <td>kg</td>
                            <td><span class="status-badge normal">Normal</span></td>
                            <td>
                                <button class="queue-btn secondary" style="padding: 0.5rem 1rem; font-size: 1.2rem;">Edit</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Whole Milk</td>
                            <td>Dairy</td>
                            <td>8 L</td>
                            <td>L</td>
                            <td><span class="status-badge low">Low Stock</span></td>
                            <td>
                                <button class="queue-btn secondary" style="padding: 0.5rem 1rem; font-size: 1.2rem;">Edit</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Sugar</td>
                            <td>Supplies</td>
                            <td>2 kg</td>
                            <td>kg</td>
                            <td><span class="status-badge critical">Critical</span></td>
                            <td>
                                <button class="queue-btn secondary" style="padding: 0.5rem 1rem; font-size: 1.2rem;">Edit</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Paper Cups (12oz)</td>
                            <td>Packaging</td>
                            <td>500 pcs</td>
                            <td>pcs</td>
                            <td><span class="status-badge normal">Normal</span></td>
                            <td>
                                <button class="queue-btn secondary" style="padding: 0.5rem 1rem; font-size: 1.2rem;">Edit</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Espresso Machine Oil</td>
                            <td>Maintenance</td>
                            <td>0.5 L</td>
                            <td>L</td>
                            <td><span class="status-badge low">Low Stock</span></td>
                            <td>
                                <button class="queue-btn secondary" style="padding: 0.5rem 1rem; font-size: 1.2rem;">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Reports Section -->
            <div id="reports-section" style="display: none;">
                <h2 class="section-title-main">Business Reports</h2>

                <div class="chart-container">
                    <h3 class="chart-title">Daily Revenue Trend</h3>
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-line"></i> Chart visualization would display here
                    </div>
                </div>

                <div class="chart-container">
                    <h3 class="chart-title">Top Selling Products</h3>
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-pie"></i> Chart visualization would display here
                    </div>
                </div>

                <div class="chart-container">
                    <h3 class="chart-title">Staff Performance</h3>
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-bar"></i> Chart visualization would display here
                    </div>
                </div>
            </div>

            <!-- Transactions Section -->
            <div id="transactions-section" style="display: none;">
                <h2 class="section-title-main">Recent Transactions</h2>
                <p style="font-size: 1.5rem; color: #888; padding: 3rem; background: white; border-radius: 1.5rem; text-align: center;">
                    Transactions module coming soon...
                </p>
            </div>

            <!-- Logs Section with Role Column -->
            <div id="logs-section" style="display: none;">
                <?php
                // Fetch real logs from database
                require_once 'log_functions.php';
                
                // Get filter parameters
                $filter_module = isset($_GET['module']) ? $_GET['module'] : '';
                $filter_date = isset($_GET['date']) ? $_GET['date'] : '';
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = 20;
                $offset = ($page - 1) * $limit;
                
                // Build filters
                $filters = [];
                if (!empty($filter_module)) {
                    $filters['module'] = $filter_module;
                }
                if (!empty($filter_date)) {
                    $filters['date_from'] = $filter_date . ' 00:00:00';
                    $filters['date_to'] = $filter_date . ' 23:59:59';
                }
                
                $logs_data = getActivityLogs($filters, $limit, $offset);
                
                // Safely extract data with defaults
                $logs = isset($logs_data['data']) ? $logs_data['data'] : [];
                $total_logs = isset($logs_data['total']) ? (int)$logs_data['total'] : 0;
                $total_pages = $total_logs > 0 ? ceil($total_logs / $limit) : 0;
                ?>
                
                <div class="section-header">
                    <div>
                        <h2 class="section-title-main">System Logs & Activity</h2>
                        <p class="page-subtitle">View all system activities and user actions</p>
                    </div>
                    <div class="filter-group">
                        <!-- Filter Dropdown -->
                        <select id="moduleFilter" class="filter-select" onchange="filterLogs()">
                            <option value="">All Modules</option>
                            <option value="Authentication" <?php echo $filter_module == 'Authentication' ? 'selected' : ''; ?>>Authentication</option>
                            <option value="Inventory" <?php echo $filter_module == 'Inventory' ? 'selected' : ''; ?>>Inventory</option>
                            <option value="Staff Mgmt" <?php echo $filter_module == 'Staff Mgmt' ? 'selected' : ''; ?>>Staff Mgmt</option>
                            <option value="Sales" <?php echo $filter_module == 'Sales' ? 'selected' : ''; ?>>Sales</option>
                            <option value="Reports" <?php echo $filter_module == 'Reports' ? 'selected' : ''; ?>>Reports</option>
                            <option value="System" <?php echo $filter_module == 'System' ? 'selected' : ''; ?>>System</option>
                        </select>
                        
                        <!-- Date Filter -->
                        <input type="date" id="dateFilter" class="filter-date" value="<?php echo htmlspecialchars($filter_date); ?>" onchange="filterLogs()">
                        
                        <!-- Export Button -->
                        <button class="add-btn" onclick="exportLogs()">
                            <i class="fas fa-download"></i> Export Logs
                        </button>
                    </div>
                </div>

                <div class="admin-table-container">
                    <table class="admin-table">
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
                                <td colspan="7" style="text-align: center; padding: 3rem; color: #888;">
                                    <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                    No logs found
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): 
                                    $details = isset($log['details']) ? json_decode($log['details'], true) : [];
                                    
                                    // Set module badge color
                                    $module_class = '';
                                    switch($log['module'] ?? '') {
                                        case 'Authentication':
                                            $module_class = 'badge-admin';
                                            break;
                                        case 'Inventory':
                                            $module_class = 'badge-inventory';
                                            break;
                                        case 'Sales':
                                            $module_class = 'badge-transaction';
                                            break;
                                        case 'Staff Mgmt':
                                            $module_class = 'badge-staff';
                                            break;
                                        default:
                                            $module_class = 'badge-admin';
                                    }
                                    
                                    // Set role badge color based on role
                                    $role_class = '';
                                    $role_display = '';
                                    switch(strtolower($log['user_role'] ?? '')) {
                                        case 'admin':
                                            $role_class = 'badge-admin';
                                            $role_display = 'Admin';
                                            break;
                                        case 'staff':
                                            $role_class = 'badge-staff';
                                            $role_display = 'Staff';
                                            break;
                                        case 'supervisor':
                                            $role_class = 'badge-supervisor';
                                            $role_display = 'Supervisor';
                                            break;
                                        case 'manager':
                                            $role_class = 'badge-inventory';
                                            $role_display = 'Manager';
                                            break;
                                        case 'system':
                                            $role_class = 'badge-admin';
                                            $role_display = 'System';
                                            break;
                                        default:
                                            $role_class = 'badge-admin';
                                            $role_display = ucfirst($log['user_role'] ?? 'Unknown');
                                    }
                                ?>
                                <tr>
                                    <td><strong><?php echo isset($log['created_at']) ? date('Y-m-d h:i A', strtotime($log['created_at'])) : 'N/A'; ?></strong></td>
                                    <td><?php echo htmlspecialchars($log['user_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $role_class; ?>">
                                            <?php echo $role_display; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['action'] ?? 'N/A'); ?></td>
                                    <td><span class="badge <?php echo $module_class; ?>"><?php echo htmlspecialchars($log['module'] ?? 'N/A'); ?></span></td>
                                    <td>
                                        <?php if (!empty($details)): ?>
                                        <button class="details-btn" onclick='showDetails(<?php echo json_encode($details); ?>)'>
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php else: ?>
                                        -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <button class="page-btn" onclick="goToPage(<?php echo $page - 1; ?>)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                    <button class="page-btn <?php echo $i == $page ? 'active' : ''; ?>" onclick="goToPage(<?php echo $i; ?>)">
                        <?php echo $i; ?>
                    </button>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <button class="page-btn" onclick="goToPage(<?php echo $page + 1; ?>)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Settings Section -->
            <div id="settings-section" style="display: none;">
                <h2 class="section-title-main">System Settings</h2>
                <p style="font-size: 1.5rem; color: #888; padding: 3rem; background: white; border-radius: 1.5rem; text-align: center;">
                    Settings module coming soon...
                </p>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <span>Activity Details</span>
                <button class="close-modal" onclick="closeModal('detailsModal')">&times;</button>
            </div>
            <div class="modal-body" id="detailsContent" style="padding: 2rem;">
                <!-- Details will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Add New Staff Member</span>
                <button class="close-modal" onclick="closeModal('addStaffModal')">&times;</button>
            </div>
            <form>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <select required>
                        <option value="">Select Position</option>
                        <option value="staff">Staff</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" placeholder="Enter phone number" required>
                </div>
                <button type="submit" class="modal-btn">Add Staff Member</button>
            </form>
        </div>
    </div>

    <!-- Add Inventory Modal -->
    <div id="addInventoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Add Inventory Item</span>
                <button class="close-modal" onclick="closeModal('addInventoryModal')">&times;</button>
            </div>
            <form>
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" placeholder="Enter product name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select required>
                        <option value="">Select Category</option>
                        <option value="coffee">Coffee</option>
                        <option value="dairy">Dairy</option>
                        <option value="supplies">Supplies</option>
                        <option value="packaging">Packaging</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" placeholder="Enter quantity" required>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <select required>
                        <option value="">Select Unit</option>
                        <option value="kg">kg</option>
                        <option value="l">L</option>
                        <option value="pcs">Pcs</option>
                    </select>
                </div>
                <button type="submit" class="modal-btn">Add Item</button>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let isSidebarCollapsed = false;
        let currentPage = 'overview';

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('active');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                isSidebarCollapsed = !isSidebarCollapsed;
            }
        }

        function navigateTo(page) {
            document.querySelectorAll('[id$="-section"]').forEach(section => {
                section.style.display = 'none';
            });

            const section = document.getElementById(page + '-section');
            if (section) {
                section.style.display = 'block';
            }

            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Find and mark the clicked nav item as active
            const navItems = document.querySelectorAll('.nav-item');
            for (let item of navItems) {
                if (item.getAttribute('onclick')?.includes(page)) {
                    item.classList.add('active');
                    break;
                }
            }

            const titles = {
                'overview': 'Management Dashboard',
                'inventory': 'Inventory Management',
                'staff': 'Staff Management',
                'reports': 'Business Reports',
                'transactions': 'Recent Transactions',
                'logs': 'System Logs & Activity',
                'settings': 'System Settings'
            };
            document.getElementById('pageTitle').textContent = titles[page] || 'Dashboard';
            currentPage = page;

            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.remove('active');
            }
        }

        function openAddStaffModal() {
            document.getElementById('addStaffModal').style.display = 'block';
        }

        function openAddInventoryModal() {
            document.getElementById('addInventoryModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function editStaff(staffId) {
            alert('Edit staff: ' + staffId);
        }

        function deactivateStaff(staffId) {
            if (confirm('Are you sure you want to deactivate this staff member?')) {
                alert('Staff ' + staffId + ' has been deactivated');
            }
        }

        function logout() {
            if (confirm('Are you sure you want to logout from Admin Panel?')) {
                window.location.href = 'log-out.php';
            }
        }

        function filterLogs() {
            const module = document.getElementById('moduleFilter').value;
            const date = document.getElementById('dateFilter').value;
            
            let url = window.location.pathname + '?logs';
            const params = new URLSearchParams();
            
            if (module) params.append('module', module);
            if (date) params.append('date', date);
            
            const queryString = params.toString();
            if (queryString) {
                url += '&' + queryString;
            }
            
            window.location.href = url;
        }

        function goToPage(page) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }

        function exportLogs() {
            const module = document.getElementById('moduleFilter').value;
            const date = document.getElementById('dateFilter').value;
            
            let url = 'export_logs.php';
            const params = [];
            if (module) params.push('module=' + encodeURIComponent(module));
            if (date) params.push('date=' + encodeURIComponent(date));
            
            if (params.length) url += '?' + params.join('&');
            
            window.location.href = url;
        }

        function showDetails(details) {
            let html = '<table style="width: 100%; border-collapse: collapse;">';
            for (let [key, value] of Object.entries(details)) {
                html += `
                    <tr>
                        <td style="padding: 1rem; font-weight: 600; border-bottom: 1px solid #e9ecef; color: #4a2c2a;">${key}:</td>
                        <td style="padding: 1rem; border-bottom: 1px solid #e9ecef;">${value}</td>
                    </tr>
                `;
            }
            html += '</table>';
            
            document.getElementById('detailsContent').innerHTML = html;
            document.getElementById('detailsModal').style.display = 'block';
        }

        window.addEventListener('resize', () => {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });

        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Check URL parameters to set active section
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('logs')) {
                navigateTo('logs');
            } else {
                document.querySelector('.nav-item').classList.add('active');
            }
        });
    </script>
</body>
</html>