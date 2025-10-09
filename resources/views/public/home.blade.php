<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - পেশাদার ইনভেন্টরি ও বিক্রয় ব্যবস্থাপনা</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <!-- Bangla Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-info: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-warning: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Noto Sans Bengali', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .hero-section {
            padding: 120px 0 80px;
            color: white;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto 2rem;
            line-height: 1.8;
        }

        .cta-buttons .btn {
            padding: 14px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-light-custom {
            background: white;
            color: #667eea;
            border: none;
        }

        .btn-light-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            background: #f8f9fa;
            color: #667eea;
        }

        .btn-outline-light-custom {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline-light-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .features-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            height: 100%;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .feature-icon.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .feature-icon.pink { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .feature-icon.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .feature-icon.orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .feature-icon.green { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
        .feature-icon.red { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); }

        .stats-section {
            padding: 60px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .footer {
            background: #2d3748;
            color: white;
            padding: 20px 0;
        }

        @media (max-width: 768px) {
            .hero-title { font-size: 2.5rem; }
            .hero-subtitle { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fas fa-boxes-stacked me-2"></i>টেক ইনভেন্টরি সিস্টেম
            </a>
            <div class="ms-auto">
                <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">লগইন</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary">শুরু করুন</a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title">আপনার ইনভেন্টরি ও বিক্রয় সহজ করুন</h1>
            <p class="hero-subtitle">
                শক্তিশালী এবং সহজবোধ্য ইনভেন্টরি ব্যবস্থাপনা সিস্টেম যা আপনার ব্যবসা বৃদ্ধিতে সহায়তা করে।
                স্টক ট্র্যাক করুন, অর্ডার পরিচালনা করুন এবং আপনার বিক্রয় দক্ষতা বৃদ্ধি করুন।
            </p>
            <div class="cta-buttons d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-light-custom">
                    <i class="fas fa-rocket me-2"></i>বিনামূল্যে ট্রায়াল শুরু করুন
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-light-custom">
                    <i class="fas fa-right-to-bracket me-2"></i>ড্যাশবোর্ডে লগইন করুন
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">আপনার ব্যবসার জন্য ১০০% উপযোগী</h2>
                <p class="text-muted">ইনভেন্টরি এবং বিক্রয় পরিচালনার জন্য প্রয়োজনীয় সবকিছু এক জায়গায়</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon purple">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3 class="h5 text-center mb-3">ইনভেন্টরি ব্যবস্থাপনা</h3>
                        <p class="text-muted text-center">
                            উন্নত স্টক নিয়ন্ত্রণ, কম স্টক সতর্কতা এবং মাল্টি-লোকেশন সাপোর্ট সহ রিয়েল-টাইমে আপনার পণ্য ট্র্যাক করুন।
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon pink">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="h5 text-center mb-3">বিক্রয় অর্ডার</h3>
                        <p class="text-muted text-center">
                            স্বয়ংক্রিয় চালান, পেমেন্ট ট্র্যাকিং এবং গ্রাহক ব্যবস্থাপনা সহ দক্ষতার সাথে বিক্রয় অর্ডার প্রক্রিয়া করুন।
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon blue">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3 class="h5 text-center mb-3">ক্রয় ব্যবস্থাপনা</h3>
                        <p class="text-muted text-center">
                            সরবরাহকারী পরিচালনা করুন, ক্রয় আদেশ তৈরি করুন এবং সবকিছু একটি কেন্দ্রীভূত সিস্টেমে ডেলিভারি ট্র্যাক করুন।
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon orange">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="h5 text-center mb-3">বিশ্লেষণ ও রিপোর্ট</h3>
                        <p class="text-muted text-center">
                            ব্যাপক রিপোর্ট, বিক্রয় বিশ্লেষণ এবং আর্থিক অন্তর্দৃষ্টি সহ ডেটা-চালিত সিদ্ধান্ত নিন।
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon green">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="h5 text-center mb-3">গ্রাহক সম্পর্ক</h3>
                        <p class="text-muted text-center">
                            একীভূত গ্রাহক ব্যবস্থাপনা এবং লেনদেন ইতিহাস ট্র্যাকিং এর মাধ্যমে দীর্ঘস্থায়ী সম্পর্ক তৈরি করুন।
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="feature-icon red">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <h3 class="h5 text-center mb-3">রোল-ভিত্তিক নিরাপত্তা</h3>
                        <p class="text-muted text-center">
                            উন্নত ব্যবহারকারী রোল, অনুমতি এবং ব্যাপক অ্যাক্সেস নিয়ন্ত্রণ দিয়ে আপনার ডেটা সুরক্ষিত করুন।
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-6 mb-4 mb-md-0">
                    <div class="stat-card">
                        <div class="stat-number">৯৯.৯%</div>
                        <div class="stat-label">আপটাইম</div>
                    </div>
                </div>
                <div class="col-md-4 col-6 mb-4 mb-md-0">
                    <div class="stat-card">
                        <div class="stat-number">১০০%</div>
                        <div class="stat-label">ডাটা সুরক্ষা</div>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="stat-card">
                        <div class="stat-number">২৪/৭</div>
                        <div class="stat-label">সাপোর্ট</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="text-center">
                <p class="mb-0">&copy; {{ date('Y') }} টেক ইনভেন্টরি সিস্টেম। সর্বস্বত্ব সংরক্ষিত।</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
