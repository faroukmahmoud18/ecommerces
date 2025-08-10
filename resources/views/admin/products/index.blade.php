
@extends('admin.layouts.app')

@section('title', 'إدارة المنتجات')

@section('content')
<div class="row">
    {{-- Product Summary Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي المنتجات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($productSummary['total_products']) }}</div>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">المنتجات النشطة</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($productSummary['active_products']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">المنتجات المميزة</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($productSummary['featured_products']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-star fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">المنتجات غير المتوفرة</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($productSummary['out_of_stock_products']) }}</div>
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
    {{-- Products Table --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">قائمة المنتجات</h6>
                <div>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة منتج جديد
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Search and Filters --}}
                <form method="GET" action="{{ route('admin.products.index') }}" class="mb-4">
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
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="category_id" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الفئات</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered" id="productsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>البائع</th>
                                <th>الفئات</th>
                                <th>السعر</th>
                                <th>المخزون</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->featured_image)
                                            <img src="{{ asset('storage/' . $product->featured_image) }}" alt="{{ $product->name }}" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 50px;">
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $product->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($product->short_description, 50) }}</small>
                                            @if($product->variants->count() > 0)
                                                <br>
                                                <span class="badge bg-info">{{ $product->variants->count() }} متغير</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->vendor->logo)
                                            <img src="{{ asset('storage/' . $product->vendor->logo) }}" alt="{{ $product->vendor->name }}" class="img-thumbnail rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                <span class="text-white fw-bold" style="font-size: 12px;">{{ $product->vendor->name|first }}</span>
                                            </div>
                                        @endif
                                        <span>{{ $product->vendor->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($product->categories->count() > 0)
                                        @foreach($product->categories->take(2) as $category)
                                            <span class="badge bg-primary me-1">{{ $category->name }}</span>
                                        @endforeach
                                        @if($product->categories->count() > 2)
                                            <span class="text-muted">+{{ $product->categories->count() - 2 }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">لا توجد فئات</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ number_format($product->price, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</strong>
                                        @if($product->compare_price && $product->compare_price > $product->price)
                                            <br>
                                            <small class="text-decoration-line-through text-muted">{{ number_format($product->compare_price, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($product->track_quantity)
                                        @if($product->quantity <= 0)
                                            <span class="badge bg-danger">غير متوفر</span>
                                        @elseif($product->quantity <= $product->low_stock_threshold)
                                            <span class="badge bg-warning">منخفض</span>
                                        @else
                                            <span class="badge bg-success">{{ number_format($product->quantity) }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">غير محدود</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $product->status === 'active' ? 'success' : 
                                                    ($product->status === 'draft' ? 'secondary' : 'danger') }}">
                                        {{ $product->status === 'active' ? 'نشط' : 
                                          ($product->status === 'draft' ? 'مسودة' : 'غير نشط') }}
                                    </span>
                                    @if($product->featured)
                                        <i class="fas fa-star text-warning ms-1" title="منتج ميز"></i>
                                    @endif
                                    @if($product->new)
                                        <i class="fas fa-sparkles text-info ms-1" title="منتج جديد"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($product->status === 'draft')
                                            <a href="{{ route('admin.products.publish', $product->id) }}" class="btn btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        @endif
                                        @if($product->status === 'active')
                                            <a href="{{ route('admin.products.draft', $product->id) }}" class="btn btn-warning">
                                                <i class="fas fa-pause"></i>
                                            </a>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.products.variants.index', $product->id) }}">
                                                    <i class="fas fa-boxes me-1"></i> المتغيرات
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.products.images.index', $product->id) }}">
                                                    <i class="fas fa-images me-1"></i> الصور
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.products.reviews', $product->id) }}">
                                                    <i class="fas fa-star me-1"></i> المراجعات
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.products.export', $product->id) }}">
                                                    <i class="fas fa-file-export me-1"></i> تصديق المنتج
                                                </a>
                                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
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

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Product Categories --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">الفئات الرئيسية</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($topCategories as $category)
                    <a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $category->name }}</h6>
                            <small>{{ number_format($category->products_count) }}</small>
                        </div>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $category->products_count / $productSummary['total_products'] * 100 }}%;" aria-valuenow="{{ $category->products_count / $productSummary['total_products'] * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Vendors --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أفضل البائعين</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($topVendors as $vendor)
                    <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $vendor->name }}</h6>
                            <small>{{ number_format($vendor->products_count) }}</small>
                        </div>
                        <small class="text-muted">{{ $vendor->email }}</small>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
