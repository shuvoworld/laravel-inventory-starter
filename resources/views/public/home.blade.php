<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (optional for icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; background: #f8f9fa; }
        .hero { max-width: 720px; margin: 0 auto; }
    </style>
</head>
<body>
    <main class="container py-5 hero">
        <div class="text-center mb-4">
            <h1 class="display-6 mb-2">{{ config('app.name') }}</h1>
            <p class="text-muted">A minimal Laravel starter with Bootstrap UI and authentication.</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="h5">Welcome</h2>
                <p class="mb-4">Please log in or create a new account to access your dashboard.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="fas fa-right-to-bracket me-1"></i> Login
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <p class="text-center text-muted small mt-3 mb-0">By continuing you agree to our terms of use.</p>
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
