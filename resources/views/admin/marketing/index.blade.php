
@extends('admin.layouts.app')

@section('title', 'إدارة التسويق')

@section('content')
<div class="row">
    {{-- Marketing Summary Cards --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">إجمالي الحملات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($marketingSummary['total_campaigns']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bullhorn fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">إجمالي الإيرادات</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($marketingSummary['total_revenue'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">إجمالي الإنفاق</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($marketingSummary['total_spent'], 2) }} {{ config('app.currency_symbol', 'ر.س') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">معدل التحويل</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($marketingSummary['conversion_rate'], 2) }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Campaigns Table --}}
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">قائمة الحملات</h6>
                <div>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="{{ route('admin.marketing.email-campaigns.create') }}">
                                <i class="fas fa-envelope me-1"></i> حملة بريدية
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.marketing.sms-campaigns.create') }}">
                                <i class="fas fa-sms me-1"></i> حملة نصية
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.marketing.coupons.create') }}">
                                <i class="fas fa-ticket-alt me-1"></i> كوبون خصم
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {{-- Search and Filters --}}
                <form method="GET" action="{{ route('admin.marketing.index') }}" class="mb-4">
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
                                <option value="email" {{ request('type') == 'email' ? 'selected' : '' }}>بريد إلكتروني</option>
                                <option value="sms" {{ request('type') == 'sms' ? 'selected' : '' }}>رسالة نصية</option>
                                <option value="social" {{ request('type') == 'social' ? 'selected' : '' }}>وسائل التواصل الاجتماعي</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الحالات</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>متوقف</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
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
                    <table class="table table-bordered" id="campaignsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>الحملة</th>
                                <th>النوع</th>
                                <th>الحالة</th>
                                <th>الإيرادات</th>
                                <th>النفقات</th>
                                <th>معدل التحويل</th>
                                <th>تاريخ البدء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $campaign)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $campaign->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($campaign->description, 50) }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $campaign->type === 'email' ? 'primary' : 
                                                    ($campaign->type === 'sms' ? 'info' : 
                                                    ($campaign->type === 'social' ? 'success' : 'warning')) }}">
                                        {{ $campaign->type === 'email' ? 'بريد إلكتروني' : 
                                          ($campaign->type === 'sms' ? 'رسالة نصية' : 
                                          ($campaign->type === 'social' ? 'وسائل تواصل' : 'أخرى')) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $campaign->status === 'active' ? 'success' : 
                                                    ($campaign->status === 'paused' ? 'warning' : 
                                                    ($campaign->status === 'completed' ? 'primary' : 'secondary')) }}">
                                        {{ $campaign->status === 'draft' ? 'مسودة' : 
                                          ($campaign->status === 'active' ? 'نشط' : 
                                          ($campaign->status === 'paused' ? 'متوقف' : 
                                          ($campaign->status === 'completed' ? 'مكتمل' : 'غير نشط'))) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ number_format($campaign->revenue, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</strong>
                                </td>
                                <td>
                                    <strong>{{ number_format($campaign->spent, 2) }} {{ config('app.currency_symbol', 'ر.س') }}</strong>
                                </td>
                                <td>
                                    @if($campaign->conversions > 0 && $campaign->impressions > 0)
                                        {{ number_format(($campaign->conversions / $campaign->impressions) * 100, 2) }}%
                                    @else
                                        <span class="text-muted">0%</span>
                                    @endif
                                </td>
                                <td>
                                    @if($campaign->start_date)
                                        {{ $campaign->start_date->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.marketing.campaigns.show', $campaign->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.marketing.campaigns.edit', $campaign->id) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($campaign->status === 'draft')
                                            <a href="{{ route('admin.marketing.campaigns.activate', $campaign->id) }}" class="btn btn-success">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        @endif
                                        @if($campaign->status === 'active')
                                            <a href="{{ route('admin.marketing.campaigns.pause', $campaign->id) }}" class="btn btn-warning">
                                                <i class="fas fa-pause"></i>
                                            </a>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.marketing.campaigns.stats', $campaign->id) }}">
                                                    <i class="fas fa-chart-bar me-1"></i> الإحصائيات
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.marketing.campaigns.duplicate', $campaign->id) }}">
                                                    <i class="fas fa-copy me-1"></i> نسخ
                                                </a>
                                                <form action="{{ route('admin.marketing.campaigns.destroy', $campaign->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الحملة؟')">
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

    {{-- Email Campaigns --}}
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أحدث حملات البريد الإلكتروني</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($emailCampaigns as $campaign)
                    <a href="{{ route('admin.marketing.email-campaigns.show', $campaign->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $campaign->name }}</h6>
                            <small>{{ $campaign->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">{{ Str::limit($campaign->subject, 50) }}</p>
                        <small>
                            <span class="badge bg-{{ $campaign->status === 'sent' ? 'success' : 
                                              ($campaign->status === 'scheduled' ? 'warning' : 
                                              ($campaign->status === 'failed' ? 'danger' : 'secondary')) }}">
                                {{ $campaign->status === 'draft' ? 'مسودة' : 
                                  ($campaign->status === 'scheduled' ? 'مجدول' : 
                                  ($campaign->status === 'sending' ? 'جاري الإرسال' : 
                                  ($campaign->status === 'sent' ? 'تم الإرسال' : 
                                  ($campaign->status === 'failed' ? 'فشل' : 'غير نشط')))) }}
                            </span>
                            @if($campaign->total_sent > 0)
                                <span class="badge bg-primary">{{ number_format($campaign->total_sent) }} تم الإرسال</span>
                            @endif
                            @if($campaign->total_opened > 0)
                                <span class="badge bg-info">{{ number_format($campaign->total_opened) }} مفتوح</span>
                            @endif
                            @if($campaign->total_clicked > 0)
                                <span class="badge bg-success">{{ number_format($campaign->total_clicked) }} نقر</span>
                            @endif
                        </small>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- SMS Campaigns --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أحدث حملات الرسائل النصية</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($smsCampaigns as $campaign)
                    <a href="{{ route('admin.marketing.sms-campaigns.show', $campaign->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $campaign->name }}</h6>
                            <small>{{ $campaign->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">{{ Str::limit($campaign->message, 80) }}</p>
                        <small>
                            <span class="badge bg-{{ $campaign->status === 'sent' ? 'success' : 
                                              ($campaign->status === 'scheduled' ? 'warning' : 
                                              ($campaign->status === 'failed' ? 'danger' : 'secondary')) }}">
                                {{ $campaign->status === 'draft' ? 'مسودة' : 
                                  ($campaign->status === 'scheduled' ? 'مجدول' : 
                                  ($campaign->status === 'sending' ? 'جاري الإرسال' : 
                                  ($campaign->status === 'sent' ? 'تم الإرسال' : 
                                  ($campaign->status === 'failed' ? 'فشل' : 'غير نشط')))) }}
                            </span>
                            @if($campaign->total_sent > 0)
                                <span class="badge bg-primary">{{ number_format($campaign->total_sent) }} تم الإرسال</span>
                            @endif
                            @if($campaign->total_delivered > 0)
                                <span class="badge bg-info">{{ number_format($campaign->total_delivered) }} تم التسليم</span>
                            @endif
                            @if($campaign->total_clicked > 0)
                                <span class="badge bg-success">{{ number_format($campaign->total_clicked) }} نقر</span>
                            @endif
                        </small>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Coupons --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">أحدث كوبونات الخصم</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($coupons as $coupon)
                    <a href="{{ route('admin.marketing.coupons.show', $coupon->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $coupon->code }}</h6>
                            <small>{{ $coupon->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">
                            <span class="badge bg-{{ $coupon->type === 'percentage' ? 'primary' : 'success' }}">
                                {{ $coupon->type === 'percentage' ? $coupon->value . '%' : config('app.currency_symbol', 'ر.س') . $coupon->value }}
                            </span>
                            @if($coupon->max_uses)
                                <span class="badge bg-info">حد أقصى {{ number_format($coupon->max_uses) }} استخدام</span>
                            @endif
                        </p>
                        <small>
                            <span class="badge bg-{{ $coupon->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $coupon->status === 'active' ? 'نشط' : 'غير نشط' }}
                            </span>
                            @if($coupon->uses > 0)
                                <span class="badge bg-primary">{{ number_format($coupon->uses) }} استخدام</span>
                            @endif
                        </small>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
