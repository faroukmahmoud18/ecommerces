@extends('layouts.app')

@section('title', 'البحث المتقدم')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Search Header -->
            <div class="text-center mb-5">
                <h1 class="mb-3">
                    <i class="fas fa-search me-2"></i>
                    البحث المتقدم
                </h1>
                <p class="text-muted">ابحث عن المنتجات التي تريدها بسهولة</p>
            </div>

            <!-- Search Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="search-form" class="search-form">
                        <div class="input-group input-group-lg">
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="search-input" 
                                   placeholder="ابحث عن منتجات، علامات تجارية، فئات..."
                                   autofocus>
                            <button class="btn btn-primary btn-lg" type="submit">
                                <i class="fas fa-search me-2"></i> بحث
                            </button>
                        </div>

                        <!-- Search Filters -->
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="category-filter">الفئة</label>
                                    <select class="form-control" id="category-filter">
                                        <option value="">كل الفئات</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="brand-filter">العلامة التجارية</label>
                                    <select class="form-control" id="brand-filter">
                                        <option value="">كل العلامات التجارية</option>
                                        @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="price-filter">السعر</label>
                                    <select class="form-control" id="price-filter">
                                        <option value="">كل الأسعار</option>
                                        <option value="0-50">حتى 50 ر.س</option>
                                        <option value="50-100">من 50 إلى 100 ر.س</option>
                                        <option value="100-500">من 100 إلى 500 ر.س</option>
                                        <option value="500+">أكثر من 500 ر.س</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sort-filter">الترتيب حسب</label>
                                    <select class="form-control" id="sort-filter">
                                        <option value="relevance">الأكثر صلة</option>
                                        <option value="price-asc">السعر: من الأقل للأعلى</option>
                                        <option value="price-desc">السعر: من الأعلى للأقل</option>
                                        <option value="newest">الأحدث</option>
                                        <option value="popular">الأكثر مبيعاً</option>
                                        <option value="rating">التقييم</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Results -->
            <div id="search-results" style="display: none;">
                <!-- Results Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        نتائج البحث (<span id="results-count">0</span>)
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-2">عرض:</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-view="grid">
                                <i class="fas fa-th"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-view="list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="loading-state" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري البحث...</span>
                    </div>
                    <p class="mt-2 text-muted">جاري البحث عن المنتجات...</p>
                </div>

                <!-- Results Container -->
                <div id="results-container">
                    <!-- Search results will be loaded here -->
                </div>

                <!-- Pagination -->
                <div id="pagination" class="d-flex justify-content-center mt-4">
                    <!-- Pagination will be loaded here -->
                </div>
            </div>

            <!-- No Results -->
            <div id="no-results" class="text-center py-5" style="display: none;">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">لم يتم العثور على نتائج</h5>
                <p class="text-muted">جرب استخدام كلمات مختلفة أو تصفية أقل تقييداً</p>

                <!-- Popular Searches -->
                <div class="mt-4">
                    <h6 class="text-muted mb-3">مصطلحات البحث الشائعة:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($popularSearches as $search)
                        <a href="#" class="badge bg-light text-dark text-decoration-none search-tag">
                            {{ $search }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Card Template -->
<template id="product-card-template">
    <div class="product-card">
        <div class="card">
            <div class="position-relative">
                <img src="{image}" class="card-img-top" alt="{name}">
                @if({has_discount})
                <div class="position-absolute top-0 start-0 m-2">
                    <span class="badge bg-danger">خصم {discount_percentage}%</span>
                </div>
                @endif
                @if({in_stock})
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge bg-success">متوفر</span>
                </div>
                @else
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge bg-secondary">غير متوفر</span>
                </div>
                @endif
            </div>
            <div class="card-body">
                <h6 class="card-title">
                    <a href="/products/{id}" class="text-decoration-none">{name}</a>
                </h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if({has_discount})
                        <span class="text-muted text-decoration-line-through">{original_price} ر.س</span>
                        <span class="text-danger ms-2">{price} ر.س</span>
                        @else
                        <span class="text-primary">{price} ر.س</span>
                        @endif
                    </div>
                    <div>
                        @if({rating} > 0)
                        <div class="rating">
                            @for($i = 1; $i <= 5; $i++)
                            @if($i <= {rating})
                            <i class="fas fa-star text-warning"></i>
                            @else
                            <i class="far fa-star text-muted"></i>
                            @endif
                            @endfor
                            <span class="text-muted ms-1">({reviews_count})</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-light text-dark">{category}</span>
                    <span class="badge bg-light text-dark">{brand}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Product List Item Template -->
<template id="product-list-item-template">
    <div class="product-list-item">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <img src="{image}" class="img-fluid rounded" alt="{name}">
                    </div>
                    <div class="col-md-7">
                        <h6 class="card-title">
                            <a href="/products/{id}" class="text-decoration-none">{name}</a>
                        </h6>
                        <p class="card-text text-muted small">{description}</p>
                        <div>
                            <span class="badge bg-light text-dark">{category}</span>
                            <span class="badge bg-light text-dark">{brand}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                @if({has_discount})
                                <div>
                                    <span class="text-muted text-decoration-line-through">{original_price} ر.س</span>
                                    <span class="text-danger ms-2">{price} ر.س</span>
                                </div>
                                @else
                                <span class="text-primary">{price} ر.س</span>
                                @endif

                                @if({rating} > 0)
                                <div class="rating mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                    @if($i <= {rating})
                                    <i class="fas fa-star text-warning"></i>
                                    @else
                                    <i class="far fa-star text-muted"></i>
                                    @endif
                                    @endfor
                                    <span class="text-muted ms-1">({reviews_count})</span>
                                </div>
                                @endif
                            </div>
                            <div>
                                @if({in_stock})
                                <span class="badge bg-success">متوفر</span>
                                @else
                                <span class="badge bg-secondary">غير متوفر</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script src="{{ asset('js/search.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality
    initSearch();
});

function initSearch() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    const noResults = document.getElementById('no-results');
    const resultsContainer = document.getElementById('results-container');
    const resultsCount = document.getElementById('results-count');
    const loadingState = document.getElementById('loading-state');
    const pagination = document.getElementById('pagination');

    // Handle search form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const query = searchInput.value.trim();

        if (!query) {
            showAlert('الرجاء إدخال مصطلح للبحث', 'warning');
            return;
        }

        performSearch(query);
    });

    // Handle view change
    document.querySelectorAll('[data-view]').forEach(button => {
        button.addEventListener('click', function() {
            const view = this.getAttribute('data-view');

            // Update active button
            document.querySelectorAll('[data-view]').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');

            // Update view class
            resultsContainer.className = view === 'list' ? 'list-view' : 'grid-view';
        });
    });

    // Handle filter changes
    document.querySelectorAll('#category-filter, #brand-filter, #price-filter, #sort-filter').forEach(select => {
        select.addEventListener('change', function() {
            const query = searchInput.value.trim();

            if (query) {
                performSearch(query);
            }
        });
    });

    // Handle popular search tags
    document.querySelectorAll('.search-tag').forEach(tag => {
        tag.addEventListener('click', function(e) {
            e.preventDefault();

            searchInput.value = this.textContent;
            performSearch(this.textContent);
        });
    });

    // Perform search
    function performSearch(query, page = 1) {
        // Show loading state
        searchResults.style.display = 'block';
        noResults.style.display = 'none';
        loadingState.style.display = 'block';
        resultsContainer.innerHTML = '';
        pagination.innerHTML = '';

        // Prepare search parameters
        const params = new URLSearchParams();
        params.append('q', query);

        // Add filters
        const categoryFilter = document.getElementById('category-filter').value;
        const brandFilter = document.getElementById('brand-filter').value;
        const priceFilter = document.getElementById('price-filter').value;
        const sortFilter = document.getElementById('sort-filter').value;

        if (categoryFilter) params.append('category', categoryFilter);
        if (brandFilter) params.append('brand', brandFilter);
        if (priceFilter) {
            const [min, max] = priceFilter.split('-');
            if (min) params.append('min_price', min);
            if (max && max !== '+') params.append('max_price', max);
        }

        // Add sorting
        if (sortFilter && sortFilter !== 'relevance') {
            let sort = '';

            switch (sortFilter) {
                case 'price-asc':
                    sort = 'price:asc';
                    break;
                case 'price-desc':
                    sort = 'price:desc';
                    break;
                case 'newest':
                    sort = 'created_at:desc';
                    break;
                case 'popular':
                    sort = 'popularity:desc';
                    break;
                case 'rating':
                    sort = 'rating:desc';
                    break;
            }

            if (sort) {
                params.append('sort', sort);
            }
        }

        // Add pagination
        if (page > 1) {
            params.append('page', page);
        }

        // Make API request
        axios.get('/api/search?' + params.toString())
            .then(response => {
                const data = response.data;

                if (data.success) {
                    // Update results count
                    resultsCount.textContent = data.totalHits;

                    // Display results
                    if (data.totalHits > 0) {
                        displayResults(data.results, data.query);
                        displayPagination(data.totalPages, page);
                    } else {
                        displayNoResults(data.query);
                    }
                } else {
                    displayNoResults(data.query);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                showAlert('حدث خطأ أثناء البحث', 'danger');
                displayNoResults(query);
            })
            .finally(() => {
                loadingState.style.display = 'none';
            });
    }

    // Display search results
    function displayResults(results, query) {
        resultsContainer.innerHTML = '';

        // Check if we have products in results
        let hasProducts = false;

        for (const [index, hits] of Object.entries(results)) {
            if (hits.length > 0) {
                hasProducts = true;
                break;
            }
        }

        if (!hasProducts) {
            displayNoResults(query);
            return;
        }

        // Display products
        for (const [index, hits] of Object.entries(results)) {
            hits.forEach(hit => {
                // Use the appropriate template based on the active view
                const templateId = document.querySelector('[data-view].active').getAttribute('data-view') === 'list' 
                    ? 'product-list-item-template' 
                    : 'product-card-template';

                const template = document.getElementById(templateId);
                const productHtml = template.innerHTML
                    .replace('{id}', hit.id)
                    .replace('{name}', hit.name)
                    .replace('{description}', hit.description || '')
                    .replace('{image}', hit.image || '/images/product-placeholder.png')
                    .replace('{price}', hit.price)
                    .replace('{original_price}', hit.original_price)
                    .replace('{has_discount}', hit.has_discount ? 'true' : 'false')
                    .replace('{discount_percentage}', hit.discount_percentage)
                    .replace('{in_stock}', hit.in_stock ? 'true' : 'false')
                    .replace('{rating}', hit.rating)
                    .replace('{reviews_count}', hit.reviews_count)
                    .replace('{category}', hit.category)
                    .replace('{brand}', hit.brand);

                const productElement = document.createElement('div');
                productElement.innerHTML = productHtml;

                if (templateId === 'product-card-template') {
                    resultsContainer.appendChild(productElement.firstElementChild);
                } else {
                    resultsContainer.appendChild(productElement.firstElementChild);
                }
            });
        }
    }

    // Display no results message
    function displayNoResults(query) {
        searchResults.style.display = 'block';
        noResults.style.display = 'block';
        resultsContainer.innerHTML = '';
        pagination.innerHTML = '';

        // Update no results message
        const noResultsTitle = noResults.querySelector('h5');
        noResultsTitle.textContent = `لم يتم العثور على نتائج لـ "${query}"`;
    }

    // Display pagination
    function displayPagination(totalPages, currentPage) {
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let paginationHtml = '<nav><ul class="pagination">';

        // Previous button
        paginationHtml += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                paginationHtml += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next button
        paginationHtml += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;

        paginationHtml += '</ul></nav>';

        pagination.innerHTML = paginationHtml;

        // Add click handlers to pagination links
        pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                const page = parseInt(this.getAttribute('data-page'));

                if (page && page !== currentPage) {
                    const query = searchInput.value.trim();
                    performSearch(query, page);
                }
            });
        });
    }
}
</script>
@endpush

@push('styles')
<style>
/* Search Page Styles */
.search-form {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.search-form .form-control {
    border-radius: 0.5rem 0 0 0.5rem;
}

.search-form .btn {
    border-radius: 0 0.5rem 0.5rem 0;
}

/* Search Results Styles */
.grid-view {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.list-view .product-list-item {
    margin-bottom: 1rem;
}

.product-card {
    transition: transform 0.2s;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-card .card {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card .card-img-top {
    height: 200px;
    object-fit: cover;
}

.product-card .card-body {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.product-card .card-title {
    font-size: 1rem;
    margin-bottom: 0.5rem;
    height: 2.5rem;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-card .card-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.rating {
    font-size: 0.875rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .grid-view {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .product-card .card-img-top {
        height: 150px;
    }
}

/* Search Tags Styles */
.search-tag {
    transition: all 0.2s;
}

.search-tag:hover {
    background-color: #e9ecef;
    transform: translateY(-2px);
}

/* Loading Animation */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    width: 3rem;
    height: 3rem;
    border: 0.25rem solid #f3f3f3;
    border-top: 0.25rem solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush
