
@extends('admin.layouts.app')

@section('title', 'إدارة الحسابات المالية')

@section('content')
<div class="row">
    {{-- Financial Summary Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي المبيعات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($financialSummary['total_sales'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">إجمالي العمولات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($financialSummary['total_commissions'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">إجمالي الرسوم</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($financialSummary['total_fees'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">إجمالي الدفعات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($financialSummary['total_payouts'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Financial Reports --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">تقارير مالية</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="{{ route('admin.accounting.sales-report') }}">تقرير المبيعات</a>
                        <a class="dropdown-item" href="{{ route('admin.accounting.commission-report') }}">تقرير العمولات</a>
                        <a class="dropdown-item" href="{{ route('admin.accounting.payout-report') }}">تقرير الدفعات</a>
                        <a class="dropdown-item" href="{{ route('admin.accounting.balance-sheet') }}">الميزانية العمومية</a>
                        <a class="dropdown-item" href="{{ route('admin.accounting.income-statement') }}">قائمة الدخل</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('admin.accounting.export', ['type' => 'all']) }}">تصدير جميع التقارير</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="financialChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Vendors by Sales --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أفضل البائعين من حيث المبيعات</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>البائع</th>
                                <th>إجمالي المبيعات</th>
                                <th>العمولات</th>
                                <th>الدفعات</th>
                                <th>الرصيد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topVendors as $vendor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($vendor->logo)
                                            <img src="{{ asset('storage/' . $vendor->logo) }}" alt="{{ $vendor->name }}" class="img-thumbnail rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                <span class="text-white fw-bold" style="font-size: 12px;">{{ $vendor->name|first }}</span>
                                            </div>
                                        @endif
                                        <span>{{ $vendor->name }}</span>
                                    </div>
                                </td>
                                <td>{{ number_format($vendor->total_sales, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
                                <td>{{ number_format($vendor->total_commissions, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
                                <td>{{ number_format($vendor->total_payouts, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
                                <td>{{ number_format($vendor->balance, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
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
    {{-- Pending Payouts --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">الدفعات المستحقة</h6>
                <a href="{{ route('admin.payouts.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> إنشاء دفعة
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>البائع</th>
                                <th>المبلغ</th>
                                <th>طريقة الدفع</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPayouts as $payout)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($payout->vendor->logo)
                                            <img src="{{ asset('storage/' . $payout->vendor->logo) }}" alt="{{ $payout->vendor->name }}" class="img-thumbnail rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                <span class="text-white fw-bold" style="font-size: 12px;">{{ $payout->vendor->name|first }}</span>
                                            </div>
                                        @endif
                                        <span>{{ $payout->vendor->name }}</span>
                                    </div>
                                </td>
                                <td>{{ number_format($payout->amount, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
                                <td>{{ $payout->payment_method }}</td>
                                <td>
                                    <span class="badge bg-{{ $payout->status === 'pending' ? 'warning' : 
                                                    ($payout->status === 'processing' ? 'info' : 
                                                    ($payout->status === 'completed' ? 'success' : 'danger')) }}">
                                        {{ $payout->status === 'pending' ? 'قيد الانتظار' : 
                                          ($payout->status === 'processing' ? 'قيد المعالجة' : 
                                          ($payout->status === 'completed' ? 'مكتمل' : 'فشل')) }}
                                    </span>
                                </td>
                                <td>{{ $payout->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.payouts.show', $payout->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($payout->status === 'pending')
                                            <a href="{{ route('admin.payouts.process', $payout->id) }}" class="btn btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="{{ route('admin.payouts.cancel', $payout->id) }}" class="btn btn-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.payouts.export', $payout->id) }}">
                                                    <i class="fas fa-file-export me-1"></i> تصدير الدفعة
                                                </a>
                                                <form action="{{ route('admin.payouts.destroy', $payout->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الدفعة؟')">
                                                        <i class="fas fa-trash me-1"></i> حذف
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
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

    {{-- Financial Summary --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">ملخص مالي</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="small text-muted">إجمالي المبيعات</div>
                    <div class="text-xs font-weight-bold text-primary mb-1">{{ number_format($financialSummary['total_sales'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">إجمالي العمولات</div>
                    <div class="text-xs font-weight-bold text-success mb-1">{{ number_format($financialSummary['total_commissions'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">إجمالي الرسوم</div>
                    <div class="text-xs font-weight-bold text-info mb-1">{{ number_format($financialSummary['total_fees'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">إجمالي الدفعات</div>
                    <div class="text-xs font-weight-bold text-warning mb-1">{{ number_format($financialSummary['total_payouts'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">صافي الإيرادات</div>
                    <div class="text-xs font-weight-bold text-primary mb-1">{{ number_format($financialSummary['net_revenue'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">إجمالي البائعين</div>
                    <div class="text-xs font-weight-bold text-primary mb-1">{{ number_format($financialSummary['total_vendors']) }}</div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">إجمالي الطلبات</div>
                    <div class="text-xs font-weight-bold text-primary mb-1">{{ number_format($financialSummary['total_orders']) }}</div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">متوسط قيمة الطلب</div>
                    <div class="text-xs font-weight-bold text-primary mb-1">{{ number_format($financialSummary['average_order_value'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
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
        // Financial Chart
        var ctx = document.getElementById('financialChart').getContext('2d');
        var financialChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                datasets: [{
                    label: 'المبيعات',
                    data: [{{ $financialData['sales'] }}],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'العمولات',
                    data: [{{ $financialData['commissions'] }}],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }, {
                    label: 'الرسوم',
                    data: [{{ $financialData['fees'] }}],
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }, {
                    label: 'الدفعات',
                    data: [{{ $financialData['payouts'] }}],
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
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
                                return value.toLocaleString() + ' {{ config('app.currency_symbol', 'ر.س') }}';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('ar-SA', { style: 'currency', currency: 'SAR' }).format(context.parsed.y);
                                }
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
