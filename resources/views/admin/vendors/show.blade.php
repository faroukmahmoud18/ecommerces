
@extends('admin.layouts.app')

@section('title', 'عرض بائع #' . $vendor->id)

@section('content')
<div class="row">
    {{-- Vendor Header --}}
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 font-weight-bold text-primary">عرض بائع: {{ $vendor->name }}</h6>
                    </div>
                    <div class="col-auto">
                        <div class="dropdown">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> إجراءات
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item {{ $vendor->status === 'active' ? 'disabled' : '' }}" href="{{ route('admin.vendors.change-status', $vendor->id) }}?status=active">
                                    <i class="fas fa-check-circle me-1"></i> تفعيل
                                </a>
                                <a class="dropdown-item {{ $vendor->status === 'suspended' ? 'disabled' : '' }}" href="{{ route('admin.vendors.change-status', $vendor->id) }}?status=suspended">
                                    <i class="fas fa-pause-circle me-1"></i> تعليق
                                </a>
                                <a class="dropdown-item {{ $vendor->status === 'pending' ? 'disabled' : '' }}" href="{{ route('admin.vendors.change-status', $vendor->id) }}?status=pending">
                                    <i class="fas fa-clock me-1"></i> وضع قيد الانتظار
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('admin.vendors.edit', $vendor->id) }}">
                                    <i class="fas fa-edit me-1"></i> تعديل
                                </a>
                                <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذا البائع؟')">
                                        <i class="fas fa-trash me-1"></i> حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            @if($vendor->user->avatar)
                                <img src="{{ asset('storage/' . $vendor->user->avatar) }}" alt="{{ $vendor->name }}" class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                                    <span class="text-white fw-bold" style="font-size: 60px;">{{ $vendor->name|first }}</span>
                                </div>
                            @endif

                            <span class="badge bg-{{ $vendor->status === 'pending' ? 'warning' : 
                                            ($vendor->status === 'active' ? 'success' : 'danger') }} fs-6">
                                {{ $vendor->status === 'pending' ? 'قيد الانتظار' : 
                                  ($vendor->status === 'active' ? 'نشط' : 'معلق') }}
                            </span>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <h4 class="text-primary mb-3">{{ $vendor->name }}</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">معلومات الحساب</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="30%">المستخدم:</th>
                                        <td>{{ $vendor->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>البريد الإلكتروني:</th>
                                        <td>{{ $vendor->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>حالة الحساب:</th>
                                        <td>
                                            <span class="badge bg-{{ $vendor->status === 'pending' ? 'warning' : 
                                                            ($vendor->status === 'active' ? 'success' : 'danger') }}">
                                                {{ $vendor->status === 'pending' ? 'قيد الانتظار' : 
                                                  ($vendor->status === 'active' ? 'نشط' : 'معلق') }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الإنشاء:</th>
                                        <td>{{ $vendor->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">معلومات المتجر</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="30%">اسم المتجر:</th>
                                        <td>{{ $vendor->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>نسبة العمولة:</th>
                                        <td>{{ $vendor->commission_rate }}%</td>
                                    </tr>
                                    <tr>
                                        <th>رصيد المحفظة:</th>
                                        <td>{{ number_format($vendor->wallet_balance, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>وصف المتجر:</th>
                                        <td>{{ $vendor->bio ?? 'لا يوجد وصف' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="bg-light rounded p-3 text-center">
                                    <h3 class="text-primary mb-1">{{ $totalProducts }}</h3>
                                    <p class="mb-0">منتجات</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded p-3 text-center">
                                    <h3 class="text-success mb-1">{{ $totalOrders }}</h3>
                                    <p class="mb-0">طلبات</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded p-3 text-center">
                                    <h3 class="text-info mb-1">{{ number_format($totalRevenue, 2) }}</h3>
                                    <p class="mb-0">إجمالي المبيعات</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vendor Orders --}}
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أحدث الطلبات</h6>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
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
                            @forelse($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-decoration-none">
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
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد طلبات لهذا البائع</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Vendor Products --}}
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أحدث المنتجات</h6>
            </div>

            <div class="card-body">
                <div class="row">
                    @forelse($vendor->products()->latest()->take(6)->get() as $product)
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100">
                            @if($product->images->count() > 0)
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text text-muted">{{ Str::limit($product->short_description, 60) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bold">{{ number_format($product->price, 2) }}</span>
                                    <span class="badge bg-{{ $product->is_active ? 'success' : 'danger' }}">
                                        {{ $product->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-primary w-100">
                                    <i class="fas fa-eye"></i> عرض
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد منتجات لهذا البائع</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
