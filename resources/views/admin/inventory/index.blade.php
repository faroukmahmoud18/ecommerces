
@extends('admin.layouts.app')

@section('title', 'إدارة المخزون')

@section('content')
<div class="row">
    {{-- Inventory Summary Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي المنتجات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($inventoryReports['total_products']) }}</div>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">قيمة المخزون</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($inventoryReports['inventory_value'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-coins fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">منتجات منخفضة المخزون</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($inventoryReports['low_stock_count']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">منتجات غير متوفرة</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($inventoryReports['out_of_stock_count']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
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
                <h6 class="m-0 font-weight-bold text-primary">المنتجات منخفضة المخزون</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="{{ route('admin.inventory.low-stock-export') }}">
                            <i class="fas fa-file-export me-1"></i> تصدير القائمة
                        </a>
                    </div>
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
                            @forelse($inventoryReports['low_stock_products'] as $product)
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
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد منتجات منخفضة المخزون</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Out of Stock Products --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">المنتجات غير المتوفرة</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="{{ route('admin.inventory.out-of-stock-export') }}">
                            <i class="fas fa-file-export me-1"></i> تصدير القائمة
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>البائع</th>
                                <th>آخر تحديث</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventoryReports['out_of_stock_products'] as $product)
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
                                <td>{{ $product->updated_at->diffForHumans() }}</td>
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
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد منتجات غير متوفرة</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Inventory Value by Category --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">قيمة المخزون حسب الفئة</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="inventoryValueChart"></canvas>
                </div>
                <div class="mt-4 small">
                    @foreach($inventoryReports['inventory_by_category'] as $category)
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold">{{ $category->name }}</div>
                        </div>
                        <div class="ms-auto me-2">
                            <span class="text-xs font-weight-bold">{{ number_format($category->total_value, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory Value by Vendor --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">قيمة المخزون حسب البائع</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar pt-4 pb-2">
                    <canvas id="inventoryVendorChart"></canvas>
                </div>
                <div class="mt-4 small">
                    @foreach($inventoryReports['inventory_by_vendor']->take(5) as $vendor)
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold">{{ $vendor->name }}</div>
                        </div>
                        <div class="ms-auto me-2">
                            <span class="text-xs font-weight-bold">{{ number_format($vendor->total_value, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Stock Alerts --}}
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">تنبيهات المخزون</h6>
                <a href="{{ route('admin.inventory.alerts.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> إنشاء تنبيه
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>نوع التنبيه</th>
                                <th>الرسالة</th>
                                <th>الأهمية</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockAlerts as $alert)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($alert->product->thumbnail)
                                            <img src="{{ asset('storage/' . $alert->product->thumbnail) }}" alt="{{ $alert->product->name }}" class="img-thumbnail me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            </div>
                                        @endif
                                        <span>{{ $alert->product->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $alert->type === 'low_stock' ? 'warning' : 'danger' }}">
                                        {{ $alert->type === 'low_stock' ? 'منخفض المخزون' : 'غير متوفر' }}
                                    </span>
                                </td>
                                <td>{{ $alert->message }}</td>
                                <td>
                                    <span class="badge bg-{{ $alert->severity === 'high' ? 'danger' : 'warning' }}">
                                        {{ $alert->severity === 'high' ? 'عالي' : 'متوسط' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $alert->resolved ? 'success' : 'secondary' }}">
                                        {{ $alert->resolved ? 'تم حله' : 'نشط' }}
                                    </span>
                                </td>
                                <td>{{ $alert->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.inventory.alerts.show', $alert->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!$alert->resolved)
                                            <a href="{{ route('admin.inventory.alerts.resolve', $alert->id) }}" class="btn btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.inventory.alerts.destroy', $alert->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا التنبيه؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد تنبيهات</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
        // Inventory Value by Category Chart
        const categoryCtx = document.getElementById('inventoryValueChart').getContext('2d');
        const inventoryValueChart = new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: [
                    @foreach($inventoryReports['inventory_by_category'] as $category)
                        '{{ $category->name }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($inventoryReports['inventory_by_category'] as $category)
                            {{ $category->total_value }},
                        @endforeach
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(199, 199, 199, 0.7)',
                        'rgba(83, 102, 255, 0.7)',
                        'rgba(40, 159, 64, 0.7)',
                        'rgba(210, 99, 132, 0.7)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(40, 159, 64, 1)',
                        'rgba(210, 99, 132, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'قيمة المخزون حسب الفئة'
                    }
                }
            }
        });

        // Inventory Value by Vendor Chart
        const vendorCtx = document.getElementById('inventoryVendorChart').getContext('2d');
        const inventoryVendorChart = new Chart(vendorCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($inventoryReports['inventory_by_vendor']->take(5) as $vendor)
                        '{{ $vendor->name }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'قيمة المخزون',
                    data: [
                        @foreach($inventoryReports['inventory_by_vendor']->take(5) as $vendor)
                            {{ $vendor->total_value }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '{{ config('app.currency_symbol', 'ر.س') }}' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'أفضل 5 بائعين من حيث قيمة المخزون'
                    }
                }
            }
        });
    });
</script>
@endsection
