
@extends('layouts.app')

@section('title', 'البائعون')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">أفضل البائعين في منصتنا</h1>
            <p class="lead text-muted">اكتشف أفضل البائعين ومنتجاتهم المميزة بأسعار تنافسية</p>
        </div>
    </div>

    <!-- شريط البحث والتصفية -->
    <div class="row mb-5">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="ابحث عن بائع...">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex justify-content-end">
                <select class="form-select" aria-label="Default select example">
                    <option selected>ترتيب حسب: الأعلى تقييماً</option>
                    <option value="1">الأكثر مبيعاً</option>
                    <option value="2">الأحدث</option>
                    <option value="3">الأكثر شعبية</option>
                </select>
            </div>
        </div>
    </div>

    <!-- بطاقات البائعين -->
    <div class="row">
        @for ($i = 1; $i <= 8; $i++)
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card vendor-card">
                <img src="https://picsum.photos/seed/vendor{{$i}}/400/200.jpg" class="card-img-top" alt="صورة البائع">
                <div class="card-body">
                    <h5 class="card-title">اسم البائع {{$i}}</h5>
                    <p class="card-text text-muted">وصف قصير عن البائع ويمكن أن يكون طويلاً بعض الشيء ليملأ المساحة المخصصة له.</p>
                    <div class="vendor-rating mb-2">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <span class="text-muted ms-2">(4.8)</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted mb-3">
                        <span><i class="fas fa-box me-1"></i> 24 منتج</span>
                        <span><i class="fas fa-shopping-bag me-1"></i> 128 عملية بيع</span>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendors.show', $i) }}" class="btn btn-primary">زيارة المتجر</a>
                    </div>
                </div>
            </div>
        </div>
        @endfor
    </div>

    <!-- التصفحات -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">السابق</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#">التالي</a>
            </li>
        </ul>
    </nav>
</div>
@endsection
