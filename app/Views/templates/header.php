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
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('admin/dashboard'); ?>">Admin Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="#">Manage Users</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="#">Manage Courses</a></li>
                        <?php elseif ($role === 'teacher'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('teacher/dashboard'); ?>">Teacher Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="#">My Courses</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="#">New Lesson</a></li>
                        <?php elseif ($role === 'student'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= base_url('student/dashboard'); ?>">Student Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="#">My Enrollments</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="#">Deadlines</a></li>
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


