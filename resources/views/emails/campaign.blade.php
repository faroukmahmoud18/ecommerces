
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaign->subject }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin-bottom: 15px;
            text-align: justify;
        }
        .content h2, .content h3 {
            color: #2c3e50;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .content ul, .content ol {
            padding-right: 20px;
            margin-bottom: 15px;
        }
        .content li {
            margin-bottom: 8px;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .products {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .product {
            width: 48%;
            margin-bottom: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
        }
        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .product-info {
            padding: 10px;
        }
        .product-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .product-price {
            color: #e74c3c;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #7f8c8d;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            margin: 0 5px;
            color: #3498db;
            text-decoration: none;
        }
        .social-links a:hover {
            color: #2980b9;
        }
        .unsubscribe {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .unsubscribe a {
            color: #e74c3c;
            text-decoration: none;
        }
        .unsubscribe a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'متجر إلكتروني') }}" class="logo">
            <div class="title">{{ $campaign->subject }}</div>
            <div class="subtitle">مرحباً {{ $user->name }}، نحن سعداء بوجودك معنا</div>
        </div>

        <div class="content">
            {!! $campaign->content !!}
        </div>

        @if($campaign->featured_products)
            <h2>منتجات متميزة</h2>
            <div class="products">
                @foreach($campaign->featured_products as $product)
                    <div class="product">
                        <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}" class="product-image">
                        <div class="product-info">
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="product-price">{{ number_format($product->price, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                            <a href="{{ route('products.show', $product->id) }}" class="button">عرض المنتج</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="footer">
            <p>{{ config('app.name', 'متجر إلكتروني') }} - جميع الحقوق محفوظة © {{ date('Y') }}</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <div class="unsubscribe">
                <p>تريد إلغاء الاشتراك؟ <a href="{{ route('unsubscribe', ['email' => $user->email, 'campaign_id' => $campaign->id]) }}">اضغط هنا</a></p>
            </div>
        </div>
    </div>
</body>
</html>
