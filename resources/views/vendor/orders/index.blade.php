
@extends('vendor.layouts.app')

@section('title', 'الطلبات')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">إدارة الطلبات</h6>
            </div>

            <div class="card-body">
                {{-- Filters --}}
                <form method="GET" action="{{ route('vendor.orders.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" name="search" class="form-control form-control-lg border-start-0" placeholder="رقم الطلب أو اسم العميل..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>قيد المعالجة</option>
                                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>تم الشحن</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>مردود</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="payment_status" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع حالات الدفع</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                <option value="partially_paid" {{ request('payment_status') == 'partially_paid' ? 'selected' : '' }}>مدفوع جزئياً</option>
                                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>مردود</option>
                                <option value="partially_refunded" {{ request('payment_status') == 'partially_refunded' ? 'selected' : '' }}>مردود جزئياً</option>
                                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>فشل</option>
                                <option value="cancelled" {{ request('payment_status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Orders Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>تاريخ الطلب</th>
                                <th>حالة الطلب</th>
                                <th>حالة الدفع</th>
                                <th>المبلغ الإجمالي</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('vendor.orders.show', $order->id) }}" class="text-decoration-none">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $order->customer_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->customer_email }}</small>
                                    </div>
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
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
                                <td>{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
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
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد طلبات</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#dataTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
            },
            order: [[2, 'desc']]
        });
    });
</script>
@endsection
