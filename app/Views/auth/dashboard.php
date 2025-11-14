<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <div class="container-fluid mt-4">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php 
        $currentSection = $section ?? 'overview';
        $userRole = strtolower($role ?? session('role') ?? '');
        ?>

        <!-- Overview Section (Default) -->
        <?php if ($currentSection === 'overview'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0"><i class="fas fa-home"></i> Dashboard Overview</h3>
                        </div>
                        <div class="card-body">
                            <!-- Academic Information Banner -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-md-4">
                                                    <h6 class="mb-1"><i class="fas fa-calendar-alt"></i> Academic Year</h6>
                                                    <p class="mb-0">
                                                        <?php if (isset($currentAcademicYear) && $currentAcademicYear): ?>
                                                            <strong><?= esc($currentAcademicYear['year_start']) ?> - <?= esc($currentAcademicYear['year_end']) ?></strong>
                                                        <?php else: ?>
                                                            <span class="text-warning">Not Set</span>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="mb-1"><i class="fas fa-calendar"></i> Semester & Term</h6>
                                                    <p class="mb-0">
                                                        <?php if (isset($currentSemester) && $currentSemester): ?>
                                                            <strong><?= esc($currentSemester['semester'] ?? 'N/A') ?> Semester - <?= esc($currentSemester['term']) ?> Term</strong>
                                                        <?php else: ?>
                                                            <span class="text-warning">Not Set</span>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="mb-1"><i class="fas fa-user-graduate"></i> Year Level</h6>
                                                    <p class="mb-0">
                                                        <?php if ($userRole === 'student'): ?>
                                                            <?php if (isset($studentYearLevel) && $studentYearLevel): ?>
                                                                <strong><?= esc($studentYearLevel['name']) ?></strong>
                                                            <?php else: ?>
                                                                <span class="text-warning">Not Assigned</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">User Information</h5>
                                            <p class="card-text">
                                                <strong>Name:</strong> <?= esc($user['name'] ?? session()->get('name')) ?><br>
                                                <strong>Email:</strong> <?= esc($user['email'] ?? session()->get('email')) ?><br>
                                                <strong>Role:</strong> <?= esc(ucfirst($userRole)) ?><br>
                                                <?php if ($userRole === 'student' && isset($studentYearLevel) && $studentYearLevel): ?>
                                                    <strong>Year Level:</strong> <?= esc($studentYearLevel['name']) ?><br>
                                                <?php endif; ?>
                                                <strong>User ID:</strong> <?= esc($user['id'] ?? session()->get('userID')) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($userRole === 'admin'): ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card bg-primary text-white mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title">Total Users</h6>
                                                <p class="mb-0"><?= $totalUsers ?? 0 ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title">Total Courses</h6>
                                                <p class="mb-0"><?= count($courses ?? []) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-info text-white mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title">Recent Activity</h6>
                                                <p class="mb-0"><?= count($recentUsers ?? []) ?> new users</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($userRole === 'teacher'): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-primary text-white mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title">My Courses</h6>
                                                <p class="mb-0"><?= count($courses ?? []) ?> courses</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-success text-white mb-3">
                                            <div class="card-body">
                                                <h6 class="card-title">Total Students</h6>
                                                <p class="mb-0"><?= $totalStudents ?? 0 ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($userRole === 'student'): ?>
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

                                <!-- My Courses Section -->
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
                                                                        <a href="<?= base_url('dashboard?section=materials&course_id=' . $enrollment['course_id']) ?>" 
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
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Admin: Users Management Section -->
        <?php if ($userRole === 'admin' && $currentSection === 'users'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h3 class="mb-0"><i class="fas fa-users"></i> User Management</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <div id="usersTableContainer">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($allUsers)): ?>
                                                <?php foreach ($allUsers as $user): ?>
                                                <tr id="user-row-<?= $user['id'] ?>">
                                                    <td><?= $user['id'] ?></td>
                                                    <td><?= esc($user['name']) ?></td>
                                                    <td><?= esc($user['email']) ?></td>
                                                    <td>
                                                        <span class="badge <?= strtolower($user['role']) === 'admin' ? 'bg-danger' : (strtolower($user['role']) === 'teacher' ? 'bg-success' : 'bg-primary') ?>">
                                                            <?= ucfirst($user['role']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (strtolower($user['role']) !== 'admin'): ?>
                                                            <select class="form-select form-select-sm" onchange="updateUserRole(<?= $user['id'] ?>, this.value)" style="width: auto; display: inline-block;">
                                                                <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                                                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                                            </select>
                                                        <?php else: ?>
                                                            <span class="text-muted"><i class="fas fa-lock"></i> Protected</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No users found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Admin: Course Management Section -->
        <?php if ($userRole === 'admin' && $currentSection === 'courses'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h3 class="mb-0"><i class="fas fa-graduation-cap"></i> Course Management</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($courses)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No courses available</h5>
                                    <p class="text-muted">Courses will appear here once they are created.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Course Title</th>
                                                <th>Description</th>
                                                <th>Materials</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($courses as $course): ?>
                                                <tr>
                                                    <td><?= $course['id'] ?></td>
                                                    <td><strong><?= esc($course['title']) ?></strong></td>
                                                    <td><?= esc(substr($course['description'], 0, 100)) ?><?= strlen($course['description']) > 100 ? '...' : '' ?></td>
                                                    <td><span class="badge bg-info"><?= $course['material_count'] ?? 0 ?> files</span></td>
                                                    <td><?= date('M d, Y', strtotime($course['created_at'])) ?></td>
                                                    <td>
                                                        <a href="<?= base_url("dashboard?section=upload&course_id={$course['id']}") ?>" 
                                                           class="btn btn-sm btn-primary mr-1" title="Manage Materials">
                                                            <i class="fas fa-upload"></i> Materials
                                                        </a>
                                                        <a href="<?= base_url("materials/view/{$course['id']}") ?>" 
                                                           class="btn btn-sm btn-info" title="View as Student">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Admin: Academic Years Management Section -->
        <?php if ($userRole === 'admin' && $currentSection === 'academic-years'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h3 class="mb-0"><i class="fas fa-calendar-alt"></i> Academic Years Management</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Create Form -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-plus"></i> Create New Academic Year</h5>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('admin/academic-year/create') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="year_start" class="form-label">Year Start <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="year_start" name="year_start" required min="2000" max="2100">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="year_end" class="form-label">Year End <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="year_end" name="year_end" required min="2000" max="2100">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Set as Active</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1">
                                                    <label class="form-check-label" for="is_active">Active Academic Year</label>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Academic Year
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- List -->
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-list"></i> Academic Years List</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($academicYears)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No academic years created yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Year Start</th>
                                                        <th>Year End</th>
                                                        <th>Status</th>
                                                        <th>Created</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($academicYears as $ay): ?>
                                                    <tr>
                                                        <td><?= $ay['id'] ?></td>
                                                        <td><strong><?= esc($ay['year_start']) ?></strong></td>
                                                        <td><strong><?= esc($ay['year_end']) ?></strong></td>
                                                        <td>
                                                            <?php if ($ay['is_active']): ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= date('M d, Y', strtotime($ay['created_at'])) ?></td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editAcademicYearModal<?= $ay['id'] ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <a href="<?= base_url('admin/academic-year/delete/' . $ay['id']) ?>" 
                                                               class="btn btn-sm btn-danger" 
                                                               onclick="return confirm('Are you sure you want to delete this academic year?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="editAcademicYearModal<?= $ay['id'] ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Academic Year</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="<?= base_url('admin/academic-year/update/' . $ay['id']) ?>" method="post">
                                                                    <?= csrf_field() ?>
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label for="year_start_edit<?= $ay['id'] ?>" class="form-label">Year Start <span class="text-danger">*</span></label>
                                                                            <input type="number" class="form-control" id="year_start_edit<?= $ay['id'] ?>" name="year_start" value="<?= esc($ay['year_start']) ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="year_end_edit<?= $ay['id'] ?>" class="form-label">Year End <span class="text-danger">*</span></label>
                                                                            <input type="number" class="form-control" id="year_end_edit<?= $ay['id'] ?>" name="year_end" value="<?= esc($ay['year_end']) ?>" required>
                                                                        </div>
                                                                        <div class="form-check form-switch">
                                                                            <input class="form-check-input" type="checkbox" id="is_active_edit<?= $ay['id'] ?>" name="is_active" value="1" <?= $ay['is_active'] ? 'checked' : '' ?>>
                                                                            <label class="form-check-label" for="is_active_edit<?= $ay['id'] ?>">Set as Active</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Admin: Semesters Management Section -->
        <?php if ($userRole === 'admin' && $currentSection === 'semesters'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h3 class="mb-0"><i class="fas fa-calendar"></i> Semesters Management</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Create Form -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-plus"></i> Create New Semester</h5>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('admin/semester/create') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="name" class="form-label">Semester Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., First Semester">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                                                <select class="form-select" id="semester" name="semester" required>
                                                    <option value="1st">1st Semester</option>
                                                    <option value="2nd">2nd Semester</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="term" class="form-label">Term <span class="text-danger">*</span></label>
                                                <select class="form-select" id="term" name="term" required>
                                                    <option value="1st">1st Term</option>
                                                    <option value="2nd">2nd Term</option>
                                                    <option value="3rd">3rd (Whole Semester)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                                <select class="form-select" id="academic_year_id" name="academic_year_id" required>
                                                    <option value="">Select Academic Year</option>
                                                    <?php foreach ($academicYears as $ay): ?>
                                                        <option value="<?= $ay['id'] ?>"><?= esc($ay['year_start']) ?> - <?= esc($ay['year_end']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="start_date" class="form-label">Start Date</label>
                                                <input type="date" class="form-control" id="start_date" name="start_date">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="end_date" class="form-label">End Date</label>
                                                <input type="date" class="form-control" id="end_date" name="end_date">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Set as Active</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1">
                                                    <label class="form-check-label" for="is_active">Active Semester</label>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Semester
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- List -->
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-list"></i> Semesters List</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($semesters)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No semesters created yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Semester</th>
                                                        <th>Term</th>
                                                        <th>Academic Year</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($semesters as $sem): ?>
                                                    <tr>
                                                        <td><?= $sem['id'] ?></td>
                                                        <td><strong><?= esc($sem['name']) ?></strong></td>
                                                        <td><span class="badge bg-primary"><?= esc($sem['semester'] ?? 'N/A') ?> Semester</span></td>
                                                        <td><span class="badge bg-info"><?= esc($sem['term']) ?> Term</span></td>
                                                        <td>
                                                            <?php if (isset($sem['academic_year'])): ?>
                                                                <?= esc($sem['academic_year']['year_start']) ?> - <?= esc($sem['academic_year']['year_end']) ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">N/A</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= $sem['start_date'] ? date('M d, Y', strtotime($sem['start_date'])) : 'N/A' ?></td>
                                                        <td><?= $sem['end_date'] ? date('M d, Y', strtotime($sem['end_date'])) : 'N/A' ?></td>
                                                        <td>
                                                            <?php if ($sem['is_active']): ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editSemesterModal<?= $sem['id'] ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <a href="<?= base_url('admin/semester/delete/' . $sem['id']) ?>" 
                                                               class="btn btn-sm btn-danger" 
                                                               onclick="return confirm('Are you sure you want to delete this semester?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="editSemesterModal<?= $sem['id'] ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Semester</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="<?= base_url('admin/semester/update/' . $sem['id']) ?>" method="post">
                                                                    <?= csrf_field() ?>
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label for="name_edit<?= $sem['id'] ?>" class="form-label">Semester Name <span class="text-danger">*</span></label>
                                                                            <input type="text" class="form-control" id="name_edit<?= $sem['id'] ?>" name="name" value="<?= esc($sem['name']) ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="semester_edit<?= $sem['id'] ?>" class="form-label">Semester <span class="text-danger">*</span></label>
                                                                            <select class="form-select" id="semester_edit<?= $sem['id'] ?>" name="semester" required>
                                                                                <option value="1st" <?= ($sem['semester'] ?? '1st') === '1st' ? 'selected' : '' ?>>1st Semester</option>
                                                                                <option value="2nd" <?= ($sem['semester'] ?? '1st') === '2nd' ? 'selected' : '' ?>>2nd Semester</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="term_edit<?= $sem['id'] ?>" class="form-label">Term <span class="text-danger">*</span></label>
                                                                            <select class="form-select" id="term_edit<?= $sem['id'] ?>" name="term" required>
                                                                                <option value="1st" <?= $sem['term'] === '1st' ? 'selected' : '' ?>>1st Term</option>
                                                                                <option value="2nd" <?= $sem['term'] === '2nd' ? 'selected' : '' ?>>2nd Term</option>
                                                                                <option value="3rd" <?= $sem['term'] === '3rd' ? 'selected' : '' ?>>3rd (Whole Semester)</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="academic_year_id_edit<?= $sem['id'] ?>" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                                                            <select class="form-select" id="academic_year_id_edit<?= $sem['id'] ?>" name="academic_year_id" required>
                                                                                <?php foreach ($academicYears as $ay): ?>
                                                                                    <option value="<?= $ay['id'] ?>" <?= $sem['academic_year_id'] == $ay['id'] ? 'selected' : '' ?>>
                                                                                        <?= esc($ay['year_start']) ?> - <?= esc($ay['year_end']) ?>
                                                                                    </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="start_date_edit<?= $sem['id'] ?>" class="form-label">Start Date</label>
                                                                            <input type="date" class="form-control" id="start_date_edit<?= $sem['id'] ?>" name="start_date" value="<?= $sem['start_date'] ? date('Y-m-d', strtotime($sem['start_date'])) : '' ?>">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="end_date_edit<?= $sem['id'] ?>" class="form-label">End Date</label>
                                                                            <input type="date" class="form-control" id="end_date_edit<?= $sem['id'] ?>" name="end_date" value="<?= $sem['end_date'] ? date('Y-m-d', strtotime($sem['end_date'])) : '' ?>">
                                                                        </div>
                                                                        <div class="form-check form-switch">
                                                                            <input class="form-check-input" type="checkbox" id="is_active_edit<?= $sem['id'] ?>" name="is_active" value="1" <?= $sem['is_active'] ? 'checked' : '' ?>>
                                                                            <label class="form-check-label" for="is_active_edit<?= $sem['id'] ?>">Set as Active</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Admin: Year Levels Management Section -->
        <?php if ($userRole === 'admin' && $currentSection === 'year-levels'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h3 class="mb-0"><i class="fas fa-user-graduate"></i> Year Levels Management</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Create Form -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-plus"></i> Create New Year Level</h5>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('admin/year-level/create') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="name" class="form-label">Year Level Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., First Year">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="level" class="form-label">Level Number <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="level" name="level" required min="1" max="10" placeholder="e.g., 1">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <input type="text" class="form-control" id="description" name="description" placeholder="Optional description">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Create Year Level
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- List -->
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-list"></i> Year Levels List</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($yearLevels)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No year levels created yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Level</th>
                                                        <th>Description</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($yearLevels as $yl): ?>
                                                    <tr>
                                                        <td><?= $yl['id'] ?></td>
                                                        <td><strong><?= esc($yl['name']) ?></strong></td>
                                                        <td><span class="badge bg-primary">Level <?= esc($yl['level']) ?></span></td>
                                                        <td><?= esc($yl['description'] ?: 'N/A') ?></td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editYearLevelModal<?= $yl['id'] ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <a href="<?= base_url('admin/year-level/delete/' . $yl['id']) ?>" 
                                                               class="btn btn-sm btn-danger" 
                                                               onclick="return confirm('Are you sure you want to delete this year level?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="editYearLevelModal<?= $yl['id'] ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Year Level</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="<?= base_url('admin/year-level/update/' . $yl['id']) ?>" method="post">
                                                                    <?= csrf_field() ?>
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label for="name_edit<?= $yl['id'] ?>" class="form-label">Year Level Name <span class="text-danger">*</span></label>
                                                                            <input type="text" class="form-control" id="name_edit<?= $yl['id'] ?>" name="name" value="<?= esc($yl['name']) ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="level_edit<?= $yl['id'] ?>" class="form-label">Level Number <span class="text-danger">*</span></label>
                                                                            <input type="number" class="form-control" id="level_edit<?= $yl['id'] ?>" name="level" value="<?= esc($yl['level']) ?>" required min="1" max="10">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="description_edit<?= $yl['id'] ?>" class="form-label">Description</label>
                                                                            <input type="text" class="form-control" id="description_edit<?= $yl['id'] ?>" name="description" value="<?= esc($yl['description'] ?? '') ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Admin: Assign Year Level to Students Section -->
        <?php if ($userRole === 'admin' && $currentSection === 'assign-year-level'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h3 class="mb-0"><i class="fas fa-user-tag"></i> Assign Year Level to Students</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Current Year Level</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($students)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No students found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?= $student['id'] ?></td>
                                                <td><strong><?= esc($student['name']) ?></strong></td>
                                                <td><?= esc($student['email']) ?></td>
                                                <td>
                                                    <?php if (isset($student['year_level'])): ?>
                                                        <span class="badge bg-primary"><?= esc($student['year_level']['name']) ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Not Assigned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form action="<?= base_url('admin/assign-year-level') ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="user_id" value="<?= $student['id'] ?>">
                                                        <div class="input-group" style="width: 250px;">
                                                            <select class="form-select form-select-sm" name="year_level_id" required>
                                                                <option value="">Select Year Level</option>
                                                                <?php foreach ($yearLevels as $yl): ?>
                                                                    <option value="<?= $yl['id'] ?>" <?= (isset($student['year_level']) && $student['year_level']['id'] == $yl['id']) ? 'selected' : '' ?>>
                                                                        <?= esc($yl['name']) ?> (Level <?= esc($yl['level']) ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-save"></i> Assign
                                                            </button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Teacher: My Courses Section -->
        <?php if ($userRole === 'teacher' && $currentSection === 'my-courses'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h3 class="mb-0"><i class="fas fa-book"></i> My Courses</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($courses)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-book fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No courses available</h5>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($courses as $course): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= esc($course['title']) ?></h5>
                                                <p class="card-text"><?= esc($course['description']) ?></p>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="fas fa-file"></i> <?= $course['material_count'] ?? 0 ?> materials
                                                    </small>
                                                </p>
                                                <a href="<?= base_url("dashboard?section=upload&course_id={$course['id']}") ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-upload"></i> Manage Materials
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Teacher/Admin: Upload Materials Section -->
        <?php if (($userRole === 'teacher' || $userRole === 'admin') && $currentSection === 'upload'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h3 class="mb-0">
                                <i class="fas fa-upload"></i> 
                                <?php if (isset($course)): ?>
                                    Upload Material for: <?= esc($course['title']) ?>
                                <?php else: ?>
                                    Upload Course Material
                                <?php endif; ?>
                            </h3>
                            <a href="<?= base_url('dashboard?section=' . ($userRole === 'admin' ? 'courses' : 'my-courses')) ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (!isset($course) && !empty($courses)): ?>
                                <!-- Course Selection -->
                                <div class="row">
                                    <?php foreach ($courses as $c): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title"><?= esc($c['title']) ?></h6>
                                                <p class="card-text small"><?= esc(substr($c['description'], 0, 100)) ?>...</p>
                                                <a href="<?= base_url("dashboard?section=upload&course_id={$c['id']}") ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-upload"></i> Upload Materials
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif (isset($course)): ?>
                                <!-- Upload Form -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">Upload New Material</h5>
                                            </div>
                                            <div class="card-body">
                                                <?php 
                                                $formAction = ($userRole === 'admin') ? "admin/course/{$course['id']}/upload" : "teacher/course/{$course['id']}/upload";
                                                ?>
                                                <form action="<?= base_url($formAction) ?>" method="post" enctype="multipart/form-data" id="uploadForm">
                                                    <?= csrf_field() ?>
                                                    <div class="mb-3">
                                                        <label for="material_file" class="form-label">Select File:</label>
                                                        <input type="file" 
                                                               class="form-control" 
                                                               id="material_file" 
                                                               name="material_file" 
                                                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt" 
                                                               required>
                                                        <small class="form-text text-muted">
                                                            Allowed formats: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT (Max: 10MB)
                                                        </small>
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-upload"></i> Upload Material
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">Course Information</h5>
                                            </div>
                                            <div class="card-body">
                                                <h6><strong>Course Title:</strong></h6>
                                                <p><?= esc($course['title']) ?></p>
                                                
                                                <h6><strong>Description:</strong></h6>
                                                <p><?= esc($course['description'] ?: 'No description available.') ?></p>
                                                
                                                <h6><strong>Total Materials:</strong></h6>
                                                <p class="text-primary"><?= count($materials ?? []) ?> files</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Materials List -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">Course Materials</h5>
                                            </div>
                                            <div class="card-body">
                                                <?php if (empty($materials)): ?>
                                                    <div class="text-center py-4">
                                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">No materials uploaded yet.</p>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>File Name</th>
                                                                    <th>Type</th>
                                                                    <th>Size</th>
                                                                    <th>Uploaded</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($materials as $material): ?>
                                                                    <tr>
                                                                        <td><i class="fas fa-file"></i> <?= esc($material['file_name']) ?></td>
                                                                        <td><span class="badge bg-info"><?= strtoupper($material['file_type']) ?></span></td>
                                                                        <td><?= formatBytes($material['file_size']) ?></td>
                                                                        <td><?= date('M d, Y H:i', strtotime($material['created_at'])) ?></td>
                                                                        <td>
                                                                            <a href="<?= base_url("materials/viewfile/{$material['id']}") ?>" 
                                                                               class="btn btn-sm btn-info mr-1" title="View" target="_blank">
                                                                                <i class="fas fa-eye"></i>
                                                                            </a>
                                                                            <a href="<?= base_url("materials/download/{$material['id']}") ?>" 
                                                                               class="btn btn-sm btn-success mr-1" title="Download">
                                                                                <i class="fas fa-download"></i>
                                                                            </a>
                                                                            <a href="<?= base_url("materials/delete/{$material['id']}") ?>" 
                                                                               class="btn btn-sm btn-danger" 
                                                                               onclick="return confirm('Are you sure you want to delete this material?')" 
                                                                               title="Delete">
                                                                                <i class="fas fa-trash"></i>
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Teacher: Enroll Students Section -->
        <?php if ($userRole === 'teacher' && $currentSection === 'enroll-students'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h3 class="mb-0"><i class="fas fa-user-plus"></i> Enroll Students</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Select Course</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if (!empty($courses)): ?>
                                                <div class="list-group">
                                                    <?php foreach ($courses as $c): ?>
                                                        <a href="<?= base_url("dashboard?section=enroll-students&course_id={$c['id']}") ?>" 
                                                           class="list-group-item list-group-item-action <?= (isset($selectedCourse) && $selectedCourse['id'] == $c['id']) ? 'active' : '' ?>">
                                                            <h6 class="mb-1"><?= esc($c['title']) ?></h6>
                                                            <p class="mb-1 small"><?= esc(substr($c['description'], 0, 80)) ?><?= strlen($c['description']) > 80 ? '...' : '' ?></p>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted">No courses available.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <?php if (isset($selectedCourse)): ?>
                                        <div class="card">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0"><i class="fas fa-book"></i> <?= esc($selectedCourse['title']) ?></h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <h6>Enroll New Student</h6>
                                                        <form id="enrollStudentForm">
                                                            <div class="mb-3">
                                                                <label for="studentSelect" class="form-label">Select Student:</label>
                                                                <select class="form-select" id="studentSelect" required>
                                                                    <option value="">Choose a student...</option>
                                                                    <?php if (!empty($students)): ?>
                                                                        <?php 
                                                                        // Get enrolled student IDs for this course
                                                                        $enrolledStudentIds = array_column($enrolledStudents ?? [], 'user_id');
                                                                        ?>
                                                                        <?php foreach ($students as $student): ?>
                                                                            <?php if (!in_array($student['id'], $enrolledStudentIds)): ?>
                                                                                <option value="<?= $student['id'] ?>"><?= esc($student['name']) ?> (<?= esc($student['email']) ?>)</option>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </select>
                                                            </div>
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fas fa-user-plus"></i> Enroll Student
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Course Information</h6>
                                                        <p><strong>Title:</strong> <?= esc($selectedCourse['title']) ?></p>
                                                        <p><strong>Description:</strong> <?= esc($selectedCourse['description'] ?: 'No description') ?></p>
                                                        <p><strong>Enrolled Students:</strong> <span class="badge bg-primary"><?= count($enrolledStudents ?? []) ?></span></p>
                                                    </div>
                                                </div>

                                                <hr>

                                                <h6>Enrolled Students</h6>
                                                <?php if (!empty($enrolledStudents)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Name</th>
                                                                    <th>Email</th>
                                                                    <th>Enrolled Date</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($enrolledStudents as $enrolled): ?>
                                                                    <tr>
                                                                        <td><?= esc($enrolled['name']) ?></td>
                                                                        <td><?= esc($enrolled['email']) ?></td>
                                                                        <td><?= date('M d, Y', strtotime($enrolled['created_at'])) ?></td>
                                                                        <td>
                                                                            <span class="badge bg-success">Enrolled</span>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i> No students enrolled in this course yet.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="card">
                                            <div class="card-body text-center py-5">
                                                <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
                                                <h5 class="text-muted">Select a Course</h5>
                                                <p class="text-muted">Choose a course from the list to enroll students.</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Teacher: Create Assignment Section -->
        <?php if ($userRole === 'teacher' && $currentSection === 'create-assignment'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white">
                            <h3 class="mb-0"><i class="fas fa-plus-circle"></i> Create New Assignment</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('assignment/create') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                    <select class="form-select" id="course_id" name="course_id" required>
                                        <option value="">Select a course...</option>
                                        <?php if (!empty($courses)): ?>
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="title" class="form-label">Assignment Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="assignment_type" class="form-label">Submission Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="assignment_type" name="assignment_type" required>
                                            <option value="file">File Upload</option>
                                            <option value="text">Text Entry</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="due_date" class="form-label">Due Date</label>
                                        <input type="datetime-local" class="form-control" id="due_date" name="due_date">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="total_points" class="form-label">Total Points</label>
                                        <input type="number" class="form-control" id="total_points" name="total_points" value="100" min="1">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Assignment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Teacher: Assignments List Section -->
        <?php if ($userRole === 'teacher' && $currentSection === 'assignments'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white">
                            <h3 class="mb-0"><i class="fas fa-tasks"></i> Course Assignments</h3>
                            <a href="<?= base_url('dashboard?section=create-assignment') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-plus"></i> Create Assignment
                            </a>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (!isset($course) && !empty($courses)): ?>
                                <!-- Course Selection -->
                                <div class="row">
                                    <?php foreach ($courses as $c): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?= esc($c['title']) ?></h6>
                                                    <p class="card-text small"><?= esc(substr($c['description'], 0, 100)) ?>...</p>
                                                    <a href="<?= base_url("dashboard?section=assignments&course_id={$c['id']}") ?>" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-tasks"></i> View Assignments
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif (isset($course)): ?>
                                <h5>Assignments for: <?= esc($course['title']) ?></h5>
                                <?php if (!empty($assignments)): ?>
                                    <div class="table-responsive mt-3">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Due Date</th>
                                                    <th>Points</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($assignments as $assignment): ?>
                                                    <tr>
                                                        <td><strong><?= esc($assignment['title']) ?></strong></td>
                                                        <td><?= $assignment['due_date'] ? date('M d, Y H:i', strtotime($assignment['due_date'])) : 'No due date' ?></td>
                                                        <td><?= esc($assignment['total_points']) ?></td>
                                                        <td><?= date('M d, Y', strtotime($assignment['created_at'])) ?></td>
                                                        <td>
                                                            <a href="<?= base_url("dashboard?section=view-assignment&assignment_id={$assignment['id']}") ?>" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <a href="<?= base_url("assignment/delete/{$assignment['id']}") ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this assignment?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i> No assignments created for this course yet.
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Teacher/Student: View Assignment Section -->
        <?php if (($userRole === 'teacher' || $userRole === 'student') && $currentSection === 'view-assignment' && isset($assignment)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white">
                            <h3 class="mb-0"><i class="fas fa-file-alt"></i> <?= esc($assignment['title']) ?></h3>
                            <a href="<?= base_url('dashboard?section=assignments' . ($userRole === 'teacher' ? '&course_id=' . $assignment['course_id'] : '')) ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <h5>Assignment Details</h5>
                                    <p><strong>Course:</strong> <?= esc($assignment['course_title']) ?></p>
                                    <p><strong>Description:</strong> <?= esc($assignment['description'] ?: 'No description') ?></p>
                                    <p><strong>Due Date:</strong> <?= $assignment['due_date'] ? date('M d, Y H:i', strtotime($assignment['due_date'])) : 'No due date' ?></p>
                                    <p><strong>Total Points:</strong> <?= esc($assignment['total_points']) ?></p>
                                    <p><strong>Created by:</strong> <?= esc($assignment['created_by_name']) ?></p>
                                </div>
                            </div>

                            <?php if ($userRole === 'student'): ?>
                                <!-- Student Submission Form -->
                                <div class="card mt-4">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="fas fa-upload"></i> Submit Assignment</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($has_submitted) && $has_submitted): ?>
                                            <div class="alert alert-success">
                                                <i class="fas fa-check-circle"></i> You have already submitted this assignment.
                                                <p class="mb-0 mt-2">
                                                    <strong>Submitted:</strong> <?= date('M d, Y H:i', strtotime($submission['submitted_at'])) ?><br>
                                                    <?php if (($submission['submission_type'] ?? 'file') === 'text'): ?>
                                                        <strong>Type:</strong> Text Entry<br>
                                                        <strong>Answer:</strong><br>
                                                        <div class="mt-2 p-3 bg-light border rounded">
                                                            <?= nl2br(esc($submission['text_entry'] ?? '')) ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <strong>Type:</strong> File Upload<br>
                                                        <strong>File:</strong> <?= esc($submission['file_name'] ?? 'N/A') ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            
                                            <?php if (isset($submission['score']) && $submission['score'] !== null): ?>
                                                <div class="alert alert-info mt-3">
                                                    <h6 class="alert-heading"><i class="fas fa-star"></i> Grade</h6>
                                                    <p class="mb-2">
                                                        <strong>Score:</strong> 
                                                        <span class="badge bg-success fs-6"><?= number_format($submission['score'], 2) ?> / <?= esc($assignment['total_points']) ?></span>
                                                    </p>
                                                    <?php if (isset($submission['feedback']) && !empty($submission['feedback'])): ?>
                                                        <hr>
                                                        <p class="mb-0">
                                                            <strong>Feedback:</strong><br>
                                                            <?= nl2br(esc($submission['feedback'])) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning mt-3">
                                                    <i class="fas fa-clock"></i> Your submission is pending grading.
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php $assignmentType = $assignment['assignment_type'] ?? 'file'; ?>
                                            <form action="<?= base_url('assignment/submit') ?>" method="post" enctype="multipart/form-data" id="assignmentSubmissionForm">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                                <input type="hidden" name="assignment_type" value="<?= $assignmentType ?>">
                                                
                                                <?php if ($assignmentType === 'text'): ?>
                                                    <div class="mb-3">
                                                        <label for="text_entry" class="form-label">Your Answer <span class="text-danger">*</span></label>
                                                        <textarea class="form-control" id="text_entry" name="text_entry" rows="10" required placeholder="Enter your assignment submission here..."></textarea>
                                                        <small class="form-text text-muted">Type your assignment submission in the text area above.</small>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="mb-3">
                                                        <label for="submission_file" class="form-label">Upload File <span class="text-danger">*</span></label>
                                                        <input type="file" class="form-control" id="submission_file" name="submission_file" required accept=".pdf,.doc,.docx,.txt,.zip,.rar">
                                                        <small class="form-text text-muted">Allowed formats: PDF, DOC, DOCX, TXT, ZIP, RAR (Max: 10MB)</small>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-upload"></i> Submit Assignment
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php elseif ($userRole === 'teacher'): ?>
                                <!-- Teacher: View Submissions -->
                                <div class="card mt-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-users"></i> Student Submissions</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($submissions)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Student Name</th>
                                                            <th>Email</th>
                                                            <th>Type</th>
                                                            <th>Submission</th>
                                                            <th>Submitted At</th>
                                                            <th>Score</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($submissions as $submission): ?>
                                                            <tr>
                                                                <td><?= esc($submission['student_name']) ?></td>
                                                                <td><?= esc($submission['student_email']) ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?= ($submission['submission_type'] ?? 'file') === 'text' ? 'info' : 'primary' ?>">
                                                                        <?= ($submission['submission_type'] ?? 'file') === 'text' ? 'Text' : 'File' ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php if (($submission['submission_type'] ?? 'file') === 'text'): ?>
                                                                        <span class="text-muted">Text Entry</span>
                                                                    <?php else: ?>
                                                                        <?= esc($submission['file_name'] ?? 'N/A') ?>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= date('M d, Y H:i', strtotime($submission['submitted_at'])) ?></td>
                                                                <td>
                                                                    <?php if (isset($submission['score']) && $submission['score'] !== null): ?>
                                                                        <strong class="text-success"><?= number_format($submission['score'], 2) ?></strong> / <?= esc($assignment['total_points']) ?>
                                                                        <?php if (isset($submission['feedback']) && !empty($submission['feedback'])): ?>
                                                                            <br><small class="text-muted" title="<?= esc($submission['feedback']) ?>"><?= esc(substr($submission['feedback'], 0, 30)) ?><?= strlen($submission['feedback']) > 30 ? '...' : '' ?></small>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-warning">Not Graded</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if (($submission['submission_type'] ?? 'file') === 'text'): ?>
                                                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#textSubmissionModal<?= $submission['id'] ?>">
                                                                            <i class="fas fa-eye"></i> View
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <a href="<?= base_url("assignment/submission/view/{$submission['id']}") ?>" class="btn btn-sm btn-info" target="_blank">
                                                                            <i class="fas fa-eye"></i> View
                                                                        </a>
                                                                        <a href="<?= base_url("assignment/submission/download/{$submission['id']}") ?>" class="btn btn-sm btn-success">
                                                                            <i class="fas fa-download"></i> Download
                                                                        </a>
                                                                    <?php endif; ?>
                                                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#gradeModal<?= $submission['id'] ?>">
                                                                        <i class="fas fa-check-circle"></i> <?= isset($submission['score']) && $submission['score'] !== null ? 'Update Grade' : 'Grade' ?>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            
                                                            <!-- Text Submission Modal -->
                                                            <?php if (($submission['submission_type'] ?? 'file') === 'text'): ?>
                                                                <div class="modal fade" id="textSubmissionModal<?= $submission['id'] ?>" tabindex="-1">
                                                                    <div class="modal-dialog modal-lg">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Text Submission - <?= esc($submission['student_name']) ?></h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <p><strong>Student:</strong> <?= esc($submission['student_name']) ?> (<?= esc($submission['student_email']) ?>)</p>
                                                                                <p><strong>Submitted:</strong> <?= date('M d, Y H:i', strtotime($submission['submitted_at'])) ?></p>
                                                                                <hr>
                                                                                <h6>Submission:</h6>
                                                                                <div class="p-3 bg-light border rounded" style="max-height: 400px; overflow-y: auto;">
                                                                                    <?= nl2br(esc($submission['text_entry'] ?? 'No text entry')) ?>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                <button type="button" class="btn btn-warning" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#gradeModal<?= $submission['id'] ?>">
                                                                                    <i class="fas fa-check-circle"></i> Grade
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Grade Submission Modal -->
                                                            <div class="modal fade" id="gradeModal<?= $submission['id'] ?>" tabindex="-1">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header bg-warning">
                                                                            <h5 class="modal-title"><i class="fas fa-check-circle"></i> Grade Submission</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <form action="<?= base_url('assignment/grade') ?>" method="post" id="gradeForm<?= $submission['id'] ?>">
                                                                            <?= csrf_field() ?>
                                                                            <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                                                                            <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                                                            <div class="modal-body">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label"><strong>Student:</strong> <?= esc($submission['student_name']) ?></label>
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <label for="score<?= $submission['id'] ?>" class="form-label">Score <span class="text-danger">*</span></label>
                                                                                    <div class="input-group">
                                                                                        <input type="number" class="form-control" id="score<?= $submission['id'] ?>" name="score" 
                                                                                               value="<?= isset($submission['score']) && $submission['score'] !== null ? number_format($submission['score'], 2) : '' ?>" 
                                                                                               min="0" max="<?= esc($assignment['total_points']) ?>" step="0.01" required>
                                                                                        <span class="input-group-text">/ <?= esc($assignment['total_points']) ?></span>
                                                                                    </div>
                                                                                    <small class="form-text text-muted">Enter score out of <?= esc($assignment['total_points']) ?> points</small>
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <label for="feedback<?= $submission['id'] ?>" class="form-label">Feedback</label>
                                                                                    <textarea class="form-control" id="feedback<?= $submission['id'] ?>" name="feedback" rows="4" placeholder="Enter feedback for the student..."><?= esc($submission['feedback'] ?? '') ?></textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                <button type="submit" class="btn btn-warning">
                                                                                    <i class="fas fa-save"></i> Save Grade
                                                                                </button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> No submissions yet.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Student: Enrollments Section -->
        <?php if ($userRole === 'student' && $currentSection === 'enrollments'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0"><i class="fas fa-graduation-cap"></i> My Enrollments</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($enrollments)): ?>
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
                                                        <a href="<?= base_url("dashboard?section=materials&course_id={$enrollment['course_id']}") ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-book"></i> Materials
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No enrollments yet</h5>
                                    <p class="text-muted">Browse available courses to enroll.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Student: Assignments Section -->
        <?php if ($userRole === 'student' && $currentSection === 'assignments'): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-dark">
                            <h3 class="mb-0"><i class="fas fa-tasks"></i> My Assignments</h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-dark mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($assignments)): ?>
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
                                                    <td><?= esc($assignment['course_title']) ?></td>
                                                    <td>
                                                        <?php if ($assignment['due_date']): ?>
                                                            <span class="text-<?= strtotime($assignment['due_date']) < time() ? 'danger' : 'dark' ?>">
                                                                <?= date('M d, Y H:i', strtotime($assignment['due_date'])) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">No due date</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= isset($assignment['has_submitted']) && $assignment['has_submitted'] ? 'success' : 'warning' ?>">
                                                            <?= isset($assignment['has_submitted']) && $assignment['has_submitted'] ? 'Submitted' : 'Pending' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="<?= base_url("dashboard?section=view-assignment&assignment_id={$assignment['id']}") ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        <?php if (!isset($assignment['has_submitted']) || !$assignment['has_submitted']): ?>
                                                            <a href="<?= base_url("dashboard?section=view-assignment&assignment_id={$assignment['id']}") ?>" class="btn btn-sm btn-outline-success">
                                                                <i class="fas fa-upload"></i> Submit
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No assignments available</h5>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Admin/Teacher/Student: Course Materials Section -->
        <?php if (($userRole === 'admin' || $userRole === 'teacher' || $userRole === 'student') && $currentSection === 'materials' && isset($course)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">
                                <i class="fas fa-book"></i> Course Materials: <?= esc($course['title']) ?>
                            </h3>
                            <a href="<?= base_url('dashboard?section=' . ($userRole === 'admin' ? 'courses' : ($userRole === 'teacher' ? 'my-courses' : ''))) ?>" class="btn btn-sm btn-light mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Course Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h5 class="alert-heading">
                                            <i class="fas fa-info-circle"></i> Course Information
                                        </h5>
                                        <p class="mb-0">
                                            <strong>Description:</strong> <?= esc($course['description'] ?: 'No description available.') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Materials List -->
                            <div class="row">
                                <div class="col-12">
                                    <?php if (empty($materials)): ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">No materials available yet</h5>
                                            <p class="text-muted">Course materials will appear here once the instructor uploads them.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="row">
                                            <?php foreach ($materials as $material): ?>
                                                <div class="col-md-6 col-lg-4 mb-4">
                                                    <div class="card h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-shrink-0">
                                                                    <?php
                                                                    $fileType = strtolower($material['file_type']);
                                                                    $iconClass = 'fas fa-file';
                                                                    if (in_array($fileType, ['pdf'])) {
                                                                        $iconClass = 'fas fa-file-pdf text-danger';
                                                                    } elseif (in_array($fileType, ['doc', 'docx'])) {
                                                                        $iconClass = 'fas fa-file-word text-primary';
                                                                    } elseif (in_array($fileType, ['ppt', 'pptx'])) {
                                                                        $iconClass = 'fas fa-file-powerpoint text-warning';
                                                                    } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                                        $iconClass = 'fas fa-file-image text-success';
                                                                    } elseif (in_array($fileType, ['txt'])) {
                                                                        $iconClass = 'fas fa-file-alt text-secondary';
                                                                    }
                                                                    ?>
                                                                    <i class="<?= $iconClass ?> fa-2x"></i>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="card-title mb-1"><?= esc($material['file_name']) ?></h6>
                                                                    <p class="card-text text-muted small mb-2">
                                                                        <span class="badge bg-light text-dark"><?= strtoupper($material['file_type']) ?></span>
                                                                        <span class="ms-2"><?= formatBytes($material['file_size']) ?></span>
                                                                    </p>
                                                                    <p class="card-text text-muted small mb-3">
                                                                        <i class="fas fa-clock"></i>
                                                                        Uploaded <?= date('M d, Y', strtotime($material['created_at'])) ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer bg-transparent">
                                                            <div class="btn-group w-100" role="group">
                                                                <a href="<?= base_url("materials/viewfile/{$material['id']}") ?>" 
                                                                   class="btn btn-info btn-sm" target="_blank">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                                <a href="<?= base_url("materials/download/{$material['id']}") ?>" 
                                                                   class="btn btn-primary btn-sm">
                                                                    <i class="fas fa-download"></i> Download
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Helper function for formatting bytes -->
    <?php
    if (!function_exists('formatBytes')) {
        function formatBytes($bytes, $precision = 2) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= (1 << (10 * $pow));
            return round($bytes, $precision) . ' ' . $units[$pow];
        }
    }
    ?>

    <!-- jQuery and AJAX Enrollment Script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle navbar button clicks
        $('a[data-bs-toggle="collapse"]').on('click', function() {
            const target = $(this).data('bs-target');
            const otherTarget = target === '#enrolledCourses' ? '#availableCourses' : '#enrolledCourses';
            $(otherTarget).collapse('hide');
        });
        
        // Handle enroll button clicks
        $(document).on('click', '.enroll-btn', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const courseId = button.data('course-id');
            const courseTitle = button.data('course-title');
            
            button.prop('disabled', true);
            button.html('<i class="fas fa-spinner fa-spin"></i> Enrolling...');
            
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
                        showAlert('success', response.message);
                        if (response.csrf_token) {
                            updateCSRFToken(response.csrf_token);
                        }
                        button.closest('.col-md-6').fadeOut(500, function() {
                            $(this).remove();
                            if ($('#availableCoursesContainer .col-md-6').length === 0) {
                                $('#availableCoursesContainer').html(`
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <p class="text-muted">Great! You're enrolled in all available courses.</p>
                                    </div>
                                `);
                            }
                        });
                        if (typeof loadEnrollments === 'function') {
                            loadEnrollments();
                        }
                        if (typeof loadMyCourses === 'function') {
                            loadMyCourses();
                        }
                    } else {
                        showAlert('danger', response.message);
                        button.prop('disabled', false);
                        button.html('<i class="fas fa-plus"></i> Enroll Now');
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('danger', 'An error occurred while enrolling. Please try again.');
                    button.prop('disabled', false);
                    button.html('<i class="fas fa-plus"></i> Enroll Now');
                }
            });
        });

        // Handle teacher enrollment form submission
        $('#enrollStudentForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const studentId = $('#studentSelect').val();
            const courseId = new URLSearchParams(window.location.search).get('course_id');
            
            if (!studentId || !courseId) {
                showAlert('danger', 'Please select a student and course.');
                return;
            }
            
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Enrolling...');
            
            $.ajax({
                url: '<?= base_url('course/enroll') ?>',
                type: 'POST',
                data: {
                    course_id: courseId,
                    student_id: studentId,
                    '<?= csrf_token() ?>': getCSRFToken()
                },
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        if (response.csrf_token) {
                            updateCSRFToken(response.csrf_token);
                        }
                        // Reload the page to refresh the enrolled students list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('danger', response.message);
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('danger', 'An error occurred while enrolling the student. Please try again.');
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }
            });
        });
    });
    
    function showAlert(type, message) {
        $('.alert-dismissible.position-fixed').remove();
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
        setTimeout(() => {
            $('.alert-dismissible.position-fixed').fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    let currentCSRFToken = '<?= csrf_hash() ?>';
    function getCSRFToken() {
        return currentCSRFToken;
    }
    function updateCSRFToken(newToken) {
        currentCSRFToken = newToken;
    }

    // User role update function (for admin)
    function updateUserRole(userId, newRole) {
        if (!confirm(`Are you sure you want to change this user's role to ${newRole}?`)) {
            location.reload();
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
                showAlert('success', data.message);
                const badge = document.querySelector(`#user-row-${userId} .badge`);
                if (badge) {
                    badge.textContent = data.newRole.charAt(0).toUpperCase() + data.newRole.slice(1);
                    badge.className = `badge ${getRoleClass(data.newRole)}`;
                }
            } else {
                showAlert('danger', data.message);
                location.reload();
            }
        })
        .catch(error => {
            showAlert('danger', 'Error updating role: ' + error.message);
            location.reload();
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
    </script>
<?= $this->endSection() ?>
