
@extends('admin.layouts.app')

@section('title', 'إدارة الحملات التسويقية')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">إدارة الحملات التسويقية</h6>
                <div>
                    <a href="{{ route('admin.email-marketing.campaigns.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إنشاء حملة جديدة
                    </a>
                </div>
            </div>

            <div class="card-body">
                {{-- Search and Filters --}}
                <form method="GET" action="{{ route('admin.email-marketing.campaigns.index') }}" class="mb-4">
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
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>مجدولة</option>
                                <option value="sending" {{ request('status') == 'sending' ? 'selected' : '' }}>جاري الإرسال</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الأنواع</option>
                                <option value="promotion" {{ request('type') == 'promotion' ? 'selected' : '' }}>ترويجية</option>
                                <option value="newsletter" {{ request('type') == 'newsletter' ? 'selected' : '' }}>نشرة</option>
                                <option value="welcome" {{ request('type') == 'welcome' ? 'selected' : '' }}>ترحيبية</option>
                                <option value="abandoned_cart" {{ request('type') == 'abandoned_cart' ? 'selected' : '' }}>عربة مهجورة</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Campaigns Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>اسم الحملة</th>
                                <th>النوع</th>
                                <th>الحالة</th>
                                <th>المرسلون</th>
                                <th>ت الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.email-marketing.campaigns.show', $campaign->id) }}" class="text-decoration-none">
                                        {{ $campaign->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $campaign->subject }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $campaign->type === 'promotion' ? 'primary' : 
                                                    ($campaign->type === 'newsletter' ? 'info' : 
                                                    ($campaign->type === 'welcome' ? 'success' : 'warning')) }}">
                                        {{ $campaign->type === 'promotion' ? 'ترويجية' : 
                                          ($campaign->type === 'newsletter' ? 'نشرة' : 
                                          ($campaign->type === 'welcome' ? 'ترحيبية' : 'عربة مهجورة')) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $campaign->status === 'draft' ? 'secondary' : 
                                                    ($campaign->status === 'scheduled' ? 'warning' : 
                                                    ($campaign->status === 'sending' ? 'info' : 
                                                    ($campaign->status === 'completed' ? 'success' : 'danger'))) }}">
                                        {{ $campaign->status === 'draft' ? 'مسودة' : 
                                          ($campaign->status === 'scheduled' ? 'مجدولة' : 
                                          ($campaign->status === 'sending' ? 'جاري الإرسال' : 
                                          ($campaign->status === 'completed' ? 'مكتملة' : 'فشلت'))) }}
                                    </span>
                                </td>
                                <td>
                                    {{ number_format($campaign->recipients()->count()) }}
                                </td>
                                <td>{{ $campaign->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.email-marketing.campaigns.show', $campaign->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($campaign->status === 'draft')
                                            <a href="{{ route('admin.email-marketing.campaigns.edit', $campaign->id) }}" class="btn btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.email-marketing.campaigns.send', $campaign->id) }}" class="btn btn-success">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                            <a href="{{ route('admin.email-marketing.campaigns.schedule', $campaign->id) }}" class="btn btn-warning">
                                                <i class="fas fa-clock"></i>
                                            </a>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.email-marketing.campaigns.clone', $campaign->id) }}">
                                                    <i class="fas fa-copy me-1"></i> استنساخ
                                                </a>
                                                <a class="dropdown-item" href="{{ route('admin.email-marketing.campaigns.export', $campaign->id) }}">
                                                    <i class="fas fa-file-export me-1"></i> تصدير
                                                </a>
                                                <form action="{{ route('admin.email-marketing.campaigns.destroy', $campaign->id) }}" method="POST" style="display: inline;">
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
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد حملات</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $campaigns->links() }}
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
            order: [[4, 'desc']]
        });
    });
</script>
@endsection
