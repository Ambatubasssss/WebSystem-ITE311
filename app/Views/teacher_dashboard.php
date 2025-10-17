<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="fas fa-chalkboard-teacher"></i> Teacher Dashboard</h3>
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
                            <i class="fas fa-chalkboard-teacher fa-5x text-success mb-4"></i>
                            <h2 class="text-success">Welcome, Teacher!</h2>
                            <p class="lead text-muted">You have successfully accessed the Teacher Dashboard.</p>
                            <p class="text-muted">This is your dedicated workspace for managing courses, students, and academic activities.</p>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-book fa-3x mb-3"></i>
                                        <h5>Course Management</h5>
                                        <p class="mb-0">Manage your courses and curriculum</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                                        <h5>Student Grades</h5>
                                        <p class="mb-0">Track and manage student progress</p>
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
