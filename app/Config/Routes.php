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


// Course enrollment routes
$routes->post('/course/enroll', 'Course::enroll');
$routes->get('/course/available', 'Course::getAvailableCourses');
$routes->get('/course/enrollments', 'Course::getUserEnrollments');
$routes->get('/course/view/(:num)', 'Course::view/$1');
$routes->get('/course/students', 'Course::getStudents');
$routes->get('/course/(:num)/students', 'Course::getCourseStudents/$1');

// Student-specific routes
$routes->group('student', function($routes) {
    $routes->get('enrollments', 'Student::enrollments');
    $routes->get('assignments', 'Student::assignments');
    $routes->get('materials/(:num)', 'Student::materials/$1');
});

// Teacher routes
$routes->group('teacher', function($routes) {
    $routes->get('dashboard', 'Teacher::dashboard');
    $routes->get('course/(:num)/upload', 'Materials::upload/$1');
    $routes->post('course/(:num)/upload', 'Materials::upload/$1');
});

// Test upload route removed

// Admin routes
$routes->group('admin', function($routes) {
    $routes->get('dashboard', 'Admin::dashboard');
    $routes->get('users', 'Admin::getUsers');
    $routes->post('roles/update/(:num)', 'Admin::updateRole/$1');
    $routes->get('courses', 'Admin::courses');
    $routes->get('course/(:num)/upload', 'Materials::upload/$1');
    $routes->post('course/(:num)/upload', 'Materials::upload/$1');
    
    // Academic management routes
    $routes->post('academic-year/create', 'Admin::createAcademicYear');
    $routes->post('academic-year/update/(:num)', 'Admin::updateAcademicYear/$1');
    $routes->get('academic-year/delete/(:num)', 'Admin::deleteAcademicYear/$1');
    $routes->post('semester/create', 'Admin::createSemester');
    $routes->post('semester/update/(:num)', 'Admin::updateSemester/$1');
    $routes->get('semester/delete/(:num)', 'Admin::deleteSemester/$1');
    $routes->post('year-level/create', 'Admin::createYearLevel');
    $routes->post('year-level/update/(:num)', 'Admin::updateYearLevel/$1');
    $routes->get('year-level/delete/(:num)', 'Admin::deleteYearLevel/$1');
    $routes->post('assign-year-level', 'Admin::assignYearLevel');
});

// Materials routes
$routes->get('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->get('/materials/download/(:num)', 'Materials::download/$1');
$routes->get('/materials/view/(:num)', 'Materials::view/$1');
$routes->get('/materials/viewfile/(:num)', 'Materials::viewFile/$1');

// Assignment routes
$routes->post('/assignment/create', 'Assignment::create');
$routes->get('/assignment/view/(:num)', 'Assignment::view/$1');
$routes->post('/assignment/submit', 'Assignment::submit');
$routes->post('/assignment/grade', 'Assignment::grade');
$routes->get('/assignment/delete/(:num)', 'Assignment::delete/$1');
$routes->get('/assignment/submission/download/(:num)', 'Assignment::downloadSubmission/$1');
$routes->get('/assignment/submission/view/(:num)', 'Assignment::viewSubmission/$1');

// Notification routes
$routes->get('/notifications', 'Notifications::get');
$routes->post('/notifications/mark_read/(:num)', 'Notifications::markAsRead/$1');
$routes->post('/notifications/create_test', 'Notifications::createTestNotification');

// Unified dashboard only per Lab 5


