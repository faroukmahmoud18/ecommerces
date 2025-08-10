# نظام الدفع - Multi-Vendor eCommerce

## نظرة عامة

تم تطوير نظام دفع متكامل يدعم عدة بوابات دفع محلية وعالمية مع إمكانيات متقدمة لإدارة المدفوعات والمردودات.

## بوابات الدفع المدعومة

### 1. Stripe
- **الوصف**: بوابة دفع عالمية تدعم البطاقات الائتمانية
- **العملات المدعومة**: جميع العملات الرئيسية
- **الميزات**: 
  - دفع فوري
  - استرداد كامل أو جزئي
  - Webhook للتحديثات الفورية
  - دعم 3D Secure

### 2. PayPal
- **الوصف**: منصة دفع عالمية شهيرة
- **العملات المدعومة**: 100+ عملة
- **الميزات**:
  - دفع عبر حساب PayPal
  - دفع بالبطاقة الائتمانية
  - استرداد سريع
  - حماية المشترين

### 3. فوري (Fawry)
- **الوصف**: بوابة دفع مصرية محلية
- **العملات المدعومة**: الجنيه المصري
- **الميزات**:
  - دفع عبر ماكينات فوري
  - دفع عبر البطاقة
  - دفع عبر المحفظة الإلكترونية

### 4. Paymob
- **الوصف**: بوابة دفع إقليمية للشرق الأوسط
- **العملات المدعومة**: EGP, SAR, AED, KWD
- **الميزات**:
  - دعم البطاقات المحلية
  - دفع بالتقسيط
  - محفظة إلكترونية

## التثبيت والإعداد

### 1. تثبيت المتطلبات

```bash
composer install
npm install
```

### 2. إعداد متغيرات البيئة

انسخ ملف `.env.example` إلى `.env` وقم بتحديث المتغيرات التالية:

```env
# Stripe
STRIPE_KEY=pk_test_your_stripe_publishable_key
STRIPE_SECRET=sk_test_your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# PayPal
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret

# Fawry
FAWRY_MERCHANT_CODE=your_merchant_code
FAWRY_SECURITY_KEY=your_security_key
FAWRY_INTEGRATION_ID=your_integration_id

# Paymob
PAYMOB_API_KEY=your_paymob_api_key
PAYMOB_IFRAME_ID=your_iframe_id
PAYMOB_INTEGRATION_ID=your_integration_id
```

### 3. تشغيل Migration

```bash
php artisan migrate
```

### 4. إعداد Webhooks

#### Stripe Webhooks
- URL: `https://yourdomain.com/webhooks/stripe`
- الأحداث: `payment_intent.succeeded`, `payment_intent.payment_failed`

#### PayPal Webhooks
- URL: `https://yourdomain.com/webhooks/paypal`
- الأحداث: `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.DENIED`

#### Fawry Webhooks
- URL: `https://yourdomain.com/webhooks/fawry`

## استخدام النظام

### 1. معالجة الدفع

```php
use App\Services\PaymentService;

$paymentService = new PaymentService();

$result = $paymentService->processPayment($order, [
    'payment_method' => 'stripe',
    'payment_method_id' => 'pm_card_visa', // للبطاقات
    'gateway_response' => null,
]);

if ($result['success']) {
    // تم الدفع بنجاح
    echo "تم الدفع بنجاح: " . $result['transaction_id'];
} else {
    // فشل الدفع
    echo "فشل الدفع: " . $result['message'];
}
```

### 2. معالجة الاسترداد

```php
$payment = Payment::find(1);

$result = $paymentService->refundPayment($payment, 100.00, 'عميل غير راضي');

if ($result['success']) {
    echo "تم الاسترداد بنجاح";
}
```

### 3. التحقق من بوابات الدفع المتاحة

```php
$gateways = $paymentService->getAvailableGateways();

foreach ($gateways as $gateway) {
    echo $gateway['name'] . ' - ' . ($gateway['enabled'] ? 'متاح' : 'غير متاح');
}
```

## هيكل قاعدة البيانات

### جدول payments

| العمود | النوع | الوصف |
|--------|-------|--------|
| id | bigint | المعرف الأساسي |
| order_id | bigint | معرف الطلب |
| vendor_id | bigint | معرف المورد |
| amount | decimal(12,2) | المبلغ |
| fee | decimal(10,2) | الرسوم |
| currency | varchar(3) | العملة |
| gateway | varchar(50) | بوابة الدفع |
| payment_method | varchar(50) | طريقة الدفع |
| payment_status | varchar(50) | حالة الدفع |
| transaction_id | varchar(255) | معرف المعاملة |
| gateway_response | json | رد بوابة الدفع |
| refunded_amount | decimal(12,2) | المبلغ المسترد |
| failure_reason | text | سبب الفشل |

### حالات الدفع

- `pending`: في انتظار الدفع
- `paid`: تم الدفع
- `failed`: فشل الدفع
- `refunded`: تم الاسترداد كاملاً
- `partially_refunded`: تم الاسترداد جزئياً
- `cancelled`: ملغي

## الأحداث (Events)

### PaymentCompleted
يتم تشغيله عند اكتمال الدفع بنجاح

```php
event(new PaymentCompleted($payment));
```

### PaymentFailed
يتم تشغيله عند فشل الدفع

```php
event(new PaymentFailed($payment));
```

### OrderPaid
يتم تشغيله عند دفع الطلب

```php
event(new OrderPaid($order));
```

## الأمان

### 1. التحقق من التوقيع (Webhook Verification)

جميع webhooks يتم التحقق من صحتها:

```php
if (!$this->paymentService->verifyWebhook('stripe', $headers, $payload)) {
    return response('Invalid signature', 400);
}
```

### 2. تشفير البيانات الحساسة

- جميع بيانات البطاقات يتم تشفيرها
- لا يتم حفظ معلومات البطاقة في قاعدة البيانات
- استخدام HTTPS إجباري

### 3. Rate Limiting

```php
Route::middleware(['throttle:60,1'])->group(function () {
    // Payment routes
});
```

## مراقبة الأداء

### 1. Logging

```php
Log::info('Payment completed', [
    'order_id' => $order->id,
    'payment_id' => $payment->id,
    'amount' => $payment->amount
]);
```

### 2. Metrics

- نسبة نجاح المدفوعات
- متوسط وقت المعالجة
- توزيع بوابات الدفع

## استكشاف الأخطاء

### مشاكل شائعة

#### 1. فشل Webhook
```bash
# تحقق من logs
tail -f storage/logs/laravel.log | grep webhook
```

#### 2. مشاكل الشبكة
```php
// زيادة timeout
Http::timeout(30)->post($url, $data);
```

#### 3. أخطاء البطاقة
- تحقق من صحة البيانات
- تأكد من وجود رصيد كافي
- تحقق من حالة البطاقة

## البيئة التجريبية (Testing)

### 1. Stripe Test Cards

```
4242424242424242 - Visa (نجح دائماً)
4000000000000002 - Visa (يفشل دائماً)
4000000000009995 - Visa (رصيد غير كافي)
```

### 2. PayPal Sandbox

استخدم حسابات PayPal التجريبية من Developer Dashboard

### 3. Fawry Testing

استخدم بيئة Staging المخصصة للاختبار

## الدعم والصيانة

### 1. تحديثات دورية

```bash
# تحديث dependencies
composer update
npm update
```

### 2. النسخ الاحتياطي

```bash
# نسخة احتياطية يومية لقاعدة البيانات
php artisan backup:run
```

### 3. مراقبة الأداء

- استخدام New Relic أو Datadog
- تنبيهات عند فشل الدفع
- تقارير شهرية عن الأداء

## المساهمة

1. Fork المشروع
2. إنشاء branch جديد (`git checkout -b feature/payment-gateway`)
3. Commit التغييرات (`git commit -am 'Add payment gateway'`)
4. Push للـ branch (`git push origin feature/payment-gateway`)
5. إنشاء Pull Request

## الترخيص

هذا المشروع مرخص تحت [MIT License](LICENSE).

## التواصل

- **البريد الإلكتروني**: support@example.com
- **الهاتف**: +966 50 123 4567
- **الموقع**: https://example.com

---

**آخر تحديث**: ديسمبر 2024
**الإصدار**: 1.0.0