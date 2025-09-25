<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1><?= $title ?></h1>
    <div class="alert alert-info">
        <strong>Admin Access:</strong> This page is only accessible to administrators.
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>All Users</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
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
                        <td><?= $user['name'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td><span class="badge bg-primary"><?= ucfirst($user['role']) ?></span></td>
                        <td><?= $user['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>
</div>
<?= $this->endSection() ?>
