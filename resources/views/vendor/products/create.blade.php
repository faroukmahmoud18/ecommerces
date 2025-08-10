
@extends('vendor.layouts.app')

@section('title', 'إضافة منتج جديد')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">إضافة منتج جديد</h6>
            </div>

            <div class="card-body">
                <form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Basic Information --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">المعلومات الأساسية</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name">اسم المنتج</label>
                                        <input type="text" name="name" class="form-control" placeholder="اسم المنتج" required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="sku">رمز المنتج (SKU)</label>
                                        <input type="text" name="sku" class="form-control" placeholder="رمز المنتج">
                                        @error('sku')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description">وصف المنتج</label>
                                <textarea name="description" class="form-control" rows="5" placeholder="وصف تفصيلي للمنتج" required></textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="short_description">وصف قصير</label>
                                <textarea name="short_description" class="form-control" rows="2" placeholder="وصف قصير للمنتج"></textarea>
                                @error('short_description')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="price">السعر</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">{{ config('app.currency_symbol', 'ر.س') }}</span>
                                            <input type="number" name="price" class="form-control border-start-0" placeholder="0.00" step="0.01" min="0" required>
                                        </div>
                                        @error('price')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="compare_price">سعر مقارنة (اختياري)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">{{ config('app.currency_symbol', 'ر.س') }}</span>
                                            <input type="number" name="compare_price" class="form-control border-start-0" placeholder="0.00" step="0.01" min="0">
                                        </div>
                                        @error('compare_price')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="cost">التكلفة (اختياري)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">{{ config('app.currency_symbol', 'ر.س') }}</span>
                                            <input type="number" name="cost" class="form-control border-start-0" placeholder="0.00" step="0.01" min="0">
                                        </div>
                                        @error('cost')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">إدارة المخزون</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="quantity">الكمية المتوفرة</label>
                                        <input type="number" name="quantity" class="form-control" placeholder="0" min="0" required>
                                        @error('quantity')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="manage_inventory" id="manage_inventory" checked>
                                            <label class="form-check-label" for="manage_inventory">
                                                إدارة المخزون
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="allow_backorder" id="allow_backorder">
                                            <label class="form-check-label" for="allow_backorder">
                                                السماح بالطلب عند نفاد المخزون
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Categories --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">الفئات</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="categories">اختر فئات المنتج</label>
                                <select name="categories[]" class="form-select" multiple size="5" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">اضغط Ctrl+لتحديد فئات متعددة</small>
                                @error('categories')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Images --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">الصور</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="images">ارفع صور المنتج</label>
                                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                                <small class="form-text text-muted">يمكنك رفع عدة صور للمنتج. الصورة الأولى ستكون الصورة الرئيسية.</small>
                            </div>

                            <div id="image-preview" class="row">
                                <!-- Image previews will be added here dynamically -->
                            </div>
                        </div>
                    </div>

                    {{-- Options --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">خيارات المنتج</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                            <label class="form-check-label" for="is_active">
                                                نشط
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="featured" id="featured">
                                            <label class="form-check-label" for="featured">
                                                مميز
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="new_arrival" id="new_arrival">
                                            <label class="form-check-label" for="new_arrival">
                                                منتج جديد
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ المنتج
                            </button>
                            <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle image preview
        const imageInput = document.querySelector('input[name="images[]"]');
        const imagePreview = document.getElementById('image-preview');

        imageInput.addEventListener('change', function() {
            imagePreview.innerHTML = '';

            if (this.files) {
                Array.from(this.files).forEach((file, index) => {
                    const reader = new FileReader();

                    reader.addEventListener('load', function() {
                        const col = document.createElement('div');
                        col.className = 'col-md-3 mb-3';

                        col.innerHTML = `
                            <div class="card">
                                <img src="${this.result}" class="card-img-top" alt="Preview">
                                <div class="card-body">
                                    <p class="card-text text-center">${file.name}</p>
                                    <button type="button" class="btn btn-sm btn-danger remove-image">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;

                        imagePreview.appendChild(col);
                    });

                    reader.readAsDataURL(file);
                });
            }
        });

        // Handle remove image button
        imagePreview.addEventListener('click', function(e) {
            if (e.target.closest('.remove-image')) {
                e.target.closest('.col-md-3').remove();
            }
        });
    });
</script>
@endsection
