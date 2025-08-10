// Search JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize search functionality
    initSearch();
});

function initSearch() {
    // Setup search form
    setupSearchForm();

    // Setup search suggestions
    setupSearchSuggestions();

    // Setup search tags
    setupSearchTags();

    // Setup search filters
    setupSearchFilters();

    // Setup search view toggle
    setupSearchViewToggle();

    // Setup search pagination
    setupSearchPagination();
}

function setupSearchForm() {
    const searchForm = document.getElementById('search-form');

    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const query = document.getElementById('search-input').value.trim();

            if (!query) {
                showAlert('الرجاء إدخال مصطلح البحث', 'warning');
                return;
            }

            // Build search URL
            const params = new URLSearchParams();
            params.append('q', query);

            // Add filters
            const categoryFilter = document.getElementById('category-filter').value;
            const brandFilter = document.getElementById('brand-filter').value;
            const priceFilter = document.getElementById('price-filter').value;
            const sortFilter = document.getElementById('sort-filter').value;

            if (categoryFilter) {
                params.append('category', categoryFilter);
            }

            if (brandFilter) {
                params.append('brand', brandFilter);
            }

            if (priceFilter) {
                params.append('price', priceFilter);
            }

            if (sortFilter) {
                params.append('sort', sortFilter);
            }

            // Redirect to search results page
            window.location.href = '/search?' + params.toString();
        });
    }
}

function setupSearchSuggestions() {
    const searchInput = document.getElementById('search-input');

    if (searchInput) {
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);

            const query = this.value.trim();

            if (query.length < 2) {
                hideSearchSuggestions();
                return;
            }

            searchTimeout = setTimeout(() => {
                fetchSearchSuggestions(query);
            }, 300);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                hideSearchSuggestions();
            }
        });
    }
}

function fetchSearchSuggestions(query) {
    axios.get('/api/search/suggestions', {
        params: {
            q: query,
            limit: 5
        }
    })
    .then(response => {
        if (response.data.success) {
            showSearchSuggestions(response.data.suggestions);
        }
    })
    .catch(error => {
        console.error('Error fetching search suggestions:', error);
    });
}

function showSearchSuggestions(suggestions) {
    const searchContainer = document.querySelector('.search-container');
    let suggestionsElement = document.getElementById('search-suggestions');

    // Create suggestions element if it doesn't exist
    if (!suggestionsElement) {
        suggestionsElement = document.createElement('div');
        suggestionsElement.id = 'search-suggestions';
        suggestionsElement.className = 'search-suggestions';
        searchContainer.appendChild(suggestionsElement);
    }

    // Clear previous suggestions
    suggestionsElement.innerHTML = '';

    // Add new suggestions
    if (suggestions.length > 0) {
        suggestions.forEach(suggestion => {
            const suggestionItem = document.createElement('div');
            suggestionItem.className = 'suggestion-item';
            suggestionItem.textContent = suggestion;
            suggestionItem.addEventListener('click', function() {
                document.getElementById('search-input').value = suggestion;
                hideSearchSuggestions();
                document.getElementById('search-form').dispatchEvent(new Event('submit'));
            });
            suggestionsElement.appendChild(suggestionItem);
        });

        // Show suggestions
        suggestionsElement.style.display = 'block';
    } else {
        hideSearchSuggestions();
    }
}

function hideSearchSuggestions() {
    const suggestionsElement = document.getElementById('search-suggestions');
    if (suggestionsElement) {
        suggestionsElement.style.display = 'none';
    }
}

function setupSearchTags() {
    const searchTags = document.querySelectorAll('.search-tag');

    searchTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const searchTerm = this.textContent.trim();
            document.getElementById('search-input').value = searchTerm;
            document.getElementById('search-form').dispatchEvent(new Event('submit'));
        });
    });
}

function setupSearchFilters() {
    const filterSelects = document.querySelectorAll('#search-form select');

    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Trigger form submission
            document.getElementById('search-form').dispatchEvent(new Event('submit'));
        });
    });
}

function setupSearchViewToggle() {
    const viewButtons = document.querySelectorAll('[data-view]');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.getAttribute('data-view');

            // Update active button
            viewButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');

            // Update view
            const resultsContainer = document.getElementById('results-container');

            if (view === 'list') {
                resultsContainer.classList.add('list-view');
            } else {
                resultsContainer.classList.remove('list-view');
            }
        });
    });
}

function setupSearchPagination() {
    const paginationLinks = document.querySelectorAll('#pagination a');

    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const page = this.getAttribute('data-page');
            const params = new URLSearchParams(window.location.search);

            params.set('page', page);

            // Update URL
            window.history.pushState({}, '', '/search?' + params.toString());

            // Load new page
            loadSearchResults(params);
        });
    });
}

function loadSearchResults(params) {
    // Show loading state
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('results-container').style.display = 'none';

    // Fetch search results
    axios.get('/api/search', {
        params: params
    })
    .then(response => {
        if (response.data.success) {
            displaySearchResults(response.data);
        }
    })
    .catch(error => {
        console.error('Error fetching search results:', error);
        document.getElementById('loading-state').style.display = 'none';
        document.getElementById('no-results').style.display = 'block';
    });
}

function displaySearchResults(data) {
    const resultsCount = document.getElementById('results-count');
    const resultsContainer = document.getElementById('results-container');
    const pagination = document.getElementById('pagination');
    const noResults = document.getElementById('no-results');

    // Update results count
    resultsCount.textContent = data.totalHits;

    // Clear previous results
    resultsContainer.innerHTML = '';

    if (data.totalHits > 0) {
        // Show results container
        document.getElementById('search-results').style.display = 'block';
        noResults.style.display = 'none';

        // Display results
        data.results.products.forEach(product => {
            const productElement = createProductElement(product);
            resultsContainer.appendChild(productElement);
        });

        // Update pagination
        updatePagination(data.page, data.totalPages, pagination);
    } else {
        // Hide results container
        document.getElementById('search-results').style.display = 'none';
        noResults.style.display = 'block';
    }

    // Hide loading state
    document.getElementById('loading-state').style.display = 'none';
}

function createProductElement(product) {
    const template = document.getElementById('product-card-template');
    const element = document.createElement('div');

    let html = template.innerHTML
        .replace('{id}', product.id)
        .replace('{name}', product.name)
        .replace('{description}', product.description)
        .replace('{price}', product.price)
        .replace('{original_price}', product.original_price)
        .replace('{has_discount}', product.has_discount ? 'true' : 'false')
        .replace('{discount_percentage}', product.discount_percentage)
        .replace('{image}', product.image || '/images/product-placeholder.png')
        .replace('{in_stock}', product.in_stock ? 'true' : 'false')
        .replace('{rating}', product.rating)
        .replace('{reviews_count}', product.reviews_count)
        .replace('{category}', product.category)
        .replace('{brand}', product.brand);

    element.innerHTML = html;
    return element;
}

function updatePagination(currentPage, totalPages, paginationElement) {
    paginationElement.innerHTML = '';

    if (totalPages <= 1) {
        return;
    }

    // Previous button
    const prevDisabled = currentPage === 1 ? 'disabled' : '';
    const prevItem = document.createElement('li');
    prevItem.className = `page-item ${prevDisabled}`;
    prevItem.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">السابق</a>`;
    paginationElement.appendChild(prevItem);

    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const active = i === currentPage ? 'active' : '';
        const pageItem = document.createElement('li');
        pageItem.className = `page-item ${active}`;
        pageItem.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        paginationElement.appendChild(pageItem);
    }

    // Next button
    const nextDisabled = currentPage === totalPages ? 'disabled' : '';
    const nextItem = document.createElement('li');
    nextItem.className = `page-item ${nextDisabled}`;
    nextItem.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">التالي</a>`;
    paginationElement.appendChild(nextItem);
}

function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.setAttribute('role', 'alert');
    alert.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

    const container = document.querySelector('.container');
    container.insertBefore(alert, container.firstChild);

    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => {
            alert.remove();
        }, 150);
    }, 5000);
}