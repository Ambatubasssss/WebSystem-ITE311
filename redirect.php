<?php
// Simple redirect script for malformed URLs
session_start();

// Get the current URL
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';

// Check if this is a malformed URL with path traversal
if (strpos($currentUrl, '../') !== false || strpos($currentUrl, '..\\') !== false) {
    // Determine redirect URL based on session role
    $role = strtolower($_SESSION['role'] ?? '');
    $baseUrl = '/ITE311-MALILAY/';
    
    if ($role === 'admin') {
        $redirectUrl = $baseUrl . 'admin/dashboard';
    } elseif ($role === 'teacher') {
        $redirectUrl = $baseUrl . 'teacher/dashboard';
    } elseif ($role === 'student') {
        $redirectUrl = $baseUrl . 'student/dashboard';
    } elseif (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        $redirectUrl = $baseUrl . 'dashboard';
    } else {
        $redirectUrl = $baseUrl . 'login';
    }
    
    // Redirect immediately
    header('Location: ' . $redirectUrl, true, 301);
    exit;
}

// If not malformed, redirect to main app
header('Location: /ITE311-MALILAY/', true, 301);
exit;
?>


