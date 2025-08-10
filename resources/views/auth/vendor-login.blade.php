<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول - {{ config('app.name', 'متجر متعدد البائعين') }}</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Custom CSS --}}
    <style>
        body {
            background-color: #f8f9fc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-header text-center">
                        <i class="fas fa-store fa-2x mb-3"></i>
                        <h4 class="text-white">تسجيل دخول البائع</h4>
                    </div>
                    <div class="card-body p-0">
                        <!-- Nested Form -->
                        <form class="user" method="POST" action="{{ route('vendor.login.submit') }}">
                            @csrf
                            <div class="form-group mb-3">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-primary"></i>
                                    </span>
                                    <input type="email" name="email" class="form-control form-control-lg border-start-0" placeholder="البريد الإلكتروني" required autofocus>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-primary"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control form-control-lg border-start-0" placeholder="كلمة المرور" required>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">
                                    تذكرني
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    تسجيل الدخول
                                </button>
                            </div>

                            <hr>

                            <div class="text-center">
                                <a class="small" href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
                            </div>

                            <div class="text-center">
                                <a class="small" href="{{ route('vendor.register') }}">إنشاء حساب بائع جديد</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Custom JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    </script>
</body>
</html>
