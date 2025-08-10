@extends('admin.layouts.app')

@section('title', 'إدارة الإشعارات')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">إدارة الإشعارات</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">الإشعارات</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">جميع الإشعارات</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-notification-modal">
                                <i class="fas fa-plus"></i> إشعار جديد
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>تصفية حسب النوع</label>
                                    <select class="form-control" id="filter-type">
                                        <option value="">الكل</option>
                                        <option value="order_placed">تم إنشاء طلب</option>
                                        <option value="payment_completed">تأكيد الدفع</option>
                                        <option value="order_shipped">تم شحن الطلب</option>
                                        <option value="promotion">عروض خاصة</option>
                                        <option value="system_alert">تنبيه نظام</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>حالة القراءة</label>
                                    <select class="form-control" id="filter-read">
                                        <option value="">الكل</option>
                                        <option value="unread">غير مقروءة</option>
                                        <option value="read">مقروءة</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>المستخدم</label>
                                    <select class="form-control select2" id="filter-user">
                                        <option value="">جميع المستخدمين</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>تاريخ الإشعار</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="text" class="form-control daterange" id="filter-date">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="notifications-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">ID</th>
                                        <th>المستخدم</th>
                                        <th>النوع</th>
                                        <th>العنوان</th>
                                        <th>الرسالة</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                        <th style="width: 120px">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                    <tr>
                                        <td>{{ $notification->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $notification->user_id) }}">
                                                {{ $notification->user->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $notification->color }}">
                                                {{ $notification->type_label }}
                                            </span>
                                        </td>
                                        <td>{{ $notification->title }}</td>
                                        <td>{{ Str::limit($notification->message, 50) }}</td>
                                        <td>
                                            @if($notification->is_read)
                                                <span class="badge badge-success">مقروءة</span>
                                            @else
                                                <span class="badge badge-warning">غير مقروءة</span>
                                            @endif
                                        </td>
                                        <td>{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm view-notification" data-id="{{ $notification->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm delete-notification" data-id="{{ $notification->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $notifications->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Widgets -->
        <div class="row">
            <!-- Total Notifications -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $counts['total'] }}</h3>
                        <p>إجمالي الإشعارات</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </div>

            <!-- Unread Notifications -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $counts['unread'] }}</h3>
                        <p>إشعارات غير مقروءة</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
            </div>

            <!-- Today's Notifications -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $counts['today'] }}</h3>
                        <p>إشعارات اليوم</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>

            <!-- Notifications per User -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($counts['per_user'], 1) }}</h3>
                        <p>متوسط الإشعارات للمستخدم</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-bell"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Notification Modal -->
<div class="modal fade" id="create-notification-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">إرسال إشعار جديد</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="create-notification-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label>المستلمين *</label>
                        <select class="form-control select2" id="recipient-type" name="recipient_type" required>
                            <option value="all">جميع المستخدمين</option>
                            <option value="user">مستخدم محدد</option>
                            <option value="vendor">البائعين</option>
                            <option value="customer">العملاء</option>
                        </select>
                    </div>
                    <div class="form-group" id="user-select-container" style="display: none;">
                        <label>المستخدم *</label>
                        <select class="form-control select2" name="user_id">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>نوع الإشعار *</label>
                        <select class="form-control" name="type" required>
                            <option value="system_alert">تنبيه نظام</option>
                            <option value="promotion">عروض خاصة</option>
                            <option value="new_message">رسالة جديدة</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>العنوان *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>الرسالة *</label>
                        <textarea class="form-control" name="message" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>رابط الإجراء</label>
                        <input type="url" class="form-control" name="action_url" placeholder="https://example.com">
                    </div>
                    <div class="form-group">
                        <label>قنوات الإرسال</label>
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="channel-database" name="channels[]" value="database" checked>
                            <label for="channel-database" class="custom-control-label">إشعار داخلي</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="channel-mail" name="channels[]" value="mail">
                            <label for="channel-mail" class="custom-control-label">بريد إلكتروني</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إرسال الإشعار</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Notification Modal -->
<div class="modal fade" id="view-notification-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">تفاصيل الإشعار</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar-lg notification-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>

                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">العنوان</th>
                        <td id="view-title"></td>
                    </tr>
                    <tr>
                        <th>الرسالة</th>
                        <td id="view-message"></td>
                    </tr>
                    <tr>
                        <th>النوع</th>
                        <td id="view-type"></td>
                    </tr>
                    <tr>
                        <th>المستخدم</th>
                        <td id="view-user"></td>
                    </tr>
                    <tr>
                        <th>الحالة</th>
                        <td id="view-status"></td>
                    </tr>
                    <tr>
                        <th>تاريخ الإنشاء</th>
                        <td id="view-created"></td>
                    </tr>
                    <tr>
                        <th>تاريخ القراءة</th>
                        <td id="view-read"></td>
                    </tr>
                    <tr id="view-data-row">
                        <th>بيانات إضافية</th>
                        <td id="view-data"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // Initialize Select2
        $('.select2').select2();

        // Initialize Date Range Picker
        $('.daterange').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'تطبيق',
                cancelLabel: 'إلغاء',
                fromLabel: 'من',
                toLabel: 'إلى',
                customRangeLabel: 'مخصص',
                daysOfWeek: ['أحد', 'إثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
                monthNames: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                firstDay: 6
            }
        });

        // Recipient Type Change
        $('#recipient-type').change(function() {
            if ($(this).val() === 'user') {
                $('#user-select-container').show();
            } else {
                $('#user-select-container').hide();
            }
        });

        // View Notification
        $('.view-notification').click(function() {
            const id = $(this).data('id');

            $.ajax({
                url: `/admin/notifications/${id}`,
                method: 'GET',
                success: function(response) {
                    const notification = response.data;

                    $('#view-title').text(notification.title);
                    $('#view-message').text(notification.message);
                    $('#view-type').html(`<span class="badge badge-${notification.color}">${notification.type_label}</span>`);
                    $('#view-user').text(notification.user.name);
                    $('#view-status').html(notification.is_read ? 
                        '<span class="badge badge-success">مقروءة</span>' : 
                        '<span class="badge badge-warning">غير مقروءة</span>');
                    $('#view-created').text(notification.created_at);
                    $('#view-read').text(notification.read_at || 'لم تقرأ بعد');

                    if (notification.data && Object.keys(notification.data).length > 0) {
                        $('#view-data').html('<pre>' + JSON.stringify(notification.data, null, 2) + '</pre>');
                        $('#view-data-row').show();
                    } else {
                        $('#view-data-row').hide();
                    }

                    $('#view-notification-modal').modal('show');
                },
                error: function() {
                    toastr.error('حدث خطأ أثناء جلب بيانات الإشعار');
                }
            });
        });

        // Delete Notification
        $('.delete-notification').click(function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من التراجع عن هذا الإجراء!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'نعم، احذفه!',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/notifications/${id}`,
                        method: 'DELETE',
                        success: function(response) {
                            toastr.success('تم حذف الإشعار بنجاح');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        },
                        error: function() {
                            toastr.error('حدث خطأ أثناء حذف الإشعار');
                        }
                    });
                }
            });
        });

        // Create Notification Form Submit
        $('#create-notification-form').submit(function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: '/admin/notifications',
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#create-notification-modal').modal('hide');
                    toastr.success('تم إرسال الإشعار بنجاح');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = 'حدث خطأ أثناء إرسال الإشعار';

                    if (errors) {
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }

                    toastr.error(errorMessage);
                }
            });
        });

        // Filters
        $('#filter-type, #filter-read, #filter-user, #filter-date').change(function() {
            applyFilters();
        });

        function applyFilters() {
            const type = $('#filter-type').val();
            const readStatus = $('#filter-read').val();
            const userId = $('#filter-user').val();
            const dateRange = $('#filter-date').val();

            window.location.href = `/admin/notifications?type=${type}&read=${readStatus}&user_id=${userId}&date_range=${dateRange}`;
        }
    });
</script>
@endsection