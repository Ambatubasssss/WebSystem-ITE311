<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-upload mr-2"></i>
                        Upload Material for: <?= esc($course['title']) ?>
                    </h3>
                    <div class="card-tools">
                        <?php 
                        $userRole = strtolower(session('role') ?? '');
                        $backUrl = ($userRole === 'admin') ? 'admin/courses' : 'teacher/dashboard';
                        ?>
                        <a href="<?= base_url($backUrl) ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Courses
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Upload Form -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Upload New Material</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (session()->getFlashdata('success')): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <?= session()->getFlashdata('success') ?>
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (session()->getFlashdata('error')): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?= session()->getFlashdata('error') ?>
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <?php 
                                    $userRole = strtolower(session('role') ?? '');
                                    $formAction = ($userRole === 'admin') ? "admin/course/{$course['id']}/upload" : "teacher/course/{$course['id']}/upload";
                                    ?>
                                    <form action="<?= base_url($formAction) ?>" method="post" enctype="multipart/form-data" id="uploadForm">
                                        <div class="form-group">
                                            <label for="material_file">Select File:</label>
                                            <input type="file" 
                                                   class="form-control-file" 
                                                   id="material_file" 
                                                   name="material_file" 
                                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt" 
                                                   required>
                                            <small class="form-text text-muted">
                                                Allowed formats: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT (Max: 10MB)
                                            </small>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-upload mr-1"></i> Upload Material
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Course Information</h5>
                                </div>
                                <div class="card-body">
                                    <h6><strong>Course Title:</strong></h6>
                                    <p><?= esc($course['title']) ?></p>
                                    
                                    <h6><strong>Description:</strong></h6>
                                    <p><?= esc($course['description']) ?: 'No description available.' ?></p>
                                    
                                    <h6><strong>Total Materials:</strong></h6>
                                    <p class="text-primary"><?= count($materials) ?> files</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Materials List -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Course Materials</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($materials)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No materials uploaded yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>File Name</th>
                                                        <th>Type</th>
                                                        <th>Size</th>
                                                        <th>Uploaded</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($materials as $material): ?>
                                                        <tr>
                                                            <td>
                                                                <i class="fas fa-file mr-2"></i>
                                                                <?= esc($material['file_name']) ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-info">
                                                                    <?= strtoupper($material['file_type']) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?= formatBytes($material['file_size']) ?>
                                                            </td>
                                                            <td>
                                                                <?= date('M d, Y H:i', strtotime($material['created_at'])) ?>
                                                            </td>
                                                            <td>
                                                                <a href="<?= base_url("materials/viewfile/{$material['id']}") ?>" 
                                                                   class="btn btn-sm btn-info mr-1" title="View" target="_blank">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="<?= base_url("materials/download/{$material['id']}") ?>" 
                                                                   class="btn btn-sm btn-success mr-1" title="Download">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                                <a href="<?= base_url("materials/delete/{$material['id']}") ?>" 
                                                                   class="btn btn-sm btn-danger" 
                                                                   onclick="return confirm('Are you sure you want to delete this material?')" 
                                                                   title="Delete">
                                                                    <i class="fas fa-trash"></i>
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
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to format bytes
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
?>

<?= $this->endSection() ?>
