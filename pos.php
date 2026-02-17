<?php
session_start();
// Security Check: Only allow 'admin'
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Coffee Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/pos.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-mug-hot"></i>
            <span class="logo-text">Coffee</span>
        </div>

        <nav class="nav-section">
            <div class="section-title">Main</div>
            <ul class="nav-menu">
                <li class="nav-item active" onclick="navigateTo('dashboard')">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Dashboard</span>
                </li>
                <li class="nav-item" onclick="navigateTo('tasks')">
                    <i class="fas fa-tasks"></i>
                    <span class="nav-text">Today's Tasks</span>
                </li>
                <li class="nav-item" onclick="navigateTo('shifts')">
                    <i class="fas fa-clock"></i>
                    <span class="nav-text">Shifts</span>
                </li>
                <li class="nav-item" onclick="navigateTo('queue')">
                    <i class="fas fa-list"></i>
                    <span class="nav-text">Order Queue</span>
                </li>
            </ul>
        </nav>

        <nav class="nav-section">
            <div class="section-title">Management</div>
            <ul class="nav-menu">
                <li class="nav-item" onclick="navigateTo('inventory')">
                    <i class="fas fa-warehouse"></i>
                    <span class="nav-text">Inventory</span>
                </li>
                <li class="nav-item" onclick="navigateTo('reports')">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Reports</span>
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
            <h2 class="topbar-title" id="pageTitle"> Dashboard</h2>
            <div class="topbar-right">
                <div class="user-info">
                    <div class="user-avatar">SA</div>
                    <div>
                        <div class="user-name">Sarah Anderson</div>
                        <div class="user-role">Barista</div>
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
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="dashboard-section">
                <div class="page-header">
                    <h1 class="page-title">Staff Dashboard</h1>
                    <p class="page-subtitle">Morning Shift • 06:00 AM - 02:00 PM</p>
                </div>

                <!-- Quick Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">24</div>
                        <div class="stat-label">Orders Today</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i> 12.5%
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">8</div>
                        <div class="stat-label">Team Members</div>
                        <div class="stat-change">
                            <i class="fas fa-check-circle"></i> All Present
                        </div>
                    </div>
                    <div class="stat-card urgent">
                        <div class="stat-number">12</div>
                        <div class="stat-label">In Queue</div>
                        <div class="stat-change negative">
                            <i class="fas fa-arrow-up"></i> +5
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-number">94%</div>
                        <div class="stat-label">Customer Satisfaction</div>
                        <div class="stat-change">
                            <i class="fas fa-star"></i> Excellent
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Section -->
            <div id="tasks-section" style="display: none;">
                <h2 class="section-title-main">Today's Tasks</h2>
                <div class="tasks-grid">
                    <div class="task-card">
                        <div class="task-icon"><i class="fas fa-check-circle"></i></div>
                        <h3 class="task-title">Stock Verification</h3>
                        <p class="task-description">Verify coffee beans and supplies inventory levels</p>
                        <div class="task-meta">
                            <span class="task-time">08:00 AM</span>
                            <span class="task-priority low">Low</span>
                        </div>
                    </div>

                    <div class="task-card urgent">
                        <div class="task-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <h3 class="task-title">Machine Maintenance</h3>
                        <p class="task-description">Clean espresso machine filters and group heads</p>
                        <div class="task-meta">
                            <span class="task-time">09:30 AM</span>
                            <span class="task-priority">Urgent</span>
                        </div>
                    </div>

                    <div class="task-card completed">
                        <div class="task-icon"><i class="fas fa-check"></i></div>
                        <h3 class="task-title">Station Setup</h3>
                        <p class="task-description">All workstations prepared and tested</p>
                        <div class="task-meta">
                            <span class="task-time">06:45 AM</span>
                            <span class="task-priority low">Completed</span>
                        </div>
                    </div>

                    <div class="task-card">
                        <div class="task-icon"><i class="fas fa-users"></i></div>
                        <h3 class="task-title">Team Briefing</h3>
                        <p class="task-description">Daily updates and shift priorities meeting</p>
                        <div class="task-meta">
                            <span class="task-time">11:00 AM</span>
                            <span class="task-priority normal">Medium</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shifts Section -->
            <div id="shifts-section" style="display: none;">
                <h2 class="section-title-main">Today's Shifts</h2>
                <div class="shifts-grid">
                    <div class="shift-card">
                        <div class="shift-header">
                            <div class="shift-time">06:00 - 14:00</div>
                            <div class="shift-badge active">Active</div>
                        </div>
                        <div class="shift-info">
                            <div class="shift-label">Morning Shift</div>
                            <div class="shift-detail">Barista Team</div>
                        </div>
                        <div>
                            <div class="shift-label">Staff Assigned (3)</div>
                            <ul class="staff-list">
                                <li>
                                    <div class="staff-avatar-sm">SA</div>
                                    <span>Sarah Anderson <strong>(You)</strong></span>
                                </li>
                                <li>
                                    <div class="staff-avatar-sm">JM</div>
                                    <span>James Martinez</span>
                                </li>
                                <li>
                                    <div class="staff-avatar-sm">EP</div>
                                    <span>Emily Parks</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="shift-card">
                        <div class="shift-header">
                            <div class="shift-time">14:00 - 22:00</div>
                            <div class="shift-badge">Upcoming</div>
                        </div>
                        <div class="shift-info">
                            <div class="shift-label">Afternoon Shift</div>
                            <div class="shift-detail">Service Team</div>
                        </div>
                        <div>
                            <div class="shift-label">Staff Assigned (3)</div>
                            <ul class="staff-list">
                                <li>
                                    <div class="staff-avatar-sm">RP</div>
                                    <span>Rachel Pearl</span>
                                </li>
                                <li>
                                    <div class="staff-avatar-sm">MK</div>
                                    <span>Marcus Kim</span>
                                </li>
                                <li>
                                    <div class="staff-avatar-sm">LS</div>
                                    <span>Lisa Santos</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="shift-card">
                        <div class="shift-header">
                            <div class="shift-time">22:00 - 06:00</div>
                            <div class="shift-badge">Upcoming</div>
                        </div>
                        <div class="shift-info">
                            <div class="shift-label">Night Shift</div>
                            <div class="shift-detail">Closing Team</div>
                        </div>
                        <div>
                            <div class="shift-label">Staff Assigned (2)</div>
                            <ul class="staff-list">
                                <li>
                                    <div class="staff-avatar-sm">CT</div>
                                    <span>Carlos Torres</span>
                                </li>
                                <li>
                                    <div class="staff-avatar-sm">NK</div>
                                    <span>Nina Kusama</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Queue Section -->
            <div id="queue-section" style="display: none;">
                <h2 class="section-title-main">Order Queue</h2>
                <div class="queue-container">
                    <div class="queue-item">
                        <div class="queue-order">
                            <div class="queue-number">01</div>
                            <div class="queue-details">
                                <h3>Caramel Macchiato x2</h3>
                                <p>Counter Order • Extra shot, Extra foam</p>
                            </div>
                        </div>
                        <div class="queue-actions">
                            <div class="wait-time">3 min</div>
                            <button class="queue-btn" onclick="markReady(this)">Ready</button>
                        </div>
                    </div>

                    <div class="queue-item">
                        <div class="queue-order">
                            <div class="queue-number">02</div>
                            <div class="queue-details">
                                <h3>Americano + Iced Latte</h3>
                                <p>Table 5 • No ice, Oat milk</p>
                            </div>
                        </div>
                        <div class="queue-actions">
                            <div class="wait-time">5 min</div>
                            <button class="queue-btn" onclick="markReady(this)">Ready</button>
                        </div>
                    </div>

                    <div class="queue-item">
                        <div class="queue-order">
                            <div class="queue-number">03</div>
                            <div class="queue-details">
                                <h3>Cappuccino + Espresso</h3>
                                <p>Dine-in • Double shot, Almond milk</p>
                            </div>
                        </div>
                        <div class="queue-actions">
                            <div class="wait-time">7 min</div>
                            <button class="queue-btn" onclick="markReady(this)">Ready</button>
                        </div>
                    </div>

                    <div class="queue-item">
                        <div class="queue-order">
                            <div class="queue-number">04</div>
                            <div class="queue-details">
                                <h3>Flat White</h3>
                                <p>Takeout • Almond milk, Extra hot</p>
                            </div>
                        </div>
                        <div class="queue-actions">
                            <div class="wait-time">9 min</div>
                            <button class="queue-btn" onclick="markReady(this)">Ready</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Placeholder sections -->
            <div id="inventory-section" style="display: none;">
                <h2 class="section-title-main">Inventory Management</h2>
                <div style="background: white; padding: 3rem; border-radius: 1.5rem; text-align: center; color: #888;">
                    <p style="font-size: 1.5rem;">Inventory management section coming soon...</p>
                </div>
            </div>

            <div id="reports-section" style="display: none;">
                <h2 class="section-title-main">Reports</h2>
                <div style="background: white; padding: 3rem; border-radius: 1.5rem; text-align: center; color: #888;">
                    <p style="font-size: 1.5rem;">Reports section coming soon...</p>
                </div>
            </div>

            <div id="settings-section" style="display: none;">
                <h2 class="section-title-main">Settings</h2>
                <div style="background: white; padding: 3rem; border-radius: 1.5rem; text-align: center; color: #888;">
                    <p style="font-size: 1.5rem;">Settings section coming soon...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let isSidebarCollapsed = false;
        let currentPage = 'dashboard';

        // Toggle Sidebar
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

        // Navigate to different sections
        function navigateTo(page) {
            // Hide all sections
            document.querySelectorAll('[id$="-section"]').forEach(section => {
                section.style.display = 'none';
            });

            // Show selected section
            const section = document.getElementById(page + '-section');
            if (section) {
                section.style.display = 'block';
            }

            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.closest('.nav-item')?.classList.add('active');

            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'tasks': "Today's Tasks",
                'shifts': 'Shifts',
                'queue': 'Order Queue',
                'inventory': 'Inventory',
                'reports': 'Reports',
                'settings': 'Settings'
            };
            document.getElementById('pageTitle').textContent = titles[page] || 'Dashboard';
            currentPage = page;

            // Close sidebar on mobile after selection
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.remove('active');
            }
        }

        // Mark order as ready
        function markReady(btn) {
            if (btn.textContent.trim() === 'Ready') {
                btn.textContent = 'Served';
                btn.classList.add('served');
                btn.parentElement.parentElement.style.opacity = '0.6';
                
                setTimeout(() => {
                    btn.textContent = 'Ready';
                    btn.classList.remove('served');
                    btn.parentElement.parentElement.style.opacity = '1';
                }, 3000);
            }
        }

        // Logout
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'log-out.php';
            }
        }

        // Handle responsive behavior
        window.addEventListener('resize', () => {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });

        // Set active nav item on page load
        document.querySelector('.nav-item').classList.add('active');
    </script>
</body>
</html>