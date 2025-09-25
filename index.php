<?php

use CodeIgniter\Boot;
use Config\Paths;

// Redirect if accessing index.php directly with clean URL
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/index.php/') !== false) {
    $cleanUrl = str_replace('/index.php/', '/', $_SERVER['REQUEST_URI']);
    $cleanUrl = str_replace('/index.php', '/', $cleanUrl);
    header('Location: ' . $cleanUrl, true, 301);
    exit;
}

// Handle path traversal attempts - redirect to appropriate dashboard
if (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], '../') !== false || strpos($_SERVER['REQUEST_URI'], '..\\') !== false)) {
    // Start session to check role
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
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
    
    header('Location: ' . $redirectUrl, true, 301);
    exit;
}

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */

$minPhpVersion = '8.1'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// LOAD OUR PATHS CONFIG FILE
// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . 'app/Config/Paths.php';
// ^^^ Change this line if you move your application folder

$paths = new Paths();

// LOAD THE FRAMEWORK BOOTSTRAP FILE
require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
