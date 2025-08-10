
@extends('admin.layouts.app')

@section('title', 'إدارة العروض الترويجية')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">إدارة العروض الترويجية</h6>
                <div>
                    <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إنشاء عرض جديد
                    </a>
                </div>
            </div>

            <div class="card-body">
                {{-- Search and Filters --}}
                <form method="GET" action="{{ route('admin.promotions.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" name="search" class="form-control form-control-lg border-start-0" placeholder="بحث..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الأنواع</option>
                                <option value="percentage" {{ request('type') == 'percentage' ? 'selected' : '' }}>نسبة مئوية</option>
                                <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                                <option value="free_shipping" {{ request('type') == 'free_shipping' ? 'selected' : '' }}>شحن مجاني</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الحالات</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منتهي الصلاحية</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Promotions Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>العرض الترويجي</th>
                                <th>الكود</th>
                                <th>النوع</th>
                                <th>القيمة</th>
                                <th>عدد الاستخدامات</th>
                                <th>الحالة</th>
                                <th>المدة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promotions as $promotion)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $promotion->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $promotion->description }}</small>
                                    </div>
                                </td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded">{{ $promotion->code }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $promotion->type === 'percentage' ? 'primary' : 
                                                    ($promotion->type === 'fixed' ? 'success' : 
                                                    ($promotion->type === 'free_shipping' ? 'info' : 'warning')) }}">
                                        {{ $promotion->type === 'percentage' ? 'نسبة مئوية' : 
                                          ($promotion->type === 'fixed' ? 'مبلغ ثابت' : 
                                          ($promotion->type === 'free_shipping' ? 'شحن مجاني' : 'آخر')) }}
                                    </span>
                                </td>
                                <td>
                                    @if($promotion->type === 'percentage')
                                        {{ $promotion->value }}%
                                    @else
                                        {{ number_format($promotion->value, 2) }} {{ config('app.currency_symbol', 'ر.س') }}
                                    @endif
                                    @if($promotion->max_discount)
                                        <br><small class="text-muted">أقصى خصم: {{ number_format($promotion->max_discount, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $promotion->max_uses && $promotion->usage->count() >= $promotion->max_uses ? 'danger' : 
                                                                ($promotion->usage->count() > $promotion->max_uses * 0.8 ? 'warning' : 'success') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $promotion->max_uses ? ($promotion->usage->count() / $promotion->max_uses) * 100 : 0 }}%;" 
                                                     aria-valuenow="{{ $promotion->max_uses ? ($promotion->usage->count() / $promotion->max_uses) * 100 : 0 }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ms-2">
                                            <span class="text-xs font-weight-bold">{{ $promotion->usage->count() }}</span>
                                            @if($promotion->max_uses)
                                                <span class="text-xs text-muted">/ {{ $promotion->max_uses }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $promotion->status === 'active' ? 'success' : 
                                                    ($promotion->status === 'inactive' ? 'secondary' : 'danger') }}">
                                        {{ $promotion->status === 'active' ? 'نشط' : 
                                          ($promotion->status === 'inactive' ? 'غير نشط' : 'منتهي الصلاحية') }}
                                    </span>
                                </td>
                                <td>
                                    @if($promotion->start_date && $promotion->end_date)
                                        {{ $promotion->start_date->format('d/m/Y') }} - {{ $promotion->end_date->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.promotions.show', $promotion->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($promotion->status === 'active')
                                            <a href="{{ route('admin.promotions.deactivate', $promotion->id) }}" class="btn btn-warning">
                                                <i class="fas fa-pause"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.promotions.activate', $promotion->id) }}" class="btn btn-success">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.promotions.clone', $promotion->id) }}">
                                                    <i class="fas fa-copy me-1"></i> استنساخ
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.promotions.export', $promotion->id) }}">
                                                    <i class="fas fa-file-export me-1"></i> تصدير
                                                </a>
                                                <form action="{{ route('admin.promotions.destroy', $promotion->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذا العرض الترويجي؟')">
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
                                    <i class="fas fa-tag fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد عروض ترويجية</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $promotions->links() }}
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
            order: [[6, 'desc']]
        });
    });
</script>
@endsection
