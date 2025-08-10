
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('app.name', 'منصة التجارة الإلكترونية') }}">
    <meta name="keywords" content="تجارة إلكترونية، متجر، منتجات، أسعار، خصومات">
    <meta name="author" content="{{ config('app.name', 'منصة التجارة الإلكترونية') }}">
    <meta name="robots" content="noindex, nofollow">

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
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        .auth-card {
            width: 100%;
            max-width: 450px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .auth-card .card-header {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 20px;
        }

        .auth-card .card-body {
            padding: 30px;
        }

        .auth-card .card-title {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .auth-card .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            margin-bottom: 15px;
        }

        .auth-card .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
        }

        .auth-card .btn-primary:hover {
            background-color: #ff5252;
        }

        .auth-card .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .auth-card .form-check {
            margin-bottom: 15px;
        }

        .auth-card .form-check-label {
            font-weight: 500;
        }

        .auth-card .form-check-input {
            margin-left: 5px;
        }

        .auth-card .links {
            text-align: center;
            margin-top: 20px;
        }

        .auth-card .links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-card .links a:hover {
            text-decoration: underline;
        }

        .auth-footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #ddd;
        }

        .divider span {
            padding: 0 10px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .social-login {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .social-login .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 8px;
            padding: 10px;
            font-weight: 500;
        }

        .social-login .btn-facebook {
            background-color: #3b5998;
            color: white;
            border: none;
        }

        .social-login .btn-google {
            background-color: #db4437;
            color: white;
            border: none;
        }

        .social-login .btn-twitter {
            background-color: #1da1f2;
            color: white;
            border: none;
        }

        .social-login .btn:hover {
            opacity: 0.9;
        }

        @media (max-width: 576px) {
            .auth-card {
                margin: 0 15px;
            }

            .auth-card .card-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="card-header">
                <h3 class="mb-0">{{ config('app.name', 'منصة التجارة الإلكترونية') }}</h3>
            </div>
            <div class="card-body">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const passwordIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
