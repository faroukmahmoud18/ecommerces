
@extends('layouts.app')

@section('title', 'من نحن')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">من نحن</h1>
            <p class="lead">نحن منصة تجارة إلكترونية متعددة البائعين نهدف إلى توفير أفضل تجربة تسوق عبر الإنترنت</p>
        </div>
    </div>

    <!-- القصة -->
    <div class="row mb-5">
        <div class="col-lg-6 mb-4">
            <h2 class="mb-4">قصة المنصة</h2>
            <p>تأسست منصتنا في عام 2020 بهدف ربط البائعين والمشترين في منصة واحدة سهلة الاستخدام وآمنة. بدأنا كفريق صغير من الشباب الطموحين الذين شهدوا صعوبات التسوق التقليدي وتطلعوا إلى تغيير تجربة التسوق في العالم العربي.</p>
            <p>اليوم، أصبحنا واحدة من أسرع منصات التجارة الإلكترونية نمواً في المنطقة، مع آلاف البائعين والملايين من المنتجات المتاحة لملايين العملاء في جميع أنحاء العالم العربي.</p>
            <p>نحن ملتزمون بتقديم تجربة تسوق فريدة تجمع بين التنوع والجودة والسعر المناسب، مع ضمان راحة وسلامة جميع المستخدمين.</p>
        </div>
        <div class="col-lg-6 mb-4">
            <img src="https://picsum.photos/seed/about/600/400.jpg" class="img-fluid rounded" alt="من نحن">
        </div>
    </div>

    <!-- الرؤية والرسالة -->
    <div class="row mb-5">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-eye fa-3x text-primary"></i>
                    </div>
                    <h3 class="card-title">رؤيتنا</h3>
                    <p class="card-text">أن نكون المنصة الرائدة في مجال التجارة الإلكترونية في العالم العربي، مقدمةً أفضل تجربة تسوق رقمية تلبي احتياجات جميع المستخدمين وتساهم في نمو الاقتصاد الرقمي.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-bullseye fa-3x text-primary"></i>
                    </div>
                    <h3 class="card-title">رسالتنا</h3>
                    <p class="card-text">ربط البائعين والمشترين في منصة آمنة وسهلة الاستخدام، مقدِّمين منتجات عالية الجودة بأسعار تنافسية، مع ضمان تجربة تسوق ممتعة ومريحة للجميع.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- القيم -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="mb-4">قمنا</h2>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-handshake fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title">الشفافية</h5>
                    <p class="card-text">نحن نؤمن بالشفافية في جميع تعاملاتنا مع عملائنا وشركائنا.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title">الأمان</h5>
                    <p class="card-text">ضمان أمان بياناتك ومعلوماتك هو أولويتنا القصوى.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-star fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title">الجودة</h5>
                    <p class="card-text">نسعى دائماً لتقديم أعلى معايير الجودة في منتجاتنا وخدماتنا.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title">الابتكار</h5>
                    <p class="card-text">نستمر في تطوير منصتنا لتقديم تجربة تسوق حديثة ومبتكرة.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- الفريق -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="mb-4">فريقنا</h2>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center">
                <img src="https://picsum.photos/seed/member1/200/200.jpg" class="card-img-top rounded-circle" alt="...">
                <div class="card-body">
                    <h5 class="card-title">أحمد محمد</h5>
                    <p class="card-text text-muted">المؤسس والرئيس التنفيذي</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center">
                <img src="https://picsum.photos/seed/member2/200/200.jpg" class="card-img-top rounded-circle" alt="...">
                <div class="card-body">
                    <h5 class="card-title">فاطمة علي</h5>
                    <p class="card-text text-muted">مديرة التسويق</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center">
                <img src="https://picsum.photos/seed/member3/200/200.jpg" class="card-img-top rounded-circle" alt="...">
                <div class="card-body">
                    <h5 class="card-title">محمد سالم</h5>
                    <p class="card-text text-muted">مطور رئيسي</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center">
                <img src="https://picsum.photos/seed/member4/200/200.jpg" class="card-img-top rounded-circle" alt="...">
                <div class="card-body">
                    <h5 class="card-title">نورا خالد</h5>
                    <p class="card-text text-muted">مديرة العلاقات مع البائعين</p>
                </div>
            </div>
        </div>
    </div>

    <!-- الإحصائيات -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="mb-4">إنجازاتنا</h2>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <h3 class="card-title">1M+</h3>
                    <p class="card-text">عميل نشط</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-store fa-2x text-primary"></i>
                    </div>
                    <h3 class="card-title">50K+</h3>
                    <p class="card-text">بائع نشط</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-box fa-2x text-primary"></i>
                    </div>
                    <h3 class="card-title">2M+</h3>
                    <p class="card-text">منتج</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-globe fa-2x text-primary"></i>
                    </div>
                    <h3 class="card-title">15+</h3>
                    <p class="card-text">دولة عربية</p>
                </div>
            </div>
        </div>
    </div>

    <!-- التواصل معنا -->
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">تواصل معنا</h2>
        </div>
        <div class="col-lg-6 mb-4">
            <p>نحن نحب دائماً سماع آراء عملاء وشركائنا. إذا كان لديك أي استفسارات أو اقتراحات، فلا تتردد في التواصل معنا.</p>
            <div class="d-flex mb-3">
                <i class="fas fa-map-marker-alt fa-2x text-primary me-3"></i>
                <div>
                    <h5>العنوان</h5>
                    <p>شارع الملك فهد، حي النخيل، الرياض، المملكة العربية السعودية</p>
                </div>
            </div>
            <div class="d-flex mb-3">
                <i class="fas fa-phone fa-2x text-primary me-3"></i>
                <div>
                    <h5>الهاتف</h5>
                    <p>+966 50 123 4567</p>
                </div>
            </div>
            <div class="d-flex mb-3">
                <i class="fas fa-envelope fa-2x text-primary me-3"></i>
                <div>
                    <h5>البريد الإلكتروني</h5>
                    <p>info@platform.com</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <form>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="الاسم">
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" placeholder="البريد الإلكتروني">
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="الموضوع">
                </div>
                <div class="mb-3">
                    <textarea class="form-control" rows="4" placeholder="الرسالة"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">إرسال الرسالة</button>
            </form>
        </div>
    </div>
</div>
@endsection
