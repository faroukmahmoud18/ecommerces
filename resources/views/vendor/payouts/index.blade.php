
@extends('vendor.layouts.app')

@section('title', 'الدفعات')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">إدارة الدفعات</h6>
                <a href="{{ route('vendor.payouts.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus"></i> طلب دفع جديد
                </a>
            </div>

            <div class="card-body">
                {{-- Filters --}}
                <form method="GET" action="{{ route('vendor.payouts.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>قيد المعالجة</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فشل</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <input type="date" name="date_from" class="form-control" placeholder="من تاريخ" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-3">
                            <input type="date" name="date_to" class="form-control" placeholder="إلى تاريخ" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Payouts Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>رقم الدفعة</th>
                                <th>تاريخ الطلب</th>
                                <th>طريقة الدفع</th>
                                <th>المبلغ الإجمالي</th>
                                <th>الرسوم</th>
                                <th>صافي المبلغ</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr>
                                <td>#{{ $payout->id }}</td>
                                <td>{{ $payout->created_at->format('d/m/Y') }}</td>
                                <td>{{ $payout->payment_method }}</td>
                                <td>{{ number_format($payout->amount, 2) }}</td>
                                <td>{{ number_format($payout->fee, 2) }}</td>
                                <td>{{ number_format($payout->net_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $payout->status === 'pending' ? 'warning' : 
                                                    ($payout->status === 'processing' ? 'info' : 
                                                    ($payout->status === 'completed' ? 'success' : 
                                                    ($payout->status === 'failed' ? 'danger' : 'dark')))) }}">
                                        {{ $payout->status_label ?? $payout->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('vendor.payouts.show', $payout->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($payout->status === 'completed')
                                            <a href="{{ route('vendor.payouts.receipt', $payout->id) }}" class="btn btn-primary">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد دفعات</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $payouts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#dataTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
            },
            order: [[0, 'desc']]
        });
    });
</script>
@endsection
