<?php
// requirelogon.php Enforces authentication requirement and provides user data access

// Start or resume session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['login'])) {
    header("Location: pleaselogin.php");
    exit;
}

// Extract and sanitize user data for page access
$user = $_SESSION['login'];

// Helper function for safe output rendering
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$firstName = isset($user['firstName']) ? h($user['firstName']) : '';
$lastName  = isset($user['lastName']) ? h($user['lastName']) : '';
$email     = isset($user['email']) ? h($user['email']) : '';
$address   = isset($user['address']) ? h($user['address']) : '';
$phone     = isset($user['phone']) ? h($user['phone']) : '';
$studentID = isset($user['id']) ? (int) $user['id'] : 0;