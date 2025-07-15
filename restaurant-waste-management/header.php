<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #007bff !important; }
        .navbar-brand, .nav-link { color: #fff !important; }
        .navbar-brand:hover, .nav-link:hover { color: #f8d210 !important; }
        .container { margin-top: 20px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">🍽 Restaurant Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="owner_dashboard.php">Owner</a></li>
                    <li class="nav-item"><a class="nav-link" href="customer_dashboard.php">Customer</a></li>
                    <li class="nav-item"><a class="nav-link" href="wastage_management.php">Wastage</a></li>
                </ul>
            </div>
        </div>
    </nav>
