<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/log_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Security Base Filters
$filters = [];
if ($_SESSION['role'] === 'admin') {
    $filters['user_role'] = ['user', 'admin'];
} elseif ($_SESSION['role'] === 'user') {
    $filters['user_id'] = $_SESSION['user_id'];
}

// Action-type mapping
$actionTypeMap = [
    'login'      => 'login,logged in,success,auth',
    'logout'     => 'logout,logged out,out',
    'promote'    => 'promot,admin,superadmin,role',
    'demote'     => 'demot,staff,user',
    'block'      => 'block,ban,restrict',
    'unblock'    => 'unblock,restor,allow',
    'delete'     => 'delete,remov,trash',
    'create'     => 'create,add,register,initiali,new',
    'update'     => 'update,edit,change,password,modify',
    'approve'    => 'approv,accept,confirm',
    'reject'     => 'reject,decline,cancel',
    'transfer'   => 'transfer,handov',
    'deactivate' => 'deactivat,soft',
    'export'     => 'export,download,csv,excel',
    'privilege'  => 'privilege,permission,access,grant,revok',
];

// Input Processing
if (!empty($_GET['action_type']) && isset($actionTypeMap[$_GET['action_type']])) {
    $filters['action_keyword'] = $actionTypeMap[$_GET['action_type']];
}

if (!empty($_GET['module'])) {
    $filters['module'] = $_GET['module'];
}

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

if (!empty($_GET['role'])) {
    $requestedRole = $_GET['role'];
    if ($_SESSION['role'] === 'admin') {
        // Admin can only see admin or user logs
        if (in_array($requestedRole, ['admin', 'user'])) {
            $filters['user_role'] = [$requestedRole];
        } else {
            $filters['user_role'] = ['admin', 'user'];
        }
    } else {
        // Superadmin can see all
        $filters['user_role'] = [$requestedRole];
    }
}

// Date Range Handling
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'] . ' 00:00:00';
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'] . ' 23:59:59';
}

// Legacy single date support
if (empty($filters['date_from']) && !empty($_GET['date'])) {
    $filters['date_from'] = $_GET['date'] . ' 00:00:00';
    $filters['date_to']   = $_GET['date'] . ' 23:59:59';
}

$result = getActivityLogs($filters, $limit, $offset);
$logs   = $result['data'];
$total  = $result['total'];

$enriched = [];
foreach ($logs as $log) {
    $ua = parseUserAgent($log['user_agent']);
    $dt = new DateTime($log['created_at']);
    $det = json_decode($log['details'], true) ?: [];

    // Summary Logic
    $summary = '';
    $parts = [];
    
    if (!empty($det)) {
        foreach (['email', 'user_id', 'target_user_name', 'new_role', 'order_id', 'total', 'method', 'target_name', 'add_users', 'block_users', 'view_users'] as $k) {
            if (!empty($det[$k])) {
                $label = str_replace('_', ' ', $k);
                $parts[] = "<strong>".ucfirst($label).":</strong> " . $det[$k];
            }
        }
        
        if (isset($det['old_role']) && isset($det['new_role'])) {
            $parts = ["Change: <code>" . $det['old_role'] . "</code> → <code>" . $det['new_role'] . "</code>"];
        }

        if ($log['module'] === 'Privileges' && (isset($det['add_users']) || isset($det['block_users']) || isset($det['view_users']))) {
            $enabled = [];
            $disabled = [];
            if (($det['add_users'] ?? '') === 'Enabled') $enabled[] = "Add Users"; else $disabled[] = "Add Users";
            if (($det['block_users'] ?? '') === 'Enabled') $enabled[] = "Block Users"; else $disabled[] = "Block Users";
            if (($det['view_users'] ?? '') === 'Enabled') $enabled[] = "View User Data"; else $disabled[] = "View User Data";
            
            $summary = "";
            if (!empty($enabled)) $summary .= "<span style='color:#2e7d32; font-weight:700;'>Enabled:</span> " . implode(', ', $enabled);
            if (!empty($disabled)) {
                if (!empty($summary)) $summary .= " | ";
                $summary .= "<span style='color:#c62828; font-weight:700;'>Disabled:</span> " . implode(', ', $disabled);
            }
        } else {
            $summary = implode(' | ', $parts);
        }
    }

    // Icon assignment
    $act_class = 'badge-admin';
    $act_icon  = 'fa-history';
    $action_l  = strtolower($log['action']);
    
    if (strpos($action_l, 'login') !== false) { $act_class = 'badge-transaction'; $act_icon = 'fa-sign-in-alt'; }
    elseif (strpos($action_l, 'logout') !== false) { $act_class = 'badge-warning'; $act_icon = 'fa-sign-out-alt'; }
    elseif (strpos($action_l, 'delete') !== false) { $act_class = 'badge-danger'; $act_icon = 'fa-trash-alt'; }
    elseif (strpos($action_l, 'update') !== false) { $act_class = 'badge-info'; $act_icon = 'fa-edit'; }
    elseif (strpos($action_l, 'create') !== false || strpos($action_l, 'add') !== false) { $act_class = 'badge-success'; $act_icon = 'fa-plus-circle'; }
    elseif (strpos($action_l, 'export') !== false) { $act_class = 'badge-info'; $act_icon = 'fa-file-export'; }
    elseif (strpos($action_l, 'privilege') !== false || strpos($action_l, 'permission') !== false) { $act_class = 'badge-admin'; $act_icon = 'fa-shield-halved'; }

    // Relative time
    $diff = time() - strtotime($log['created_at']);
    $time_ago = "Just now";
    if ($diff >= 60) {
        $m = floor($diff/60);
        $time_ago = $m == 1 ? "1 min ago" : "$m mins ago";
        if ($m >= 60) {
            $h = floor($m/60);
            $time_ago = $h == 1 ? "1 hour ago" : "$h hours ago";
            if ($h >= 24) {
                $d = floor($h/24);
                $time_ago = $d == 1 ? "Yesterday" : "$d days ago";
            }
        }
    }

    $enriched[] = [
        'id'         => $log['id'],
        'user_id'    => $log['user_id'],
        'user_name'  => $log['user_name'],
        'user_role'  => $log['user_role'],
        'role_class' => in_array(strtolower($log['user_role']), ['admin', 'superadmin']) ? 'badge-admin' : 'badge-user',
        'action'     => $log['action'],
        'module'     => $log['module'],
        'ip_address' => $log['ip_address'],
        'platform'   => $ua['platform'],
        'browser'    => $ua['browser'],
        'ua_icon'    => $ua['icon'],
        'date_fmt'   => $dt->format('M j, Y'),
        'time_fmt'   => $dt->format('h:i A'),
        'time_ago'   => $time_ago,
        'act_class'  => $act_class,
        'act_icon'   => $act_icon,
        'summary'    => $summary,
        'details'    => $det
    ];
}

echo json_encode([
    'success' => true,
    'logs'    => $enriched,
    'total'   => $total,
    'page'    => $page,
    'total_pages' => ceil($total / $limit)
]);
