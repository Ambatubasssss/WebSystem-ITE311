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
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <h5>User Management</h5>
                                        <p class="mb-0">Manage users and roles</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-cogs fa-3x mb-3"></i>
                                        <h5>System Settings</h5>
                                        <p class="mb-0">Configure system parameters</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                        <h5>Analytics</h5>
                                        <p class="mb-0">View system statistics</p>
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
