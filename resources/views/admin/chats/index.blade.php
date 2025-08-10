
@extends('admin.layouts.app')

@section('title', 'إدارة المحادثات')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">إدارة المحادثات</h6>
            </div>

            <div class="card-body">
                {{-- Search and Filters --}}
                <form method="GET" action="{{ route('admin.chats.index') }}" class="mb-4">
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
                                <option value="customer_support" {{ request('type') == 'customer_support' ? 'selected' : '' }}>دعم العملاء</option>
                                <option value="customer_vendor" {{ request('type') == 'customer_vendor' ? 'selected' : '' }}>عميل-بائع</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الحالات</option>
                                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>مفتوحة</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Chats Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>الموضوع</th>
                                <th>النوع</th>
                                <th>المشاركون</th>
                                <th>آخر رسالة</th>
                                <th>عدد الرسائل غير المقروءة</th>
                                <th>ت الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chats as $chat)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.chats.show', $chat->id) }}" class="text-decoration-none">
                                        {{ $chat->subject }}
                                    </a>
                                    @if($chat->order)
                                        <br>
                                        <small class="text-muted">الطلب #{{ $chat->order->order_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $chat->type === 'customer_support' ? 'primary' : 'info' }}">
                                        {{ $chat->type === 'customer_support' ? 'دعم العملاء' : 'عميل-بائع' }}
                                    </span>
                                </td>
                                <td>
                                    @foreach($chat->participants as $participant)
                                        <div class="d-flex align-items-center mb-1">
                                            @if($participant->user->avatar)
                                                <img src="{{ asset('storage/' . $participant->user->avatar) }}" alt="{{ $participant->user->name }}" class="img-thumbnail rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                    <span class="text-white fw-bold" style="font-size: 12px;">{{ $participant->user->name|first }}</span>
                                                </div>
                                            @endif
                                            <span>{{ $participant->user->name }}</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    @if($chat->lastMessage)
                                        <div class="text-truncate" style="max-width: 200px;">
                                            {{ $chat->lastMessage->message }}
                                        </div>
                                        <small class="text-muted">{{ $chat->lastMessage->created_at->diffForHumans() }}</small>
                                    @else
                                        <small class="text-muted">لا توجد رسائل</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-danger">
                                        {{ $chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->count() }}
                                    </span>
                                </td>
                                <td>{{ $chat->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.chats.show', $chat->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($chat->status === 'open')
                                            <a href="{{ route('admin.chats.close', $chat->id) }}" class="btn btn-warning">
                                                <i class="fas fa-lock"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.chats.open', $chat->id) }}" class="btn btn-success">
                                                <i class="fas fa-unlock"></i>
                                            </a>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('admin.chats.export', $chat->id) }}">
                                                    <i class="fas fa-file-export me-1"></i> تصدير المحادثة
                                                </a>
                                                <form action="{{ route('admin.chats.destroy', $chat->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذه المحادثة؟')">
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
                                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد محادثات</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $chats->links() }}
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
