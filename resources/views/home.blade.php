
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="منصة تجارة إلكترونية متعددة البائعين، اشترِ من أفضل البائعين واستمتع بتجربة تسوق فريدة">
    <meta name="keywords" content="تجارة إلكترونية، متجر، منتجات، أسعار، خصومات">
    <meta name="author" content="منصة التجارة الإلكترونية">
    <meta name="robots" content="index, follow">

    <title>منصة التجارة الإلكترونية | تسوق من أفضل البائعين</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #4ecdc4;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --font-family: 'Tajawal', sans-serif;
        }

        body {
            font-family: var(--font-family);
            direction: rtl;
        }

        .navbar {
            background-color: var(--dark-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }

        .navbar-brand:hover {
            color: var(--primary-color) !important;
        }

        .nav-link {
            color: white !important;
            margin-right: 15px;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            height: 70vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 20px;
        }

        .hero-content p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 30px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #ff5252;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
        }

        .btn-outline-light {
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 30px;
            transition: all 0.3s;
        }

        .btn-outline-light:hover {
            background-color: white;
            color: var(--dark-color);
            transform: translateY(-3px);
        }

        .category-card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-10px);
        }

        .category-card img {
            height: 200px;
            object-fit: cover;
        }

        .category-card .card-body {
            background-color: white;
            padding: 20px;
        }

        .category-card h5 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .product-card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-10px);
        }

        .product-card img {
            height: 250px;
            object-fit: cover;
        }

        .product-card .card-body {
            background-color: white;
            padding: 20px;
        }

        .product-card h5 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .product-price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.2rem;
        }

        .vendor-card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: 100%;
        }

        .vendor-card:hover {
            transform: translateY(-10px);
        }

        .vendor-card img {
            height: 150px;
            object-fit: cover;
        }

        .vendor-card .card-body {
            background-color: white;
            padding: 20px;
        }

        .vendor-card h5 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .vendor-rating {
            color: #ffc107;
        }

        .feature-box {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
        }

        .feature-box:hover {
            transform: translateY(-10px);
        }

        .feature-box i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .feature-box h4 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 15px;
        }

        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 50px 0 20px;
            margin-top: 80px;
        }

        .footer h5 {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .footer ul {
            list-style: none;
            padding: 0;
        }

        .footer ul li {
            margin-bottom: 10px;
        }

        .footer ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer ul li a:hover {
            color: var(--primary-color);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 30px;
            padding-top: 20px;
            text-align: center;
        }

        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin-left: 15px;
            transition: color 0.3s;
        }

        .social-icons a:hover {
            color: var(--primary-color);
        }

        .search-box {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-box input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border-radius: 30px;
            border: none;
            font-size: 1.1rem;
        }

        .search-box button {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.2rem;
            }

            .search-box input {
                padding-right: 50px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">منصة التجارة الإلكترونية</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.index') }}">المنتجات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('vendors.index') }}">البائعون</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('categories.index') }}">التصنيفات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('offers.index') }}">العروض</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.about') }}">من نحن</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.contact') }}">اتصل بنا</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('search') }}">
                            <i class="fas fa-search"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('wishlist.index') }}">
                            <i class="fas fa-heart"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cart.index') }}">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                    </li>
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">تسجيل الدخول</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">إنشاء حساب</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">لوحة التحكم</a></li>
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">طلباتي</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">تعديل الملف الشخصي</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        تسجيل الخروج
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>مرحباً بك في منصة التجارة الإلكترونية</h1>
                <p>تسوق من أفضل البائعين واستمتع بتجربة تسوق فريدة</p>
                <div class="search-box">
                    <form action="{{ route('search') }}" method="GET">
                        <input type="text" name="q" placeholder="ابحث عن منتجات...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-primary me-2">تسوق الآن</a>
                    <a href="{{ route('offers.index') }}" class="btn btn-outline-light">عرض العروض</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">تصفح حسب التصنيفات</h2>
                <p class="text-muted">اكتشف منتجاتنا حسب الفئات المختلفة</p>
            </div>
            <div class="row">
                @foreach($categories as $category)
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="category-card">
                        <a href="{{ route('categories.show', $category->id) }}">
                            <img src="{{ asset('storage/' . $category->image) }}" class="card-img-top" alt="{{ $category->name }}">
                            <div class="card-body">
                                <h5>{{ $category->name }}</h5>
                                <p class="text-muted">{{ $category->products_count }} منتج</p>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('categories.index') }}" class="btn btn-primary">عرض جميع التصنيفات</a>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">أفضل المنتجات المميزة</h2>
                <p class="text-muted">اكتشف أفضل المنتجات المختارة من قبل فريقنا</p>
            </div>
            <div class="row">
                @foreach($featuredProducts as $product)
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="product-card">
                        <a href="{{ route('products.show', $product->id) }}">
                            <img src="{{ asset('storage/' . $product->featured_image) }}" class="card-img-top" alt="{{ $product->name }}">
                            <div class="card-body">
                                <h5>{{ $product->name }}</h5>
                                <p class="text-muted">{{ $product->vendor->name }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="product-price">{{ number_format($product->price, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                                    <div>
                                        @if($product->rating > 0)
                                            <i class="fas fa-star vendor-rating"></i>
                                            <span>{{ number_format($product->rating, 1) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('products.index') }}" class="btn btn-primary">عرض جميع المنتجات</a>
            </div>
        </div>
    </section>

    <!-- Top Vendors Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">أفضل البائعين</h2>
                <p class="text-muted">تسوق من أفضل البائعين في منصتنا</p>
            </div>
            <div class="row">
                @foreach($topVendors as $vendor)
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="vendor-card">
                        <a href="{{ route('vendors.show', $vendor->id) }}">
                            <img src="{{ asset('storage/' . $vendor->logo) }}" class="card-img-top" alt="{{ $vendor->name }}">
                            <div class="card-body">
                                <h5>{{ $vendor->name }}</h5>
                                <p class="text-muted">{{ $vendor->description }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>{{ $vendor->products_count }} منتج</span>
                                    <div>
                                        @if($vendor->rating > 0)
                                            <i class="fas fa-star vendor-rating"></i>
                                            <span>{{ number_format($vendor->rating, 1) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('vendors.index') }}" class="btn btn-primary">عرض جميع البائعين</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">لماذا تختار منصتنا؟</h2>
                <p class="text-muted">نقدم لك أفضل تجربة تسوق إلكترونية</p>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="feature-box">
                        <i class="fas fa-shipping-fast"></i>
                        <h4>توصيل سريع</h4>
                        <p>توصيل سريع وموثوق إلى جميع أنحاء المملكة</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="feature-box">
                        <i class="fas fa-shield-alt"></i>
                        <h4>دفع آمن</h4>
                        <p>نظام دفع آمن وموثوق لضمان سلامة معاملاتك</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="feature-box">
                        <i class="fas fa-undo"></i>
                        <h4>إرجاع سهل</h4>
                        <p>استرجاع المنتجات بسهولة خلال 14 يومًا</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="feature-box">
                        <i class="fas fa-headset"></i>
                        <h4>دعم 24/7</h4>
                        <p>فريق دعم متاح على مدار الساعة لمساعدتك</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto text-center">
                    <h3 class="fw-bold mb-3">اشترك في نشرتنا الإخبارية</h3>
                    <p class="text-muted mb-4">احصل على أحدث العروض والمنتجات مباشرة على بريدك الإلكتروني</p>
                    <form action="#" method="POST" class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="البريد الإلكتروني" required>
                            <button class="btn btn-primary" type="submit">اشترك الآن</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>منصة التجارة الإلكترونية</h5>
                    <p>منصة تجارة إلكترونية متعددة البائعين، نقدم لك أفضل المنتجات من أفضل البائعين بأسعار تنافسية.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>روابط سريعة</h5>
                    <ul>
                        <li><a href="{{ route('pages.about') }}">من نحن</a></li>
                        <li><a href="{{ route('pages.contact') }}">اتصل بنا</a></li>
                        <li><a href="{{ route('pages.terms') }}">الشروط والأحكام</a></li>
                        <li><a href="{{ route('pages.privacy') }}">سياسة الخصوصية</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>خدماتنا</h5>
                    <ul>
                        <li><a href="{{ route('products.index') }}">المنتجات</a></li>
                        <li><a href="{{ route('vendors.index') }}">البائعون</a></li>
                        <li><a href="{{ route('offers.index') }}">العروض</a></li>
                        {{-- <li><a href="{{ route('pages.shipping') }}">شحن واسترجاع</a></li> --}}
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>تواصل معنا</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            الرياض، المملكة العربية السعودية
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +966 50 123 4567
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            info@example.com
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} منصة التجارة الإلكترونية. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Newsletter form submission
        document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = this.querySelector('input[type="email"]').value;
            const button = this.querySelector('button');

            // Show loading state
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري الإرسال...';
            button.disabled = true;

            // Simulate form submission
            setTimeout(() => {
                // Reset form
                this.reset();

                // Reset button
                button.innerHTML = 'اشترك الآن';
                button.disabled = false;

                // Show success message
                alert('شكراً لك! لقد تم اشتراكك بنجاح في نشرتنا الإخبارية.');
            }, 1500);
        });
    </script>
</body>
</html>
