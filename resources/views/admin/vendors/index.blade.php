
@extends('admin.layouts.app')

@section('title', 'البائعون')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">إدارة البائعين</h6>
                <a href="{{ route('admin.vendors.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus"></i> إضافة بائع جديد
                </a>
            </div>

            <div class="card-body">
                {{-- Search and Filters --}}
                <form method="GET" action="{{ route('admin.vendors.index') }}" class="mb-4">
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
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>معلق</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Vendors Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>حالة الحساب</th>
                                <th>نسبة العمولة</th>
                                <th>عدد المنتجات</th>
                                <th>عدد الطلبات</th>
                                <th>إجمالي المبيعات</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $vendor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($vendor->user->avatar)
                                            <img src="{{ asset('storage/' . $vendor->user->avatar) }}" alt="{{ $vendor->name }}" class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <span class="text-white fw-bold">{{ $vendor->name|first }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="text-decoration-none">
                                                <strong>{{ $vendor->name }}</strong>
                                            </a>
                                            <br>
                                            <small class="text-muted">@{{ $vendor->user->username ?? $vendor->slug }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $vendor->user->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $vendor->status === 'pending' ? 'warning' : 
                                                    ($vendor->status === 'active' ? 'success' : 'danger') }}">
                                        {{ $vendor->status === 'pending' ? 'قيد الانتظار' : 
                                          ($vendor->status === 'active' ? 'نشط' : 'معلق') }}
                                    </span>
                                </td>
                                <td>{{ $vendor->commission_rate }}%</td>
                                <td>{{ $vendor->products()->count() }}</td>
                                <td>{{ $vendor->orders()->count() }}</td>
                                <td>{{ number_format($vendor->orders()->sum('total_amount'), 2) }}</td>
                                <td>{{ $vendor->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item {{ $vendor->status === 'active' ? 'disabled' : '' }}" href="{{ route('admin.vendors.change-status', $vendor->id) }}?status=active">
                                                    <i class="fas fa-check-circle me-1"></i> تفعيل
                                                </a>
                                                <a class="dropdown-item {{ $vendor->status === 'suspended' ? 'disabled' : '' }}" href="{{ route('admin.vendors.change-status', $vendor->id) }}?status=suspended">
                                                    <i class="fas fa-pause-circle me-1"></i> تعليق
                                                </a>
                                                <a class="dropdown-item {{ $vendor->status === 'pending' ? 'disabled' : '' }}" href="{{ route('admin.vendors.change-status', $vendor->id) }}?status=pending">
                                                    <i class="fas fa-clock me-1"></i> وضع قيد الانتظار
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذا البائع؟')">
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
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-store fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد بائعين</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $vendors->links() }}
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
            order: [[7, 'desc']]
        });
    });
</script>
@endsection
