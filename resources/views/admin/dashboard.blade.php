
@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="row">
    {{-- Summary Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي المبيعات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($dashboardData['total_sales'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">إجمالي الطلبات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($dashboardData['total_orders']) }}</div>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">إجمالي العملاء</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($dashboardData['total_customers']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">إجمالي البائعين</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($dashboardData['total_vendors']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-store fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Sales Chart --}}
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">مبيعات الشهر</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">فترة التقرير:</div>
                        <a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => 'day']) }}">يومي</a>
                        <a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => 'week']) }}">أسبوعي</a>
                        <a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => 'month']) }}">شهري</a>
                        <a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => 'year']) }}">سنوي</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('admin.reports.index', ['type' => 'sales']) }}">عرض التقرير الكامل</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Breakdown --}}
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">توزيع الإيرادات</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> المبيعات
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> العمولات
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-info"></i> الضرائب
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-warning"></i> الشحن
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Recent Orders --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">الطلبات الأخيرة</h6>
                <div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">
                        عرض الكل
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboardData['recent_orders'] as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-decoration-none">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->customer->name }}</td>
                                <td>{{ number_format($order->total_amount, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : 
                                                    ($order->status === 'processing' ? 'primary' : 
                                                    ($order->status === 'shipped' ? 'info' : 
                                                    ($order->status === 'cancelled' ? 'danger' : 'warning'))) }}">
                                        {{ $order->status === 'pending' ? 'قيد الانتظار' : 
                                          ($order->status === 'processing' ? 'قيد المعالجة' : 
                                          ($order->status === 'shipped' ? 'تم الشحن' : 
                                          ($order->status === 'delivered' ? 'تم التسليم' : 
                                          ($order->status === 'cancelled' ? 'ملغى' : 'مرفوض')))) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Vendors --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">أفضل البائعين</h6>
                <div>
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-primary">
                        عرض الكل
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>البائع</th>
                                <th>إجمالي المبيعات</th>
                                <th>عدد الطلبات</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboardData['top_vendors'] as $vendor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($vendor->vendor->logo)
                                            <img src="{{ asset('storage/' . $vendor->vendor->logo) }}" alt="{{ $vendor->vendor->name }}" class="img-thumbnail rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                <span class="text-white fw-bold" style="font-size: 12px;">{{ $vendor->vendor->name|first }}</span>
                                            </div>
                                        @endif
                                        <span>{{ $vendor->vendor->name }}</span>
                                    </div>
                                </td>
                                <td>{{ number_format($vendor->total_sales, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
                                <td>{{ number_format($vendor->orders_count) }}</td>
                                <td>
                                    <a href="{{ route('admin.vendors.show', $vendor->vendor_id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Low Stock Products --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">المنتجات منخفضة المخزون</h6>
                <div>
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-primary">
                        إدارة المخزون
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>البائع</th>
                                <th>الكمية الحالية</th>
                                <th>الحد الأدنى</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboardData['low_stock_products'] as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->thumbnail)
                                            <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}" class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            </div>
                                        @endif
                                        <span>{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $product->vendor->name }}</td>
                                <td>{{ $product->quantity }}</td>
                                <td>{{ config('app.low_stock_threshold', 10) }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.inventory.edit', $product->id) }}" class="btn btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.inventory.adjust', $product->id) }}" class="btn btn-warning">
                                            <i class="fas fa-sync"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Reviews --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">المراجعات الأخيرة</h6>
                <div>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-primary">
                        إدارة المراجعات
                    </a>
                </div>
            </div>
            <div class="card-body">
                @foreach($dashboardData['recent_reviews'] as $review)
                <div class="d-flex align-items-center mb-3">
                    @if($review->user->avatar)
                        <img src="{{ asset('storage/' . $review->user->avatar) }}" alt="{{ $review->user->name }}" class="img-thumbnail rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                    @else
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <span class="text-white fw-bold" style="font-size: 12px;">{{ $review->user->name|first }}</span>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-1">{{ $review->product->name }}</h6>
                            <div class="text-warning">
                                @for($i = 0; $i < $review->rating; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="mb-1">{{ Str::limit($review->comment, 100) }}</p>
                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sales Chart
        var salesCtx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($dashboardData['sales_by_day'] as $sale)
                        '{{ $sale->day }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'المبيعات',
                    data: [
                        @foreach($dashboardData['sales_by_day'] as $sale)
                            {{ number_format($sale->total_amount, 2) }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
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
                            callback: function(value) {
                                return value + ' {{ config('app.currency_symbol', 'ر.س') }}';
                            }
                        }
                    }
                }
            }
        });

        // Revenue Chart
        var revenueCtx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: ['المبيعات', 'العمولات', 'الضرائب', 'الشحن'],
                datasets: [{
                    data: [
                        {{ number_format($dashboardData['revenue_breakdown']['sales'], 2) }},
                        {{ number_format($dashboardData['revenue_breakdown']['commissions'], 2) }},
                        {{ number_format($dashboardData['revenue_breakdown']['taxes'], 2) }},
                        {{ number_format($dashboardData['revenue_breakdown']['shipping'], 2) }}
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 206, 86, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('ar-SA', { style: 'currency', currency: 'SAR' }).format(context.raw);
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
