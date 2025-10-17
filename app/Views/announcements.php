<?= $this->extend('template') ?>

<?= $this->section('content') ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fas fa-bullhorn"></i> Announcements</h3>
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

                        <?php if (!empty($announcements)): ?>
                            <div class="row">
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="col-12 mb-4">
                                        <div class="card border-left-primary">
                                            <div class="card-body">
                                                <h5 class="card-title text-primary">
                                                    <i class="fas fa-newspaper"></i> <?= esc($announcement['title']) ?>
                                                </h5>
                                                <p class="card-text"><?= nl2br(esc($announcement['content'])) ?></p>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> 
                                                    Posted: <?= date('F j, Y \a\t g:i A', strtotime($announcement['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No Announcements Available</h5>
                                <p class="text-muted">There are currently no announcements to display. Check back later for updates.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
