
@extends('vendor.layouts.app')

@section('title', 'المنتجات')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">إدارة المنتجات</h6>
                <a href="{{ route('vendor.products.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus"></i> إضافة منتج جديد
                </a>
            </div>

            <div class="card-body">
                {{-- Filters --}}
                <form method="GET" action="{{ route('vendor.products.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
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
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الفئات</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Products Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>الصورة</th>
                                <th>اسم المنتج</th>
                                <th>السعر</th>
                                <th>المخزون</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>
                                    @if($product->images->count() > 0)
                                        <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('vendor.products.show', $product->id) }}" class="text-decoration-none">
                                        {{ $product->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $product->sku }}</small>
                                </td>
                                <td>{{ number_format($product->price, 2) }}</td>
                                <td>
                                    @if($product->manage_inventory)
                                        {{ $product->quantity }}
                                    @else
                                        <span class="text-muted">غير محدود</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('vendor.products.show', $product->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('vendor.products.edit', $product->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('vendor.products.destroy', $product->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد منتجات</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $products->links() }}
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
            order: [[1, 'asc']]
        });
    });
</script>
@endsection
