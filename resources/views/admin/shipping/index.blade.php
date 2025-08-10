
@extends('admin.layouts.app')

@section('title', 'إدارة الشحن والتتبع')

@section('content')
<div class="row">
    {{-- Shipping Summary Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي الشحنات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($shippingSummary['total_shipments']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">شحنات قيد الشحن</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($shippingSummary['in_transit_shipments']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">شحنات تم التسليم</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($shippingSummary['delivered_shipments']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">إجمالي تكاليف الشحن</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($shippingSummary['total_shipping_cost'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Shipments Table --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">قائمة الشحنات</h6>
                <div>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="{{ route('admin.shipments.create') }}">
                                <i class="fas fa-plus me-1"></i> إنشاء شحنة جديدة
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.shipments.export') }}">
                                <i class="fas fa-file-export me-1"></i> تصديق الشحنات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="shipmentsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>رقم الشحنة</th>
                                <th>الطلب</th>
                                <th>البائع</th>
                                <th>طريقة الشحن</th>
                                <th>الحالة</th>
                                <th>التتبع</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shipments as $shipment)
                            <tr>
                                <td>
                                    <strong>{{ $shipment->tracking_number }}</strong>
                                </td>
                                <td>
                                    #{{ $shipment->order->order_number }}
                                    <br>
                                    <small class="text-muted">{{ $shipment->order->customer->name }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($shipment->vendor->logo)
                                            <img src="{{ asset('storage/' . $shipment->vendor->logo) }}" alt="{{ $shipment->vendor->name }}" class="img-thumbnail rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                <span class="text-white fw-bold" style="font-size: 12px;">{{ $shipment->vendor->name|first }}</span>
                                            </div>
                                        @endif
                                        <span>{{ $shipment->vendor->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    {{ $shipment->shippingMethod->name }}
                                    <br>
                                    <small class="text-muted">{{ $shipment->carrier }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $shipment->status === 'pending' ? 'secondary' : 
                                                    ($shipment->status === 'in_transit' ? 'primary' : 
                                                    ($shipment->status === 'delivered' ? 'success' : 
                                                    ($shipment->status === 'exception' ? 'warning' : 'danger'))) }}">
                                        {{ $shipment->status === 'pending' ? 'قيد الانتظار' : 
                                          ($shipment->status === 'in_transit' ? 'في Transit' : 
                                          ($shipment->status === 'delivered' ? 'تم التسليم' : 
                                          ($shipment->status === 'exception' ? 'استثناء' : 'ملغى'))) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.shipments.tracking', $shipment->tracking_number) }}" class="text-decoration-none">
                                        <i class="fas fa-search"></i> تتبع
                                    </a>
                                </td>
                                <td>
                                    {{ $shipment->shipped_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.shipments.show', $shipment->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.shipments.edit', $shipment->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($shipment->status === 'pending')
                                            <a href="{{ route('admin.shipments.ship', $shipment->id) }}" class="btn btn-success">
                                                <i class="fas fa-shipping-fast"></i>
                                            </a>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.shipments.print-label', $shipment->id) }}">
                                                    <i class="fas fa-print me-1"></i> طباعة التسمية
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.shipments.print-invoice', $shipment->id) }}">
                                                    <i class="fas fa-file-invoice me-1"></i> طباعة الفاتورة
                                                </a>
                                                <form action="{{ route('admin.shipments.destroy', $shipment->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الشحنة؟')">
                                                        <i class="fas fa-trash me-1"></i> حذف
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد شحنات</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Shipping Methods --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">طرق الشحن المتاحة</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($shippingMethods as $method)
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $method->name }}</h6>
                            <small>{{ $method->carrier }}</small>
                        </div>
                        <p class="mb-1">{{ $method->description }}</p>
                        <small>
                            <i class="fas fa-clock me-1"></i> 
                            {{ $method->estimated_delivery }} يوم
                        </small>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-primary w-100">
                        <i class="fas fa-cog me-1"></i> إدارة طرق الشحن
                    </a>
                </div>
            </div>
        </div>

        {{-- Shipping Stats --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">إحصائيات الشحن</h6>
            </div>
            <div class="card-body">
                <canvas id="shippingStatsChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#shipmentsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
            },
            order: [[6, 'desc']]
        });

        // Initialize Shipping Stats Chart
        const ctx = document.getElementById('shippingStatsChart').getContext('2d');
        const shippingStatsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['في Transit', 'تم التسليم', 'قيد الانتظار', 'استثناء', 'ملغى'],
                datasets: [{
                    data: [
                        {{ $shippingSummary['in_transit_shipments'] }},
                        {{ $shippingSummary['delivered_shipments'] }},
                        {{ $shippingSummary['pending_shipments'] }},
                        {{ $shippingSummary['exception_shipments'] }},
                        {{ $shippingSummary['cancelled_shipments'] }}
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)'
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
                    }
                }
            }
        });
    });
</script>
@endsection
