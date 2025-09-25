<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// routes for login register and dashboard
$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::register');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');
$routes->get('/dashboard', 'Auth::dashboard');

// Role-specific protected routes (examples)
$routes->group('admin', ['filter' => 'role:admin'], function($routes) {
    $routes->get('users', 'Admin::users');
    $routes->get('settings', 'Admin::settings');
});

$routes->group('teacher', ['filter' => 'role:teacher'], function($routes) {
    $routes->get('courses', 'Teacher::courses');
    $routes->get('grades', 'Teacher::grades');
});

$routes->group('student', ['filter' => 'role:student'], function($routes) {
    $routes->get('enrollments', 'Student::enrollments');
    $routes->get('assignments', 'Student::assignments');
});


// Unified dashboard only per Lab 5


