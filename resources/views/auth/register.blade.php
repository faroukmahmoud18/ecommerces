{{-- I will assume a main layout file exists at layouts.app --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        {{-- Name --}}
                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        {{-- Register as Vendor Checkbox --}}
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="register_as_vendor" id="register_as_vendor" {{ old('register_as_vendor') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="register_as_vendor">
                                        {{ __('Register as a Vendor') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Vendor Name Field (optional, shown with JS) --}}
                        <div class="row mb-3" id="vendor-name-field" style="display: none;">
                            <label for="vendor_name" class="col-md-4 col-form-label text-md-end">{{ __('Shop Name') }}</label>
                            <div class="col-md-6">
                                <input id="vendor_name" type="text" class="form-control @error('vendor_name') is-invalid @enderror" name="vendor_name" value="{{ old('vendor_name') }}">
                                @error('vendor_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const vendorCheckbox = document.getElementById('register_as_vendor');
        const vendorNameField = document.getElementById('vendor-name-field');
        const vendorNameInput = document.getElementById('vendor_name');

        function toggleVendorField() {
            if (vendorCheckbox.checked) {
                vendorNameField.style.display = '';
                vendorNameInput.required = true;
            } else {
                vendorNameField.style.display = 'none';
                vendorNameInput.required = false;
            }
        }

        toggleVendorField(); // Check on page load
        vendorCheckbox.addEventListener('change', toggleVendorField);
    });
</script>
@endsection
