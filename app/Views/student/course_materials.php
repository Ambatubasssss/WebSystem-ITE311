<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-book mr-2"></i>
                        Course Materials: <?= esc($course['title']) ?>
                    </h3>
                    <div class="card-tools">
                        <?php 
                        $userRole = strtolower(session('role') ?? '');
                        $backUrl = 'dashboard';
                        if ($userRole === 'admin') {
                            $backUrl = 'admin/dashboard';
                        } elseif ($userRole === 'teacher') {
                            $backUrl = 'teacher/dashboard';
                        }
                        ?>
                        <a href="<?= base_url($backUrl) ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Course Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5 class="alert-heading">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Course Information
                                </h5>
                                <p class="mb-0">
                                    <strong>Description:</strong> <?= esc($course['description']) ?: 'No description available.' ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Materials List -->
                    <div class="row">
                        <div class="col-12">
                            <?php if (empty($materials)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No materials available yet</h5>
                                    <p class="text-muted">Course materials will appear here once the instructor uploads them.</p>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($materials as $material): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0">
                                                            <?php
                                                            $fileType = strtolower($material['file_type']);
                                                            $iconClass = 'fas fa-file';
                                                            if (in_array($fileType, ['pdf'])) {
                                                                $iconClass = 'fas fa-file-pdf text-danger';
                                                            } elseif (in_array($fileType, ['doc', 'docx'])) {
                                                                $iconClass = 'fas fa-file-word text-primary';
                                                            } elseif (in_array($fileType, ['ppt', 'pptx'])) {
                                                                $iconClass = 'fas fa-file-powerpoint text-warning';
                                                            } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                                $iconClass = 'fas fa-file-image text-success';
                                                            } elseif (in_array($fileType, ['txt'])) {
                                                                $iconClass = 'fas fa-file-alt text-secondary';
                                                            }
                                                            ?>
                                                            <i class="<?= $iconClass ?> fa-2x"></i>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="card-title mb-1">
                                                                <?= esc($material['file_name']) ?>
                                                            </h6>
                                                            <p class="card-text text-muted small mb-2">
                                                                <span class="badge badge-light">
                                                                    <?= strtoupper($material['file_type']) ?>
                                                                </span>
                                                                <span class="ml-2">
                                                                    <?= formatBytes($material['file_size']) ?>
                                                                </span>
                                                            </p>
                                                            <p class="card-text text-muted small mb-3">
                                                                <i class="fas fa-clock mr-1"></i>
                                                                Uploaded <?= date('M d, Y', strtotime($material['created_at'])) ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-transparent">
                                                    <div class="btn-group w-100" role="group">
                                                        <a href="<?= base_url("materials/viewfile/{$material['id']}") ?>" 
                                                           class="btn btn-info btn-sm" target="_blank">
                                                            <i class="fas fa-eye mr-1"></i>
                                                            View
                                                        </a>
                                                        <a href="<?= base_url("materials/download/{$material['id']}") ?>" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fas fa-download mr-1"></i>
                                                            Download
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
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
