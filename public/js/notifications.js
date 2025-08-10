// Notifications management script
let currentPage = 1;
let unreadCurrentPage = 1;
let isLoading = false;
let isLoadingUnread = false;

// Initialize notifications page
function initializeNotifications() {
    loadNotifications();
    loadUnreadNotifications();
    loadStatistics();
    setupEventListeners();
    startNotificationPoll();
}

// Load all notifications
function loadNotifications() {
    if (isLoading) return;

    isLoading = true;
    showLoading('all');

    axios.get('/notifications', {
        params: {
            limit: 10,
            page: currentPage
        }
    })
    .then(response => {
        const data = response.data;
        hideLoading('all');

        if (data.data.data.length > 0) {
            displayNotifications(data.data.data, 'notifications-list');
            currentPage++;

            if (data.data.data.length < 10) {
                document.getElementById('load-more-container').classList.add('d-none');
            } else {
                document.getElementById('load-more-container').classList.remove('d-none');
            }
        } else {
            document.getElementById('notifications-list').classList.add('d-none');
            document.getElementById('no-notifications').classList.remove('d-none');
        }

        updateCounts(data.unread_count);
        isLoading = false;
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        hideLoading('all');
        showAlert('خطأ في تحميل الإشعارات', 'danger');
        isLoading = false;
    });
}

// Load unread notifications
function loadUnreadNotifications() {
    if (isLoadingUnread) return;

    isLoadingUnread = true;
    showLoading('unread');

    axios.get('/notifications', {
        params: {
            limit: 10,
            page: unreadCurrentPage,
            type: 'unread'
        }
    })
    .then(response => {
        const data = response.data;
        hideLoading('unread');

        if (data.data.data.length > 0) {
            displayNotifications(data.data.data, 'unread-notifications-list', true);
            unreadCurrentPage++;

            if (data.data.data.length < 10) {
                document.getElementById('load-more-unread-container').classList.add('d-none');
            } else {
                document.getElementById('load-more-unread-container').classList.remove('d-none');
            }
        } else {
            document.getElementById('unread-notifications-list').classList.add('d-none');
            document.getElementById('no-unread').classList.remove('d-none');
        }

        isLoadingUnread = false;
    })
    .catch(error => {
        console.error('Error loading unread notifications:', error);
        hideLoading('unread');
        showAlert('خطأ في تحميل الإشعارات غير المقروءة', 'danger');
        isLoadingUnread = false;
    });
}

// Display notifications in container
function displayNotifications(notifications, containerId, isUnread = false) {
    const container = document.getElementById(containerId);

    notifications.forEach(notification => {
        const notificationElement = createNotificationElement(notification, isUnread);
        container.appendChild(notificationElement);
    });

    container.classList.remove('d-none');
}

// Create notification element
function createNotificationElement(notification, isUnread = false) {
    const div = document.createElement('div');
    div.className = `notification-item border-bottom pb-3 mb-3 ${!notification.is_read ? 'unread' : ''}`;
    div.setAttribute('data-id', notification.id);

    const colorMap = {
        'order_placed': 'primary',
        'order_shipped': 'info',
        'order_delivered': 'success',
        'payment_completed': 'primary',
        'payment_failed': 'danger',
        'new_message': 'primary',
        'promotion': 'warning'
    };

    const iconMap = {
        'order_placed': 'fa-shopping-cart',
        'order_shipped': 'fa-truck',
        'order_delivered': 'fa-check-circle',
        'payment_completed': 'fa-credit-card',
        'payment_failed': 'fa-times-circle',
        'new_message': 'fa-envelope',
        'promotion': 'fa-tag'
    };

    const color = colorMap[notification.type] || 'secondary';
    const icon = iconMap[notification.type] || 'fa-bell';

    div.innerHTML = `
        <div class="d-flex">
            <div class="flex-shrink-0">
                <div class="avatar-sm bg-${color} rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas ${icon} text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-1">${notification.title}</h6>
                    <small class="text-muted">${formatTimeAgo(notification.created_at)}</small>
                </div>
                <p class="text-muted mb-2">${notification.message}</p>
                <div class="d-flex justify-content-between align-items-center">
                    ${notification.data && notification.data.action_url ? 
                        `<a href="${notification.data.action_url}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i>
                            عرض التفاصيل
                        </a>` : '<div></div>'
                    }
                    <div>
                        ${!notification.is_read ? 
                            `<button class="btn btn-sm btn-outline-primary mark-read-btn" title="وضع كمقروء">
                                <i class="fas fa-check"></i>
                            </button>` : ''
                        }
                        <button class="btn btn-sm btn-outline-danger delete-btn" title="حذف الإشعار">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add event listeners
    const markReadBtn = div.querySelector('.mark-read-btn');
    if (markReadBtn) {
        markReadBtn.addEventListener('click', () => markAsRead(notification.id, div));
    }

    const deleteBtn = div.querySelector('.delete-btn');
    deleteBtn.addEventListener('click', () => deleteNotification(notification.id, div));

    return div;
}

// Mark notification as read
function markAsRead(notificationId, element) {
    axios.put(`/notifications/${notificationId}/mark-read`)
        .then(response => {
            if (response.data.success) {
                element.classList.remove('unread');
                const markReadBtn = element.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
                updateUnreadCount();
                showAlert('تم وضع الإشعار كمقروء', 'success');
            }
        })
        .catch(error => {
            console.error('Error marking as read:', error);
            showAlert('خطأ في تحديث الإشعار', 'danger');
        });
}

// Delete notification
function deleteNotification(notificationId, element) {
    if (confirm('هل تريد حذف هذا الإشعار؟')) {
        axios.delete(`/notifications/${notificationId}`)
            .then(response => {
                if (response.data.success) {
                    element.remove();
                    updateUnreadCount();
                    showAlert('تم حذف الإشعار', 'success');
                }
            })
            .catch(error => {
                console.error('Error deleting notification:', error);
                showAlert('خطأ في حذف الإشعار', 'danger');
            });
    }
}

// Mark all as read
function markAllAsRead() {
    axios.put('/notifications/mark-all-read')
        .then(response => {
            if (response.data.success) {
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    const markReadBtn = item.querySelector('.mark-read-btn');
                    if (markReadBtn) {
                        markReadBtn.remove();
                    }
                });
                updateUnreadCount();
                showAlert(response.data.message, 'success');
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
            showAlert('خطأ في تحديث الإشعارات', 'danger');
        });
}

// Load statistics
function loadStatistics() {
    axios.get('/notifications/statistics')
        .then(response => {
            const stats = response.data.data;
            document.getElementById('stat-total').textContent = stats.total_count;
            document.getElementById('stat-unread').textContent = stats.unread_count;
            document.getElementById('stat-read').textContent = stats.total_count - stats.unread_count;
            // Calculate today's notifications (would need additional API endpoint)
            document.getElementById('stat-today').textContent = '0';
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

// Update counts
function updateCounts(unreadCount) {
    document.getElementById('unread-count').textContent = unreadCount;
    document.getElementById('all-count').textContent = unreadCount;
}

// Update unread count
function updateUnreadCount() {
    axios.get('/notifications/unread-count')
        .then(response => {
            updateCounts(response.data.count);
            loadStatistics();
        })
        .catch(error => {
            console.error('Error updating unread count:', error);
        });
}

// Show loading
function showLoading(type) {
    document.getElementById(`loading-${type}`).classList.remove('d-none');
}

// Hide loading
function hideLoading(type) {
    document.getElementById(`loading-${type}`).classList.add('d-none');
}

// Show alert
function showAlert(message, type = 'info') {
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

// Format time ago
function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMs = now - date;
    const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
    const diffInHours = Math.floor(diffInMinutes / 60);
    const diffInDays = Math.floor(diffInHours / 24);

    if (diffInMinutes < 1) {
        return 'الآن';
    } else if (diffInMinutes < 60) {
        return `منذ ${diffInMinutes} دقيقة`;
    } else if (diffInHours < 24) {
        return `منذ ${diffInHours} ساعة`;
    } else if (diffInDays < 30) {
        return `منذ ${diffInDays} يوم`;
    } else {
        return date.toLocaleDateString('ar-SA');
    }
}

// Setup event listeners
function setupEventListeners() {
    // Mark all as read button
    document.getElementById('mark-all-read').addEventListener('click', markAllAsRead);

    // Load more buttons
    document.getElementById('load-more').addEventListener('click', loadNotifications);
    document.getElementById('load-more-unread').addEventListener('click', loadUnreadNotifications);
}

// Start notification polling
function startNotificationPoll() {
    setInterval(updateUnreadCount, 30000); // Update every 30 seconds
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeNotifications);