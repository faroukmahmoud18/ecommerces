
@extends('admin.layouts.app')

@section('title', 'التقارير')

@section('content')
<div class="row">
    {{-- Report Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي المبيعات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($salesReport['data']['totals']['total_sales'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($salesReport['data']['totals']['total_orders']) }}</div>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">عدد العملاء</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($customerReport['data']['customer_stats']->total_customers) }}</div>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($salesReport['data']['totals']['average_order_value'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
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
                <h6 class="m-0 font-weight-bold text-primary">مبيعات {{ $salesReport['data']['period'] }}</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">فترة التقرير:</div>
                        <a class="dropdown-item" href="{{ route('admin.reports.index', ['period' => 'day']) }}">يومي</a>
                        <a class="dropdown-item" href="{{ route('admin.reports.index', ['period' => 'week']) }}">أسبوعي</a>
                        <a class="dropdown-item" href="{{ route('admin.reports.index', ['period' => 'month']) }}">شهري</a>
                        <a class="dropdown-item" href="{{ route('admin.reports.index', ['period' => 'year']) }}">سنوي</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'sales']) }}">تصدير تقرير المبيعات</a>
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
                    @foreach($salesReport['data']['top_products'] as $product)
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold">{{ $product->product->name }}</div>
                        </div>
                        <div class="ms-auto me-2">
                            <span class="text-xs font-weight-bold">{{ number_format($product->total_sales, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
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
                                <th>عدد الطلبات</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesReport['data']['top_vendors'] as $vendor)
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

    {{-- Customer Demographics --}}
    <div class="col-xl-6 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">توزيع العملاء حسب الجنس</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="customerDemographicsChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> ذكور
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-danger"></i> إناث
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-secondary"></i> لم يحدد
                    </span>
                </div>
                <div class="mt-4 small">
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold">متوسط العمر</div>
                        </div>
                        <div class="ms-auto me-2">
                            <span class="text-xs font-weight-bold">{{ number_format($customerReport['data']['customer_demographics']->avg_age, 1) }} سنة</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold">إجمالي العملاء</div>
                        </div>
                        <div class="ms-auto me-2">
                            <span class="text-xs font-weight-bold">{{ number_format($customerReport['data']['customer_stats']->total_customers) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Sales by Payment Method --}}
    <div class="col-xl-6 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">المبيعات حسب طريقة الدفع</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar pt-4 pb-2">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
                <div class="mt-4 small">
                    @foreach($salesReport['data']['sales_by_payment_method'] as $paymentMethod)
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold">{{ $paymentMethod->payment_method }}</div>
                        </div>
                        <div class="ms-auto me-2">
                            <span class="text-xs font-weight-bold">{{ number_format($paymentMethod->total_sales, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Sales by Shipping Method --}}
    <div class="col-xl-6 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">المبيعات حسب طريقة الشحن</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar pt-4 pb-2">
                    <canvas id="shippingMethodChart"></canvas>
                </div>
                <div class="mt-4 small">
                    @foreach($salesReport['data']['sales_by_shipping_method'] as $shippingMethod)
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold">{{ $shippingMethod->shipping_method }}</div>
                        </div>
                        <div class="ms-auto me-2">
                            <span class="text-xs font-weight-bold">{{ number_format($shippingMethod->total_sales, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                        </div>
                    </div>
                    @endforeach
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
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($salesReport['data']['results'] as $result)
                        '{{ $result->period }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'إجمالي المبيعات',
                    data: [
                        @foreach($salesReport['data']['results'] as $result)
                            {{ number_format($result->total_sales, 2) }},
                        @endforeach
                    ],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' {{ config('app.currency_symbol', 'ر.س') }}';
                            }
                        }
                    }
                }
            }
        });

        // Top Products Chart
        const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
        const topProductsChart = new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($salesReport['data']['top_products'] as $product)
                        '{{ $product->product->name }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'الكمية',
                    data: [
                        @foreach($salesReport['data']['top_products'] as $product)
                            {{ $product->total_quantity }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'المبيعات',
                    data: [
                        @foreach($salesReport['data']['top_products'] as $product)
                            {{ number_format($product->total_sales, 2) }},
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
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Customer Demographics Chart
        const customerDemographicsCtx = document.getElementById('customerDemographicsChart').getContext('2d');
        const customerDemographicsChart = new Chart(customerDemographicsCtx, {
            type: 'doughnut',
            data: {
                labels: ['ذكور', 'إناث', 'لم يحدد'],
                datasets: [{
                    data: [
                        {{ $customerReport['data']['customer_demographics']->male }},
                        {{ $customerReport['data']['customer_demographics']->female }},
                        {{ $customerReport['data']['customer_demographics']->not_specified }}
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(201, 203, 207, 0.5)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(201, 203, 207, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: false,
                    }
                }
            }
        });

        // Payment Method Chart
        const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
        const paymentMethodChart = new Chart(paymentMethodCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($salesReport['data']['sales_by_payment_method'] as $paymentMethod)
                        '{{ $paymentMethod->payment_method }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'إجمالي المبيعات',
                    data: [
                        @foreach($salesReport['data']['sales_by_payment_method'] as $paymentMethod)
                            {{ number_format($paymentMethod->total_sales, 2) }},
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
                        display: false,
                    },
                    title: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' {{ config('app.currency_symbol', 'ر.س') }}';
                            }
                        }
                    }
                }
            }
        });

        // Shipping Method Chart
        const shippingMethodCtx = document.getElementById('shippingMethodChart').getContext('2d');
        const shippingMethodChart = new Chart(shippingMethodCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($salesReport['data']['sales_by_shipping_method'] as $shippingMethod)
                        '{{ $shippingMethod->shipping_method }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'إجمالي المبيعات',
                    data: [
                        @foreach($salesReport['data']['sales_by_shipping_method'] as $shippingMethod)
                            {{ number_format($shippingMethod->total_sales, 2) }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(255, 159, 64, 0.5)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' {{ config('app.currency_symbol', 'ر.س') }}';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
