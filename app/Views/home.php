<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="text-center mb-4">LMS-MALILAY</h1>
            <div class="card bg-secondary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Learning Management System</h5>
                    <p class="card-text">
                        Welcome to our LMS system! This platform provides a complete authentication system 
                        with user registration, login, and protected dashboard functionality.
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-primary text-white mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Authentication Features</h6>
                            <ul class="list-unstyled mb-0">
                                <li>✓ User Registration</li>
                                <li>✓ Secure Login</li>
                                <li>✓ Protected Dashboard</li>
                                <li>✓ Session Management</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white mb-3">
                        <div class="card-body">
                            <h6 class="card-title">LMS Features</h6>
                            <ul class="list-unstyled mb-0">
                                <li>✓ Course Management</li>
                                <li>✓ Student Enrollments</li>
                                <li>✓ Lesson Content</li>
                                <li>✓ Quiz System</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (session()->get('logged_in')): ?>
                <div class="text-center mt-4">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-success">Go to Dashboard</a>
                </div>
            <?php endif; ?>

            <script>
                // Redirect malformed URLs to appropriate dashboard
                if (window.location.href.includes('../') || window.location.href.includes('..\\')) {
                    const role = '<?= strtolower(session('role') ?? '') ?>';
                    let redirectUrl = '/ITE311-MALILAY/';
                    
                    if (role === 'admin') {
                        redirectUrl = '/ITE311-MALILAY/admin/dashboard';
                    } else if (role === 'teacher') {
                        redirectUrl = '/ITE311-MALILAY/teacher/dashboard';
                    } else if (role === 'student') {
                        redirectUrl = '/ITE311-MALILAY/dashboard';
                    } else if ('<?= session()->get('logged_in') ? 'true' : 'false' ?>' === 'true') {
                        redirectUrl = '/ITE311-MALILAY/dashboard';
                    } else {
                        redirectUrl = '/ITE311-MALILAY/login';
                    }
                    
                    window.location.href = redirectUrl;
                }
            </script>
        </div>
    </div>
<?= $this->endSection() ?>
