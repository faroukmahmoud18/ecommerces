@extends('layouts.app')

@section('title', 'تم إلغاء العملية')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center">
                <!-- Cancel Icon -->
                <div class="cancel-icon mb-4">
                    <i class="fas fa-times-circle text-warning" style="font-size: 4rem;"></i>
                </div>

                <!-- Cancel Message -->
                <h1 class="text-warning mb-3">تم إلغاء العملية</h1>
                <p class="lead text-muted mb-4">
                    تم إلغاء عملية الدفع. لم يتم خصم أي مبلغ من حسابك.
                </p>

                <!-- Reasons -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title">أسباب محتملة للإلغاء:</h6>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-muted me-2"></i> إلغاء العملية من قبل المستخدم</li>
                            <li><i class="fas fa-check text-muted me-2"></i> انتهاء وقت جلسة الدفع</li>
                            <li><i class="fas fa-check text-muted me-2"></i> مشكلة في بيانات البطاقة</li>
                            <li><i class="fas fa-check text-muted me-2"></i> عدم توفر رصيد كافي</li>
                        </ul>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-4">
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary me-md-2">
                        <i class="fas fa-redo me-2"></i>
                        إعادة المحاولة
                    </a>
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-cart me-2"></i>
                        العودة للسلة
                    </a>
                </div>

                <div class="d-grid">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>
                        العودة للرئيسية
                    </a>
                </div>

                <!-- Help Section -->
                <div class="alert alert-info mt-5">
                    <h6 class="alert-heading">
                        <i class="fas fa-question-circle me-2"></i>
                        تحتاج مساعدة؟
                    </h6>
                    <p class="mb-0">
                        إذا واجهت مشكلة في عملية الدفع، يمكنك التواصل معنا:
                        <br>
                        <strong>البريد الإلكتروني:</strong> support@example.com
                        <br>
                        <strong>الهاتف:</strong> +966 50 123 4567
                        <br>
                        <strong>الدردشة المباشرة:</strong> متاحة 24/7
                    </p>
                </div>

                <!-- Payment Methods -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">طرق الدفع المتاحة</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <i class="fab fa-cc-visa fa-2x text-primary"></i>
                                <div class="small mt-1">Visa</div>
                            </div>
                            <div class="col-3">
                                <i class="fab fa-cc-mastercard fa-2x text-warning"></i>
                                <div class="small mt-1">Mastercard</div>
                            </div>
                            <div class="col-3">
                                <i class="fab fa-paypal fa-2x text-info"></i>
                                <div class="small mt-1">PayPal</div>
                            </div>
                            <div class="col-3">
                                <i class="fas fa-mobile-alt fa-2x text-success"></i>
                                <div class="small mt-1">فوري</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.cancel-icon i {
    animation: cancel-animation 0.8s ease-in-out;
}

@keyframes cancel-animation {
    0% {
        transform: scale(0) rotate(0deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.2) rotate(180deg);
    }
    100% {
        transform: scale(1) rotate(360deg);
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

.fab, .fas {
    transition: transform 0.3s ease;
}

.fab:hover, .fas:hover {
    transform: scale(1.1);
}
</style>
@endsection