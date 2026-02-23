<?php
session_start();
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['staff', 'supervisor', 'manager'])) {
    header("Location: index.php");
    exit();
}
require_once 'db.php';
require_once 'log_functions.php';

$userId = $_SESSION['user_id'] ?? '';
$userName = $_SESSION['user_name'] ?? 'Staff';
$userRole = $_SESSION['role'] ?? 'staff';

// Avatar initials
$name_parts = explode(' ', $userName);
$initials = '';
foreach ($name_parts as $p) { $initials .= strtoupper(substr($p, 0, 1)); }
$initials = substr($initials, 0, 2);

// Fetch personal activity logs
$personalLogs = [];
try {
    // Attempt to match by user_id in details JSON or description
    $stmt = $conn->prepare("
        SELECT * FROM activity_logs 
        WHERE (user_id = :id) 
           OR (user_name LIKE :desc)
        ORDER BY created_at DESC 
        LIMIT 8
    ");
    $stmt->execute(['id' => $userId, 'desc' => "%$userName%"]);
    $personalLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fail silently
}

// Stats
$logsToday = 0;
foreach($personalLogs as $log) {
    if (date('Y-m-d', strtotime($log['created_at'])) === date('Y-m-d')) {
        $logsToday++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal – Coffee Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        :root {
            --staff-accent: #8B6347;
        }

        .quick-pos-banner {
            background: linear-gradient(135deg, var(--brown-dark) 0%, var(--brown) 100%);
            color: var(--cream);
            padding: 3.5rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-lg);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .quick-pos-banner::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            pointer-events: none;
        }

        .quick-pos-banner:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(62, 31, 0, 0.25);
        }

        .quick-pos-banner:active {
            transform: scale(0.98);
        }

        .banner-text h2 { 
            font-size: 3.2rem; 
            font-weight: 800;
            margin-bottom: 0.8rem; 
            letter-spacing: -0.5px;
        }

        .banner-text p { 
            opacity: 0.85; 
            font-size: 1.5rem; 
            max-width: 400px;
        }

        .banner-icon { 
            font-size: 6rem; 
            color: var(--cream);
            opacity: 0.4;
            transition: transform 0.3s ease;
        }

        .quick-pos-banner:hover .banner-icon {
            transform: rotate(10deg) scale(1.1);
        }
        
        #realtime-clock {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--brown);
            background: var(--cream-light);
            padding: 0.6rem 1.8rem;
            border-radius: 3rem;
            border: 1.5px solid rgba(96, 63, 38, 0.12);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .staff-welcome {
            background: white;
            padding: 2.4rem;
            border-radius: var(--radius);
            margin-bottom: 3rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(96, 63, 38, 0.05);
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .welcome-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--brown);
            color: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .welcome-text h1 {
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--brown-dark);
            margin-bottom: 0.4rem;
        }

        .welcome-text p {
            color: var(--muted);
            font-size: 1.4rem;
        }

        @media (max-width: 768px) {
            .quick-pos-banner { padding: 2.5rem; }
            .banner-text h2 { font-size: 2.4rem; }
            .banner-icon { display: none; }
        }
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-mug-hot"></i>
        <span class="logo-text">Brew<span>STAFF</span></span>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Main Workspace</div>
        <div class="nav-link active">
            <i class="fas fa-house"></i><span class="nav-text">Home</span>
        </div>
        <div class="nav-link" onclick="window.location.href='pos.php'">
            <i class="fas fa-cash-register"></i><span class="nav-text">POS System</span>
        </div>
        
        <div class="nav-label">Resources</div>
        <div class="nav-link">
            <i class="fas fa-book-open"></i><span class="nav-text">Menu Guide</span>
        </div>
        <div class="nav-link">
            <i class="fas fa-clock-rotate-left"></i><span class="nav-text">My History</span>
        </div>
    </nav>

    <div class="sidebar-footer">
        <button class="logout-btn" onclick="confirmLogout()" style="width: 100%; justify-content: center;">
            <i class="fas fa-power-off"></i><span>Sign Out</span>
        </button>
    </div>
</aside>

<div class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <div id="realtime-clock">
                <i class="far fa-clock"></i>
                <span id="clock-text">00:00:00 AM</span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="user-chip">
                <div class="user-avatar"><?php echo $initials; ?></div>
                <div style="text-align: left;">
                    <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="user-role-lbl"><?php echo ucfirst($userRole); ?></div>
                </div>
            </div>
        </div>
    </header>

    <div class="content-area">
        
        <div class="staff-welcome">
            <div class="welcome-avatar"><?php echo $initials; ?></div>
            <div class="welcome-text">
                <h1>Hello, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>!</h1>
                <p>Welcome back to your shift. Have a productive day!</p>
            </div>
        </div>

        <div class="quick-pos-banner" onclick="window.location.href='pos.php'">
            <div class="banner-text">
                <h2>Point of Sale</h2>
                <p>Process your sales, manage orders, and handle transactions in real-time.</p>
            </div>
            <div class="banner-icon">
                <i class="fas fa-cash-register"></i>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon info"><i class="fas fa-bolt"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?php echo $logsToday; ?></div>
                    <div class="stat-label">Actions Stamped</div>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon success"><i class="fas fa-shield-halved"></i></div>
                <div class="stat-body">
                    <div class="stat-value">On Duty</div>
                    <div class="stat-label">Shift Status</div>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon warning"><i class="fas fa-calendar-day"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?php echo date('M d'); ?></div>
                    <div class="stat-label">Current Date</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-stream"></i>
                    <span>My Recent Logs</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Module</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($personalLogs)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 4rem; color: var(--muted);">
                                        <i class="fas fa-folder-open" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.3;"></i>
                                        No recent activity recorded for your account.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($personalLogs as $log): ?>
                                    <tr>
                                        <td style="font-weight: 600;"><?php echo date('h:i A', strtotime($log['created_at'])); ?></td>
                                        <td><span class="badge badge-user"><?php echo htmlspecialchars($log['module']); ?></span></td>
                                        <td style="color: var(--muted);"><?php echo htmlspecialchars($log['action']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function updateClock() {
    const clockText = document.getElementById('clock-text');
    const now = new Date();
    clockText.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
setInterval(updateClock, 1000);
updateClock();

function confirmLogout() {
    Swal.fire({
        title: 'Sign Out?',
        text: "You will be returned to the login screen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#603F26',
        cancelButtonColor: '#muted',
        confirmButtonText: 'Yes, Sign Out'
    }).then((result) => {
            window.location.href = 'log-out.php';
    })
}
</script>
</body>
</html>
