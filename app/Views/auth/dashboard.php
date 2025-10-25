<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <div class="container mt-4">

        
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">Dashboard</h3>
                    </div>
                    <div class="card-body">
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success">
                                <?= session()->getFlashdata('success') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger">
                                <?= session()->getFlashdata('error') ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">User Information</h5>
                                        <p class="card-text">
                                            <strong>Name:</strong> <?= esc($user['name'] ?? session()->get('name')) ?><br>
                                            <strong>Email:</strong> <?= esc($user['email'] ?? session()->get('email')) ?><br>
                                            <strong>Role:</strong> <?= esc(ucfirst($role ?? session()->get('role'))) ?><br>
                                            <strong>User ID:</strong> <?= esc($user['id'] ?? session()->get('userID')) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Quick Actions</h5>
                                        <div class="d-grid gap-2">
                                            <a href="<?= base_url('teacher/dashboard') ?>" class="btn btn-outline-primary">Go to Dashboard</a>
                                            <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger">Logout</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (($role ?? session('role')) === 'admin'): ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white mb-3"><div class="card-body"><h6 class="card-title">Total Users</h6><p class="mb-0">--</p></div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white mb-3"><div class="card-body"><h6 class="card-title">Total Courses</h6><p class="mb-0">--</p></div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white mb-3"><div class="card-body"><h6 class="card-title">Recent Activity</h6><p class="mb-0">--</p></div></div>
                            </div>
                        </div>
                        
                        <!-- Test Notification Button (for Lab 8) -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Lab 8: Notification Testing</h6>
                                    </div>
                                    <div class="card-body">
                                        <button class="btn btn-warning" id="createTestNotification">
                                            <i class="fas fa-bell"></i> Create Test Notification
                                        </button>
                                        <small class="text-muted d-block mt-2">Click to create a test notification for testing the notification system.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php elseif (($role ?? session('role')) === 'teacher'): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white mb-3"><div class="card-body"><h6 class="card-title">My Courses</h6><p class="mb-0">--</p></div></div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success text-white mb-3"><div class="card-body"><h6 class="card-title">New Submissions</h6><p class="mb-0">--</p></div></div>
                            </div>
                        </div>
                        <?php elseif (($role ?? session('role')) === 'student'): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">Enrolled Courses</h6>
                                        <p class="mb-0"><?= count($enrolledCourses ?? []) ?> courses</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success text-white mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">Available Courses</h6>
                                        <p class="mb-0"><?= count($availableCourses ?? []) ?> courses</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- My Courses Navbar Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fas fa-book"></i> My Courses</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-grid">
                                                    <a href="#enrolledCourses" class="btn btn-primary btn-lg" data-bs-toggle="collapse" data-bs-target="#enrolledCourses">
                                                        <i class="fas fa-graduation-cap"></i> View My Enrolled Courses
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-grid">
                                                    <a href="#availableCourses" class="btn btn-success btn-lg" data-bs-toggle="collapse" data-bs-target="#availableCourses">
                                                        <i class="fas fa-plus-circle"></i> Browse Available Courses
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enrolled Courses Section -->
                        <div class="row mt-4 collapse" id="enrolledCourses">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> My Enrolled Courses</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="enrolledCoursesContainer">
                                            <?php if (!empty($enrolledCourses)): ?>
                                                <div class="row">
                                                    <?php foreach ($enrolledCourses as $enrollment): ?>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card border-primary">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-primary"><?= esc($enrollment['title']) ?></h6>
                                                                <p class="card-text small"><?= esc($enrollment['description']) ?></p>
                                                                <small class="text-muted d-block mb-2">
                                                                    <i class="fas fa-calendar"></i> 
                                                                    Enrolled: <?= date('M j, Y', strtotime($enrollment['created_at'])) ?>
                                                                </small>
                                                                <a href="<?= base_url('materials/view/' . $enrollment['course_id']) ?>" 
                                                                   class="btn btn-primary btn-sm">
                                                                    <i class="fas fa-file-alt"></i> View Materials
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center py-4">
                                                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">You haven't enrolled in any courses yet.</p>
                                                    <p class="text-muted">Browse available courses below to get started!</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Available Courses Section -->
                        <div class="row mt-4 collapse" id="availableCourses">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Available Courses</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="availableCoursesContainer">
                                            <?php if (!empty($availableCourses)): ?>
                                                <div class="row">
                                                    <?php foreach ($availableCourses as $course): ?>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card border-success">
                                                            <div class="card-body">
                                                                <h6 class="card-title text-success"><?= esc($course['title']) ?></h6>
                                                                <p class="card-text small"><?= esc($course['description']) ?></p>
                                                                <button class="btn btn-success btn-sm enroll-btn" 
                                                                        data-course-id="<?= $course['id'] ?>"
                                                                        data-course-title="<?= esc($course['title']) ?>">
                                                                    <i class="fas fa-plus"></i> Enroll Now
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center py-4">
                                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                    <p class="text-muted">Great! You're enrolled in all available courses.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Enrollments Section (always shown for students) -->
                        <?php if (strtolower(session('role')) === 'student' && !empty($enrollments ?? [])): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> My Enrollments</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Course</th>
                                                        <th>Instructor</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($enrollments as $enrollment): ?>
                                                        <tr>
                                                            <td><strong><?= esc($enrollment['course']) ?></strong></td>
                                                            <td><?= esc($enrollment['instructor']) ?></td>
                                                            <td>
                                                                <span class="badge bg-<?= $enrollment['status'] === 'Active' ? 'success' : 'warning' ?>">
                                                                    <?= esc($enrollment['status']) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="<?= base_url("materials/view/{$enrollment['course_id']}") ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-book"></i> Materials
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Assignments Section (shown when redirected from student/assignments) -->
                        <?php if (session()->getFlashdata('show_assignments') && !empty($assignments ?? [])): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0"><i class="fas fa-tasks"></i> My Assignments</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Assignment</th>
                                                        <th>Course</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($assignments as $assignment): ?>
                                                        <tr>
                                                            <td><strong><?= esc($assignment['title']) ?></strong></td>
                                                            <td><?= esc($assignment['course']) ?></td>
                                                            <td>
                                                                <span class="text-<?= strtotime($assignment['due_date']) < time() ? 'danger' : 'dark' ?>">
                                                                    <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?= $assignment['status'] === 'Completed' ? 'success' : 'warning' ?>">
                                                                    <?= esc($assignment['status']) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="#" class="btn btn-sm btn-outline-primary" onclick="return false;">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                                <?php if ($assignment['status'] === 'Pending'): ?>
                                                                    <a href="#" class="btn btn-sm btn-outline-success" onclick="return false;">
                                                                        <i class="fas fa-upload"></i> Submit
                                                                    </a>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="alert alert-info">
                            <h6>Protected Dashboard</h6>
                            <p class="mb-0">This is a protected page that only logged-in users can access. If you can see this, it means your authentication system is working correctly!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and AJAX Enrollment Script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle navbar button clicks
        $('a[data-bs-toggle="collapse"]').on('click', function() {
            const target = $(this).data('bs-target');
            const otherTarget = target === '#enrolledCourses' ? '#availableCourses' : '#enrolledCourses';
            
            // Close other section when opening one
            $(otherTarget).collapse('hide');
        });
        
        // Handle enroll button clicks
        $(document).on('click', '.enroll-btn', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const courseId = button.data('course-id');
            const courseTitle = button.data('course-title');
            
            // Disable button and show loading state
            button.prop('disabled', true);
            button.html('<i class="fas fa-spinner fa-spin"></i> Enrolling...');
            
            // Send AJAX request
            $.ajax({
                url: '<?= base_url('course/enroll') ?>',
                type: 'POST',
                data: {
                    course_id: courseId,
                    '<?= csrf_token() ?>': getCSRFToken()
                },
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        showAlert('success', response.message);
                        
                        // Update CSRF token for next request
                        if (response.csrf_token) {
                            updateCSRFToken(response.csrf_token);
                        }
                        
                        // Hide the enrolled course card
                        button.closest('.col-md-6').fadeOut(500, function() {
                            $(this).remove();
                            
                            // Check if no more available courses
                            if ($('#availableCoursesContainer .col-md-6').length === 0) {
                                $('#availableCoursesContainer').html(`
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <p class="text-muted">Great! You're enrolled in all available courses.</p>
                                    </div>
                                `);
                            }
                        });
                        
                        // Add to enrolled courses section
                        addToEnrolledCourses(response.course);
                        
                        // Update course counts
                        updateCourseCounts();
                        
                        // Refresh header enrollments if function exists
                        if (typeof loadEnrollments === 'function') {
                            loadEnrollments();
                        }
                        
                        // Refresh header my courses if function exists
                        if (typeof loadMyCourses === 'function') {
                            loadMyCourses();
                        }
                        
                    } else {
                        // Show error message
                        showAlert('danger', response.message);
                        
                        // Reset button
                        button.prop('disabled', false);
                        button.html('<i class="fas fa-plus"></i> Enroll Now');
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    showAlert('danger', 'An error occurred while enrolling. Please try again.');
                    
                    // Reset button
                    button.prop('disabled', false);
                    button.html('<i class="fas fa-plus"></i> Enroll Now');
                }
            });
        });
    });
    
    function addToEnrolledCourses(course) {
        const enrolledContainer = $('#enrolledCoursesContainer');
        
        // Check if container is empty (showing "no courses" message)
        if (enrolledContainer.find('.text-center').length > 0) {
            enrolledContainer.html('<div class="row"></div>');
        }
        
        // Create new course card
        const courseCard = `
            <div class="col-md-6 mb-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <h6 class="card-title text-primary">${course.title}</h6>
                        <p class="card-text small">${course.description}</p>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> 
                            Enrolled: ${new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </small>
                    </div>
                </div>
            </div>
        `;
        
        // Add to container with animation
        enrolledContainer.find('.row').append(courseCard);
        enrolledContainer.find('.col-md-6').last().hide().fadeIn(500);
    }
    
    function updateCourseCounts() {
        const enrolledCount = $('#enrolledCoursesContainer .col-md-6').length;
        const availableCount = $('#availableCoursesContainer .col-md-6').length;
        
        $('.card.bg-primary .mb-0').text(`${enrolledCount} courses`);
        $('.card.bg-success .mb-0').text(`${availableCount} courses`);
    }
    
    function showAlert(type, message) {
        // Remove existing alerts
        $('.alert-dismissible').remove();
        
        // Create new alert
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${iconClass}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').append(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            $('.alert-dismissible').fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // CSRF Token Management
    let currentCSRFToken = '<?= csrf_hash() ?>';
    
    function getCSRFToken() {
        return currentCSRFToken;
    }
    
    function updateCSRFToken(newToken) {
        currentCSRFToken = newToken;
    }
    
    // Test jQuery loading
    console.log('Dashboard: jQuery available?', typeof $ !== 'undefined');
    
    // Test Notification Button (Lab 8) - Using vanilla JS
    const testBtn = document.getElementById('createTestNotification');
    if (testBtn) {
        testBtn.addEventListener('click', function() {
            fetch('<?= base_url('notifications/create_test') ?>', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Test notification created successfully!');
                    // Refresh notifications
                    if (typeof loadNotifications === 'function') {
                        loadNotifications();
                    }
                } else {
                    showAlert('danger', 'Failed to create test notification: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Test notification error:', error);
                showAlert('danger', 'Failed to create test notification');
            });
        });
    }
    </script>
<?= $this->endSection() ?>
