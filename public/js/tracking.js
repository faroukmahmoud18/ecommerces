// Shipping Tracking JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tracking functionality
    initTracking();
});

function initTracking() {
    // Setup shipment status update buttons
    document.querySelectorAll('.update-status').forEach(button => {
        button.addEventListener('click', function() {
            const shipmentId = this.getAttribute('data-id');
            showUpdateStatusModal(shipmentId);
        });
    });

    // Setup provider edit buttons
    document.querySelectorAll('.edit-provider').forEach(button => {
        button.addEventListener('click', function() {
            const providerId = this.getAttribute('data-id');
            loadProviderForm(providerId);
        });
    });

    // Setup zone edit buttons
    document.querySelectorAll('.edit-zone').forEach(button => {
        button.addEventListener('click', function() {
            const zoneId = this.getAttribute('data-id');
            loadZoneForm(zoneId);
        });
    });

    // Setup rate edit buttons
    document.querySelectorAll('.edit-rate').forEach(button => {
        button.addEventListener('click', function() {
            const rateId = this.getAttribute('data-id');
            loadRateForm(rateId);
        });
    });

    // Setup delete buttons
    setupDeleteButtons();

    // Setup filters
    setupFilters();

    // Setup search
    setupSearch();

    // Setup form submissions
    setupForms();
}

function showUpdateStatusModal(shipmentId) {
    const modal = $('#update-status-modal');

    // Load shipment data
    axios.get(`/admin/api/shipments/${shipmentId}`)
        .then(response => {
            const shipment = response.data.shipment;

            // Update form
            modal.find('form').attr('action', `/admin/shipments/${shipmentId}/status`);
            modal.find('#status-select').val(shipment.status);
            modal.find('#location-input').val(shipment.trackingEvents[0]?.event_location || '');
            modal.find('#description-input').val(shipment.trackingEvents[0]?.event_description || '');

            // Show modal
            modal.modal('show');
        })
        .catch(error => {
            showAlert('فشل تحميل بيانات الشحنة', 'danger');
        });
}

function loadProviderForm(providerId) {
    const modal = $('#add-provider-modal');

    // Load provider data
    axios.get(`/admin/api/shipping-providers/${providerId}`)
        .then(response => {
            const provider = response.data.provider;

            // Update form
            modal.find('form').attr('action', `/admin/shipping-providers/${providerId}`);
            modal.find('input[name="name"]').val(provider.name);
            modal.find('input[name="code"]').val(provider.code);
            modal.find('textarea[name="description"]').val(provider.description || '');
            modal.find('input[name="logo"]').val(provider.logo || '');
            modal.find('select[name="is_active"]').val(provider.is_active ? 1 : 0);
            modal.find('input[name="priority"]').val(provider.priority);

            // Update config fields
            const config = provider.config || {};
            modal.find('input[name="config[account_number]"]').val(config.account_number || '');
            modal.find('input[name="config[account_pin]"]').val(config.account_pin || '');
            modal.find('input[name="config[username]"]').val(config.username || '');
            modal.find('input[name="config[password]"]').val(config.password || '');

            // Show modal
            modal.modal('show');
        })
        .catch(error => {
            showAlert('فشل تحميل بيانات مزود الشحن', 'danger');
        });
}

function loadZoneForm(zoneId) {
    const modal = $('#add-zone-modal');

    // Load zone data
    axios.get(`/admin/api/shipping-zones/${zoneId}`)
        .then(response => {
            const zone = response.data.zone;

            // Update form
            modal.find('form').attr('action', `/admin/shipping-zones/${zoneId}`);
            modal.find('select[name="provider_id"]').val(zone.provider_id);
            modal.find('input[name="name"]').val(zone.name);
            modal.find('select[name="country"]').val(zone.country);
            modal.find('select[name="state"]').val(zone.state || '');
            modal.find('input[name="city"]').val(zone.city || '');
            modal.find('input[name="zip_from"]').val(zone.zip_from || '');
            modal.find('input[name="zip_to"]').val(zone.zip_to || '');
            modal.find('input[name="estimated_delivery_days"]').val(zone.estimated_delivery_days);
            modal.find('select[name="is_active"]').val(zone.is_active ? 1 : 0);
            modal.find('input[name="priority"]').val(zone.priority);

            // Show modal
            modal.modal('show');
        })
        .catch(error => {
            showAlert('فشل تحميل بيانات المنطقة', 'danger');
        });
}

function loadRateForm(rateId) {
    const modal = $('#add-rate-modal');

    // Load rate data
    axios.get(`/admin/api/shipping-rates/${rateId}`)
        .then(response => {
            const rate = response.data.rate;

            // Update form
            modal.find('form').attr('action', `/admin/shipping-rates/${rateId}`);
            modal.find('select[name="zone_id"]').val(rate.zone_id);
            modal.find('input[name="name"]').val(rate.name);
            modal.find('textarea[name="description"]').val(rate.description || '');
            modal.find('input[name="min_weight"]').val(rate.min_weight);
            modal.find('input[name="max_weight"]').val(rate.max_weight);
            modal.find('input[name="min_order_amount"]').val(rate.min_order_amount || '');
            modal.find('input[name="max_order_amount"]').val(rate.max_order_amount || '');
            modal.find('input[name="rate"]').val(rate.rate);
            modal.find('input[name="free_shipping_threshold"]').val(rate.free_shipping_threshold || '');
            modal.find('input[name="handling_fee"]').val(rate.handling_fee);
            modal.find('input[name="tax_rate"]').val(rate.tax_rate);
            modal.find('select[name="is_active"]').val(rate.is_active ? 1 : 0);
            modal.find('input[name="priority"]').val(rate.priority);

            // Show modal
            modal.modal('show');
        })
        .catch(error => {
            showAlert('فشل تحميل بيانات السعر', 'danger');
        });
}

function setupDeleteButtons() {
    document.querySelectorAll('.delete-provider').forEach(button => {
        button.addEventListener('click', function() {
            const providerId = this.getAttribute('data-id');
            confirmDelete(`admin/shipping-providers/${providerId}`, 'مزود الشحن');
        });
    });

    document.querySelectorAll('.delete-zone').forEach(button => {
        button.addEventListener('click', function() {
            const zoneId = this.getAttribute('data-id');
            confirmDelete(`admin/shipping-zones/${zoneId}`, 'منطقة الشحن');
        });
    });

    document.querySelectorAll('.delete-rate').forEach(button => {
        button.addEventListener('click', function() {
            const rateId = this.getAttribute('data-id');
            confirmDelete(`admin/shipping-rates/${rateId}`, 'سعر الشحن');
        });
    });
}

function setupFilters() {
    // Provider filter
    document.getElementById('provider-filter').addEventListener('change', function() {
        const providerId = this.value;
        const rows = document.querySelectorAll('#zones tbody tr');

        rows.forEach(row => {
            const providerName = row.querySelector('td:nth-child(1)').textContent;

            if (providerId === '' || providerName.includes(this.options[this.selectedIndex].text)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Zone filter
    document.getElementById('zone-filter').addEventListener('change', function() {
        const zoneId = this.value;
        const rows = document.querySelectorAll('#rates tbody tr');

        rows.forEach(row => {
            if (zoneId === '' || row.querySelector('td:nth-child(1)').textContent.includes(this.options[this.selectedIndex].text)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Weight filter
    document.getElementById('weight-filter').addEventListener('change', function() {
        const weightRange = this.value;
        const rows = document.querySelectorAll('#rates tbody tr');

        rows.forEach(row => {
            const weightRangeText = row.querySelector('td:nth-child(3)').textContent;

            if (weightRange === '' || 
                (weightRange === '0-1' && weightRangeText.includes('حتى 1')) ||
                (weightRange === '1-5' && weightRangeText.includes('1-5')) ||
                (weightRange === '5-10' && weightRangeText.includes('5-10')) ||
                (weightRange === '10+' && weightRangeText.includes('أكثر من'))) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Status filter
    document.getElementById('status-filter').addEventListener('change', function() {
        const status = this.value;
        const rows = document.querySelectorAll('#shipments tbody tr');

        rows.forEach(row => {
            if (status === '' || row.querySelector('td:nth-child(7) span').textContent.includes(getStatusLabel(status))) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}

function setupSearch() {
    document.getElementById('shipment-search').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#shipments tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();

            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}

function setupForms() {
    // Add provider form
    document.getElementById('add-provider-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const jsonData = {};

        // Convert FormData to JSON
        for (const [key, value] of formData.entries()) {
            if (key.startsWith('config[') && key.endsWith(']')) {
                const configKey = key.match(/\[(.*?)\]/)[1];
                if (!jsonData.config) jsonData.config = {};
                jsonData.config[configKey] = value;
            } else {
                jsonData[key] = value;
            }
        }

        // Submit data
        axios.post(this.getAttribute('action'), jsonData)
            .then(response => {
                showAlert('تم حفظ مزود الشحن بنجاح', 'success');
                location.reload();
            })
            .catch(error => {
                showAlert('فشل حفظ مزود الشحن', 'danger');
            });
    });

    // Add zone form
    document.getElementById('add-zone-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const jsonData = {};

        // Convert FormData to JSON
        for (const [key, value] of formData.entries()) {
            jsonData[key] = value;
        }

        // Submit data
        axios.post(this.getAttribute('action'), jsonData)
            .then(response => {
                showAlert('تم حفظ منطقة الشحن بنجاح', 'success');
                location.reload();
            })
            .catch(error => {
                showAlert('فشل حفظ منطقة الشحن', 'danger');
            });
    });

    // Add rate form
    document.getElementById('add-rate-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const jsonData = {};

        // Convert FormData to JSON
        for (const [key, value] of formData.entries()) {
            jsonData[key] = value;
        }

        // Submit data
        axios.post(this.getAttribute('action'), jsonData)
            .then(response => {
                showAlert('تم حفظ سعر الشحن بنجاح', 'success');
                location.reload();
            })
            .catch(error => {
                showAlert('فشل حفظ سعر الشحن', 'danger');
            });
    });

    // Update status form
    document.querySelector('#update-status-modal form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const jsonData = {};

        // Convert FormData to JSON
        for (const [key, value] of formData.entries()) {
            jsonData[key] = value;
        }

        // Submit data
        axios.post(this.getAttribute('action'), jsonData)
            .then(response => {
                showAlert('تم تحديث حالة الشحنة بنجاح', 'success');
                $('#update-status-modal').modal('hide');
                location.reload();
            })
            .catch(error => {
                showAlert('فشل تحديث حالة الشحنة', 'danger');
            });
    });
}

function confirmDelete(url, itemName) {
    if (confirm(`هل أنت متأكد من حذف ${itemName}؟`)) {
        axios.delete(url)
            .then(response => {
                showAlert('تم الحذف بنجاح', 'success');
                location.reload();
            })
            .catch(error => {
                showAlert('فشل عملية الحذف', 'danger');
            });
    }
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);

    // Auto remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
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
        'out_for_delivery': 'primary',
        'delivered': 'success',
        'failed': 'danger',
        'returned': 'secondary'
    };

    return classes[status] || 'secondary';
}