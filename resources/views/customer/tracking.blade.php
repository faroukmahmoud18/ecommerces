@extends('layouts.app')

@section('title', 'تتبع الشحن')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="mb-3">
                    <i class="fas fa-shipping-fast me-2"></i>
                    تتبع الشحن
                </h1>
                <p class="text-muted">تتبع طلباتك بسهولة</p>
            </div>

            <!-- Tracking Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="tracking-form">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="tracking-number">رقم التتبع</label>
                                    <input type="text" class="form-control" id="tracking-number" 
                                           placeholder="أدخل رقم التتبع" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="carrier">الناقل</label>
                                    <select class="form-control" id="carrier" required>
                                        <option value="">اختر الناقل</option>
                                        <option value="aramex">أرامكس</option>
                                        <option value="dhl">DHL</option>
                                        <option value="smsa">سمسا</option>
                                        <option value="spl">البريد السعودي</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i> تتبع الشحنة
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="text-center my-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p class="mt-2 text-muted">جاري جلب بيانات التتبع...</p>
            </div>

            <!-- Tracking Results -->
            <div id="tracking-results" style="display: none;">
                <!-- Shipment Info Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">معلومات الشحنة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>رقم الشحنة:</strong> <span id="shipment-id"></span></p>
                                <p class="mb-1"><strong>رقم التتبع:</strong> <span id="tracking-number-display"></span></p>
                                <p class="mb-1"><strong>الناقل:</strong> <span id="carrier-name"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>حالة الشحنة:</strong> <span id="shipment-status" class="badge"></span></p>
                                <p class="mb-1"><strong>تاريخ الشحن:</strong> <span id="shipment-date"></span></p>
                                <p class="mb-1"><strong>الوزن:</strong> <span id="shipment-weight"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Info Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">معلومات الطلب</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>رقم الطلب:</strong> <span id="order-number"></span></p>
                                <p class="mb-1"><strong>تاريخ الطلب:</strong> <span id="order-date"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>البائع:</strong> <span id="vendor-name"></span></p>
                                <p class="mb-1"><strong>إجمالي الطلب:</strong> <span id="order-total"></span></p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="#" id="view-order-details" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> عرض تفاصيل الطلب
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tracking Timeline -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">سير الشحن</h5>
                    </div>
                    <div class="card-body">
                        <div id="tracking-timeline" class="tracking-timeline">
                            <!-- Timeline events will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Results -->
            <div id="no-results" class="text-center my-5" style="display: none;">
                <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                <p class="text-muted">لم يتم العثور على شحنة بهذا الرقم</p>
                <p class="text-muted">تأكد من صحة رقم التتبع والناقل وحاول مرة أخرى</p>
            </div>
        </div>
    </div>
</div>

<!-- Timeline Event Template -->
<template id="timeline-event-template">
    <div class="timeline-event">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <div class="timeline-dot bg-{status_color}"></div>
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="mb-1">{event_title}</h6>
                <p class="text-muted mb-1">{event_description}</p>
                <small class="text-muted">{event_location} - {event_time}</small>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script src="{{ asset('js/tracking.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tracking form
    const trackingForm = document.getElementById('tracking-form');
    const loadingState = document.getElementById('loading-state');
    const trackingResults = document.getElementById('tracking-results');
    const noResults = document.getElementById('no-results');

    // Handle form submission
    trackingForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const trackingNumber = document.getElementById('tracking-number').value;
        const carrier = document.getElementById('carrier').value;

        if (!trackingNumber || !carrier) {
            showAlert('الرجاء إدخال رقم التتبع والناقل', 'warning');
            return;
        }

        // Show loading state
        loadingState.style.display = 'block';
        trackingResults.style.display = 'none';
        noResults.style.display = 'none';

        // Call API to get tracking data
        axios.post('/api/tracking', {
            tracking_number: trackingNumber,
            carrier: carrier
        })
        .then(response => {
            const data = response.data;

            if (data.success) {
                displayTrackingResults(data.shipment);
                trackingResults.style.display = 'block';
            } else {
                noResults.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Tracking error:', error);
            noResults.style.display = 'block';
            showAlert('حدث خطأ أثناء جلب بيانات التتبع', 'danger');
        })
        .finally(() => {
            loadingState.style.display = 'none';
        });
    });

    // Display tracking results
    function displayTrackingResults(shipment) {
        // Fill shipment info
        document.getElementById('shipment-id').textContent = shipment.id;
        document.getElementById('tracking-number-display').textContent = shipment.tracking_number;
        document.getElementById('carrier-name').textContent = getCarrierName(shipment.carrier);
        document.getElementById('shipment-status').textContent = getStatusLabel(shipment.status);
        document.getElementById('shipment-status').className = 'badge badge-' + getStatusBadgeClass(shipment.status);
        document.getElementById('shipment-date').textContent = formatDate(shipment.created_at);
        document.getElementById('shipment-weight').textContent = shipment.weight + ' كجم';

        // Fill order info
        document.getElementById('order-number').textContent = shipment.order.order_number;
        document.getElementById('order-date').textContent = formatDate(shipment.order.created_at);
        document.getElementById('vendor-name').textContent = shipment.order.vendor.name;
        document.getElementById('order-total').textContent = formatCurrency(shipment.order.total_amount);

        // Set view order details link
        document.getElementById('view-order-details').href = '/orders/' + shipment.order.id;

        // Display tracking timeline
        displayTrackingTimeline(shipment.trackingEvents);
    }

    // Display tracking timeline
    function displayTrackingTimeline(events) {
        const timeline = document.getElementById('tracking-timeline');
        timeline.innerHTML = '';

        const template = document.getElementById('timeline-event-template');

        events.forEach(event => {
            const statusColor = getStatusColorClass(event.event_status);
            const eventTitle = getEventTitle(event.event_status);

            const eventHtml = template.innerHTML
                .replace('{status_color}', statusColor)
                .replace('{event_title}', eventTitle)
                .replace('{event_description}', event.event_description || '')
                .replace('{event_location}', event.event_location || '')
                .replace('{event_time}', formatDateTime(event.event_time));

            const eventElement = document.createElement('div');
            eventElement.className = 'timeline-event';
            eventElement.innerHTML = eventHtml;

            timeline.appendChild(eventElement);
        });
    }

    // Helper functions
    function getCarrierName(carrier) {
        const names = {
            'aramex': 'أرامكس',
            'dhl': 'DHL',
            'smsa': 'سمسا',
            'spl': 'البريد السعودي'
        };

        return names[carrier] || carrier;
    }

    function getStatusLabel(status) {
        const labels = {
            'pending': 'قيد الانتظار',
            'created': 'تم الإنشاء',
            'shipped': 'تم الشحن',
            'in_transit': 'قيد النقل',
            'out_for_delivery': 'خارج للتوصيل',
            'delivered': 'تم التوصيل',
            'failed': 'فشل التوصيل',
            'returned': 'تم الإرجاع'
        };

        return labels[status] || status;
    }

    function getStatusBadgeClass(status) {
        const classes = {
            'pending': 'warning',
            'created': 'info',
            'shipped': 'primary',
            'in_transit': 'primary',
            'out_for_delivery': 'success',
            'delivered': 'success',
            'failed': 'danger',
            'returned': 'secondary'
        };

        return classes[status] || 'secondary';
    }

    function getStatusColorClass(status) {
        const colors = {
            'pending': 'warning',
            'created': 'info',
            'shipped': 'primary',
            'in_transit': 'primary',
            'out_for_delivery': 'success',
            'delivered': 'success',
            'failed': 'danger',
            'returned': 'secondary'
        };

        return colors[status] || 'secondary';
    }

    function getEventTitle(status) {
        const titles = {
            'pending': 'الشحنة قيد الانتظار',
            'created': 'تم إنشاء الشحنة',
            'shipped': 'تم شحن الشحنة',
            'in_transit': 'الشحنة في طريقها',
            'out_for_delivery': 'الشحنة خارج للتوصيل',
            'delivered': 'تم تسليم الشحنة',
            'failed': 'فشل التوصيل',
            'returned': 'تم إرجاع الشحنة'
        };

        return titles[status] || status;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ar-SA', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ar-SA', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('ar-SA', {
            style: 'currency',
            currency: 'SAR'
        }).format(amount);
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
</script>
@endpush
