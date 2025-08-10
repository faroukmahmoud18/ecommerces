
@extends('admin.layouts.app')

@section('title', 'إدارة المراجعات')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">إدارة المراجعات</h6>
            </div>

            <div class="card-body">
                {{-- Search and Filters --}}
                <form method="GET" action="{{ route('admin.reviews.index') }}" class="mb-4">
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
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مؤكد</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="rating" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع التقييمات</option>
                                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 نجوم</option>
                                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 نجوم</option>
                                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 نجوم</option>
                                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 نجوم</option>
                                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 نجمة</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Reviews Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>العميل</th>
                                <th>التقييم</th>
                                <th>العنوان</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                            <tr>
                                <td>
                                    <a href="{{ route('products.show', $review->product->id) }}" class="text-decoration-none">
                                        @if($review->product->thumbnail)
                                            <img src="{{ asset('storage/' . $review->product->thumbnail) }}" alt="{{ $review->product->name }}" class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            </div>
                                        @endif
                                        {{ $review->product->name }}
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $review->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $review->user->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                        <span class="text-muted">({{ $review->rating }})</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
                                        {{ $review->title }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $review->status === 'pending' ? 'warning' : 
                                                    ($review->status === 'approved' ? 'success' : 'danger') }}">
                                        {{ $review->status === 'pending' ? 'قيد الانتظار' : 
                                          ($review->status === 'approved' ? 'مؤكد' : 'مرفوض') }}
                                    </span>
                                </td>
                                <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($review->status === 'pending')
                                            <a href="{{ route('admin.reviews.approve', $review->id) }}" class="btn btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="{{ route('admin.reviews.reject', $review->id) }}" class="btn btn-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.reviews.export', $review->id) }}">
                                                    <i class="fas fa-file-export me-1"></i> تصدير المراجعة
                                                </a>
                                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذه المراجعة؟')">
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
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد مراجعات</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $reviews->links() }}
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
            order: [[5, 'desc']]
        });
    });
</script>
@endsection
