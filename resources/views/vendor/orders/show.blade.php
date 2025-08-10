
@extends('vendor.layouts.app')

@section('title', 'عرض الطلب #' . $order->order_number)

@section('content')
<div class="row">
    {{-- Order Header --}}
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 font-weight-bold text-primary">عرض الطلب #{{ $order->order_number }}</h6>
                    </div>
                    <div class="col-auto">
                        <div class="dropdown">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> إجراءات
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item {{ $order->status === 'pending' ? '' : 'disabled' }}" href="{{ route('vendor.orders.update-status', $order->id) }}?status=confirmed">
                                    <i class="fas fa-check-circle me-1"></i> تأكيد الطلب
                                </a>
                                <a class="dropdown-item {{ in_array($order->status, ['pending', 'confirmed']) ? '' : 'disabled' }}" href="{{ route('vendor.orders.update-status', $order->id) }}?status=processing">
                                    <i class="fas fa-cogs me-1"></i> معالجة الطلب
                                </a>
                                <a class="dropdown-item {{ in_array($order->status, ['pending', 'confirmed', 'processing']) ? '' : 'disabled' }}" href="{{ route('vendor.orders.create-shipment', $order->id) }}">
                                    <i class="fas fa-shipping-fast me-1"></i> شحن الطلب
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item {{ $order->status === 'pending' ? '' : 'disabled' }}" href="{{ route('vendor.orders.update-status', $order->id) }}?status=cancelled">
                                    <i class="fas fa-times-circle me-1"></i> إلغاء الطلب
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">معلومات العميل</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="30%">الاسم:</th>
                                <td>{{ $order->customer_name }}</td>
                            </tr>
                            <tr>
                                <th>البريد الإلكتروني:</th>
                                <td>{{ $order->customer_email }}</td>
                            </tr>
                            <tr>
                                <th>رقم الهاتف:</th>
                                <td>{{ $order->customer_phone }}</td>
                            </tr>
                            <tr>
                                <th>العنوان:</th>
                                <td>
                                    {{ $order->customer_address }},<br>
                                    {{ $order->customer_city }},<br>
                                    {{ $order->customer_state }},<br>
                                    {{ $order->customer_country }} {{ $order->customer_postal_code }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">معلومات الطلب</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="30%">رقم الطلب:</th>
                                <td>#{{ $order->order_number }}</td>
                            </tr>
                            <tr>
                                <th>تاريخ الطلب:</th>
                                <td>{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>حالة الطلب:</th>
                                <td>
                                    <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : 
                                                    ($order->status === 'confirmed' ? 'info' : 
                                                    ($order->status === 'processing' ? 'primary' : 
                                                    ($order->status === 'shipped' ? 'secondary' : 
                                                    ($order->status === 'delivered' ? 'success' : 
                                                    ($order->status === 'cancelled' ? 'danger' : 'dark'))))) }}">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>حالة الدفع:</th>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status === 'pending' ? 'warning' : 
                                                    ($order->payment_status === 'paid' ? 'success' : 
                                                    ($order->payment_status === 'partially_paid' ? 'info' : 
                                                    ($order->payment_status === 'refunded' ? 'secondary' : 
                                                    ($order->payment_status === 'partially_refunded' ? 'info' : 
                                                    ($order->payment_status === 'failed' ? 'danger' : 'dark'))))) }}">
                                        {{ $order->payment_status_label }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Items --}}
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">منتجات الطلب</h6>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>الSKU</th>
                                <th>السعر</th>
                                <th>الكمية</th>
                                <th>المجموع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product->images->count() > 0)
                                            <img src="{{ asset('storage/' . $item->product->images->first()->path) }}" alt="{{ $item->product->name }}" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 50px;">
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('vendor.products.show', $item->product->id) }}" class="text-decoration-none">
                                                {{ $item->product_name }}
                                            </a>
                                            @if($item->variant)
                                                <br>
                                                <small class="text-muted">{{ $item->variant->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->product_sku }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>المجموع الفرعي:</strong></td>
                                <td>{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>تكلفة الشحن:</strong></td>
                                <td>{{ number_format($order->shipping_cost, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>الضريبة:</strong></td>
                                <td>{{ number_format($order->tax_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>الخصم:</strong></td>
                                <td>-{{ number_format($order->discount_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>المبلغ الإجمالي:</strong></td>
                                <td>{{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Shipping Information --}}
    @if($order->shipments->count() > 0)
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">معلومات الشحن</h6>
            </div>

            <div class="card-body">
                @foreach($order->shipments as $shipment)
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">تفاصيل الشحنة</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">رقم التتبع:</th>
                                    <td>{{ $shipment->tracking_number }}</td>
                                </tr>
                                <tr>
                                    <th>شركة الشحن:</th>
                                    <td>{{ $shipment->carrier }}</td>
                                </tr>
                                <tr>
                                    <th>طريقة الشحن:</th>
                                    <td>{{ $shipment->shipping_method }}</td>
                                </tr>
                                <tr>
                                    <th>حالة الشحنة:</th>
                                    <td>
                                        <span class="badge bg-{{ $shipment->status === 'pending' ? 'warning' : 
                                                        ($shipment->status === 'picked' ? 'info' : 
                                                        ($shipment->status === 'in_transit' ? 'primary' : 
                                                        ($shipment->status === 'out_for_delivery' ? 'secondary' : 
                                                        ($shipment->status === 'delivered' ? 'success' : 
                                                        ($shipment->status === 'exception' ? 'danger' : 'dark'))))) }}">
                                            {{ $shipment->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>تاريخ الشحن:</th>
                                    <td>{{ $shipment->shipped_at ? $shipment->shipped_at->format('d/m/Y H:i:s') : '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">تتبع الشحنة</h6>
                            <div class="timeline">
                                @foreach($shipment->trackingEvents()->orderBy('event_date', 'desc')->orderBy('event_time', 'desc')->get() as $event)
                                <div class="timeline-item">
                                    <div class="timeline-point">
                                        <div class="timeline-point-icon bg-primary"></div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-content-title">{{ $event->event_description }}</div>
                                        <div class="timeline-content-date">{{ $event->event_date->format('d/m/Y') }} {{ $event->event_time ? $event->event_time->format('H:i') : '' }}</div>
                                        @if($event->event_location)
                                            <div class="timeline-content-subtitle">{{ $event->event_location }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Payment Information --}}
    @if($order->payments->count() > 0)
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">معلومات الدفع</h6>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>طريقة الدفع</th>
                                <th>حالة الدفع</th>
                                <th>المبلغ</th>
                                <th>تاريخ الدفع</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_method }}</td>
                                <td>
                                    <span class="badge bg-{{ $payment->payment_status === 'pending' ? 'warning' : 
                                                    ($payment->payment_status === 'paid' ? 'success' : 
                                                    ($payment->payment_status === 'partially_paid' ? 'info' : 
                                                    ($payment->payment_status === 'refunded' ? 'secondary' : 
                                                    ($payment->payment_status === 'partially_refunded' ? 'info' : 
                                                    ($payment->payment_status === 'failed' ? 'danger' : 'dark'))))) }}">
                                        {{ $payment->payment_status_label }}
                                    </span>
                                </td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $payment->notes }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Order Notes --}}
    @if($order->notes)
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">ملاحظات الطلب</h6>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    {{ $order->notes }}
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
