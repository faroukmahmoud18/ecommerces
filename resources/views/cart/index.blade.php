
@extends('layouts.app')

@section('title', 'سلة التسوق')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-4">سلة التسوق</h1>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- عناصر السلة -->
            <div class="card mb-4">
                <div class="card-body">
                    @for ($i = 1; $i <= 3; $i++)
                    <div class="cart-item mb-4 pb-4 border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <input type="checkbox" class="form-check-input me-3" checked>
                                    <img src="https://picsum.photos/seed/cart{{ $i }}/100/100.jpg" class="rounded me-3" alt="...">
                                    <div>
                                        <h5 class="mb-1">منتج {{$i}}</h5>
                                        <p class="text-muted mb-1">اللون: أبيض، المقاس: M</p>
                                        <p class="text-muted mb-0">البائع: متجر الرياض</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group quantity">
                                    <button class="btn btn-outline-secondary" type="button">-</button>
                                    <input type="text" class="form-control text-center" value="{{ rand(1, 5) }}" readonly>
                                    <button class="btn btn-outline-secondary" type="button">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0">{{ rand(100, 500) }} {{ config('app.currency_symbol', 'ر.س') }}</h5>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-light">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            <!-- الكوبونات -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">كوبون الخصم</h5>
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="أدخل كود الخصم...">
                        <button class="btn btn-primary" type="button">تطبيق</button>
                    </div>
                </div>
            </div>

            <!-- متابعة التسوق -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> متابعة التسوق
                </a>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- ملخص الطلب -->
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title">ملخص الطلب</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span>المجموع الفرعي:</span>
                        <span>{{ rand(500, 1500) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>الشحن:</span>
                        <span>{{ rand(20, 50) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>الضريبة:</span>
                        <span>{{ rand(50, 150) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span>الخصم:</span>
                        <span class="text-danger">-{{ rand(50, 200) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="mb-0">المجموع الكلي:</h5>
                        <h5 class="mb-0 text-primary">{{ rand(600, 1800) }} {{ config('app.currency_symbol', 'ر.س') }}</h5>
                    </div>

                    <button class="btn btn-primary w-100 mb-3">إتمام الشراء</button>

                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-lock me-1"></i>
                            معلومات الدفع آمنة ومشفرة
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
