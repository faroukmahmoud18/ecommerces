
@extends('layouts.app')

@section('title', 'التصنيفات')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">تصفح حسب التصنيف</h1>
            <p class="lead text-muted">اكتشف منتجاتنا المنظمة حسب التصنيفات المختلفة</p>
        </div>
    </div>

    <!-- البحث -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="ابحث عن تصنيف...">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- الشبكة الرئيسية للتصنيفات -->
    <div class="row mb-5">
        <div class="col-md-3 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/electronics/400/300.jpg" class="card-img-top" alt="إلكترونيات">
                <div class="card-body">
                    <h5 class="card-title">إلكترونيات</h5>
                    <p class="card-text text-muted">أحدث التقنيات والأجهزة الإلكترونية</p>
                    <a href="{{ route('categories.show', 1) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/clothing/400/300.jpg" class="card-img-top" alt="ملابس">
                <div class="card-body">
                    <h5 class="card-title">ملابس</h5>
                    <p class="card-text text-muted">أحدث الموديلات والأناقة</p>
                    <a href="{{ route('categories.show', 2) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/home/400/300.jpg" class="card-img-top" alt="منزل وديكور">
                <div class="card-body">
                    <h5 class="card-title">منزل وديكور</h5>
                    <p class="card-text text-muted">لجعل منزلك أكثر جمالاً</p>
                    <a href="{{ route('categories.show', 3) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/sports/400/300.jpg" class="card-img-top" alt="رياضة">
                <div class="card-body">
                    <h5 class="card-title">رياضة</h5>
                    <p class="card-text text-muted">مستلزمات رياضية للجميع</p>
                    <a href="{{ route('categories.show', 4) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>
    </div>

    <!-- شبكة ثانوية للتصنيفات -->
    <div class="row">
        <div class="col-md-4 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/beauty/400/300.jpg" class="card-img-top" alt="جمال">
                <div class="card-body">
                    <h5 class="card-title">جمال</h5>
                    <p class="card-text text-muted">مستحضرات تجميل وعناية بالبشرة</p>
                    <a href="{{ route('categories.show', 5) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/books/400/300.jpg" class="card-img-top" alt="كتب">
                <div class="card-body">
                    <h5 class="card-title">كتب</h5>
                    <p class="card-text text-muted">أفضل الكتب في مختلف المجالات</p>
                    <a href="{{ route('categories.show', 6) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/toys/400/300.jpg" class="card-img-top" alt="ألعاب">
                <div class="card-body">
                    <h5 class="card-title">ألعاب</h5>
                    <p class="card-text text-muted">ألعاب أطفال وألعاب فيديو</p>
                    <a href="{{ route('categories.show', 7) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/food/400/300.jpg" class="card-img-top" alt="طعام">
                <div class="card-body">
                    <h5 class="card-title">طعام ومشروبات</h5>
                    <p class="card-text text-muted">منتجات غذائية ومشروبات</p>
                    <a href="{{ route('categories.show', 8) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/health/400/300.jpg" class="card-img-top" alt="صحة">
                <div class="card-body">
                    <h5 class="card-title">صحة</h5>
                    <p class="card-text text-muted">منتجات صحية ومكملات غذائية</p>
                    <a href="{{ route('categories.show', 9) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6 mb-4">
            <div class="card category-card">
                <img src="https://picsum.photos/seed/auto/400/300.jpg" class="card-img-top" alt="سيارات">
                <div class="card-body">
                    <h5 class="card-title">سيارات ودراجات</h5>
                    <p class="card-text text-muted">قطع غيار وملحقات للسيارات</p>
                    <a href="{{ route('categories.show', 10) }}" class="btn btn-primary">استكشف</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
