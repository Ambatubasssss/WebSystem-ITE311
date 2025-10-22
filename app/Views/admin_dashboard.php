<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0"><i class="fas fa-user-shield"></i> Admin Dashboard</h3>
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

                        <div class="text-center py-5">
                            <i class="fas fa-user-shield fa-5x text-danger mb-4"></i>
                            <h2 class="text-danger">Welcome, Admin!</h2>
                            <p class="lead text-muted">You have successfully accessed the Admin Dashboard.</p>
                            <p class="text-muted">This is your administrative workspace for managing the entire system.</p>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <h5>User Management</h5>
                                        <p class="mb-3">Manage users and roles</p>
                                        <button class="btn btn-light btn-sm" disabled>
                                            <i class="fas fa-users mr-1"></i> Manage Users
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                                        <h5>Course Management</h5>
                                        <p class="mb-3">Manage courses and materials</p>
                                        <a href="<?= base_url('admin/courses') ?>" class="btn btn-light btn-sm">
                                            <i class="fas fa-book mr-1"></i> Manage Courses
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-upload fa-3x mb-3"></i>
                                        <h5>Upload Materials</h5>
                                        <p class="mb-3">Upload course materials</p>
                                        <a href="<?= base_url('admin/courses') ?>" class="btn btn-light btn-sm">
                                            <i class="fas fa-upload mr-1"></i> Upload Files
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                        <h5>Analytics</h5>
                                        <p class="mb-3">View system statistics</p>
                                        <button class="btn btn-dark btn-sm" disabled>
                                            <i class="fas fa-chart-line mr-1"></i> Coming Soon
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions Section -->
                        <div class="row mt-5">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-bolt mr-2"></i>
                                            Quick Actions
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="d-grid gap-2">
                                                    <a href="<?= base_url('admin/courses') ?>" class="btn btn-primary btn-lg">
                                                        <i class="fas fa-upload mr-2"></i>
                                                        Upload Materials
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-grid gap-2">
                                                    <a href="<?= base_url('admin/courses') ?>" class="btn btn-success btn-lg">
                                                        <i class="fas fa-list mr-2"></i>
                                                        Manage Courses
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-grid gap-2">
                                                    <a href="<?= base_url('admin/users') ?>" class="btn btn-warning btn-lg">
                                                        <i class="fas fa-users mr-2"></i>
                                                        Manage Users
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
