<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1><?= $title ?></h1>
    <div class="alert alert-primary">
        <strong>Student Access:</strong> This page is only accessible to students.
    </div>
    
    <div class="row">
        <?php foreach ($enrollments as $enrollment): ?>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?= $enrollment['course'] ?></h5>
                    <p class="card-text">
                        <strong>Instructor:</strong> <?= $enrollment['instructor'] ?><br>
                        <strong>Status:</strong> <span class="badge bg-success"><?= $enrollment['status'] ?></span>
                    </p>
                    <a href="#" class="btn btn-primary">View Course</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>
</div>
<?= $this->endSection() ?>
