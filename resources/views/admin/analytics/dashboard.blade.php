
@extends('admin.layouts.app')

@section('title', 'لوحة التحليلات')

@section('content')
<div class="row">
    {{-- Stats Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي الإيرادات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_revenue'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_orders']) }}</div>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_customers']) }}</div>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">متوسط قيمة الطلب</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['average_order_value'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                <h6 class="m-0 font-weight-bold text-primary">مبيعات {{ $periodLabel }}</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">فترة التقرير:</div>
                        <a class="dropdown-item" href="{{ route('admin.analytics.dashboard', ['period' => 'day']) }}">يومي</a>
                        <a class="dropdown-item" href="{{ route('admin.analytics.dashboard', ['period' => 'week']) }}">أسبوعي</a>
                        <a class="dropdown-item" href="{{ route('admin.analytics.dashboard', ['period' => 'month']) }}">شهري</a>
                        <a class="dropdown-item" href="{{ route('admin.analytics.dashboard', ['period' => 'year']) }}">سنوي</a>
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

    {{-- Top Products --}}
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">أفضل المنتجات مبيعاً</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="topProductsChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> الكمية
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> المبيعات
                    </span>
                </div>
                <div class="mt-4 small">
                    @foreach($stats['top_products'] as $product)
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold">{{ $product['product_name'] }}</div>
                        </div>
                        <div class="ms-auto me-2">
                            <span class="text-xs font-weight-bold">{{ number_format($product['total_sales'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Top Vendors --}}
    <div class="col-xl-6 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أفضل البائعين</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>البائع</th>
                                <th>إجمالي المبيعات</th>
                                <th>النسبة المئوية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['top_vendors'] as $vendor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($vendor->vendor->logo)
                                            <img src="{{ asset('storage/' . $vendor->vendor->logo) }}" alt="{{ $vendor['vendor_name'] }}" class="img-thumbnail rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                <span class="text-white fw-bold" style="font-size: 12px;">{{ $vendor['vendor_name']|first }}</span>
                                            </div>
                                        @endif
                                        <span>{{ $vendor['vendor_name'] }}</span>
                                    </div>
                                </td>
                                <td>{{ number_format($vendor['total_sales'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $stats['total_revenue'] > 0 ? ($vendor['total_sales'] / $stats['total_revenue']) * 100 : 0 }}%;" aria-valuenow="{{ $stats['total_revenue'] > 0 ? ($vendor['total_sales'] / $stats['total_revenue']) * 100 : 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($stats['total_revenue'] > 0 ? ($vendor['total_sales'] / $stats['total_revenue']) * 100 : 0, 2) }}%</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales by Category --}}
    <div class="col-xl-6 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">المبيعات حسب الفئة</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar pt-4 pb-2">
                    <canvas id="categoryChart"></canvas>
                </div>
                <div class="mt-4 small">
                    @foreach($stats['sales_by_category'] as $category)
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold">{{ $category['category_name'] }}</div>
                        </div>
                        <div class="ms-auto me-2">
                            <span class="text-xs font-weight-bold">{{ number_format($category['total_sales'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Product Metrics --}}
    <div class="col-xl-6 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">مؤشرات أداء المنتجات</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="bg-primary rounded-3 shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي المنتجات</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['product_metrics']['total_products']) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-box fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="bg-success rounded-3 shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">إجمالي المشاهدات</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['product_metrics']['total_views']) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-eye fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="bg-warning rounded-3 shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">منتجات المخزون المنخفض</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['product_metrics']['low_stock_products']) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="bg-danger rounded-3 shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">منتجات نفدت من المخزون</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['product_metrics']['out_of_stock_products']) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="m-0 font-weight-bold text-primary">أكثر المنتجات مشاهدة</h6>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>المنتج</th>
                                    <th>المشاهدات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['product_metrics']['top_viewed_products'] as $product)
                                <tr>
                                    <td>{{ $product['name'] }}</td>
                                    <td>{{ number_format($product['views']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Metrics --}}
    <div class="col-xl-6 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">مؤشرات أداء العملاء</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="bg-primary rounded-3 shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">عملاء جدد</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['new_customers']) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="bg-success rounded-3 shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">عملاء متكررون</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['returning_customers']) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="bg-info rounded-3 shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">معدل التحويل</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['conversion_rate'], 2) }}%</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="bg-warning rounded-3 shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col me-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">تكلفة اكتساب العميل</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['customer_acquisition_cost'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="bg-secondary rounded-3 shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">قيمة العميل مدى الحياة</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['customer_lifetime_value'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-heart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                    @foreach($stats['sales_trends'] as $trend)
                        '{{ $trend['date'] }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'المبيعات',
                    data: [
                        @foreach($stats['sales_trends'] as $trend)
                            {{ number_format($trend['total_sales'], 2) }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'عدد الطلبات',
                    data: [
                        @foreach($stats['sales_trends'] as $trend)
                            {{ $trend['orders_count'] }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                        text: 'مبيعات {{ $periodLabel }}'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Top Products Chart
        var topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
        var topProductsChart = new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($stats['top_products'] as $product)
                        '{{ Str::limit($product['product_name'], 15) }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'الكمية',
                    data: [
                        @foreach($stats['top_products'] as $product)
                            {{ $product['total_quantity'] }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'المبيعات',
                    data: [
                        @foreach($stats['top_products'] as $product)
                            {{ number_format($product['total_sales'], 2) }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                        text: 'أفضل المنتجات مبيعاً'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Chart
        var categoryCtx = document.getElementById('categoryChart').getContext('2d');
        var categoryChart = new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($stats['sales_by_category'] as $category)
                        '{{ $category['category_name'] }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'المبيعات',
                    data: [
                        @foreach($stats['sales_by_category'] as $category)
                            {{ number_format($category['total_sales'], 2) }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(153, 102, 255, 0.5)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                        text: 'المبيعات حسب الفئة'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endsection
