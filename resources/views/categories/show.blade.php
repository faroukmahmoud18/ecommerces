
@extends('layouts.app')

@section('title', 'التصنيف')

@section('content')
<div class="container py-5">
    <!-- مسار التنقل -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">التصنيفات</a></li>
            <li class="breadcrumb-item active">التصنيف</li>
        </ol>
    </nav>

    <div class="row">
        <!-- معلومات التصنيف -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <img src="https://picsum.photos/seed/category{{ $id }}/400/300.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h2 class="card-title">اسم التصنيف</h2>
                    <p class="card-text">وصف قصير عن هذا التصنيف ويمكن أن يكون طويلاً بعض الشيء ليملأ المساحة المخصصة له. يمكن أن يتضمن معلومات عن أنواع المنتجات المتوفرة في هذا التصنيف.</p>

                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-primary">
                            <i class="fas fa-bell me-2"></i>تنبيهات عند توفر منتجات جديدة
                        </button>
                    </div>
                </div>
            </div>

            <!-- تصنيفات فرعية -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">التصنيفات الفرعية</h5>
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-chevron-left me-2"></i>التصنيف الفرعي 1
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-chevron-left me-2"></i>التصنيف الفرعي 2
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-chevron-left me-2"></i>التصنيف الفرعي 3
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-chevron-left me-2"></i>التصنيف الفرعي 4
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-chevron-left me-2"></i>التصنيف الفرعي 5
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- المنتجات -->
        <div class="col-lg-8">
            <!-- شريط البحث والتصفية -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="ابحث عن منتج...">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <select class="form-select" aria-label="Default select example">
                            <option selected>ترتيب حسب: الأحدث</option>
                            <option value="1">السعر: من الأقل للأعلى</option>
                            <option value="2">السعر: من الأعلى للأقل</option>
                            <option value="3">الأكثر مبيعاً</option>
                            <option value="4">الأعلى تقييماً</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- المنتجات -->
            <div class="row">
                @for ($i = 1; $i <= 12; $i++)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card product-card">
                        <div class="position-relative">
                            <img src="https://picsum.photos/seed/category{{ $id }}-product{{$i}}/400/300.jpg" class="card-img-top" alt="...">
                            <div class="position-absolute top-0 start-0">
                                <span class="badge bg-danger">خصم 20%</span>
                            </div>
                            <div class="position-absolute top-0 end-0">
                                <button class="btn btn-sm btn-light">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">منتج {{$i}}</h5>
                            <p class="card-text text-muted">وصف قصير للمنتج ويمكن أن يكون طويلاً بعض الشيء ليملأ المساحة المخصصة له.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="product-price">199 {{ config('app.currency_symbol', 'ر.س') }}</span>
                                    <span class="text-muted text-decoration-line-through">249 {{ config('app.currency_symbol', 'ر.س') }}</span>
                                </div>
                                <div class="vendor-rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="far fa-star text-warning"></i>
                                    <span class="text-muted">(24)</span>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <button class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i> أضف إلى السلة
                                </button>
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
    </div>
</div>
@endsection
