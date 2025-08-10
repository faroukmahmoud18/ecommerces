
@extends('layouts.app')

@section('title', 'اتصل بنا')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">اتصل بنا</h1>
            <p class="lead">نحن نحب دائماً سماع آراء عملاء وشركائنا. إذا كان لديك أي استفسارات أو اقتراحات، فلا تتردد في التواصل معنا.</p>
        </div>
    </div>

    <!-- معلومات التواصل -->
    <div class="row mb-5">
        <div class="col-lg-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt fa-3x text-primary"></i>
                    </div>
                    <h4 class="card-title">العنوان</h4>
                    <p class="card-text">شارع الملك فهد، حي النخيل، الرياض، المملكة العربية السعودية</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-phone fa-3x text-primary"></i>
                    </div>
                    <h4 class="card-title">الهاتف</h4>
                    <p class="card-text">+966 50 123 4567</p>
                    <p class="card-text">+966 11 456 7890</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-envelope fa-3x text-primary"></i>
                    </div>
                    <h4 class="card-title">البريد الإلكتروني</h4>
                    <p class="card-text">info@platform.com</p>
                    <p class="card-text">support@platform.com</p>
                </div>
            </div>
        </div>
    </div>

    <!-- نموذج التواصل -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">أرسل لنا رسالة</h4>
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">الاسم</label>
                                <input type="text" class="form-control" id="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">الهاتف</label>
                                <input type="tel" class="form-control" id="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">الموضوع</label>
                                <select class="form-select" id="subject">
                                    <option selected>اختر الموضوع</option>
                                    <option value="1">استفسار عام</option>
                                    <option value="2">شكوى</option>
                                    <option value="3">اقتراح</option>
                                    <option value="4">دعم فني</option>
                                    <option value="5">طلب تعاون</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">الرسالة</label>
                            <textarea class="form-control" id="message" rows="5" required></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="subscribe">
                                <label class="form-check-label" for="subscribe">
                                    أرغب في تلقي النشرة البريدية والعروض الخاصة
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">إرسال الرسالة</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- الأسئلة الشائعة -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="mb-4">الأسئلة الشائعة</h2>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            كيف يمكنني إنشاء حساب في المنصة؟
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            لإنشاء حساب في منصتنا، ما عليك سوى النقر على زر "إنشاء حساب" في أعلى الصفحة، ثم ملء البيانات المطلوبة مثل الاسم والبريد الإلكتروني وكلمة المرور. بعد ذلك، سيتوفر لديك حساب يمكنك استخدامه للاستمتاع بجميع خدماتنا.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            كيف يمكنني إضافة منتجات إلى سلة التسوق؟
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            لإضافة منتج إلى سلة التسوق، ما عليك سوى النقر على زر "أضف إلى السلة" بجوار المنتج الذي تريده. يمكنك متابعة إضافة منتجات أخرى، وعندما تنتهي، يمكنك مراجعة سلة التسوق وإتمام عملية الشراء.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            ما هي وسائل الدفع المتاحة في المنصة؟
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            نوفر مجموعة متنوعة من وسائل الد fortal以便包括信用卡、借记卡、PayPal、以及银行转账等多种支付方式，确保您可以按照最方便的方式进行支付。
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="accordion" id="faqAccordion2">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="true" aria-controls="collapseFour">
                            كم تستغرق عملية الشحن والتوصيل؟
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse show" aria-labelledby="headingFour" data-bs-parent="#faqAccordion2">
                        <div class="accordion-body">
                            تستغرق عملية الشحن والتوصيل من 1 إلى 5 أيام عمل حسب مكانك ومكان البائع. سيتم إبلاغك بتفاصيل الشحن وتتبع شحنتك بعد إتمام عملية الشراء.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            كيف يمكنني إرجاع منتج تم شراؤه؟
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion2">
                        <div class="accordion-body">
                            يمكنك إرجاع المنتجات خلال 14 يومًا من تاريخ الاستلام. ما عليك سوى الدخول إلى حسابك والذهاب إلى قسم "طلباتي" واختيار الطلب الذي تريد إرجاع جزء منه أو كل منه، ثم اتباع التعليمات المعروضة.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSix">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                            كيف يمكنني أن أصبح بائعًا في المنصة؟
                        </button>
                    </h2>
                    <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#faqAccordion2">
                        <div class="accordion-body">
                            يمكنك التسجيل كبائع في منصتنا بسهولة من خلال زيارة صفحة "كن بائعًا" وملء النموذج المطلوب. بعد مراجعة طلبك من قبل فريقنا، سيتم إبلاغك بقبولك في المنصة وستتمكن من البدء في إدراج منتجاتك للبيع.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- خريطة Google -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="mb-4">مكتبنا</h2>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3624.224!2d46.7321!3d24.7136!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e2f01d7f0e2b0d7%3A0x1234567890abcdef!2sRiyadh%2C%20Saudi%20Arabia!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
