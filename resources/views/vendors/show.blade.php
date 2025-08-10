
@extends('layouts.app')

@section('title', 'متجر البائع')

@section('content')
<div class="container py-5">
    <!-- مسار التنقل -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vendors.index') }}">البائعون</a></li>
            <li class="breadcrumb-item active">متجر البائع</li>
        </ol>
    </nav>

    <div class="row">
        <!-- معلومات البائع -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="https://picsum.photos/seed/vendor{{ $id }}/200/200.jpg" class="rounded-circle mb-3" alt="صورة البائع">
                    <h3>اسم البائع</h3>
                    <div class="vendor-rating mb-3">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <span class="text-muted ms-2">(4.8)</span>
                    </div>
                    <p class="text-muted">وصف قصير عن البائع ومتجره ويمكن أن يكون طويلاً بعض الشيء ليملأ المساحة المخصصة له.</p>

                    <div class="d-grid gap-2 mt-4">
                        <a href="#" class="btn btn-primary">متابعة المتجر</a>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-share-alt me-2"></i>مشاركة
                        </button>
                    </div>
                </div>
            </div>

            <!-- إحصائيات البائع -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">إحصائيات المتجر</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-center">
                            <h4 class="text-primary">24</h4>
                            <p class="text-muted mb-0">منتج</p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-primary">128</h4>
                            <p class="text-muted mb-0">عمليات بيع</p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-primary">1.2K</h4>
                            <p class="text-muted mb-0">متابع</p>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <h4 class="text-success">98%</h4>
                            <p class="text-muted mb-0">تقييم إيجابي</p>
                        </div>
                        <div class="text-center">
                            <h4 class="text-success">24 ساعة</h4>
                            <p class="text-muted mb-0">وقت الشحن</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- وسائل التواصل -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">تواصل مع البائع</h5>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-comments me-2"></i>محادثة فورية
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fab fa-whatsapp me-2"></i>تواصل عبر واتساب
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-phone me-2"></i>اتصل بالبائع
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- منتجات البائع -->
        <div class="col-lg-8">
            <!-- علامات التبويب -->
            <ul class="nav nav-tabs mb-4" id="vendorTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab" aria-controls="products" aria-selected="true">المنتجات</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">المراجعات</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab" aria-controls="about" aria-selected="false">عن المتجر</button>
                </li>
            </ul>

            <!-- محتوى علامات التبويب -->
            <div class="tab-content" id="vendorTabsContent">
                <!-- علامة تبويب المنتجات -->
                <div class="tab-pane fade show active" id="products" role="tabpanel" aria-labelledby="products-tab">
                    <div class="row">
                        @for ($i = 1; $i <= 6; $i++)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card product-card">
                                <div class="position-relative">
                                    <img src="https://picsum.photos/seed/vendor{{ $id }}-product{{$i}}/400/300.jpg" class="card-img-top" alt="...">
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

                <!-- علامة تبويب المراجعات -->
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">تقييمات المتجر</h5>
                            <div class="d-flex align-items-center mb-4">
                                <div class="me-4">
                                    <h2 class="mb-0">4.8</h2>
                                    <div class="vendor-rating">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <p class="text-muted mb-0">128 تقييم</p>
                                </div>
                                <div class="flex-grow-1">
                                    @for ($i = 5; $i >= 1; $i--)
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="me-2">{{ $i }} نجوم</span>
                                        <div class="progress flex-grow-1" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ rand(60, 95) }}%;" aria-valuenow="{{ rand(60, 95) }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="ms-2">{{ rand(10, 40) }}%</span>
                                    </div>
                                    @endfor
                                </div>
                            </div>

                            <hr>

                            <!-- المراجعات -->
                            <div class="mb-4">
                                <div class="d-flex align-items-start mb-3">
                                    <img src="https://picsum.photos/seed/review1/50/50.jpg" class="rounded-circle me-3" alt="...">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-0">اسم العميل</h6>
                                            <small class="text-muted">منذ 3 أيام</small>
                                        </div>
                                        <div class="vendor-rating mb-2">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                        </div>
                                        <p>مراجعة رائعة للمنتج والخدمة. البائع كان سريع الاستجابة والمنتج كما هو موضح في الصور. سأعود للتسوق من هذا المتجر مرة أخرى بالتأكيد.</p>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary">مفيد</button>
                                            <button class="btn btn-outline-primary">الإبلاغ عن إساءة</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-start mb-3">
                                    <img src="https://picsum.photos/seed/review2/50/50.jpg" class="rounded-circle me-3" alt="...">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-0">اسم العميل</h6>
                                            <small class="text-muted">منذ أسبوع</small>
                                        </div>
                                        <div class="vendor-rating mb-2">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="far fa-star text-warning"></i>
                                        </div>
                                        <p>تجربة تسوق جيدة جداً، المنتج جودة عالية والتوصيل كان في الوقت المحدد. الوصف دقيق والصور تعكس المنتج الحقيقي. سأوصي بهذا المتجر لأصدقائي.</p>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary">مفيد</button>
                                            <button class="btn btn-outline-primary">الإبلاغ عن إساءة</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-start">
                                    <img src="https://picsum.photos/seed/review3/50/50.jpg" class="rounded-circle me-3" alt="...">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-0">اسم العميل</h6>
                                            <small class="text-muted">منذ أسبوعين</small>
                                        </div>
                                        <div class="vendor-rating mb-2">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                        </div>
                                        <p>منتج ممتاز والبائع موثوق جداً. الخدمة الاستثنائية والمنتج كما وصف بالضبط. التوصيل سريع والمنتج مغلف بشكل احترافي. سأطلب مرة أخرى بالتأكيد.</p>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary">مفيد</button>
                                            <button class="btn btn-outline-primary">الإبلاغ عن إساءة</button>
                                        </div>
                                    </div>
                                </div>
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

                <!-- علامة تبويب عن المتجر -->
                <div class="tab-pane fade" id="about" role="tabpanel" aria-labelledby="about-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">عن المتجر</h5>
                            <p>هذا هو وصف متجر البائع. يمكن أن يتضمن معلومات عن تاريخ المتجر، رؤيته وأهدافه، والمنتجات التي يبيعها. يجب أن يكون الوصف شاملاً ومفصلاً لمساعدة العميل على فهم المتجر بشكل أفضل.</p>
                            <p>يمكن أيضاً ذكر معلومات عن سياسة الشحن والاسترجاع، والضمانات المقدمة للعملاء، وأي معلومات أخرى مهمة للعميل.</p>

                            <hr>

                            <h5 class="card-title">سياسة الشحن</h5>
                            <p>نحن نقدم خدمة شحن سريعة وموثوقة لجميع مناطق المملكة. وقت الشحن العادي يتراوح بين 24-48 ساعة للمدن الرئيسية، و3-5 أيام للمناطق الأخرى.</p>
                            <p>نحن نستخدم شركات شحن موثوقة وتتبع جميع الطلبات لضمان وصولها إلى العميل بأمان وفي الوقت المحدد.</p>

                            <hr>

                            <h5 class="card-title">سياسة الاسترجاع</h5>
                            <p>نحن نضمن راحة العميل وراحته التامة. يمكن إرجاع المنتج خلال 14 يوماً من تاريخ الاستلام، بشرط أن يكون المنتج في حالته الأصلية وغير مستخدم.</p>
                            <p>تكاليف الشحن للاسترجاع تكون على البائع في حالة وجود عيب في المنتج أو عدم تطابق المنتج مع الوصف. في الحالات الأخرى، تكون تكاليف الشحن على العميل.</p>

                            <hr>

                            <h5 class="card-title">طرق الدفع</h5>
                            <p>نحن نقبل多种دفعات مختلفة لتسهيل عملية الشراء:</p>
                            <ul>
                                <li>الدفع عند الاستلام</li>
                                <li>بطاقات الائتمان والخصم</li>
                                <li>تحويل بنكي</li>
                                <li>محفظة الدفع الإلكتروني</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
