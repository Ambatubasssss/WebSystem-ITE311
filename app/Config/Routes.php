<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// routes for login and dashboard
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');
$routes->get('/dashboard', 'Auth::dashboard');
$routes->post('/profile/update', 'Auth::updateProfile');
$routes->post('/password/update', 'Auth::updatePassword');
$routes->get('/dashboard/search', 'Auth::search');
$routes->post('/dashboard/search', 'Auth::search');

// All role-specific content is now handled in header.php navigation dropdowns
// No separate routes needed since everything is accessible through the unified dashboard


// Course enrollment routes
$routes->post('/course/enroll', 'Course::enroll');
$routes->get('/course/available', 'Course::getAvailableCourses');
$routes->get('/course/enrollments', 'Course::getUserEnrollments');
$routes->post('/course/enrollment/approve', 'Course::approveEnrollment');
$routes->post('/course/enrollment/reject', 'Course::rejectEnrollment');
$routes->post('/course/enrollment/unenroll', 'Course::unenrollStudent');
$routes->get('/course/(:num)/pending-enrollments', 'Course::getPendingEnrollments/$1');
$routes->get('/course/pending-enrollments', 'Course::getPendingEnrollments');
$routes->get('/course/view/(:num)', 'Course::view/$1');
$routes->get('/course/students', 'Course::getStudents');
$routes->get('/course/(:num)/students', 'Course::getCourseStudents/$1');

// Course search routes (Lab 9)
$routes->get('/courses/search', 'Course::search');
$routes->post('/courses/search', 'Course::search');

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
    $routes->post('users/create', 'Admin::createUser');
    $routes->post('roles/update/(:num)', 'Admin::updateRole/$1');
    $routes->post('users/activate/(:num)', 'Admin::activateUser/$1');
    $routes->post('users/deactivate/(:num)', 'Admin::deactivateUser/$1');
    $routes->get('courses', 'Admin::courses');
    $routes->post('courses/create', 'Admin::createCourse');
    $routes->post('courses/delete/(:num)', 'Admin::deleteCourse/$1');
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
    
    // Course scheduling and teacher assignment routes
    $routes->get('courses/(:num)/schedules', 'Admin::getCourseSchedules/$1');
    $routes->post('courses/(:num)/schedule', 'Admin::addCourseSchedule/$1');
    $routes->post('courses/(:num)/schedule/delete/(:num)', 'Admin::deleteCourseSchedule/$1/$2');
    $routes->get('courses/(:num)/teachers', 'Admin::getCourseTeachers/$1');
    $routes->post('courses/(:num)/teacher', 'Admin::assignTeacherToCourse/$1');
    $routes->post('courses/(:num)/teacher/update', 'Admin::updateTeacherAssignment/$1');
    $routes->post('courses/(:num)/teacher/remove/(:num)', 'Admin::removeTeacherFromCourse/$1/$2');
    $routes->post('courses/restore', 'Admin::restoreCourse');
    $routes->post('courses/reactivate', 'Admin::reactivateCourse');
    $routes->post('courses/teacher-assignment/restore', 'Admin::restoreTeacherAssignment');
    $routes->post('courses/update', 'Admin::updateCourse');
    $routes->get('courses/check-cn', 'Admin::checkControlNumber');
});

// Materials routes
// Admin course upload routes (standalone format for instructions)
$routes->get('/admin/course/(:num)/upload', 'Materials::upload/$1');
$routes->post('/admin/course/(:num)/upload', 'Materials::upload/$1');

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
$routes->get('/assignment/download-attachment/(:num)', 'Assignment::downloadAttachment/$1');

// Notification routes
$routes->get('/notifications', 'Notifications::get');
$routes->post('/notifications/mark_read/(:num)', 'Notifications::markAsRead/$1');
$routes->post('/notifications/create_test', 'Notifications::createTestNotification');

// Unified dashboard only per Lab 5

// Catch-all route for invalid URLs - redirect to homepage
// This handles URLs like /ITE311-MALILAY/... or /ITE311/MALILAY/... etc.
$routes->set404Override(function() {
    $uri = service('uri');
    $segments = $uri->getSegments();
    
    // If it looks like a project folder path, redirect to homepage
    if (count($segments) > 0) {
        $firstSegment = strtolower($segments[0]);
        if (in_array($firstSegment, ['ite311-malilay', 'ite311', 'malilay'])) {
            return redirect()->to('/');
        }
    }
    
    // For other 404s, redirect to homepage as well
    return redirect()->to('/');
});

