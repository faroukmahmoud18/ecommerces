
@extends('layouts.app')

@section('title', 'البحث')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">البحث عن منتجات</h1>
            <p class="lead text-muted">ابحث عن المنتجات التي تبحث عنها بسهولة</p>
        </div>
    </div>

    <!-- شريط البحث -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="search-box">
                <input type="text" class="form-control" placeholder="ابحث عن منتجات، تصنيفات، أو بائعين..." value="{{ request('q') }}">
                <button type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- تصفيات البحث -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">التصفيات</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- تصفية حسب السعر -->
                        <div class="col-md-3 mb-3">
                            <h6>السعر</h6>
                            <div class="range-slider">
                                <input type="range" class="form-range" id="priceRange" min="0" max="10000" value="10000">
                                <div class="d-flex justify-content-between mt-2">
                                    <span>0 {{ config('app.currency_symbol', 'ر.س') }}</span>
                                    <span id="priceValue">10000 {{ config('app.currency_symbol', 'ر.س') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- تصفية حسب التصنيف -->
                        <div class="col-md-3 mb-3">
                            <h6>التصنيفات</h6>
                            <select class="form-select" multiple>
                                <option>إلكترونيات</option>
                                <option>ملابس</option>
                                <option>منزل وديكور</option>
                                <option>رياضة</option>
                                <option>جمال</option>
                                <option>كتب</option>
                            </select>
                        </div>

                        <!-- تصفية حسب البائع -->
                        <div class="col-md-3 mb-3">
                            <h6>البائعون</h6>
                            <select class="form-select" multiple>
                                <option>متجر الرياض</option>
                                <option>جدة للتجارة</option>
                                <option>الدمام للإلكترونيات</option>
                                <option>مكة للملابس</option>
                            </select>
                        </div>

                        <!-- تصفية حسب التقييم -->
                        <div class="col-md-3 mb-3">
                            <h6>التقييم</h6>
                            <select class="form-select">
                                <option>جميع التقييمات</option>
                                <option>5 نجوم</option>
                                <option>4 نجوم فما فوق</option>
                                <option>3 نجوم فما فوق</option>
                                <option>2 نجوم فما فوق</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary">تطبيق التصفيات</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- نتائج البحث -->
    <div class="row">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5>نتائج البحث ({{ rand(10, 50) }} منتج)</h5>
                <select class="form-select" style="width: auto;">
                    <option>ترتيب حسب: الأحدث</option>
                    <option>السعر: من الأقل للأعلى</option>
                    <option>السعر: من الأعلى للأقل</option>
                    <option>الأكثر مبيعاً</option>
                    <option>الأعلى تقييماً</option>
                </select>
            </div>

            <div class="row">
                @for ($i = 1; $i <= 12; $i++)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card product-card">
                        <div class="position-relative">
                            <img src="https://picsum.photos/seed/search{{ $i }}/400/300.jpg" class="card-img-top" alt="...">
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

        <!-- البحث المتقدم -->
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">بحث متقدم</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>الكلمات المفتاحية</h6>
                        <input type="text" class="form-control" placeholder="أضف كلمات مفتاحية...">
                    </div>

                    <div class="mb-3">
                        <h6>العلامات التجارية</h6>
                        <select class="form-select">
                            <option>جميع العلامات التجارية</option>
                            <option>علامة تجارية 1</option>
                            <option>علامة تجارية 2</option>
                            <option>علامة تجارية 3</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <h6>الخصومات</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="discount">
                            <label class="form-check-label" for="discount">
                                فقط المنتجات المخفضة
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6>الشحن المجاني</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="shipping">
                            <label class="form-check-label" for="shipping">
                                فقط المنتجات بشحن مجاني
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6>التوصيل السريع</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="delivery">
                            <label class="form-check-label" for="delivery">
                                فقط المنتجات بتوصيل سريع
                            </label>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100">بحث متقدم</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
