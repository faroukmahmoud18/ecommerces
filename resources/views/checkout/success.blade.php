@extends('layouts.app')

@section('title', 'تم إنشاء الطلب بنجاح')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="success-icon mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h1 class="text-success mb-3">تم إنشاء طلبك بنجاح!</h1>
                <p class="lead text-muted">
                    شكراً لك على طلبك. سيتم معالجة طلبك قريباً وستصلك رسالة تأكيد على بريدك الإلكتروني.
                </p>
            </div>

            <!-- Order Details -->
            @foreach($orders as $order)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        طلب رقم: #{{ $order->order_number }}
                    </h5>
                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                        {{ $order->payment_status === 'paid' ? 'تم الدفع' : 'في انتظار الدفع' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>معلومات المتجر:</h6>
                            <p class="text-muted mb-3">
                                <i class="fas fa-store me-2"></i>
                                {{ $order->vendor->name }}
                            </p>

                            <h6>معلومات الشحن:</h6>
                            <address class="text-muted">
                                {{ $order->customer_name }}<br>
                                {{ $order->customer_address }}<br>
                                {{ $order->customer_city }}, {{ $order->customer_country }}<br>
                                @if($order->customer_postal_code)
                                    {{ $order->customer_postal_code }}<br>
                                @endif
                                <strong>الهاتف:</strong> {{ $order->customer_phone }}<br>
                                <strong>البريد:</strong> {{ $order->customer_email }}
                            </address>
                        </div>

                        <div class="col-md-6">
                            <h6>تفاصيل الطلب:</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td>تاريخ الطلب:</td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>طريقة الدفع:</td>
                                    <td>{{ ucfirst($order->payment_method) }}</td>
                                </tr>
                                <tr>
                                    <td>حالة الطلب:</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $order->status_label }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>المبلغ الإجمالي:</td>
                                    <td><strong>{{ number_format($order->total_amount, 2) }} ر.س</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h6 class="mt-4 mb-3">عناصر الطلب:</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>السعر</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ $item->product_name }}</h6>
                                                <small class="text-muted">SKU: {{ $item->product_sku }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->price, 2) }} ر.س</td>
                                    <td>{{ number_format($item->total, 2) }} ر.س</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3">المجموع الفرعي:</th>
                                    <th>{{ number_format($order->subtotal, 2) }} ر.س</th>
                                </tr>
                                <tr>
                                    <th colspan="3">الشحن:</th>
                                    <th>{{ number_format($order->shipping_cost, 2) }} ر.س</th>
                                </tr>
                                <tr>
                                    <th colspan="3">الضريبة:</th>
                                    <th>{{ number_format($order->tax_amount, 2) }} ر.س</th>
                                </tr>
                                <tr class="table-success">
                                    <th colspan="3">الإجمالي:</th>
                                    <th>{{ number_format($order->total_amount, 2) }} ر.س</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Action Buttons -->
            <div class="text-center mt-5">
                <a href="{{ route('home') }}" class="btn btn-primary me-3">
                    <i class="fas fa-home me-2"></i>
                    العودة للرئيسية
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-shopping-bag me-2"></i>
                    متابعة التسوق
                </a>
            </div>

            <!-- What's Next -->
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        ماذا بعد؟
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                            <h6>رسالة تأكيد</h6>
                            <p class="text-muted small">
                                ستصلك رسالة تأكيد على بريدك الإلكتروني تحتوي على تفاصيل الطلب
                            </p>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-cog fa-2x text-warning mb-3"></i>
                            <h6>معالجة الطلب</h6>
                            <p class="text-muted small">
                                سيقوم المتجر بمعالجة طلبك وتجهيزه للشحن خلال 1-2 أيام عمل
                            </p>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-shipping-fast fa-2x text-success mb-3"></i>
                            <h6>شحن الطلب</h6>
                            <p class="text-muted small">
                                ستحصل على رقم تتبع الشحنة لمتابعة حالة التوصيل
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="alert alert-info mt-4">
                <h6 class="alert-heading">
                    <i class="fas fa-question-circle me-2"></i>
                    تحتاج مساعدة؟
                </h6>
                <p class="mb-0">
                    إذا كان لديك أي استفسار حول طلبك، يمكنك التواصل معنا على:
                    <br>
                    <strong>البريد الإلكتروني:</strong> support@example.com
                    <br>
                    <strong>الهاتف:</strong> +966 50 123 4567
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.success-icon i {
    animation: success-animation 0.8s ease-in-out;
}

@keyframes success-animation {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.75rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 0.75rem 0.75rem 0 0 !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.alert-info {
    background-color: #e7f3ff;
    border-color: #b8daff;
    color: #004085;
}

.btn {
    border-radius: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
}

.btn-outline-primary {
    border-width: 2px;
}

.badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endsection