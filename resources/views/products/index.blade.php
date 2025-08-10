
@extends('layouts.app')

@section('title', 'المنتجات')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-3">
            <!-- تصفيات المنتجات -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">التصفيات</h5>
                </div>
                <div class="card-body">
                    <!-- تصفية حسب السعر -->
                    <div class="mb-4">
                        <h6 class="mb-3">السعر</h6>
                        <div class="range-slider">
                            <input type="range" class="form-range" id="priceRange" min="0" max="10000" value="10000">
                            <div class="d-flex justify-content-between mt-2">
                                <span>0 {{ config('app.currency_symbol', 'ر.س') }}</span>
                                <span id="priceValue">10000 {{ config('app.currency_symbol', 'ر.س') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- تصفية حسب التصنيف -->
                    <div class="mb-4">
                        <h6 class="mb-3">التصنيفات</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cat1" value="option1">
                            <label class="form-check-label" for="cat1">
                                إلكترونيات
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cat2" value="option2">
                            <label class="form-check-label" for="cat2">
                                ملابس
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cat3" value="option3">
                            <label class="form-check-label" for="cat3">
                                منزل وديكور
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cat4" value="option4">
                            <label class="form-check-label" for="cat4">
                                مستلزمات رياضية
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cat5" value="option5">
                            <label class="form-check-label" for="cat5">
                                مستحضرات تجميل
                            </label>
                        </div>
                    </div>

                    <!-- تصفية حسب البائع -->
                    <div class="mb-4">
                        <h6 class="mb-3">البائعون</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="vendor1" value="option1">
                            <label class="form-check-label" for="vendor1">
                                متجر الرياض
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="vendor2" value="option2">
                            <label class="form-check-label" for="vendor2">
                                جدة للتجارة
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="vendor3" value="option3">
                            <label class="form-check-label" for="vendor3">
                                الدمام للإلكترونيات
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="vendor4" value="option4">
                            <label class="form-check-label" for="vendor4">
                                مكة للملابس
                            </label>
                        </div>
                    </div>

                    <!-- تصفية حسب التقييم -->
                    <div class="mb-4">
                        <h6 class="mb-3">التقييم</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rating5" value="option1">
                            <label class="form-check-label" for="rating5">
                                5 نجوم
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rating4" value="option2">
                            <label class="form-check-label" for="rating4">
                                4 نجوم فما فوق
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rating3" value="option3">
                            <label class="form-check-label" for="rating3">
                                3 نجوم فما فوق
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rating2" value="option4">
                            <label class="form-check-label" for="rating2">
                                2 نجوم فما فوق
                            </label>
                        </div>
                    </div>

                    <!-- تصفية حسب الخصم -->
                    <div class="mb-4">
                        <h6 class="mb-3">الخصم</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="discount50" value="option1">
                            <label class="form-check-label" for="discount50">
                                50% فما فوق
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="discount25" value="option2">
                            <label class="form-check-label" for="discount25">
                                25% فما فوق
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="discount10" value="option3">
                            <label class="form-check-label" for="discount10">
                                10% فما فوق
                            </label>
                        </div>
                    </div>

                    <!-- زر التطبيق -->
                    <button class="btn btn-primary w-100">تطبيق التصفيات</button>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- شريط البحث والفرز -->
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
                            <img src="https://picsum.photos/seed/product{{$i}}/400/300.jpg" class="card-img-top" alt="...">
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
