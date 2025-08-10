
@extends('layouts.app')

@section('title', 'تفاصيل المنتج')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">المنتجات</a></li>
            <li class="breadcrumb-item active">تفاصيل المنتج</li>
        </ol>
    </nav>

    <div class="row">
        <!-- صور المنتج -->
        <div class="col-lg-6 mb-4">
            <div class="card product-image-container">
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            {{-- <img src="/800/600.jpg" class="d-block w-100" alt="صورة المنتج"> --}}
                        </div>
                        <div class="carousel-item">
                            {{-- <img src="-2/800/600.jpg" class="d-block w-100" alt="صورة المنتج"> --}}
                        </div>
                        <div class="carousel-item">
                            {{-- <img src="-3/800/600.jpg" class="d-block w-100" alt="صورة المنتج"> --}}
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">السابق</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">التالي</span>
                    </button>
                </div>

                <!-- صور مصغرة -->
                <div class="row mt-3">
                    <div class="col-4">
                        <div class="thumbnail-container active">
                            <img src="/150/150.jpg" class="img-thumbnail" alt="صورة مصغرة">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="thumbnail-container">
                            <img src="-2/150/150.jpg" class="img-thumbnail" alt="صورة مصغرة">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="thumbnail-container">
                            <img src="-3/150/150.jpg" class="img-thumbnail" alt="صورة مصغرة">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- معلومات المنتج -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h1 class="card-title">اسم المنتج هنا</h1>
                        <button class="btn btn-light btn-sm">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>

                    <!-- معلومات البائع -->
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://picsum.photos/seed/vendor/50/50.jpg" class="rounded-circle me-3" alt="صورة البائع">
                        <div>
                            <h6 class="mb-0">اسم البائع</h6>
                            <div class="vendor-rating">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="far fa-star text-warning"></i>
                                <span class="text-muted ms-2">(24 تقييم)</span>
                            </div>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-primary ms-auto">زيارة المتجر</a>
                    </div>

                    <!-- السعر -->
                    <div class="d-flex align-items-center mb-4">
                        <div>
                            <span class="product-price fs-4 fw-bold">199 {{ config('app.currency_symbol', 'ر.س') }}</span>
                            <span class="text-muted text-decoration-line-through ms-2">249 {{ config('app.currency_symbol', 'ر.س') }}</span>
                        </div>
                        <span class="badge bg-danger ms-2">خصم 20%</span>
                    </div>

                    <!-- خيارات المنتج -->
                    <div class="mb-4">
                        <h6>اللون:</h6>
                        <div class="d-flex">
                            <button class="btn btn-sm btn-outline-secondary me-2 active">أبيض</button>
                            <button class="btn btn-sm btn-outline-secondary me-2">أسود</button>
                            <button class="btn btn-sm btn-outline-secondary me-2">أزرق</button>
                            <button class="btn btn-sm btn-outline-secondary">أحمر</button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6>المقاس:</h6>
                        <div class="d-flex">
                            <button class="btn btn-sm btn-outline-secondary me-2 active">S</button>
                            <button class="btn btn-sm btn-outline-secondary me-2">M</button>
                            <button class="btn btn-sm btn-outline-secondary me-2">L</button>
                            <button class="btn btn-sm btn-outline-secondary">XL</button>
                        </div>
                    </div>

                    <!-- الكمية -->
                    <div class="mb-4">
                        <h6>الكمية:</h6>
                        <div class="input-group" style="max-width: 150px;">
                            <button class="btn btn-outline-secondary" type="button" id="decreaseQuantity">-</button>
                            <input type="text" class="form-control text-center" value="1" id="quantity" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="increaseQuantity">+</button>
                        </div>
                    </div>

                    <!-- أزرار الإجراء -->
                    <div class="d-grid gap-2 mb-4">
                        <button class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i> أضف إلى السلة
                        </button>
                        <button class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-bolt me-2"></i> شراء فوري
                        </button>
                    </div>

                    <!-- معلومات إضافية -->
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <i class="fas fa-truck fs-4 text-primary"></i>
                            <p class="mb-0">توصيل سريع</p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-undo fs-4 text-primary"></i>
                            <p class="mb-0">إرجاع مجاني</p>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-shield-alt fs-4 text-primary"></i>
                            <p class="mb-0">ضمان المنتج</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- علامات التبويب -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">الوصف</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab" aria-controls="specifications" aria-selected="false">المواصفات</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">المراجعات</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">الشحن والتوصيل</button>
                </li>
            </ul>

            <div class="tab-content" id="productTabsContent">
                <!-- علامة تبويب الوصف -->
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">وصف المنتج</h5>
                            <p>هذا هو الوصف التفصيلي للمنتج. يمكن أن يتضمن معلومات عن المواد المستخدمة، طريقة الاستخدام، وميزات المنتج المختلفة. يجب أن يكون الوصف واضحاً ومفصلاً لمساعدة العميل على فهم المنتج بشكل كامل.</p>
                            <p>يمكنك أيضاً إضافة معلومات عن فوائد المنتج أو مميزاته التي تميزه عن المنتجات الأخرى في السوق.</p>
                            <p>تأكد من تقديم معلومات دقيقة وصحيحة عن المنتج لتجنب أي سوء فهم من قبل العميل.</p>
                        </div>
                    </div>
                </div>

                <!-- علامة تبويب المواصفات -->
                <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">مواصفات المنتج</h5>
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th scope="row">الماركة</th>
                                        <td>ماركة المنتج</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">الموديل</th>
                                        <td>موديل المنتج</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">البلد الأصلي</th>
                                        <td>البلد الأصلي للمنتج</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">الوزن</th>
                                        <td>1.2 كجم</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">الأبعاد</th>
                                        <td>20 × 15 × 5 سم</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">الضمان</th>
                                        <td>سنة واحدة</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">المواد</th>
                                        <td>مادة المنتج الرئيسية</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- علامة تبويب المراجعات -->
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-3 text-center">
                                    <div class="display-4 fw-bold">4.2</div>
                                    <div class="vendor-rating">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="far fa-star text-warning"></i>
                                    </div>
                                    <div class="text-muted">24 مراجعة</div>
                                </div>
                                <div class="col-md-9">
                                    <div class="d-flex mb-1">
                                        <div class="me-2" style="width: 100px;">
                                            <span>5 نجوم</span>
                                        </div>
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div>
                                            60%
                                        </div>
                                    </div>
                                    <div class="d-flex mb-1">
                                        <div class="me-2" style="width: 100px;">
                                            <span>4 نجوم</span>
                                        </div>
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div>
                                            25%
                                        </div>
                                    </div>
                                    <div class="d-flex mb-1">
                                        <div class="me-2" style="width: 100px;">
                                            <span>3 نجوم</span>
                                        </div>
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div>
                                            10%
                                        </div>
                                    </div>
                                    <div class="d-flex mb-1">
                                        <div class="me-2" style="width: 100px;">
                                            <span>2 نجوم</span>
                                        </div>
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 3%" aria-valuenow="3" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div>
                                            3%
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="me-2" style="width: 100px;">
                                            <span>1 نجمة</span>
                                        </div>
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 2%" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div>
                                            2%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- نموذج إضافة مراجعة -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">أضف مراجعة للمنتج</h5>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="mb-3">
                                            <label for="rating" class="form-label">التقييم</label>
                                            <div class="rating">
                                                <i class="far fa-star" data-rating="1"></i>
                                                <i class="far fa-star" data-rating="2"></i>
                                                <i class="far fa-star" data-rating="3"></i>
                                                <i class="far fa-star" data-rating="4"></i>
                                                <i class="far fa-star" data-rating="5"></i>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reviewTitle" class="form-label">عنوان المراجعة</label>
                                            <input type="text" class="form-control" id="reviewTitle" placeholder="اكتب عنواناً للمراجعة">
                                        </div>
                                        <div class="mb-3">
                                            <label for="reviewText" class="form-label">نص المراجعة</label>
                                            <textarea class="form-control" id="reviewText" rows="4" placeholder="اكتب مراجعتك هنا..."></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reviewImages" class="form-label">صور المراجعة (اختياري)</label>
                                            <input type="file" class="form-control" id="reviewImages" multiple>
                                        </div>
                                        <button type="submit" class="btn btn-primary">إرسال المراجعة</button>
                                    </form>
                                </div>
                            </div>

                            <!-- قائمة المراجعات -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">المراجعات (24)</h5>
                                    <select class="form-select" style="width: auto;">
                                        <option>الأحدث</option>
                                        <option>الأعلى تقييماً</option>
                                        <option>الأقل تقييماً</option>
                                    </select>
                                </div>
                                <div class="card-body">
                                    <!-- مراجعة 1 -->
                                    <div class="mb-4 pb-4 border-bottom">
                                        <div class="d-flex justify-content-between mb-2">
                                            <div>
                                                <h6>أحمد محمد</h6>
                                                <div class="vendor-rating">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                </div>
                                            </div>
                                            <div class="text-muted">2023-05-15</div>
                                        </div>
                                        <p>منتج ممتاز جداً والجودة عالية جداً والسريع جداً والتوصيل في الموعد المحدد جداً</p>
                                        <div class="d-flex">
                                            <img src="https://picsum.photos/seed/review1-1/100/100.jpg" class="img-thumbnail me-2" style="width: 80px; height: 80px; object-fit: cover;">
                                            <img src="https://picsum.photos/seed/review1-2/100/100.jpg" class="img-thumbnail me-2" style="width: 80px; height: 80px; object-fit: cover;">
                                            <img src="https://picsum.photos/seed/review1-3/100/100.jpg" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                        </div>
                                    </div>

                                    <!-- مراجعة 2 -->
                                    <div class="mb-4 pb-4 border-bottom">
                                        <div class="d-flex justify-content-between mb-2">
                                            <div>
                                                <h6>فاطمة علي</h6>
                                                <div class="vendor-rating">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="far fa-star text-warning"></i>
                                                </div>
                                            </div>
                                            <div class="text-muted">2023-05-10</div>
                                        </div>
                                        <p>المنجم جيد جداً والجيدة جداً والتوصيل في الموعد المحدد جداً</p>
                                    </div>

                                    <!-- مراجعة 3 -->
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <div>
                                                <h6>خالد سعود</h6>
                                                <div class="vendor-rating">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="far fa-star text-warning"></i>
                                                    <i class="far fa-star text-warning"></i>
                                                </div>
                                            </div>
                                            <div class="text-muted">2023-05-05</div>
                                        </div>
                                        <p>المنتج جيد لكنه أقل من التوقعات</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- علامة تبويب الشحن والتوصيل -->
                <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">سياسة الشحن والتوصيل</h5>
                            <p>نحن نقدم خدمة شحن وتوصيل سريعة وموثوقة لجميع أنحاء المملكة العربية السعودية. يمكنك الاطلاع على تفاصيل سياسة الشحن والتوصيل من خلال الرابط أدناه.</p>

                            <div class="accordion" id="shippingAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            فترات الشحن
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#shippingAccordion">
                                        <div class="accordion-body">
                                            <p>تختلف فترات الشحن حسب الموقع:</p>
                                            <ul>
                                                <li>الرياض والمنطقة الوسطى: 1-2 يوم عمل</li>
                                                <li>جدة والمنطقة الغربية: 2-3 أيام عمل</li>
                                                <li>الدمام والمنطقة الشرقية: 2-3 أيام عمل</li>
                                                <li>المناطق الأخرى: 3-5 أيام عمل</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            تكاليف الشحن
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#shippingAccordion">
                                        <div class="accordion-body">
                                            <p>تكاليف الشحن تعتمد على قيمة الطلب وموقعه:</p>
                                            <ul>
                                                <li>الطلبات التي تقل قيمتها عن 100 ريال: 15 ريال</li>
                                                <li>الطلبات التي تزيد قيمتها عن 100 ريال: مجاناً</li>
                                                <li>المناطق النائية: قد تضاف تكلفة إضافية</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            تتبع الطلبات
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#shippingAccordion">
                                        <div class="accordion-body">
                                            <p>بعد شحن طلبك، سيتلقى بريدك الإلكتروني رابطاً لتتبع شحنتك. يمكنك أيضاً تتبع طلبك من خلال حسابك في الموقع.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                            استلام الطلبات
                                        </button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#shippingAccordion">
                                        <div class="accordion-body">
                                            <p>عند استلام طلبك، يرجى فحص المنتجات والتأكد من عدم وجود أي تلف. في حالة وجود أي مشكلة، يرجى التواصل مع خدمة العملاء خلال 24 ساعة من الاستلام.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- المنتجات ذات الصلة -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">المنتجات ذات الصلة</h3>
            <div class="row">
                @for ($i = 1; $i <= 4; $i++)
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card product-card">
                        <div class="position-relative">
                            <img src="https://picsum.photos/seed/related{{$i}}/400/300.jpg" class="card-img-top" alt="...">
                            <div class="position-absolute top-0 start-0">
                                <span class="badge bg-primary">جديد</span>
                            </div>
                            <div class="position-absolute top-0 end-0">
                                <button class="btn btn-sm btn-light">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">منتج ذو صلة {{$i}}</h5>
                            <p class="card-text text-muted">وصف قصير للمنتج.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="product-price">149 {{ config('app.currency_symbol', 'ر.س') }}</span>
                                </div>
                                <div class="vendor-rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="far fa-star text-warning"></i>
                                    <span class="text-muted">(12)</span>
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
        </div>
    </div>
</div>
@endsection
