@extends('layouts.app')

@section('title', 'إتمام الطلب')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shipping-fast me-2"></i>
                        معلومات الشحن
                    </h5>
                </div>
                <div class="card-body">
                    <form id="checkout-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label">الاسم الكامل *</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_email" class="form-label">البريد الإلكتروني *</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_phone" class="form-label">رقم الهاتف *</label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_city" class="form-label">المدينة *</label>
                                <input type="text" class="form-control" id="customer_city" name="customer_city" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_country" class="form-label">البلد *</label>
                                <select class="form-select" id="customer_country" name="customer_country" required>
                                    <option value="">اختر البلد</option>
                                    <option value="Saudi Arabia" selected>المملكة العربية السعودية</option>
                                    <option value="UAE">الإمارات العربية المتحدة</option>
                                    <option value="Kuwait">الكويت</option>
                                    <option value="Qatar">قطر</option>
                                    <option value="Bahrain">البحرين</option>
                                    <option value="Oman">عُمان</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_state" class="form-label">المنطقة</label>
                                <input type="text" class="form-control" id="customer_state" name="customer_state">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="customer_address" class="form-label">العنوان الكامل *</label>
                            <textarea class="form-control" id="customer_address" name="customer_address" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="customer_postal_code" class="form-label">الرمز البريدي</label>
                            <input type="text" class="form-control" id="customer_postal_code" name="customer_postal_code">
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        طريقة الدفع
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($paymentGateways as $gateway)
                        <div class="col-md-4 mb-3">
                            <div class="payment-method">
                                <input type="radio" class="btn-check" name="payment_method" id="{{ $gateway['key'] }}" value="{{ $gateway['key'] }}" autocomplete="off">
                                <label class="btn btn-outline-primary w-100 p-3" for="{{ $gateway['key'] }}">
                                    <div class="d-flex flex-column align-items-center">
                                        @if($gateway['key'] === 'stripe')
                                            <i class="fab fa-stripe fa-2x mb-2"></i>
                                        @elseif($gateway['key'] === 'paypal')
                                            <i class="fab fa-paypal fa-2x mb-2"></i>
                                        @elseif($gateway['key'] === 'fawry')
                                            <i class="fas fa-mobile-alt fa-2x mb-2"></i>
                                        @else
                                            <i class="fas fa-credit-card fa-2x mb-2"></i>
                                        @endif
                                        <span>{{ $gateway['name'] }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Stripe Card Element -->
                    <div id="stripe-card-element" style="display: none;">
                        <div class="mt-3">
                            <label class="form-label">معلومات البطاقة</label>
                            <div id="card-element" class="form-control" style="height: 40px; padding: 10px;">
                                <!-- Stripe Elements will create form elements here -->
                            </div>
                            <div id="card-errors" role="alert" class="text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coupon Code -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tag me-2"></i>
                        كود الخصم
                    </h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" id="coupon_code" placeholder="أدخل كود الخصم">
                        <button class="btn btn-outline-primary" type="button" id="apply-coupon">
                            <i class="fas fa-check"></i> تطبيق
                        </button>
                    </div>
                    <div id="coupon-message" class="mt-2"></div>
                </div>
            </div>

            <!-- Terms -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                <label class="form-check-label" for="agree_terms">
                    أوافق على <a href="#" target="_blank">الشروط والأحكام</a> و <a href="#" target="_blank">سياسة الخصوصية</a>
                </label>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 2rem;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        ملخص الطلب
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Cart Items -->
                    @foreach($vendorItems as $vendorId => $items)
                        <div class="vendor-section mb-4">
                            <h6 class="text-muted border-bottom pb-2">
                                <i class="fas fa-store me-1"></i>
                                {{ $items->first()['vendor_name'] ?? 'متجر #' . $vendorId }}
                            </h6>
                            @foreach($items as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 text-sm">{{ $item['name'] }}</h6>
                                    <small class="text-muted">الكمية: {{ $item['quantity'] }}</small>
                                </div>
                                <span class="fw-bold">{{ number_format($item['price'] * $item['quantity'], 2) }} ر.س</span>
                            </div>
                            @endforeach
                        </div>
                    @endforeach

                    <hr>

                    <!-- Order Totals -->
                    <div class="d-flex justify-content-between mb-2">
                        <span>المجموع الفرعي:</span>
                        <span id="subtotal">{{ number_format($subtotal, 2) }} ر.س</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>الشحن:</span>
                        <span id="shipping">{{ number_format($shipping, 2) }} ر.س</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>الضريبة (15%):</span>
                        <span id="tax">{{ number_format($tax, 2) }} ر.س</span>
                    </div>

                    <div id="discount-section" style="display: none;">
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>الخصم:</span>
                            <span id="discount">-0.00 ر.س</span>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>الإجمالي:</span>
                        <span id="total">{{ number_format($total, 2) }} ر.س</span>
                    </div>

                    <button type="button" class="btn btn-primary w-100 mt-3" id="place-order">
                        <i class="fas fa-lock me-2"></i>
                        إتمام الطلب
                    </button>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            دفع آمن ومحمي
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">جاري المعالجة...</span>
                </div>
                <h5>جاري معالجة طلبك...</h5>
                <p class="text-muted mb-0">يرجى عدم إغلاق هذه الصفحة</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>

<script>
$(document).ready(function() {
    let stripe = null;
    let cardElement = null;
    let appliedCoupon = null;

    // Initialize Stripe
    if ($('input[name="payment_method"][value="stripe"]').length > 0) {
        stripe = Stripe('{{ config("services.stripe.key") }}');
        const elements = stripe.elements();

        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
            },
        });
    }

    // Payment method change
    $('input[name="payment_method"]').change(function() {
        const selectedMethod = $(this).val();

        // Hide all payment-specific elements
        $('#stripe-card-element').hide();

        // Show method-specific elements
        if (selectedMethod === 'stripe' && cardElement) {
            $('#stripe-card-element').show();
            cardElement.mount('#card-element');
        }
    });

    // Apply coupon
    $('#apply-coupon').click(function() {
        const couponCode = $('#coupon_code').val().trim();

        if (!couponCode) {
            showCouponMessage('يرجى إدخال كود الخصم', 'danger');
            return;
        }

        $.ajax({
            url: '{{ route("checkout.apply-coupon") }}',
            method: 'POST',
            data: {
                coupon_code: couponCode,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    appliedCoupon = response;
                    showCouponMessage(response.message, 'success');
                    updateOrderSummary(response.discount);

                    // Add remove button
                    $('#apply-coupon').html('<i class="fas fa-times"></i> إزالة').removeClass('btn-outline-primary').addClass('btn-outline-danger');
                    $('#apply-coupon').off('click').on('click', removeCoupon);
                } else {
                    showCouponMessage(response.message, 'danger');
                }
            },
            error: function() {
                showCouponMessage('حدث خطأ أثناء تطبيق الكود', 'danger');
            }
        });
    });

    // Remove coupon
    function removeCoupon() {
        $.ajax({
            url: '{{ route("checkout.remove-coupon") }}',
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                appliedCoupon = null;
                showCouponMessage(response.message, 'success');
                updateOrderSummary(0);

                $('#apply-coupon').html('<i class="fas fa-check"></i> تطبيق').removeClass('btn-outline-danger').addClass('btn-outline-primary');
                $('#apply-coupon').off('click').on('click', function() {
                    $('#apply-coupon').click();
                });
                $('#coupon_code').val('');
            }
        });
    }

    // Update order summary
    function updateOrderSummary(discount) {
        const subtotal = {{ $subtotal }};
        const shipping = {{ $shipping }};
        const tax = {{ $tax }};
        const total = subtotal + shipping + tax - discount;

        $('#total').text(formatMoney(total));

        if (discount > 0) {
            $('#discount').text('-' + formatMoney(discount));
            $('#discount-section').show();
        } else {
            $('#discount-section').hide();
        }
    }

    // Show coupon message
    function showCouponMessage(message, type) {
        $('#coupon-message').html(`<div class="alert alert-${type} alert-sm mb-0">${message}</div>`);
        setTimeout(() => {
            $('#coupon-message').html('');
        }, 5000);
    }

    // Format money
    function formatMoney(amount) {
        return new Intl.NumberFormat('ar-SA', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount) + ' ر.س';
    }

    // Place order
    $('#place-order').click(async function() {
        const form = $('#checkout-form')[0];
        const formData = new FormData(form);

        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Check payment method
        const paymentMethod = $('input[name="payment_method"]:checked').val();
        if (!paymentMethod) {
            alert('يرجى اختيار طريقة الدفع');
            return;
        }

        formData.append('payment_method', paymentMethod);

        // Handle Stripe payment
        if (paymentMethod === 'stripe' && stripe && cardElement) {
            $('#loadingModal').modal('show');

            const {token, error} = await stripe.createToken(cardElement);

            if (error) {
                $('#loadingModal').modal('hide');
                $('#card-errors').text(error.message);
                return;
            }

            formData.append('payment_method_id', token.id);
        }

        // Submit order
        $('#loadingModal').modal('show');

        $.ajax({
            url: '{{ route("checkout.process") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        $('#loadingModal').modal('hide');
                        alert(response.message);
                    }
                } else {
                    $('#loadingModal').modal('hide');
                    alert(response.message);
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMessages = [];
                    Object.values(errors).forEach(fieldErrors => {
                        errorMessages = errorMessages.concat(fieldErrors);
                    });
                    alert(errorMessages.join('
'));
                } else {
                    alert('حدث خطأ أثناء معالجة الطلب');
                }
            }
        });
    });

    // Update shipping cost on country change
    $('#customer_country, #customer_city').change(function() {
        const country = $('#customer_country').val();
        const city = $('#customer_city').val();

        if (country && city) {
            $.ajax({
                url: '{{ route("checkout.shipping-cost") }}',
                method: 'POST',
                data: {
                    country: country,
                    city: city,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#shipping').text(formatMoney(response.shipping_cost));
                    // Recalculate total
                    const discount = appliedCoupon ? appliedCoupon.discount : 0;
                    updateOrderSummary(discount);
                }
            });
        }
    });
});
</script>
@endsection