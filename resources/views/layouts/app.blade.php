
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('app.name', 'منصة التجارة الإلكترونية') }}">
    <meta name="keywords" content="تجارة إلكترونية، متجر، منتجات، أسعار، خصومات">
    <meta name="author" content="{{ config('app.name', 'منصة التجارة الإلكترونية') }}">
    <meta name="robots" content="index, follow">

    <title>{{ config('app.name', 'منصة التجارة الإلكترونية') }} - @yield('title')</title>

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

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: #ff5252;
        }

        .product-price {
            color: var(--primary-color);
            font-weight: 700;
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

        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-img-top {
            height: 250px;
            object-fit: cover;
        }

        .vendor-rating {
            color: #ffc107;
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

        .badge {
            font-size: 0.9rem;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }

        .quantity {
            max-width: 120px;
        }

        .quantity input {
            text-align: center;
        }

        .sticky-top {
            position: sticky;
            top: 20px;
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

            .navbar-nav {
                text-align: right;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name', 'منصة التجارة الإلكترونية') }}</a>
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
                            <span class="badge bg-danger">3</span>
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
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('account.index') }}">حسابي</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.orders') }}>طلباتي</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.profile') }}">الملف الشخصي</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    تسجيل الخروج
                                </a></li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>{{ config('app.name', 'منصة التجارة الإلكترونية') }}</h5>
                    <p>منصة تجارة إلكترونية متعددة البائعين، نقدم لك أفضل المنتجات بأفضل الأسعار.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>روابط سريعة</h5>
                    <ul>
                        <li><a href="{{ route('products.index') }}">المنتجات</a></li>
                        <li><a href="{{ route('vendors.index') }}">البائعون</a></li>
                        <li><a href="{{ route('categories.index') }}">التصنيفات</a></li>
                        <li><a href="{{ route('offers.index') }}">العروض</a></li>
                        <li><a href="{{ route('pages.about') }}">من نحن</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>دعم العملاء</h5>
                    <ul>
                        <li><a href="{{ route('pages.help') }}">المساعدة</a></li>
                        <li><a href="{{ route('pages.faq') }}">الأسئلة الشائعة</a></li>
                        <li><a href="{{ route('pages.contact') }}">اتصل بنا</a></li>
                        <li><a href="{{ route('pages.terms') }}">الشروط والأحكام</a></li>
                        <li><a href="{{ route('pages.privacy') }}">سياسة الخصوصية</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>النشرة البريدية</h5>
                    <p>اشترك في نشرتنا البريدية لتكون على اطلاع دائم بأحدث العروض والمنتجات</p>
                    <form>
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="البريد الإلكتروني">
                            <button class="btn btn-primary" type="button">اشترك</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'منصة التجارة الإلكترونية') }}. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Quantity buttons
        document.getElementById('increaseQuantity').addEventListener('click', function() {
            const quantityInput = document.getElementById('quantity');
            quantityInput.value = parseInt(quantityInput.value) + 1;
        });

        document.getElementById('decreaseQuantity').addEventListener('click', function() {
            const quantityInput = document.getElementById('quantity');
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        });

        // Price range slider
        const priceRange = document.getElementById('priceRange');
        const priceValue = document.getElementById('priceValue');

        if (priceRange && priceValue) {
            priceRange.addEventListener('input', function() {
                priceValue.textContent = this.value + ' {{ config('app.currency_symbol', 'ر.س') }}';
            });
        }

        // Add to cart functionality
        document.querySelectorAll('.btn-primary').forEach(button => {
            if (button.textContent.includes('أضف إلى السلة')) {
                button.addEventListener('click', function() {
                    // Show a success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    alert.style.zIndex = '9999';
                    alert.innerHTML = 'تم إضافة المنتج إلى سلة التسوق بنجاح!';

                    const closeButton = document.createElement('button');
                    closeButton.type = 'button';
                    closeButton.className = 'btn-close';
                    closeButton.setAttribute('data-bs-dismiss', 'alert');
                    closeButton.setAttribute('aria-label', 'Close');

                    alert.appendChild(closeButton);
                    document.body.appendChild(alert);

                    // Remove the alert after 3 seconds
                    setTimeout(() => {
                        alert.remove();
                    }, 3000);
                });
            }
        });
    </script>
</body>
</html>
