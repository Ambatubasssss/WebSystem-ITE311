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
                                            <a href="<?= base_url('/') ?>" class="btn btn-outline-primary">Go to Homepage</a>
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

                        <!-- Enrolled Courses Section -->
                        <div class="row mt-4">
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
                                                                <small class="text-muted">
                                                                    <i class="fas fa-calendar"></i> 
                                                                    Enrolled: <?= date('M j, Y', strtotime($enrollment['enrollment_date'])) ?>
                                                                </small>
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
                        <div class="row mt-4">
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

                        <div class="alert alert-info">
                            <h6>Protected Dashboard</h6>
                            <p class="mb-0">This is a protected page that only logged-in users can access. If you can see this, it means your authentication system is working correctly!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
