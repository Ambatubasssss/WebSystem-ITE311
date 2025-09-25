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
