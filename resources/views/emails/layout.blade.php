<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            direction: rtl;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #007bff;
            padding: 20px;
            text-align: center;
        }
        .email-logo {
            max-width: 150px;
            height: auto;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 5px;
            text-decoration: none;
        }
        .social-icon {
            width: 32px;
            height: 32px;
            background-color: #007bff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .social-icon img {
            width: 16px;
            height: 16px;
            filter: brightness(0) invert(1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        p {
            margin: 10px 0;
            line-height: 1.5;
        }
        .copyright {
            margin-top: 15px;
            font-size: 12px;
            color: #adb5bd;
        }
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <table>
            <!-- Header -->
            <tr>
                <td class="email-header">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('images/logo-white.png') }}" alt="{{ config('app.name') }}" class="email-logo">
                    </a>
                </td>
            </tr>

            <!-- Content -->
            @yield('content')

            <!-- Footer -->
            <tr>
                <td class="email-footer">
                    <div class="social-links">
                        <a href="#" class="social-icon"><img src="{{ asset('images/social/facebook.png') }}" alt="Facebook"></a>
                        <a href="#" class="social-icon"><img src="{{ asset('images/social/twitter.png') }}" alt="Twitter"></a>
                        <a href="#" class="social-icon"><img src="{{ asset('images/social/instagram.png') }}" alt="Instagram"></a>
                        <a href="#" class="social-icon"><img src="{{ asset('images/social/linkedin.png') }}" alt="LinkedIn"></a>
                    </div>

                    <p>
                        إذا كان لديك أي استفسارات، فلا تتردد في الاتصال بنا على<br>
                        <a href="mailto:support@example.com" style="color: #007bff; text-decoration: none;">support@example.com</a>
                    </p>

                    <p style="margin-bottom: 5px;">
                        {{ config('app.name') }}<br>
                        الرياض، المملكة العربية السعودية
                    </p>

                    <div class="copyright">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>