
@extends('admin.layouts.app')

@section('title', 'إدارة العملاء')

@section('content')
<div class="row">
    {{-- Customer Summary Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي العملاء</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($customerSummary['total_customers']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">العملاء النشطون</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($customerSummary['active_customers']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">العملاء الجدد</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($customerSummary['new_customers']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">إجمالي الإيرادات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($customerSummary['total_revenue'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
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
    {{-- Customers Table --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">قائمة العملاء</h6>
                <div>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="{{ route('admin.customers.create') }}">
                                <i class="fas fa-plus me-1"></i> إضافة عميل جديد
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.customers.export') }}">
                                <i class="fas fa-file-export me-1"></i> تصديق العملاء
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {{-- Search and Filters --}}
                <form method="GET" action="{{ route('admin.customers.index') }}" class="mb-4">
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
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="customer_group_id" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع المجموعات</option>
                                @foreach($customerGroups as $group)
                                    <option value="{{ $group->id }}" {{ request('customer_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
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
                    <table class="table table-bordered" id="customersTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>العميل</th>
                                <th>الحالة</th>
                                <th>المجموعة</th>
                                <th>الطلبات</th>
                                <th>إجمالي الإنفاق</th>
                                <th>آخر طلب</th>
                                <th>التسجيل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($customer->avatar)
                                            <img src="{{ asset('storage/' . $customer->avatar) }}" alt="{{ $customer->name }}" class="img-thumbnail rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <span class="text-white fw-bold" style="font-size: 12px;">{{ $customer->name|first }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $customer->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $customer->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ $customer->status === 'active' ? 'نشط' : 'غير نشط' }}
                                    </span>
                                    @if($customer->email_verified_at)
                                        <i class="fas fa-check-circle text-success ms-1" title="تم التحقق من البريد الإلكتروني"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger ms-1" title="لم يتم التحقق من البريد الإلكتروني"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->customerGroups->count() > 0)
                                        @foreach($customer->customerGroups as $group)
                                            <span class="badge bg-primary me-1">{{ $group->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">لا يوجد مجموعة</span>
                                    @endif
                                </td>
                                <td>{{ number_format($customer->orders_count) }}</td>
                                <td>{{ number_format($customer->total_spent, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</td>
                                <td>
                                    @if($customer->last_order)
                                        {{ $customer->last_order->created_at->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">لا يوجد طلبات</span>
                                    @endif
                                </td>
                                <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.customers.orders', $customer->id) }}">
                                                    <i class="fas fa-shopping-cart me-1"></i> الطلبات
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.customers.wishlist', $customer->id) }}">
                                                    <i class="fas fa-heart me-1"></i> قائمة الرغبات
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.customers.reviews', $customer->id) }}">
                                                    <i class="fas fa-star me-1"></i> المراجعات
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                @if($customer->status === 'active')
                                                    <a class="dropdown-item text-warning" href="{{ route('admin.customers.deactivate', $customer->id) }}">
                                                        <i class="fas fa-pause me-1"></i> تعطيل العميل
                                                    </a>
                                                @else
                                                    <a class="dropdown-item text-success" href="{{ route('admin.customers.activate', $customer->id) }}">
                                                        <i class="fas fa-play me-1"></i> تفعيل العميل
                                                    </a>
                                                @endif
                                                <a class="dropdown-item text-danger" href="{{ route('admin.customers.destroy', $customer->id) }}" onclick="return confirm('هل أنت متأكد من حذف هذا العميل؟')">
                                                    <i class="fas fa-trash me-1"></i> حذف العميل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $customers->links() }}
            </div>
        </div>
    </div>

    {{-- Top Customers --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أفضل العملاء إنفاقاً</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($topCustomers as $customer)
                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $customer->name }}</h6>
                            <small>{{ number_format($customer->total_spent, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</small>
                        </div>
                        <p class="mb-1">{{ number_format($customer->orders_count) }} طلب</p>
                        <small class="text-muted">{{ $customer->email }}</small>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Customer Groups --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">مجموعات العملاء</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($customerGroups as $group)
                    <a href="{{ route('admin.customers.index', ['customer_group_id' => $group->id]) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $group->name }}</h6>
                            <small>{{ number_format($group->customers_count) }} عميل</small>
                        </div>
                        <p class="mb-1">{{ $group->description }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
