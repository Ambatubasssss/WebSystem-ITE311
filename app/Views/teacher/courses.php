<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1><?= $title ?></h1>
    <div class="alert alert-success">
        <strong>Teacher Access:</strong> This page is only accessible to teachers.
    </div>
    
    <div class="row">
        <?php foreach ($courses as $course): ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?= $course ?></h5>
                    <p class="card-text">Course description and details.</p>
                    <a href="#" class="btn btn-primary">Manage Course</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>
</div>
<?= $this->endSection() ?>
