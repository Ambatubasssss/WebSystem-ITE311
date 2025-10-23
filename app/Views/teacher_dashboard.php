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
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-book fa-3x mb-3"></i>
                                        <h5>Course Management</h5>
                                        <p class="mb-3">Manage your courses and curriculum</p>
                                        <a href="<?= base_url('admin/courses') ?>" class="btn btn-light btn-sm">
                                            <i class="fas fa-cog mr-1"></i> Manage Courses
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-upload fa-3x mb-3"></i>
                                        <h5>Upload Materials</h5>
                                        <p class="mb-3">Upload course materials and resources</p>
                                        <a href="<?= base_url('teacher/course/1/upload') ?>" class="btn btn-light btn-sm">
                                            <i class="fas fa-upload mr-1"></i> Upload Files
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                                        <h5>Student Grades</h5>
                                        <p class="mb-3">Track and manage student progress</p>
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
                                            <div class="col-md-6">
                                                <div class="d-grid gap-2">
                                                    <a href="<?= base_url('teacher/course/1/upload') ?>" class="btn btn-primary btn-lg">
                                                        <i class="fas fa-upload mr-2"></i>
                                                        Upload Course Materials
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-grid gap-2">
                                                    <a href="<?= base_url('admin/courses') ?>" class="btn btn-success btn-lg">
                                                        <i class="fas fa-list mr-2"></i>
                                                        Manage All Courses
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Course Management Section -->
                        <div class="row mt-5">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-graduation-cap mr-2"></i>
                                            Course Management & Materials
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card border-primary">
                                                    <div class="card-header bg-primary text-white">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-list mr-2"></i>
                                                            Manage Courses
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="card-text">View and manage all your courses, including course details and student enrollments.</p>
                                                        <div class="d-grid">
                                                            <a href="<?= base_url('admin/courses') ?>" class="btn btn-primary">
                                                                <i class="fas fa-cog mr-2"></i>
                                                                Manage All Courses
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card border-info">
                                                    <div class="card-header bg-info text-white">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-upload mr-2"></i>
                                                            Upload Materials
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="card-text">Upload course materials, documents, and resources for your students to access.</p>
                                                        <div class="d-grid">
                                                            <a href="<?= base_url('teacher/course/1/upload') ?>" class="btn btn-info">
                                                                <i class="fas fa-upload mr-2"></i>
                                                                Upload Course Materials
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Course List with Quick Actions -->
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-book mr-2"></i>
                                                            Your Courses
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Course ID</th>
                                                                        <th>Course Title</th>
                                                                        <th>Description</th>
                                                                        <th>Materials</th>
                                                                        <th>Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($courses)): ?>
                                                                        <tr>
                                                                            <td colspan="5" class="text-center py-4">
                                                                                <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                                                                                <p class="text-muted">No courses available yet.</p>
                                                                            </td>
                                                                        </tr>
                                                                    <?php else: ?>
                                                                        <?php foreach ($courses as $course): ?>
                                                                            <tr>
                                                                                <td><?= $course['id'] ?></td>
                                                                                <td><?= esc($course['title']) ?></td>
                                                                                <td><?= esc($course['description']) ?></td>
                                                                                <td>
                                                                                    <?php
                                                                                    $materialCount = $course['material_count'] ?? 0;
                                                                                    if ($materialCount > 0) {
                                                                                        $badgeClass = $materialCount >= 3 ? 'bg-success' : 'bg-warning';
                                                                                        $badgeText = $materialCount . ' file' . ($materialCount > 1 ? 's' : '');
                                                                                    } else {
                                                                                        $badgeClass = 'bg-danger';
                                                                                        $badgeText = 'No files';
                                                                                    }
                                                                                    ?>
                                                                                    <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                                                                </td>
                                                                                <td>
                                                                                    <div class="btn-group" role="group">
                                                                                        <a href="<?= base_url("teacher/course/{$course['id']}/upload") ?>" class="btn btn-sm btn-primary" title="Upload Materials">
                                                                                            <i class="fas fa-upload"></i>
                                                                                        </a>
                                                                                        <a href="<?= base_url("materials/view/{$course['id']}") ?>" class="btn btn-sm btn-info" title="View Materials">
                                                                                            <i class="fas fa-eye"></i>
                                                                                        </a>
                                                                                        <a href="<?= base_url('admin/courses') ?>" class="btn btn-sm btn-secondary" title="Manage Course">
                                                                                            <i class="fas fa-cog"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quick Upload Section -->
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <div class="card border-success">
                                                    <div class="card-header bg-success text-white">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-plus-circle mr-2"></i>
                                                            Quick Upload
                                                        </h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="card-text mb-3">Quickly upload materials to any of your courses:</p>
                                                        <?php if (empty($courses)): ?>
                                                            <div class="text-center py-3">
                                                                <i class="fas fa-graduation-cap fa-2x text-muted mb-2"></i>
                                                                <p class="text-muted">No courses available for upload.</p>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="row">
                                                                <?php foreach ($courses as $course): ?>
                                                                    <div class="col-md-4 mb-2">
                                                                        <a href="<?= base_url("teacher/course/{$course['id']}/upload") ?>" class="btn btn-outline-success btn-block">
                                                                            <i class="fas fa-upload mr-1"></i>
                                                                            <?= esc($course['title']) ?>
                                                                        </a>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
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
    </div>
<?= $this->endSection() ?>
