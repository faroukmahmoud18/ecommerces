
@extends('admin.layouts.app')

@section('title', 'عرض المحادثة #' . $chat->id)

@section('content')
<div class="row">
    {{-- Chat Header --}}
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 font-weight-bold text-primary">عرض المحادثة: {{ $chat->subject }}</h6>
                    </div>
                    <div class="col-auto">
                        <div class="dropdown">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> إجراءات
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                @if($chat->status === 'open')
                                    <a href="{{ route('admin.chats.close', $chat->id) }}" class="dropdown-item">
                                        <i class="fas fa-lock me-1"></i> إغلاق المحادثة
                                    </a>
                                @else
                                    <a href="{{ route('admin.chats.open', $chat->id) }}" class="dropdown-item">
                                        <i class="fas fa-unlock me-1"></i> فتح المحادثة
                                    </a>
                                @endif
                                <a href="{{ route('admin.chats.export', $chat->id) }}" class="dropdown-item">
                                    <i class="fas fa-file-export me-1"></i> تصدير المحادثة
                                </a>
                                <form action="{{ route('admin.chats.destroy', $chat->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('هل أنت متأكد من حذف هذه المحادثة؟')">
                                        <i class="fas fa-trash me-1"></i> حذف المحادثة
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="text-primary mb-3">تفاصيل المحادثة</h4>

                        <table class="table table-sm">
                            <tr>
                                <th width="30%">الموضوع:</th>
                                <td>{{ $chat->subject }}</td>
                            </tr>
                            <tr>
                                <th>النوع:</th>
                                <td>
                                    <span class="badge bg-{{ $chat->type === 'customer_support' ? 'primary' : 'info' }}">
                                        {{ $chat->type === 'customer_support' ? 'دعم العملاء' : 'عميل-بائع' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>الحالة:</th>
                                <td>
                                    <span class="badge bg-{{ $chat->status === 'open' ? 'success' : 'secondary' }}">
                                        {{ $chat->status === 'open' ? 'مفتوحة' : 'مغلقة' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>ت الإنشاء:</th>
                                <td>{{ $chat->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>آخر تحديث:</th>
                                <td>{{ $chat->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>المرسلون:</th>
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
                                            @if($participant->user->hasRole('admin'))
                                                <span class="badge bg-primary rounded-pill ms-2">مسؤول</span>
                                            @endif
                                            @if($participant->user->hasRole('vendor'))
                                                <span class="badge bg-info rounded-pill ms-2">بائع</span>
                                            @endif
                                            @if($participant->user->hasRole('customer'))
                                                <span class="badge bg-success rounded-pill ms-2">عميل</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                            </tr>
                        </table>

                        @if($chat->order)
                            <h5 class="text-primary mt-4 mb-3">معلومات الطلب</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">رقم الطلب:</th>
                                    <td><a href="{{ route('admin.orders.show', $chat->order->id) }}" class="text-decoration-none">#{{ $chat->order->order_number }}</a></td>
                                </tr>
                                <tr>
                                    <th>العميل:</th>
                                    <td>{{ $chat->order->customer_name }}</td>
                                </tr>
                                <tr>
                                    <th>البائع:</th>
                                    <td>{{ $chat->order->vendor->name }}</td>
                                </tr>
                                <tr>
                                    <th>حالة الطلب:</th>
                                    <td>
                                        <span class="badge bg-{{ $chat->order->status === 'pending' ? 'warning' : 
                                                        ($chat->order->status === 'confirmed' ? 'info' : 
                                                        ($chat->order->status === 'processing' ? 'primary' : 
                                                        ($chat->order->status === 'shipped' ? 'secondary' : 
                                                        ($chat->order->status === 'delivered' ? 'success' : 
                                                        ($chat->order->status === 'cancelled' ? 'danger' : 'dark'))))) }}">
                                            {{ $chat->order->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </div>

                    <div class="col-md-4">
                        {{-- Unread Messages --}}
                        <div class="card mb-4">
                            <div class="card-header py-2">
                                <h6 class="m-0 font-weight-bold">الرسائل غير المقروءة</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span>عدد الرسائل غير المقروءة:</span>
                                    <span class="badge bg-danger">{{ $chat->messages()->where('is_read', false)->count() }}</span>
                                </div>

                                <div class="d-flex align-items-center justify-content-between">
                                    <span>عدد الرسائل الإجمالي:</span>
                                    <span class="badge bg-primary">{{ $chat->messages()->count() }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Add Message --}}
                        <div class="card">
                            <div class="card-header py-2">
                                <h6 class="m-0 font-weight-bold">إضافة رسالة</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.chats.messages.store', $chat->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <textarea name="message" class="form-control" rows="3" placeholder="اكتب رسالتك هنا..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-paper-plane"></i> إرسال الرسالة
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chat Messages --}}
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">الرسائل</h6>
            </div>

            <div class="card-body">
                <div class="chat-container" style="height: 500px; overflow-y: auto;">
                    @foreach($chat->messages as $message)
                        <div class="message mb-3 {{ $message->user_id === auth()->id ? 'text-start' : 'text-end' }}">
                            <div class="d-flex align-items-start">
                                @if($message->user_id !== auth()->id)
                                    @if($message->user->avatar)
                                        <img src="{{ asset('storage/' . $message->user->avatar) }}" alt="{{ $message->user->name }}" class="img-thumbnail rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                            <span class="text-white fw-bold" style="font-size: 16px;">{{ $message->user->name|first }}</span>
                                        </div>
                                    @endif
                                @endif

                                <div class="message-content {{ $message->user_id === auth()->id ? 'bg-primary text-white' : 'bg-light text-dark' }} rounded-3 p-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <strong>{{ $message->user->name }}</strong>
                                        <small class="text-muted">{{ $message->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <p class="mb-0">{{ $message->message }}</p>
                                </div>

                                @if($message->user_id === auth()->id)
                                    @if($message->user->avatar)
                                        <img src="{{ asset('storage/' . $message->user->avatar) }}" alt="{{ $message->user->name }}" class="img-thumbnail rounded-circle ms-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center ms-2" style="width: 40px; height: 40px;">
                                            <span class="text-white fw-bold" style="font-size: 16px;">{{ $message->user->name|first }}</span>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            @if(!$message->is_read)
                                <small class="text-muted mt-1">
                                    <i class="fas fa-circle text-danger me-1"></i>غير مقروء
                                </small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Scroll to bottom of chat container
    document.addEventListener('DOMContentLoaded', function() {
        const chatContainer = document.querySelector('.chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });
</script>
@endsection
