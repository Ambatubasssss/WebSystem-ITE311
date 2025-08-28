<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITE311 - MALILAY</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
<<<<<<< HEAD
            <a class="navbar-brand fw-bold text-white" href="#">ITE311-MALILAY</a>
=======
            <a class="navbar-brand fw-bold text-white" href="<?= base_url(''); ?>">ITE311-MALILAY</a>
>>>>>>> f9b7058 (Added Home controller, routes, and views for basic navigation)
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
<<<<<<< HEAD
                        <a class="nav-link text-white" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Contact</a>
=======
                        <a class="nav-link text-white" href="<?= base_url(''); ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('about'); ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="<?= base_url('contact'); ?>">Contact</a>
>>>>>>> f9b7058 (Added Home controller, routes, and views for basic navigation)
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Footer -->
    <footer class="bg-primary text-center text-white py-3 mt-5">
        <p class="mb-0">&copy; <?= date('Y') ?> ITE311-MALILAY - All Rights Reserved</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
