
@extends('admin.layouts.app')

@section('title', 'إدارة التوصيات')

@section('content')
<div class="row">
    {{-- Recommendations Summary Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي التوصيات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($recommendationsSummary['total_recommendations']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-lightbulb fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">معدل التحويل</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($recommendationsSummary['conversion_rate'], 2) }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">معدل النقر</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($recommendationsSummary['click_through_rate'], 2) }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-mouse-pointer fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">متوسط القيمة</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($recommendationsSummary['average_value'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-coins fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Recommendations Performance Chart --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">أداء التوصيات</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="#" onclick="exportChart('recommendations-chart'); return false;">
                            <i class="fas fa-file-export me-1"></i> تصديق الرسم البياني
                        </a>
                        <a class="dropdown-item" href="#" onclick="exportChart('recommendations-chart', 'pdf'); return false;">
                            <i class="fas fa-file-pdf me-1"></i> تصديق كملف PDF
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="recommendationsChart" width="100%" height="40"></canvas>
            </div>
        </div>
    </div>

    {{-- Recommendations Types --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">أنواع التوصيات</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="{{ route('admin.recommendations.export') }}">
                            <i class="fas fa-file-export me-1"></i> تصديق التقارير
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.recommendations.settings') }}">
                            <i class="fas fa-cog me-1"></i> إعدادات التوصيات
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="recommendationsTypesChart" width="100%" height="40"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Top Recommended Products --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">أفضل المنتجات الموصى بها</h6>
                <div>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="{{ route('admin.recommendations.products.export') }}">
                                <i class="fas fa-file-export me-1"></i> تصديق التقارير
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.recommendations.products.refresh') }}">
                                <i class="fas fa-sync me-1"></i> تحديث التوصيات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>عدد التوصيات</th>
                                <th>معدل النقر</th>
                                <th>معدل التحويل</th>
                                <th>الإيرادات</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recommendedProducts as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->featured_image)
                                            <img src="{{ asset('storage/' . $product->featured_image) }}" alt="{{ $product->name }}" class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span>{{ $product->name }}</span>
                                            <br>
                                            <small class="text-muted">
                                                @if($product->category)
                                                    {{ $product->category->name }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format($product->recommendation_count) }}</td>
                                <td>{{ number_format($product->click_through_rate, 2) }}%</td>
                                <td>{{ number_format($product->conversion_rate, 2) }}%</td>
                                <td>{{ number_format($product->revenue, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.recommendations.products.show', $product->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.recommendations.products.edit', $product->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $recommendedProducts->links() }}
            </div>
        </div>
    </div>

    {{-- Customer Recommendations --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">توصيات العملاء</h6>
                <div>
                    <a href="{{ route('admin.recommendations.customers.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> توصية جديدة
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>العميل</th>
                                <th>التوصيات</th>
                                <th>معدل التحويل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customerRecommendations as $customer)
                            <tr>
                                <td>
                                    @if($customer->customer)
                                        <div>{{ $customer->customer->name }}</div>
                                        <small class="text-muted">{{ $customer->customer->email }}</small>
                                    @else
                                        <span class="text-muted">بدون عميل</span>
                                    @endif
                                </td>
                                <td>{{ number_format($customer->recommendation_count) }}</td>
                                <td>{{ number_format($customer->conversion_rate, 2) }}%</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.recommendations.customers.show', $customer->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.recommendations.customers.edit', $customer->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
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
</div>

<div class="row">
    {{-- Recommendations Settings --}}
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">إعدادات التوصيات</h6>
                <div>
                    <a href="{{ route('admin.recommendations.settings') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-cog"></i> إعدادات متقدمة
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">نوع التوصية الافتراضي</label>
                            <select class="form-select" id="defaultRecommendationType">
                                <option value="mixed" {{ $settings['default_type'] == 'mixed' ? 'selected' : '' }}>مختلط</option>
                                <option value="similar" {{ $settings['default_type'] == 'similar' ? 'selected' : '' }}>مشابه</option>
                                <option value="popular" {{ $settings['default_type'] == 'popular' ? 'selected' : '' }}>شائع</option>
                                <option value="new" {{ $settings['default_type'] == 'new' ? 'selected' : '' }}>جديد</option>
                                <option value="related" {{ $settings['default_type'] == 'related' ? 'selected' : '' }}>مرتبط</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">عدد التوصيات الافتراضي</label>
                            <input type="number" class="form-control" id="defaultRecommendationCount" value="{{ $settings['default_count'] }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تفعيل التوصيات</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enableRecommendations" {{ $settings['enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="enableRecommendations">
                                    {{ $settings['enabled'] ? 'مفعل' : 'معطل' }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">تحديث التوصيات</label>
                            <select class="form-select" id="recommendationUpdateFrequency">
                                <option value="manual" {{ $settings['update_frequency'] == 'manual' ? 'selected' : }}>يدوي</option>
                                <option value="daily" {{ $settings['update_frequency'] == 'daily' ? 'selected' : }}>يومي</option>
                                <option value="weekly" {{ $settings['update_frequency'] == 'weekly' ? 'selected' : }}>أسبوعي</option>
                                <option value="monthly" {{ $settings['update_frequency'] == 'monthly' ? 'selected' : }}>شهري</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الحد الأقصى للمنتجات الموصى بها</label>
                            <input type="number" class="form-control" id="maxRecommendedProducts" value="{{ $settings['max_products'] }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تفعيل التوصيات الشخصية</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enablePersonalizedRecommendations" {{ $settings['personalized'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="enablePersonalizedRecommendations">
                                    {{ $settings['personalized'] ? 'مفعل' : 'معطل' }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-primary" onclick="saveRecommendationSettings()">حفظ الإعدادات</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize Recommendations Chart
    const recommendationsCtx = document.getElementById('recommendationsChart').getContext('2d');
    const recommendationsChart = new Chart(recommendationsCtx, {
        type: 'line',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو'],
            datasets: [
                {
                    label: 'معدل التحويل',
                    data: [2.5, 3.2, 2.8, 3.5, 4.1, 4.7, 5.2],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: false,
                    yAxisID: 'y',
                },
                {
                    label: 'معدل النقر',
                    data: [5.2, 6.1, 5.8, 6.5, 7.1, 7.7, 8.2],
                    borderColor: 'rgb(255, 159, 64)',
                    tension: 0.1,
                    fill: false,
                    yAxisID: 'y1',
                },
                {
                    label: 'الإيرادات',
                    data: [1200, 1350, 1280, 1450, 1620, 1780, 1950],
                    borderColor: 'rgb(153, 102, 255)',
                    tension: 0.1,
                    fill: false,
                    yAxisID: 'y2',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'النسبة المئوية (%)'
                    },
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                    title: {
                        display: true,
                        text: 'النسبة المئوية (%)'
                    },
                },
                y2: {
                    type: 'linear',
                    display: false,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                },
            },
        },
    });

    // Initialize Recommendations Types Chart
    const recommendationsTypesCtx = document.getElementById('recommendationsTypesChart').getContext('2d');
    const recommendationsTypesChart = new Chart(recommendationsTypesCtx, {
        type: 'doughnut',
        data: {
            labels: ['مشابه', 'شائع', 'جديد', 'مرتبط'],
            datasets: [{
                data: [35, 25, 20, 20],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'نسب التوصيات'
                }
            }
        },
    });

    // Save Recommendation Settings
    function saveRecommendationSettings() {
        const settings = {
            default_type: document.getElementById('defaultRecommendationType').value,
            default_count: document.getElementById('defaultRecommendationCount').value,
            enabled: document.getElementById('enableRecommendations').checked,
            update_frequency: document.getElementById('recommendationUpdateFrequency').value,
            max_products: document.getElementById('maxRecommendedProducts').value,
            personalized: document.getElementById('enablePersonalizedRecommendations').checked,
        };

        // Send settings to server
        fetch('{{ route("admin.recommendations.settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(settings),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم حفظ الإعدادات بنجاح');
            } else {
                alert('حدث خطأ أثناء حفظ الإعدادات: ' + data.message);
            }
        })
        .catch(error => {
            alert('حدث خطأ أثناء حفظ الإعدادات: ' + error);
        });
    }

    // Export Chart
    function exportChart(chartId, format = 'png') {
        const canvas = document.getElementById(chartId);
        const url = canvas.toDataURL(`image/${format}`);
        const link = document.createElement('a');
        link.download = `chart_${chartId}_${new Date().toISOString().slice(0, 10)}.${format}`;
        link.href = url;
        link.click();
    }
</script>
@endpush
