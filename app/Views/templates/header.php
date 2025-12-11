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
                        
                        <!-- Settings Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= base_url('dashboard?section=settings'); ?>"><i class="fas fa-user-edit"></i> Edit Personal Info</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('dashboard?section=settings&tab=password'); ?>"><i class="fas fa-key"></i> Update Password</a></li>
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
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>`;
        
        users.forEach(user => {
            const roleClass = getRoleClass(user.role);
            const canEdit = user.role.toLowerCase() !== 'admin';
            const isActive = user.is_active !== undefined ? (user.is_active === 1 || user.is_active === true) : true;
            const statusClass = isActive ? 'bg-success' : 'bg-secondary';
            const statusText = isActive ? 'Active' : 'Deactivated';
            
            html += `
                <tr id="user-row-${user.id}">
                    <td>${user.id}</td>
                    <td>${escapeHtml(user.name)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td><span class="badge ${roleClass}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
                    <td><span class="badge ${statusClass}" id="status-badge-${user.id}">${statusText}</span></td>
                    <td>`;
            
            if (canEdit) {
                html += `
                    <div class="btn-group" role="group">
                        <select class="form-select form-select-sm" onchange="updateUserRole(${user.id}, this.value)" style="width: auto;">
                            <option value="teacher" ${user.role === 'teacher' ? 'selected' : ''}>Teacher</option>
                            <option value="student" ${user.role === 'student' ? 'selected' : ''}>Student</option>
                        </select>`;
                
                if (isActive) {
                    html += `<button class="btn btn-sm btn-warning ms-2" onclick="deactivateUser(${user.id})" title="Deactivate User"><i class="fas fa-ban"></i> Deactivate</button>`;
                } else {
                    html += `<button class="btn btn-sm btn-success ms-2" onclick="activateUser(${user.id})" title="Activate User"><i class="fas fa-check"></i> Activate</button>`;
                }
                
                html += `</div>`;
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
                    Admin roles are protected and cannot be changed. Deactivated users remain in the database.
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

    // Activate user function
    function activateUser(userId) {
        if (!confirm('Are you sure you want to activate this user?')) {
            return;
        }

        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch(`<?= base_url('admin/users/activate') ?>/${userId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                // Reload users to reflect changes
                loadUsers();
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Error activating user: ' + error.message);
        });
    }

    // Deactivate user function
    function deactivateUser(userId) {
        if (!confirm('Are you sure you want to deactivate this user? The account will remain in the database but will be inactive.')) {
            return;
        }

        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch(`<?= base_url('admin/users/deactivate') ?>/${userId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                // Reload users to reflect changes
                loadUsers();
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Error deactivating user: ' + error.message);
        });
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

    // Notification System JavaScript using jQuery with vanilla JS fallback
    function initNotifications() {
        console.log('Initializing notifications...');
        
        // Check if user is logged in before loading notifications
        <?php if (session()->get('logged_in')): ?>
        // Load notifications when page loads
        loadNotifications();
        <?php else: ?>
        // User not logged in, show empty state immediately
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
        
        // Aggressive timeout to clear loading state (3 seconds) - MUST clear loading
        // This is a critical fallback to prevent infinite loading
        setTimeout(function() {
            const container = document.getElementById('notificationsContainer');
            if (container) {
                const html = container.innerHTML;
                if (html && (html.includes('Loading notifications') || html.includes('spinner-border'))) {
                    console.warn('CRITICAL: Timeout fallback triggered - forcing empty state');
                    updateNotificationsList([]);
                }
            }
        }, 3000);
    }
    
    // Use jQuery if available, otherwise vanilla JS
    if (typeof jQuery !== 'undefined') {
        $(document).ready(function() {
            console.log('DOM loaded with jQuery...');
            initNotifications();
        });
    } else {
        // Vanilla JS fallback
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initNotifications);
        } else {
            initNotifications();
        }
    }

    function loadNotifications() {
        console.log('Loading notifications...');
        
        // Set a flag to track if we've updated the UI
        let uiUpdated = false;
        
        // Force clear loading after 3 seconds if still loading - CRITICAL FALLBACK
        const forceClearTimeout = setTimeout(function() {
            if (!uiUpdated) {
                console.warn('FORCE CLEAR: Loading state cleared after 3 seconds timeout');
                updateNotificationsList([]);
                uiUpdated = true;
            }
        }, 3000);
        
        // Use jQuery if available, otherwise vanilla fetch
        if (typeof jQuery !== 'undefined' && typeof $.ajax === 'function') {
            $.ajax({
                url: '<?= base_url('notifications') ?>',
                type: 'GET',
                dataType: 'json',
                timeout: 5000, // 5 second timeout
                success: function(data) {
                    clearTimeout(forceClearTimeout);
                    uiUpdated = true;
                    console.log('Notifications response:', data);
                    
                    try {
                        if (data && data.success !== false) {
                            updateNotificationBadge(data.unread_count || 0);
                            updateNotificationsList(data.notifications || []);
                        } else {
                            console.error('Failed to load notifications:', data?.message || 'Unknown error');
                            updateNotificationsList([]);
                        }
                    } catch (e) {
                        console.error('Error processing notifications:', e);
                        updateNotificationsList([]);
                    }
                },
                error: function(xhr, status, error) {
                    clearTimeout(forceClearTimeout);
                    uiUpdated = true;
                    console.error('AJAX error:', error, 'Status:', status, 'Response:', xhr.responseText);
                    updateNotificationsList([]);
                },
                complete: function() {
                    uiUpdated = true;
                    console.log('Notifications request completed');
                }
            });
        } else {
            // Vanilla fetch fallback
            console.log('Using vanilla fetch for notifications');
            fetch('<?= base_url('notifications') ?>', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                clearTimeout(forceClearTimeout);
                uiUpdated = true;
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                console.log('Notifications response:', data);
                if (data && data.success !== false) {
                    updateNotificationBadge(data.unread_count || 0);
                    updateNotificationsList(data.notifications || []);
                } else {
                    updateNotificationsList([]);
                }
            })
            .catch(error => {
                clearTimeout(forceClearTimeout);
                uiUpdated = true;
                console.error('Fetch error:', error);
                updateNotificationsList([]);
            });
        }
    }

    function updateNotificationBadge(count) {
        const badge = typeof jQuery !== 'undefined' ? $('#notificationBadge') : null;
        const badgeEl = badge && badge.length ? badge[0] : document.getElementById('notificationBadge');
        
        if (badgeEl) {
            if (count > 0) {
                badgeEl.textContent = count;
                badgeEl.style.display = '';
            } else {
                badgeEl.style.display = 'none';
            }
        }
    }

    function updateNotificationsList(notifications) {
        // Get container using both jQuery and vanilla JS
        const container = typeof jQuery !== 'undefined' ? $('#notificationsContainer') : null;
        const containerEl = container && container.length ? container[0] : document.getElementById('notificationsContainer');
        
        console.log('Updating notifications list:', notifications);
        
        if (!containerEl) {
            console.warn('Notifications container not found');
            return;
        }
        
        // Always clear loading state first
        if (!notifications || notifications.length === 0) {
            containerEl.innerHTML = `
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
        
        containerEl.innerHTML = html;
        
        // Bind mark as read events
        const markReadButtons = containerEl.querySelectorAll('.mark-read-btn');
        markReadButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.getAttribute('data-id');
                console.log('Mark as read button clicked for notification:', notificationId);
                markAsRead(notificationId);
            });
        });
    }

    function markAsRead(notificationId) {
        console.log('Marking notification as read:', notificationId);
        
        // Use jQuery $.post() to mark notification as read
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', getCSRFToken());
        
        $.ajax({
            url: '<?= base_url('notifications/mark_read') ?>/' + notificationId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
            console.log('Mark as read response data:', data);
            if (data && data.success) {
                // Update the notification's appearance instead of removing it
                const notificationItem = $('.notification-item[data-id="' + notificationId + '"]');
                
                // Change styling to show it's read
                notificationItem.removeClass('alert-info').addClass('alert-light');
                notificationItem.find('p').removeClass('fw-bold').addClass('text-muted');
                
                // Remove the mark as read button
                notificationItem.find('.mark-read-btn').fadeOut(200, function() {
                    $(this).remove();
                });
                
                // Update badge count
                updateNotificationBadge(data.unread_count || 0);
                
                // Update CSRF token if provided
                if (data.csrf_token) {
                    updateCSRFToken(data.csrf_token);
                }
                
                // Reload notifications to ensure consistency
                loadNotifications();
            } else {
                const errorMessage = (data && data.message) ? data.message : 'Unknown error occurred';
                console.error('Mark as read failed:', errorMessage);
                showAlert('danger', 'Failed to mark notification as read: ' + errorMessage);
            }
            },
            error: function(xhr, status, error) {
                console.error('Mark as read error:', error);
                showAlert('danger', 'Failed to mark notification as read');
            }
        });
    }
    </script>


