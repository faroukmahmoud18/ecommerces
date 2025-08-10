
@extends('vendor.layouts.app')

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
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            الطلبات الإجمالية
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $totalOrders }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                            إجمالي الإيرادات
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($totalRevenue, 2) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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

    {{-- Recent Orders --}}
    <div class="col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">الطلبات الأخيرة</h6>
                <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="{{ route('vendor.orders.index') }}">
                    عرض الكل <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @forelse ($recentOrders as $order)
                    <a href="{{ route('vendor.orders.show', $order->id) }}" class="list-group-item list-group-item-action">
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
    {{-- Low Stock Products --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">منتجات المخزون المنخفض</h6>
                <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="{{ route('vendor.products.index') }}">
                    عرض الكل <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <div class="card-body">
                @forelse ($lowStockProducts as $product)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-box-open me-2"></i>
                        <div>
                            <strong>{{ $product->name }}</strong>
                            <span class="badge bg-warning rounded-pill ms-2">{{ $product->quantity }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">جميع المنتجات لديها مخزون كافٍ</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Reviews --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">آراء العملاء الأخيرة</h6>
                <a class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" href="{{ route('vendor.reviews.index') }}">
                    عرض الكل <i class="fas fa-arrow-left"></i>
                </a>
            </div>
            <div class="card-body">
                @forelse ($recentReviews as $review)
                <div class="border-bottom mb-3 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">{{ $review->product->name }}</h6>
                        <div class="text-warning">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $review->rating)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                    <p class="mb-1">{{ $review->comment }}</p>
                    <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <p class="text-muted">لا توجد آراء بعد</p>
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
    // Revenue Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');

        const revenueData = @json($revenueData);

        new Chart(ctx, {
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
    });
</script>
@endsection
