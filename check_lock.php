<?php
session_start();
header('Content-Type: application/json');

$response = [
    'locked' => false,
    'remainingTime' => 0
];

if (isset($_SESSION['lock_time']) && time() < $_SESSION['lock_time']) {
    $response['locked'] = true;
    $response['remainingTime'] = $_SESSION['lock_time'] - time();
}

echo json_encode($response);