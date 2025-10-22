<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Course Management
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($courses)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No courses available</h5>
                            <p class="text-muted">Courses will appear here once they are created.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Course Title</th>
                                        <th>Description</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td><?= $course['id'] ?></td>
                                            <td>
                                                <strong><?= esc($course['title']) ?></strong>
                                            </td>
                                            <td>
                                                <?= esc(substr($course['description'], 0, 100)) ?>
                                                <?= strlen($course['description']) > 100 ? '...' : '' ?>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($course['created_at'])) ?>
                                            </td>
                                            <td>
                                                <a href="<?= base_url("admin/course/{$course['id']}/upload") ?>" 
                                                   class="btn btn-sm btn-primary mr-1" title="Manage Materials">
                                                    <i class="fas fa-upload"></i> Materials
                                                </a>
                                                <a href="<?= base_url("student/materials/{$course['id']}") ?>" 
                                                   class="btn btn-sm btn-info" title="View as Student">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
