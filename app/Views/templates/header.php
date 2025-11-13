    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-white" href="<?= base_url(''); ?>">LMS-MALILAY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!session()->get('logged_in')): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url(''); ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('about'); ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('contact'); ?>">Contact</a>
                    </li>
                    <?php endif; ?>
                    <?php if (session()->get('logged_in')): ?>
                        <?php $role = strtolower(session('role') ?? ''); ?>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard'); ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                        
                        <!-- Dynamic Role-Based Navigation -->
                        <?php if ($role === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard?section=users'); ?>"><i class="fas fa-users"></i> Manage Users</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard?section=courses'); ?>"><i class="fas fa-graduation-cap"></i> Course Management</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-calendar-alt"></i> Academic Management
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?= base_url('dashboard?section=academic-years'); ?>"><i class="fas fa-calendar-alt"></i> Academic Years</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('dashboard?section=semesters'); ?>"><i class="fas fa-calendar"></i> Semesters</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('dashboard?section=year-levels'); ?>"><i class="fas fa-user-graduate"></i> Year Levels</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= base_url('dashboard?section=assign-year-level'); ?>"><i class="fas fa-user-tag"></i> Assign Year Level</a></li>
                                </ul>
                            </li>
                        <?php elseif ($role === 'teacher'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard?section=my-courses'); ?>"><i class="fas fa-book"></i> My Courses</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard?section=enroll-students'); ?>"><i class="fas fa-user-plus"></i> Enroll Students</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard?section=assignments'); ?>"><i class="fas fa-tasks"></i> Assignments</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard?section=create-assignment'); ?>"><i class="fas fa-plus-circle"></i> Create Assignment</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard?section=upload'); ?>"><i class="fas fa-upload"></i> Upload Materials</a></li>
                        <?php elseif ($role === 'student'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard?section=enrollments'); ?>"><i class="fas fa-graduation-cap"></i> My Enrollments</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard?section=assignments'); ?>"><i class="fas fa-tasks"></i> Assignments</a></li>
                        <?php endif; ?>
                        
                        <!-- Notifications Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white position-relative" href="#" role="button" data-bs-toggle="dropdown" id="notificationsDropdown">
                                <i class="fas fa-bell"></i>
                                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="notificationBadge" style="display: none;">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="width: 350px; max-height: 400px; overflow-y: auto;">
                                <li class="dropdown-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Notifications</h6>
                                        <button class="btn btn-sm btn-outline-primary" id="refreshNotifications">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </li>
                                <li class="dropdown-item-text">
                                    <div id="notificationsContainer">
                                        <div class="text-center p-3">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2">Loading notifications...</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= base_url('logout'); ?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= base_url('login'); ?>">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Role Management JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load users when admin dropdown is opened
        const manageUsersDropdown = document.querySelector('a[data-bs-toggle="dropdown"]');
        if (manageUsersDropdown && manageUsersDropdown.textContent.includes('Manage Users')) {
            manageUsersDropdown.addEventListener('click', function() {
                loadUsers();
            });
        }
        
        // Load enrollments when student dropdown is opened
        const allDropdowns = document.querySelectorAll('a[data-bs-toggle="dropdown"]');
        allDropdowns.forEach(dropdown => {
            if (dropdown.textContent.includes('My Enrollments')) {
                dropdown.addEventListener('click', function() {
                    loadEnrollments();
                });
            } else if (dropdown.textContent.includes('My Courses')) {
                dropdown.addEventListener('click', function() {
                    loadMyCourses();
                });
            }
        });
    });

    function loadUsers() {
        fetch('<?= base_url('admin/users') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayUsers(data.users);
                } else {
                    const container = document.getElementById('usersTableContainer');
                    if (container) {
                        container.innerHTML = 
                            '<div class="alert alert-danger">' + data.message + '</div>';
                    }
                }
            })
            .catch(error => {
                const container = document.getElementById('usersTableContainer');
                if (container) {
                    container.innerHTML = 
                        '<div class="alert alert-danger">Error loading users: ' + error.message + '</div>';
                }
            });
    }

    function loadEnrollments() {
        console.log('Loading enrollments from: <?= base_url('course/enrollments') ?>');
        
        fetch('<?= base_url('course/enrollments') ?>', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Enrollments data:', data);
            if (data.success) {
                displayEnrollments(data.enrollments);
            } else {
                document.getElementById('headerEnrollmentsContainer').innerHTML = 
                    '<div class="alert alert-danger">' + (data.message || 'Failed to load enrollments') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error loading enrollments:', error);
            document.getElementById('headerEnrollmentsContainer').innerHTML = 
                '<div class="alert alert-danger">Error loading enrollments: ' + error.message + '</div>';
        });
    }

    function displayUsers(users) {
        let html = `
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>`;
        
        users.forEach(user => {
            const roleClass = getRoleClass(user.role);
            const canEdit = user.role.toLowerCase() !== 'admin';
            
            html += `
                <tr id="user-row-${user.id}">
                    <td>${user.id}</td>
                    <td>${escapeHtml(user.name)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td><span class="badge ${roleClass}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
                    <td>`;
            
            if (canEdit) {
                html += `
                    <div class="btn-group" role="group">
                        <select class="form-select form-select-sm" onchange="updateUserRole(${user.id}, this.value)" style="width: auto;">
                            <option value="teacher" ${user.role === 'teacher' ? 'selected' : ''}>Teacher</option>
                            <option value="student" ${user.role === 'student' ? 'selected' : ''}>Student</option>
                        </select>
                    </div>`;
            } else {
                html += '<span class="text-muted"><i class="fas fa-lock"></i> Protected</span>';
            }
            
            html += `
                    </td>
                </tr>`;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Admin roles are protected and cannot be changed.
                </small>
            </div>`;
        
        document.getElementById('usersTableContainer').innerHTML = html;
    }

    function displayEnrollments(enrollments) {
        if (enrollments.length === 0) {
            document.getElementById('headerEnrollmentsContainer').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">You haven't enrolled in any courses yet.</p>
                    <p class="text-muted">Visit your dashboard to browse available courses!</p>
                </div>
            `;
            return;
        }

        let html = '<div class="row">';
        
        enrollments.forEach(enrollment => {
            const enrollmentDate = new Date(enrollment.created_at).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
            
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">${escapeHtml(enrollment.title)}</h6>
                            <p class="card-text small">
                                <strong>Enrolled:</strong> ${enrollmentDate}<br>
                                <strong>Status:</strong> <span class="badge bg-success">Active</span>
                            </p>
                            <a href="#" class="btn btn-sm btn-primary">View Course</a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        document.getElementById('headerEnrollmentsContainer').innerHTML = html;
    }

    function loadMyCourses() {
        // Load only available courses
        fetch('<?= base_url('course/available') ?>')
        .then(response => response.json())
        .then(availableData => {
            let html = '';
            
            // Show available courses section
            if (availableData.success && availableData.courses.length > 0) {
                html += `
                    <div class="mb-3">
                        <h6 class="text-success"><i class="fas fa-plus-circle"></i> Available Courses</h6>
                        <div class="row">
                `;
                
                availableData.courses.forEach(course => {
                    html += `
                        <div class="col-12 mb-2">
                            <div class="card border-success">
                                <div class="card-body p-2">
                                    <h6 class="card-title mb-1">${escapeHtml(course.title)}</h6>
                                    <p class="card-text small mb-2">${escapeHtml(course.description)}</p>
                                    <button class="btn btn-success btn-sm enroll-btn-header" 
                                            data-course-id="${course.id}"
                                            data-course-title="${escapeHtml(course.title)}">
                                        <i class="fas fa-plus"></i> Enroll Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div></div>';
            } else {
                // Show message if no available courses
                html = `
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Great! You're enrolled in all available courses.</p>
                        <p class="text-muted">Check your enrolled courses in the "My Enrollments" dropdown.</p>
                    </div>
                `;
            }
            
            document.getElementById('myCoursesContainer').innerHTML = html;
            
            // Add event listeners for enroll buttons in header
            document.querySelectorAll('.enroll-btn-header').forEach(button => {
                button.addEventListener('click', function() {
                    const courseId = this.dataset.courseId;
                    const courseTitle = this.dataset.courseTitle;
                    
                    // Disable button and show loading
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enrolling...';
                    
                    // Send enrollment request
                    fetch('<?= base_url('course/enroll') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `course_id=${courseId}&<?= csrf_token() ?>=${typeof getCSRFToken === 'function' ? getCSRFToken() : '<?= csrf_hash() ?>'}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message);
                            // Refresh both dropdowns
                            loadMyCourses();
                            if (typeof loadEnrollments === 'function') {
                                loadEnrollments();
                            }
                        } else {
                            showAlert('danger', data.message);
                            // Reset button
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-plus"></i> Enroll';
                        }
                    })
                    .catch(error => {
                        showAlert('danger', 'An error occurred while enrolling. Please try again.');
                        // Reset button
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-plus"></i> Enroll';
                    });
                });
            });
        })
        .catch(error => {
            document.getElementById('myCoursesContainer').innerHTML = 
                '<div class="alert alert-danger">Error loading courses: ' + error.message + '</div>';
        });
    }

    function updateUserRole(userId, newRole) {
        if (!confirm(`Are you sure you want to change this user's role to ${newRole}?`)) {
            // Reset the select to original value
            loadUsers();
            return;
        }

        const formData = new FormData();
        formData.append('role', newRole);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch(`<?= base_url('admin/roles/update') ?>/${userId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', data.message);
                // Update the role badge
                const badge = document.querySelector(`#user-row-${userId} .badge`);
                if (badge) {
                    badge.textContent = data.newRole.charAt(0).toUpperCase() + data.newRole.slice(1);
                    badge.className = `badge ${getRoleClass(data.newRole)}`;
                }
            } else {
                showAlert('danger', data.message);
                // Reset the select
                loadUsers();
            }
        })
        .catch(error => {
            showAlert('danger', 'Error updating role: ' + error.message);
            loadUsers();
        });
    }

    function getRoleClass(role) {
        switch (role.toLowerCase()) {
            case 'admin': return 'bg-danger';
            case 'teacher': return 'bg-success';
            case 'student': return 'bg-primary';
            default: return 'bg-secondary';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showAlert(type, message) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> 
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Notification System JavaScript (Vanilla JS + jQuery fallback)
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, checking for jQuery...');
        
        // Check if user is logged in before loading notifications
        <?php if (session()->get('logged_in')): ?>
        // Load notifications when page loads
        loadNotifications();
        <?php else: ?>
        // User not logged in, show empty state
        const container = document.getElementById('notificationsContainer');
        if (container) {
            container.innerHTML = `
                <div class="text-center p-3">
                    <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">Please login to see notifications</p>
                </div>
            `;
        }
        <?php endif; ?>
        
        // Refresh notifications every 60 seconds (only if logged in)
        <?php if (session()->get('logged_in')): ?>
        setInterval(loadNotifications, 60000);
        <?php endif; ?>
        
        // Manual refresh button
        const refreshBtn = document.getElementById('refreshNotifications');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function(e) {
                e.preventDefault();
                loadNotifications();
            });
        }
        
        // Timeout for loading state (10 seconds)
        setTimeout(function() {
            const container = document.getElementById('notificationsContainer');
            if (container && container.innerHTML.includes('Loading notifications')) {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Loading timeout. Please try refreshing.
                    </div>
                `;
            }
        }, 10000);
    });

    function loadNotifications() {
        console.log('Loading notifications...');
        
        // Use vanilla JavaScript fetch API
        fetch('<?= base_url('notifications') ?>', {
            method: 'GET',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Notifications response:', data);
            if (data.success) {
                updateNotificationBadge(data.unread_count);
                updateNotificationsList(data.notifications);
            } else {
                console.error('Failed to load notifications:', data.message);
                const container = document.getElementById('notificationsContainer');
                if (container) {
                    container.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            ${data.message || 'Failed to load notifications'}
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            const container = document.getElementById('notificationsContainer');
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Failed to load notifications: ${error.message}
                    </div>
                `;
            }
        });
    }

    function updateNotificationBadge(count) {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    function updateNotificationsList(notifications) {
        const container = document.getElementById('notificationsContainer');
        
        console.log('Updating notifications list:', notifications);
        
        if (!container) return;
        
        if (!notifications || notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center p-3">
                    <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No notifications</p>
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(function(notification) {
            const alertClass = notification.is_read ? 'alert-light' : 'alert-info';
            const readClass = notification.is_read ? 'text-muted' : 'fw-bold';
            
            html += `
                <div class="alert ${alertClass} mb-2 notification-item" data-id="${notification.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="mb-1 ${readClass}">${escapeHtml(notification.message)}</p>
                            <small class="text-muted">${notification.time_ago}</small>
                        </div>
                        ${!notification.is_read ? `
                            <button class="btn btn-sm btn-outline-success mark-read-btn" data-id="${notification.id}">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Bind mark as read events
        const markReadButtons = container.querySelectorAll('.mark-read-btn');
        console.log('Found mark as read buttons:', markReadButtons.length);
        markReadButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.getAttribute('data-id');
                console.log('Mark as read button clicked for notification:', notificationId);
                markAsRead(notificationId);
            });
        });
    }

    function markAsRead(notificationId) {
        console.log('Marking notification as read:', notificationId);
        fetch(`<?= base_url('notifications/mark_read') ?>/${notificationId}`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
        })
        .then(response => {
            console.log('Mark as read response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Mark as read response data:', data);
            if (data && data.success) {
                // Remove the notification from the list
                const notificationElement = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.remove();
                }
                
                // Update badge count
                updateNotificationBadge(data.unread_count || 0);
                
                // Show success message
                showAlert('success', 'Notification marked as read');
            } else {
                const errorMessage = (data && data.message) ? data.message : 'Unknown error occurred';
                console.error('Mark as read failed:', errorMessage);
                showAlert('danger', 'Failed to mark notification as read: ' + errorMessage);
            }
        })
        .catch(error => {
            console.error('Mark as read error:', error);
            showAlert('danger', 'Failed to mark notification as read');
        });
    }
    </script>


