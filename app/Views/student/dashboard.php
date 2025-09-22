<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <h1 class="text-center mb-4">Student Dashboard</h1>

        <div class="card bg-secondary text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Welcome, <?= esc(session('name')) ?>!</h5>
                <p class="card-text">Use the navbar to view your courses and deadlines.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card bg-primary text-white mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Enrolled Courses</h6>
                        <p class="mb-0">--</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Upcoming Deadlines</h6>
                        <p class="mb-0">--</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="<?= base_url('student/dashboard') ?>" class="btn btn-success">Student Home</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


