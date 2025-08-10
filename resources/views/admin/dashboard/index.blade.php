@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="row">
    {{-- Statistics Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            المستخدمون الإجماليون
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $totalUsers }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            البائعون الإجماليون
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $totalVendors }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-store fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            المنتجات الإجمالية
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $totalProducts }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            الطلبات قيد الانتظار
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $pendingOrders }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Revenue Chart --}}
    <div class="col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">إيرادات آخر 30 يومًا</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Chart --}}
    <div class="col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">مبيعات آخر 30 يومًا</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Pending Vendors --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">البائعون قيد الانتظار</h6>
                <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="{{ route('admin.vendors.index') }}">
                    عرض الكل <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <div class="card-body">
                @forelse($recentVendors as $vendor)
                <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-store me-2"></i>
                        <div>
                            <strong>{{ $vendor->name }}</strong>
                            <span class="badge bg-warning rounded-pill ms-2">قيد الانتظار</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.vendors.change-status', $vendor->id) }}?status=active" class="btn btn-sm btn-success">
                            <i class="fas fa-check"></i> الموافقة
                        </a>
                        <a href="{{ route('admin.vendors.change-status', $vendor->id) }}?status=suspended" class="btn btn-sm btn-danger">
                            <i class="fas fa-times"></i> رفض
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">لا توجد بائعون قيد الانتظار</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">الطلبات الأخيرة</h6>
                <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="{{ route('admin.orders.index') }}">
                    عرض الكل <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @forelse($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">#{{ $order->order_number }}</h6>
                            <small class="text-muted">{{ $order->created_at->format('d/m/Y') }}</small>
                        </div>
                        <p class="mb-1">{{ $order->customer_name }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $order->status_label }}</small>
                            <span class="badge bg-primary rounded-pill">{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد طلبات</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Top Vendors --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أفضل البائعين من حيث المبيعات</h6>
            </div>
            <div class="card-body">
                @forelse($topVendors as $vendor)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <span class="text-white fw-bold">{{ $vendor->name|first }}</span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $vendor->name }}</h6>
                            <small class="text-muted">{{ $vendor->orders_count }} طلب</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">{{ number_format($vendor->orders_sum_total_amount, 2) }}</div>
                        <small class="text-muted">إجمالي المبيعات</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <p class="text-muted">لا توجد بيانات</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أفضل المنتجات من حيث المبيعات</h6>
            </div>
            <div class="card-body">
                @forelse($topProducts as $product)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        @if($product->images->count() > 0)
                            <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}" class="img-thumbnail me-3" style="width: 40px; height: 40px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fas fa-image fa-2x text-muted"></i>
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0">{{ $product->name }}</h6>
                            <small class="text-muted">{{ $product->orders_count }} طلب</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">{{ $product->orders_sum_quantity }}</div>
                        <small class="text-muted">الكمية المباعة</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <p class="text-muted">لا توجد بيانات</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');

        const revenueData = @json($revenueData);

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(item => item.date),
                datasets: [{
                    label: 'الإيرادات',
                    data: revenueData.map(item => item.total),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '{{ config("app.currency_symbol", "ر.س") }}' + value;
                            }
                        }
                    }
                }
            }
        });

        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');

        const salesData = @json($salesData);

        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: salesData.map(item => item.date),
                datasets: [{
                    label: 'عدد الطلبات',
                    data: salesData.map(item => item.count),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
