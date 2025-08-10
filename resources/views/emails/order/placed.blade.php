@extends('emails.layout')

@section('title', 'تأكيد الطلب')

@section('content')
<tr>
    <td style="padding: 40px 30px;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #2c3e50; margin: 0; font-size: 28px;">تم استلام طلبك بنجاح!</h1>
            <p style="color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;">
                شكراً لك {{ $user->name }} على طلبك
            </p>
        </div>

        <!-- Order Details -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h2 style="color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;">تفاصيل الطلب</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #7f8c8d;">رقم الطلب:</td>
                    <td style="padding: 8px 0; font-weight: bold;">{{ $order->order_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #7f8c8d;">تاريخ الطلب:</td>
                    <td style="padding: 8px 0;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #7f8c8d;">إجمالي المبلغ:</td>
                    <td style="padding: 8px 0; font-weight: bold; color: #27ae60;">{{ number_format($order->total_amount, 2) }} ر.س</td>
                </tr>
            </table>
        </div>

        <!-- Order Items -->
        <div style="margin-bottom: 20px;">
            <h3 style="color: #2c3e50; margin: 0 0 15px 0;">عناصر الطلب</h3>
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #dee2e6;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">المنتج</th>
                        <th style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">الكمية</th>
                        <th style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">السعر</th>
                        <th style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">{{ $item->product_name }}</td>
                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">{{ $item->quantity }}</td>
                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">{{ number_format($item->price, 2) }} ر.س</td>
                        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">{{ number_format($item->total, 2) }} ر.س</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Next Steps -->
        <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="color: #1976d2; margin: 0 0 15px 0;">الخطوات التالية</h3>
            <ol style="margin: 0; padding-right: 20px; color: #555;">
                <li style="margin-bottom: 8px;">سيتم مراجعة طلبك والتأكد من توفر المنتجات</li>
                <li style="margin-bottom: 8px;">ستصلك رسالة تأكيد عند تحضير الطلب للشحن</li>
                <li style="margin-bottom: 8px;">سيتم شحن طلبك خلال 2-3 أيام عمل</li>
                <li>ستحصل على رقم تتبع الشحنة</li>
            </ol>
        </div>

        <!-- Action Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/orders/' . $order->id) }}" 
               style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
                تتبع الطلب
            </a>
        </div>

        <!-- Support Info -->
        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;">
            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                لديك استفسار؟ تواصل معنا على 
                <a href="mailto:support@example.com" style="color: #007bff;">support@example.com</a>
                أو اتصل بنا على +966 50 123 4567
            </p>
        </div>
    </td>
</tr>
@endsection