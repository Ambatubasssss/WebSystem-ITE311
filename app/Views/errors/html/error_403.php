<?php
// Use data from controller
$dashboardUrl = $dashboardUrl ?? '/ITE311-MALILAY/';
$role = $role ?? 'guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Redirecting...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Auto-redirect after 2 seconds
        setTimeout(function() {
            window.location.href = '<?= $dashboardUrl ?>';
        }, 2000);
    </script>
</head>
<body class="bg-dark text-white">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h1 class="card-title">403 - Access Denied</h1>
                        <p class="card-text">You don't have permission to access this resource.</p>
                        <p class="card-text">Redirecting you back to your dashboard...</p>
                        <div class="spinner-border text-light" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-3">
                            <a href="<?= $dashboardUrl ?>" class="btn btn-light">Go to Dashboard Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
