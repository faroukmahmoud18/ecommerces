// Admin Search JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize analytics
    loadAnalytics();

    // Initialize indexes
    loadIndexes();

    // Initialize settings
    loadSettings();

    // Setup event listeners
    setupEventListeners();
});

function loadAnalytics() {
    // Show loading state
    document.getElementById('total-searches').textContent = '...';
    document.getElementById('unique-searches').textContent = '...';
    document.getElementById('avg-results').textContent = '...';
    document.getElementById('no-results').textContent = '...';

    // Get analytics data
    axios.get('/admin/api/search/analytics')
        .then(response => {
            const analytics = response.data.analytics;

            // Update metrics
            document.getElementById('total-searches').textContent = analytics.total_searches.toLocaleString();
            document.getElementById('unique-searches').textContent = analytics.unique_searches.toLocaleString();
            document.getElementById('avg-results').textContent = analytics.average_results_per_search.toFixed(1);
            document.getElementById('no-results').textContent = analytics.no_results_percentage.toFixed(1) + '%';

            // Update popular searches table
            const popularSearchesTable = document.getElementById('popular-searches');
            popularSearchesTable.innerHTML = '';

            analytics.popular_searches.forEach(search => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${search.term}</td>
                    <td>${search.count}</td>
                    <td>${search.conversions || '-'} (${search.conversion_rate || '-'}%)</td>
                `;
                popularSearchesTable.appendChild(row);
            });

            // Update popular filters table
            const popularFiltersTable = document.getElementById('popular-filters');
            popularFiltersTable.innerHTML = '';

            analytics.popular_filters.forEach(filter => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${filter.filter}</td>
                    <td>${filter.count}</td>
                `;
                popularFiltersTable.appendChild(row);
            });

            // Update search by hour chart
            updateSearchByHourChart(analytics.search_by_hour);
        })
        .catch(error => {
            console.error('Error loading analytics:', error);
            showAlert('فشل في تحليلات تحميل', 'danger');
        });
}

function updateSearchByHourChart(data) {
    const ctx = document.getElementById('search-by-hour-chart').getContext('2d');

    // Destroy existing chart if it exists
    if (window.searchByHourChart) {
        window.searchByHourChart.destroy();
    }

    // Create new chart
    window.searchByHourChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.hour),
            datasets: [{
                label: 'عدد عمليات البحث',
                data: data.map(item => item.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'حركة البحث حسب الوقت'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function loadIndexes() {
    // Show loading state
    const indexesList = document.getElementById('indexes-list');
    indexesList.innerHTML = '<tr><td colspan="6" class="text-center">جاري التحميل...</td></tr>';

    // Get indexes data
    axios.get('/admin/api/search/indexes')
        .then(response => {
            const indexes = response.data.indexes;

            // Update indexes table
            indexesList.innerHTML = '';

            indexes.forEach(index => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index.name}</td>
                    <td>${index.numberOfDocuments.toLocaleString()}</td>
                    <td>${formatFileSize(index.fileSize)}</td>
                    <td>${formatDate(index.lastUpdate)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info view-index-settings" data-index="${index.name}">
                            <i class="fas fa-cog"></i>
                        </button>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-warning reindex-index" data-index="${index.name}">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-danger delete-index" data-index="${index.name}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                indexesList.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error loading indexes:', error);
            showAlert('فشل في تحميل الفهارس', 'danger');
        });
}

function loadSettings() {
    // Get settings data
    axios.get('/admin/api/search/settings')
        .then(response => {
            const settings = response.data.settings;

            // Update form fields
            document.getElementById('search-results-per-page').value = settings.results_per_page || 20;
            document.getElementById('search-suggestions-limit').value = settings.suggestions_limit || 5;
            document.getElementById('search-highlight-length').value = settings.highlight_length || 20;
            document.getElementById('search-max-synonyms').value = settings.max_synonyms || 10;
            document.getElementById('search-stop-words').value = settings.stop_words ? settings.stop_words.join(', ') : '';
            document.getElementById('search-enable-analytics').checked = settings.enable_analytics !== false;
            document.getElementById('search-enable-suggestions').checked = settings.enable_suggestions !== false;
        })
        .catch(error => {
            console.error('Error loading settings:', error);
            showAlert('فشل في تحميل الإعدادات', 'danger');
        });
}

function setupEventListeners() {
    // Refresh indexes button
    document.getElementById('refresh-indexes').addEventListener('click', function() {
        loadIndexes();
        showAlert('تم تحديث الفهارس بنجاح', 'success');
    });

    // Search settings form
    document.getElementById('search-settings-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const settings = {
            results_per_page: parseInt(document.getElementById('search-results-per-page').value),
            suggestions_limit: parseInt(document.getElementById('search-suggestions-limit').value),
            highlight_length: parseInt(document.getElementById('search-highlight-length').value),
            max_synonyms: parseInt(document.getElementById('search-max-synonyms').value),
            stop_words: document.getElementById('search-stop-words').value.split(',').map(word => word.trim()).filter(word => word),
            enable_analytics: document.getElementById('search-enable-analytics').checked,
            enable_suggestions: document.getElementById('search-enable-suggestions').checked,
        };

        // Save settings
        axios.post('/admin/api/search/settings', { settings })
            .then(response => {
                showAlert('تم حفظ الإعدادات بنجاح', 'success');
            })
            .catch(error => {
                console.error('Error saving settings:', error);
                showAlert('فشل في حفظ الإعدادات', 'danger');
            });
    });

    // Reset settings button
    document.getElementById('reset-settings').addEventListener('click', function() {
        if (confirm('هل أنت متأكد من إعادة تعيين الإعدادات إلى القيم الافتراضية؟')) {
            loadSettings();
            showAlert('تم إعادة تعيين الإعدادات', 'info');
        }
    });

    // View index settings buttons
    document.querySelectorAll('.view-index-settings').forEach(button => {
        button.addEventListener('click', function() {
            const indexName = this.getAttribute('data-index');
            loadIndexSettings(indexName);
        });
    });

    // Reindex index buttons
    document.querySelectorAll('.reindex-index').forEach(button => {
        button.addEventListener('click', function() {
            const indexName = this.getAttribute('data-index');
            showReindexModal(indexName);
        });
    });

    // Delete index buttons
    document.querySelectorAll('.delete-index').forEach(button => {
        button.addEventListener('click', function() {
            const indexName = this.getAttribute('data-index');
            showDeleteIndexModal(indexName);
        });
    });

    // Save index settings button
    document.getElementById('save-index-settings').addEventListener('click', function() {
        const indexName = document.getElementById('index-settings-name').textContent;
        const settings = {
            searchableAttributes: document.getElementById('index-searchable-attributes').value.split(',').map(attr => attr.trim()),
            filterableAttributes: document.getElementById('index-filterable-attributes').value.split(',').map(attr => attr.trim()),
            sortableAttributes: document.getElementById('index-sortable-attributes').value.split(',').map(attr => attr.trim()),
            displayedAttributes: document.getElementById('index-displayed-attributes').value.split(',').map(attr => attr.trim()),
            rankingRules: document.getElementById('index-ranking-rules').value.split(',').map(rule => rule.trim()),
        };

        // Save index settings
        axios.post(`/admin/api/search/indexes/${indexName}/settings`, { settings })
            .then(response => {
                $('#index-settings-modal').modal('hide');
                showAlert('تم حفظ إعدادات الفهرس بنجاح', 'success');
            })
            .catch(error => {
                console.error('Error saving index settings:', error);
                showAlert('فشل في حفظ إعدادات الفهرس', 'danger');
            });
    });

    // Confirm reindex button
    document.getElementById('confirm-reindex').addEventListener('click', function() {
        const indexName = document.getElementById('reindex-index-name').textContent;

        // Reindex index
        axios.post(`/admin/api/search/indexes/${indexName}/reindex`)
            .then(response => {
                $('#reindex-modal').modal('hide');
                showAlert('تم بدء إعادة الفهرسة بنجاح', 'success');
                loadIndexes();
            })
            .catch(error => {
                console.error('Error reindexing:', error);
                showAlert('فشل في بدء إعادة الفهرسة', 'danger');
            });
    });

    // Confirm delete index button
    document.getElementById('confirm-delete-index').addEventListener('click', function() {
        const indexName = document.getElementById('delete-index-name').textContent;

        // Delete index
        axios.delete(`/admin/api/search/indexes/${indexName}`)
            .then(response => {
                $('#delete-index-modal').modal('hide');
                showAlert('تم حذف الفهرس بنجاح', 'success');
                loadIndexes();
            })
            .catch(error => {
                console.error('Error deleting index:', error);
                showAlert('فشل في حذف الفهرس', 'danger');
            });
    });
}

function loadIndexSettings(indexName) {
    // Get index settings
    axios.get(`/admin/api/search/indexes/${indexName}/settings`)
        .then(response => {
            const settings = response.data.settings;

            // Update modal title
            document.getElementById('index-settings-name').textContent = indexName;

            // Update form fields
            document.getElementById('index-searchable-attributes').value = settings.searchableAttributes ? settings.searchableAttributes.join(', ') : '';
            document.getElementById('index-filterable-attributes').value = settings.filterableAttributes ? settings.filterableAttributes.join(', ') : '';
            document.getElementById('index-sortable-attributes').value = settings.sortableAttributes ? settings.sortableAttributes.join(', ') : '';
            document.getElementById('index-displayed-attributes').value = settings.displayedAttributes ? settings.displayedAttributes.join(', ') : '';
            document.getElementById('index-ranking-rules').value = settings.rankingRules ? settings.rankingRules.join(', ') : '';

            // Show modal
            $('#index-settings-modal').modal('show');
        })
        .catch(error => {
            console.error('Error loading index settings:', error);
            showAlert('فشل في تحميل إعدادات الفهرس', 'danger');
        });
}

function showReindexModal(indexName) {
    // Update modal content
    document.getElementById('reindex-index-name').textContent = indexName;

    // Show modal
    $('#reindex-modal').modal('show');
}

function showDeleteIndexModal(indexName) {
    // Update modal content
    document.getElementById('delete-index-name').textContent = indexName;

    // Show modal
    $('#delete-index-modal').modal('show');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('ar-SA');
}

function showAlert(message, type) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.setAttribute('role', 'alert');
    alert.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

    // Insert alert at top of content
    const content = document.querySelector('.content');
    content.insertBefore(alert, content.firstChild);

    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => {
            alert.remove();
        }, 150);
    }, 5000);
}