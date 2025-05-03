<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Online Grocery Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-5 shadow-sm">
                    <h1 class="text-danger mb-4">404 - Page Not Found</h1>
                    
                    <p class="lead">The page you were looking for could not be found.</p>
                    
                    <div class="alert alert-info mt-4">
                        <strong>Requested URL:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>
                    </div>
                    
                    <p class="mt-4">This could be because:</p>
                    <ul>
                        <li>The URL was typed incorrectly</li>
                        <li>The page has been moved or deleted</li>
                        <li>There is an issue with the routing configuration</li>
                    </ul>
                    
                    <div class="mt-4">
                        <a href="/home" class="btn btn-primary">Go to Home Page</a>
                        <a href="/products" class="btn btn-outline-secondary ms-2">Browse Products</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 