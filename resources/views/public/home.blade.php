<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Simple Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
        }

        /* Navigation */
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0583F2 !important;
        }

        .nav-btns .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
        }

        /* Hero Section */
        .hero {
            padding: 100px 0 80px;
            background: linear-gradient(135deg, #0583F2 0%, #0467C7 100%);
            color: white;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero-btns .btn {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-white {
            background: white;
            color: #0583F2;
            border: none;
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            background: #f8f9fa;
            color: #0583F2;
        }

        .btn-outline-white {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline-white:hover {
            background: white;
            color: #0583F2;
            transform: translateY(-2px);
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1a1a1a;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            height: 100%;
            border: 1px solid #e9ecef;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: #0583F2;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1a1a1a;
        }

        .feature-card p {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        .icon-blue { background: linear-gradient(135deg, #0583F2, #0467C7); }
        .icon-green { background: linear-gradient(135deg, #10b981, #059669); }
        .icon-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .icon-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .icon-pink { background: linear-gradient(135deg, #ec4899, #db2777); }
        .icon-teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }

        /* Why Choose Section */
        .why-choose {
            padding: 80px 0;
            background: white;
        }

        .why-item {
            display: flex;
            align-items: start;
            gap: 1rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .why-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .why-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0583F2, #0467C7);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .why-content h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1a1a1a;
        }

        .why-content p {
            margin: 0;
            color: #6c757d;
            font-size: 0.95rem;
        }

        /* CTA Section */
        .cta {
            padding: 80px 0;
            background: linear-gradient(135deg, #0583F2 0%, #0467C7 100%);
            color: white;
            text-align: center;
        }

        .cta h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 2rem 0;
            text-align: center;
        }

        .footer p {
            margin: 0;
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            .hero p {
                font-size: 1.1rem;
            }
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-box me-2"></i>{{ config('app.name', 'Inventory System') }}
            </a>
            <div class="nav-btns d-flex gap-2">
                <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1>Manage Your Inventory the Easy Way</h1>
                    <p>Track products, manage sales, monitor stock levels, and grow your business. All in one simple system.</p>
                    <div class="hero-btns d-flex gap-3 flex-wrap">
                        <a href="{{ route('register') }}" class="btn btn-white">
                            <i class="fas fa-rocket me-2"></i>Start Free
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-white">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title">Everything You Need</h2>
                <p class="section-subtitle">Simple tools to run your business better</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-blue">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3>Product Management</h3>
                        <p>Add products, upload images, set prices. See what's in stock at a glance. Get alerts when items run low.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-green">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3>Sales Made Simple</h3>
                        <p>Create orders in seconds. Print invoices instantly. Track every sale. Your stock updates automatically.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-purple">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Know Your Profit</h3>
                        <p>See today's profit instantly. Track expenses. View monthly reports. Make better business decisions.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-orange">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <h3>Stock Control</h3>
                        <p>Track every item movement. Fix stock errors easily. Reconcile inventory monthly. Always know what you have.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-pink">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Team Ready</h3>
                        <p>Add your staff. Set permissions. See who made changes. Perfect for teams of any size.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon icon-teal">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Works Everywhere</h3>
                        <p>Use on your phone, tablet, or computer. Access from anywhere. Always up to date.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Section -->
    <section class="why-choose">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Why Choose Us</h2>
                <p class="section-subtitle">Built for real businesses, not tech experts</p>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="why-content">
                            <h4>Quick to Learn</h4>
                            <p>Start using it in minutes. No training needed. Everything is where you expect it.</p>
                        </div>
                    </div>

                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fas fa-sync"></i>
                        </div>
                        <div class="why-content">
                            <h4>Always Current</h4>
                            <p>Stock levels update in real-time. See changes instantly across all devices.</p>
                        </div>
                    </div>

                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="why-content">
                            <h4>Your Data is Safe</h4>
                            <p>Each store's data is completely private. Only your team can access it.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="why-content">
                            <h4>See Your Profit Daily</h4>
                            <p>Know exactly how much you made today. Track expenses. Monitor profit margins.</p>
                        </div>
                    </div>

                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="why-content">
                            <h4>Clear Reports</h4>
                            <p>Simple reports that make sense. No complex charts. Just the info you need.</p>
                        </div>
                    </div>

                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <div class="why-content">
                            <h4>No Limits</h4>
                            <p>Add unlimited products. Create unlimited orders. Grow as big as you want.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Get Started?</h2>
            <p>Join hundreds of businesses managing their inventory the easy way</p>
            <div class="hero-btns d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-white">
                    <i class="fas fa-rocket me-2"></i>Create Free Account
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-white">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
