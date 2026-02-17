<?php
session_start();
header('Content-Type: application/json');

// Only clear lock if timer has actually expired
if (isset($_SESSION['lock_time']) && time() >= $_SESSION['lock_time']) {
    unset($_SESSION['lock_time']);
    echo json_encode(['status' => 'success', 'message' => 'Lock cleared']);
} else {
    echo json_encode(['status' => 'still_locked']);
}