
@extends('admin.layouts.app')

@section('title', 'الإعدادات')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">إعدادات الموقع</h6>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf

                    {{-- Tabs --}}
                    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">الإعدادات العامة</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">إعدادات الدفع</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">إعدادات الشحن</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab" aria-controls="email" aria-selected="false">إعدادات البريد الإلكتروني</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false">إعدادات التواصل الاجتماعي</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab" aria-controls="seo" aria-selected="false">إعدادات تحسين محركات البحث (SEO)</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="settingsTabContent">
                        {{-- General Settings --}}
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="app_name" class="form-label">اسم التطبيق</label>
                                        <input type="text" name="app_name" class="form-control" value="{{ old('app_name', config('app.name')) }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="app_description" class="form-label">وصف التطبيق</label>
                                        <textarea name="app_description" class="form-control" rows="3">{{ old('app_description', config('app.description')) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="app_url" class="form-label">رابط التطبيق</label>
                                        <input type="url" name="app_url" class="form-control" value="{{ old('app_url', config('app.url')) }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="app_logo" class="form-label">شعار التطبيق</label>
                                        <input type="file" name="app_logo" class="form-control">
                                        @if(config('app.logo'))
                                            <div class="mt-2">
                                                <img src="{{ asset(config('app.logo')) }}" alt="شعار التطبيق" class="img-thumbnail" style="max-height: 50px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="app_favicon" class="form-label">الأيقونة المفضلة (Favicon)</label>
                                        <input type="file" name="app_favicon" class="form-control">
                                        @if(config('app.favicon'))
                                            <div class="mt-2">
                                                <img src="{{ asset(config('app.favicon')) }}" alt="الأيقونة المفضلة" class="img-thumbnail" style="max-height: 50px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="currency_code" class="form-label">رمز العملة</label>
                                        <select name="currency_code" class="form-select">
                                            <option value="SAR" {{ old('currency_code', config('app.currency_code', 'SAR')) == 'SAR' ? 'selected' : '' }}>SAR - ريال سعودي</option>
                                            <option value="AED" {{ old('currency_code', config('app.currency_code', 'SAR')) == 'AED' ? 'selected' : '' }}>AED - درهم إماراتي</option>
                                            <option value="USD" {{ old('currency_code', config('app.currency_code', 'SAR')) == 'USD' ? 'selected' : '' }}>USD - دولار أمريكي</option>
                                            <option value="EUR" {{ old('currency_code', config('app.currency_code', 'SAR')) == 'EUR' ? 'selected' : '' }}>EUR - يورو</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="currency_symbol" class="form-label">رمز العملة</label>
                                        <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', config('app.currency_symbol', 'ر.س')) }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="currency_position" class="form-label">موضع رمز العملة</label>
                                        <select name="currency_position" class="form-select">
                                            <option value="left" {{ old('currency_position', config('app.currency_position', 'left')) == 'left' ? 'selected' : '' }}>يسار</option>
                                            <option value="right" {{ old('currency_position', config('app.currency_position', 'left')) == 'right' ? 'selected' : '' }}>يمين</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="date_format" class="form-label">صيغة التاريخ</label>
                                        <select name="date_format" class="form-select">
                                            <option value="Y-m-d" {{ old('date_format', config('app.date_format', 'Y-m-d')) == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                            <option value="d-m-Y" {{ old('date_format', config('app.date_format', 'Y-m-d')) == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY</option>
                                            <option value="m-d-Y" {{ old('date_format', config('app.date_format', 'Y-m-d')) == 'm-d-Y' ? 'selected' : '' }}>MM-DD-YYYY</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="time_format" class="form-label">صيغة الوقت</label>
                                        <select name="time_format" class="form-select">
                                            <option value="H:i" {{ old('time_format', config('app.time_format', 'H:i')) == 'H:i' ? 'selected' : '' }}>24 ساعة</option>
                                            <option value="h:i A" {{ old('time_format', config('app.time_format', 'H:i')) == 'h:i A' ? 'selected' : '' }}>12 ساعة</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="timezone" class="form-label">المنطقة الزمنية</label>
                                        <select name="timezone" class="form-select">
                                            <option value="UTC" {{ old('timezone', config('app.timezone', 'UTC')) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                            <option value="Asia/Riyadh" {{ old('timezone', config('app.timezone', 'UTC')) == 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh (السعودية)</option>
                                            <option value="Asia/Dubai" {{ old('timezone', config('app.timezone', 'UTC')) == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (الإمارات)</option>
                                            <option value="Europe/London" {{ old('timezone', config('app.timezone', 'UTC')) == 'Europe/London' ? 'selected' : '' }}>Europe/London (المملكة المتحدة)</option>
                                            <option value="America/New_York" {{ old('timezone', config('app.timezone', 'UTC')) == 'America/New_York' ? 'selected' : '' }}>America/New_York (الولايات المتحدة)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="language" class="form-label">اللغة</label>
                                        <select name="language" class="form-select">
                                            <option value="ar" {{ old('language', config('app.locale', 'ar')) == 'ar' ? 'selected' : '' }}>العربية</option>
                                            <option value="en" {{ old('language', config('app.locale', 'ar')) == 'en' ? 'selected' : '' }}>English</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="admin_email" class="form-label">البريد الإلكتروني للمسؤول</label>
                                        <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email', config('app.admin_email')) }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="contact_email" class="form-label">بريد الاتصال</label>
                                        <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', config('app.contact_email')) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="contact_phone" class="form-label">هاتف الاتصال</label>
                                        <input type="tel" name="contact_phone" class="form-control" value="{{ old('contact_phone', config('app.contact_phone')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="contact_address" class="form-label">عنوان الاتصال</label>
                                        <textarea name="contact_address" class="form-control" rows="1">{{ old('contact_address', config('app.contact_address')) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Settings --}}
                        <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="default_payment_method" class="form-label">طريقة الدفع الافتراضية</label>
                                        <select name="default_payment_method" class="form-select">
                                            <option value="stripe" {{ old('default_payment_method', config('app.default_payment_method', 'stripe')) == 'stripe' ? 'selected' : '' }}>Stripe</option>
                                            <option value="paypal" {{ old('default_payment_method', config('app.default_payment_method', 'stripe')) == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                            <option value="fawry" {{ old('default_payment_method', config('app.default_payment_method', 'stripe')) == 'fawry' ? 'selected' : '' }}>Fawry</option>
                                            <option value="bank_transfer" {{ old('default_payment_method', config('app.default_payment_method', 'stripe')) == 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="default_commission_rate" class="form-label">نسبة العمولة الافتراضية (%)</label>
                                        <input type="number" name="default_commission_rate" class="form-control" value="{{ old('default_commission_rate', config('app.default_commission_rate', 10)) }}" min="0" max="100" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">إعدادات Stripe</h5>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="stripe_publishable_key" class="form-label">مفتاح Stripe القابل للنشر</label>
                                        <input type="text" name="stripe_publishable_key" class="form-control" value="{{ old('stripe_publishable_key', config('services.stripe.key')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="stripe_secret_key" class="form-label">مفتاح Stripe السري</label>
                                        <input type="text" name="stripe_secret_key" class="form-control" value="{{ old('stripe_secret_key', config('services.stripe.secret')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">إعدادات PayPal</h5>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="paypal_client_id" class="form-label">معرّف العميل الخاص بـ PayPal</label>
                                        <input type="text" name="paypal_client_id" class="form-control" value="{{ old('paypal_client_id', config('services.paypal.client_id')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="paypal_client_secret" class="form-label">مفتاح العميل السري الخاص بـ PayPal</label>
                                        <input type="text" name="paypal_client_secret" class="form-control" value="{{ old('paypal_client_secret', config('services.paypal.client_secret')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">إعدادات Fawry</h5>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="fawry_merchant_code" class="form-label">رمت التاجر الخاص بـ Fawry</label>
                                        <input type="text" name="fawry_merchant_code" class="form-control" value="{{ old('fawry_merchant_code', config('services.fawry.merchant_code')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="fawry_security_key" class="form-label">مفتاح الأمان الخاص بـ Fawry</label>
                                        <input type="text" name="fawry_security_key" class="form-control" value="{{ old('fawry_security_key', config('services.fawry.security_key')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="fawry_integration_id" class="form-label">معرّف التكامل الخاص بـ Fawry</label>
                                        <input type="text" name="fawry_integration_id" class="form-control" value="{{ old('fawry_integration_id', config('services.fawry.integration_id')) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Shipping Settings --}}
                        <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="default_shipping_method" class="form-label">طريقة الشحن الافتراضية</label>
                                        <select name="default_shipping_method" class="form-select">
                                            <option value="standard" {{ old('default_shipping_method', config('app.default_shipping_method', 'standard')) == 'standard' ? 'selected' : '' }}>شحن قياسي</option>
                                            <option value="express" {{ old('default_shipping_method', config('app.default_shipping_method', 'standard')) == 'express' ? 'selected' : '' }}>شحن سريع</option>
                                            <option value="pickup" {{ old('default_shipping_method', config('app.default_shipping_method', 'standard')) == 'pickup' ? 'selected' : '' }}>استلام من الفرع</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="default_shipping_cost" class="form-label">تكلفة الشحن الافتراضية</label>
                                        <input type="number" name="default_shipping_cost" class="form-control" value="{{ old('default_shipping_cost', config('app.default_shipping_cost', 15)) }}" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="free_shipping_threshold" class="form-label">عتبة الشحن المجاني</label>
                                        <input type="number" name="free_shipping_threshold" class="form-control" value="{{ old('free_shipping_threshold', config('app.free_shipping_threshold', 200)) }}" min="0" step="0.01">
                                        <small class="form-text text-muted">يتم الشحن مجانًا إذا كان إجمالي الطلب أكبر من هذا المبلغ</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="default_shipping_days" class="form-label">أيام الشحن الافتراضية</label>
                                        <input type="number" name="default_shipping_days" class="form-control" value="{{ old('default_shipping_days', config('app.default_shipping_days', 3)) }}" min="1">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-3">مناطق الشحن المتاحة</h5>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="shipping_regions" class="form-label">المناطق</label>
                                        <textarea name="shipping_regions" class="form-control" rows="5">{{ old('shipping_regions', config('app.shipping_regions', implode("
", [
                                            "الرياض",
                                            "جدة",
                                            "الدمام",
                                            "الخبر",
                                            "الطائف",
                                            "تبوك",
                                            "مكة المكرمة",
                                            "المدينة المنورة",
                                            "حائل",
                                            "نجران",
                                            "جازان",
                                            "الجوف",
                                            "الباحة",
                                            "عسير",
                                            "الحدود الشمالية",
                                            "القصيم"
                                        ]))) }}</textarea>
                                        <small class="form-text text-muted">اكتب منطقة في كل سطر</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Email Settings --}}
                        <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="smtp_host" class="form-label">مضيف SMTP</label>
                                        <input type="text" name="smtp_host" class="form-control" value="{{ old('smtp_host', config('mail.mailers.smtp.host')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="smtp_port" class="form-label">منفذ SMTP</label>
                                        <input type="number" name="smtp_port" class="form-control" value="{{ old('smtp_port', config('mail.mailers.smtp.port')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="smtp_username" class="form-label">اسم مستخدم SMTP</label>
                                        <input type="text" name="smtp_username" class="form-control" value="{{ old('smtp_username', config('mail.mailers.smtp.username')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="smtp_password" class="form-label">كلمة مرور SMTP</label>
                                        <input type="password" name="smtp_password" class="form-control" value="{{ old('smtp_password', config('mail.mailers.smtp.password')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="smtp_encryption" class="form-label">تشفير SMTP</label>
                                        <select name="smtp_encryption" class="form-select">
                                            <option value="tls" {{ old('smtp_encryption', config('mail.mailers.smtp.encryption', 'tls')) == 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ old('smtp_encryption', config('mail.mailers.smtp.encryption', 'tls')) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                            <option value="none" {{ old('smtp_encryption', config('mail.mailers.smtp.encryption', 'tls')) == 'none' ? 'selected' : '' }}>لا يوجد</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="from_name" class="form-label">الاسم من</label>
                                        <input type="text" name="from_name" class="form-control" value="{{ old('from_name', config('mail.from.name')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="from_address" class="form-label">البريد من</label>
                                        <input type="email" name="from_address" class="form-control" value="{{ old('from_address', config('mail.from.address')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="enable_queue" id="enable_queue" {{ old('enable_queue', config('app.enable_email_queue', true)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_queue">
                                            تمكين قائمة انتظار البريد الإلكتروني
                                        </label>
                                        <small class="form-text text-muted">عند التمكين، سيتم إرسال رسائل البريد الإلكتروني بشكل غير متزامن</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Social Settings --}}
                        <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="facebook_url" class="form-label">رابط فيسبوك</label>
                                        <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', config('app.facebook_url')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="twitter_url" class="form-label">رابط تويتر</label>
                                        <input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', config('app.twitter_url')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="instagram_url" class="form-label">رابط انستجرام</label>
                                        <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', config('app.instagram_url')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="youtube_url" class="form-label">رابط يوتيوب</label>
                                        <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', config('app.youtube_url')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="linkedin_url" class="form-label">رابط لينكدإن</label>
                                        <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', config('app.linkedin_url')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tiktok_url" class="form-label">رابط تيك توك</label>
                                        <input type="url" name="tiktok_url" class="form-control" value="{{ old('tiktok_url', config('app.tiktok_url')) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SEO Settings --}}
                        <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="seo_meta_title" class="form-label">عنوان Meta الافتراضي</label>
                                        <input type="text" name="seo_meta_title" class="form-control" value="{{ old('seo_meta_title', config('app.seo_meta_title')) }}">
                                        <small class="form-text text-muted">سيتم استخدامه كعنوان افتراضي لصفحات الموقع</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="seo_meta_description" class="form-label">وصف Meta الافتراضي</label>
                                        <textarea name="seo_meta_description" class="form-control" rows="3">{{ old('seo_meta_description', config('app.seo_meta_description')) }}</textarea>
                                        <small class="form-text text-muted">سيتم استخدامه كوصف افتراضي لصفحات الموقع</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="seo_meta_keywords" class="form-label">كلمات مفتاحية Meta الافتراضية</label>
                                        <input type="text" name="seo_meta_keywords" class="form-control" value="{{ old('seo_meta_keywords', config('app.seo_meta_keywords')) }}">
                                        <small class="form-text text-muted">افصل الكلمات المفتاحية بفاصلة</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="seo_og_title" class="form-label">عنوان Open Graph الافتراضي</label>
                                        <input type="text" name="seo_og_title" class="form-control" value="{{ old('seo_og_title', config('app.seo_og_title')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="seo_og_description" class="form-label">وصف Open Graph الافتراضي</label>
                                        <textarea name="seo_og_description" class="form-control" rows="3">{{ old('seo_og_description', config('app.seo_og_description')) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="seo_og_image" class="form-label">صورة Open Graph الافتراضية</label>
                                        <input type="url" name="seo_og_image" class="form-control" value="{{ old('seo_og_image', config('app.seo_og_image')) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // File preview
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // You can add file preview logic here if needed
                    console.log('Selected file:', file.name);
                }
            });
        });
    });
</script>
@endsection
