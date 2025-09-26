    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-white" href="<?= base_url(''); ?>">LMS-MALILAY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url(''); ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('about'); ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('contact'); ?>">Contact</a>
                    </li>
                    <?php if (session()->get('logged_in')): ?>
                        <?php $role = strtolower(session('role') ?? ''); ?>
                        <?php if ($role === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard'); ?>">Admin Dashboard</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                    Manage Users
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" style="width: 500px; max-height: 400px; overflow-y: auto;">
                                    <li class="dropdown-header">
                                        <div class="alert alert-info mb-2">
                                            <strong>Admin Access:</strong> This page is only accessible to administrators.
                                        </div>
                                        <h6>All Users</h6>
                                    </li>
                                    <?php
                                    $userModel = new \App\Models\UserModel();
                                    $users = $userModel->findAll();
                                    ?>
                                    <li class="dropdown-item-text">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Role</th>
                                                        <th>Created</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($users as $user): ?>
                                                    <tr>
                                                        <td><?= $user['id'] ?></td>
                                                        <td><?= esc($user['name']) ?></td>
                                                        <td><?= esc($user['email']) ?></td>
                                                        <td><span class="badge bg-primary"><?= ucfirst($user['role']) ?></span></td>
                                                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link text-white" href="#" onclick="return false;">Settings</a></li>
                        <?php elseif ($role === 'teacher'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard'); ?>">Teacher Dashboard</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                    My Courses
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" style="width: 450px; max-height: 400px; overflow-y: auto;">
                                    <li class="dropdown-header">
                                        <div class="alert alert-success mb-2">
                                            <strong>Teacher Access:</strong> This page is only accessible to teachers.
                                        </div>
                                        <h6>My Courses</h6>
                                    </li>
                                    <?php
                                    $courses = ['Math 101', 'Science 202', 'Physics 301'];
                                    ?>
                                    <li class="dropdown-item-text">
                                        <div class="row">
                                            <?php foreach ($courses as $course): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?= esc($course) ?></h6>
                                                        <p class="card-text small">Course description and details.</p>
                                                        <a href="#" class="btn btn-sm btn-primary">Manage Course</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link text-white" href="#" onclick="return false;">Grades</a></li>
                        <?php elseif ($role === 'student'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard'); ?>">Student Dashboard</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                    My Enrollments
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" style="width: 450px; max-height: 400px; overflow-y: auto;">
                                    <li class="dropdown-header">
                                        <div class="alert alert-primary mb-2">
                                            <strong>Student Access:</strong> This page is only accessible to students.
                                        </div>
                                        <h6>My Enrollments</h6>
                                    </li>
                                    <?php
                                    $enrollments = [
                                        ['course' => 'History 101', 'instructor' => 'Dr. Smith', 'status' => 'Active'],
                                        ['course' => 'Art 303', 'instructor' => 'Prof. Johnson', 'status' => 'Active']
                                    ];
                                    ?>
                                    <li class="dropdown-item-text">
                                        <div class="row">
                                            <?php foreach ($enrollments as $enrollment): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?= esc($enrollment['course']) ?></h6>
                                                        <p class="card-text small">
                                                            <strong>Instructor:</strong> <?= esc($enrollment['instructor']) ?><br>
                                                            <strong>Status:</strong> <span class="badge bg-success"><?= esc($enrollment['status']) ?></span>
                                                        </p>
                                                        <a href="#" class="btn btn-sm btn-primary">View Course</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link text-white" href="#" onclick="return false;">Assignments</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('dashboard'); ?>">Dashboard</a></li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= base_url('logout'); ?>">Logout</a>
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


