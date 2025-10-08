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

// All role-specific content is now handled in header.php navigation dropdowns
// No separate routes needed since everything is accessible through the unified dashboard


// Role management routes (Admin only)
$routes->get('/admin/users', 'Admin::getUsers');
$routes->post('/admin/roles/update/(:num)', 'Admin::updateRole/$1');

// Course enrollment routes
$routes->post('/course/enroll', 'Course::enroll');
$routes->get('/course/available', 'Course::getAvailableCourses');
$routes->get('/course/enrollments', 'Course::getUserEnrollments');
$routes->get('/course/view/(:num)', 'Course::view/$1');

// Unified dashboard only per Lab 5


