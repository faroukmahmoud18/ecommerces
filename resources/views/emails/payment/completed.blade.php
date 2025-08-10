@extends('emails.layout')

@section('title', 'تأكيد الدفع')

@section('content')
<tr>
    <td style="padding: 40px 30px;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="background: #28a745; width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                <span style="color: white; font-size: 40px;">✓</span>
            </div>
            <h1 style="color: #2c3e50; margin: 0; font-size: 28px;">تم تأكيد الدفع بنجاح!</h1>
            <p style="color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;">
                شكراً لك {{ $user->name }} على إتمام عملية الدفع
            </p>
        </div>

        <!-- Payment Details -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h2 style="color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;">تفاصيل الدفع</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #7f8c8d;">رقم العملية:</td>
                    <td style="padding: 8px 0; font-weight: bold;">{{ $payment->transaction_id }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #7f8c8d;">رقم الطلب:</td>
                    <td style="padding: 8px 0; font-weight: bold;">{{ $payment->order->order_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #7f8c8d;">طريقة الدفع:</td>
                    <td style="padding: 8px 0;">{{ ucfirst($payment->payment_method) }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #7f8c8d;">المبلغ المدفوع:</td>
                    <td style="padding: 8px 0; font-weight: bold; color: #28a745;">{{ number_format($payment->amount, 2) }} ر.س</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #7f8c8d;">تاريخ الدفع:</td>
                    <td style="padding: 8px 0;">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <!-- Order Summary -->
        <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="color: #2c3e50; margin: 0 0 15px 0;">ملخص الطلب</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px 0; color: #7f8c8d;">المجموع الفرعي:</td>
                    <td style="padding: 5px 0; text-align: left;">{{ number_format($payment->order->subtotal, 2) }} ر.س</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #7f8c8d;">الشحن:</td>
                    <td style="padding: 5px 0; text-align: left;">{{ number_format($payment->order->shipping_cost, 2) }} ر.س</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #7f8c8d;">الضريبة:</td>
                    <td style="padding: 5px 0; text-align: left;">{{ number_format($payment->order->tax_amount, 2) }} ر.س</td>
                </tr>
                <tr style="border-top: 2px solid #28a745;">
                    <td style="padding: 10px 0 5px; font-weight: bold; color: #2c3e50;">الإجمالي:</td>
                    <td style="padding: 10px 0 5px; text-align: left; font-weight: bold; color: #28a745;">{{ number_format($payment->order->total_amount, 2) }} ر.س</td>
                </tr>
            </table>
        </div>

        <!-- Receipt Information -->
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <h4 style="color: #856404; margin: 0 0 10px 0; font-size: 16px;">معلومات الإيصال</h4>
            <p style="margin: 0; color: #856404; font-size: 14px;">
                يمكنك استخدام هذا البريد الإلكتروني كإيصال للدفع. 
                احتفظ به للمراجعة أو لأغراض المحاسبة.
            </p>
        </div>

        <!-- Next Steps -->
        <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="color: #1976d2; margin: 0 0 15px 0;">ماذا بعد؟</h3>
            <ul style="margin: 0; padding-right: 20px; color: #555;">
                <li style="margin-bottom: 8px;">تم تأكيد طلبك وسيتم تحضيره للشحن</li>
                <li style="margin-bottom: 8px;">ستصلك رسالة عند شحن الطلب مع رقم التتبع</li>
                <li style="margin-bottom: 8px;">يمكنك تتبع حالة طلبك من حسابك</li>
                <li>في حالة وجود أي مشكلة، تواصل معنا فوراً</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/orders/' . $payment->order->id) }}" 
               style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold; margin: 0 10px;">
                تتبع الطلب
            </a>
            <a href="{{ url('/') }}" 
               style="background: #6c757d; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold; margin: 0 10px;">
                متابعة التسوق
            </a>
        </div>

        <!-- Security Notice -->
        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-right: 4px solid #28a745;">
            <h4 style="color: #28a745; margin: 0 0 10px 0; font-size: 16px;">
                🔒 معاملة آمنة
            </h4>
            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                تمت معالجة دفعتك بأمان عبر نظام الدفع المشفر. 
                جميع معلوماتك المالية محمية ولا نحتفظ بتفاصيل بطاقتك الائتمانية.
            </p>
        </div>

        <!-- Support Info -->
        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center; margin-top: 20px;">
            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                لديك استفسار حول الدفع؟ تواصل معنا على 
                <a href="mailto:billing@example.com" style="color: #007bff;">billing@example.com</a>
                أو اتصل بنا على +966 50 123 4567
            </p>
        </div>
    </td>
</tr>
@endsection