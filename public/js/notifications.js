/**
 * Real-time Notifications System
 * 
 * This file handles:
 * - Notification bell icon with unread count
 * - Toast notifications for real-time updates
 * - Notification dropdown list
 * - AJAX polling for new notifications
 * - Mark as read/unread functionality
 */

(function($) {
    'use strict';

    const NotificationManager = {
        config: {
            pollInterval: 30000, // 30 seconds
            unreadCountUrl: '/notifications/unread-count',
            recentNotificationsUrl: '/notifications/recent',
            markReadUrl: '/notifications/{id}/read',
            markAllReadUrl: '/notifications/mark-all-read',
            deleteUrl: '/notifications/{id}',
            toastDuration: 5000, // 5 seconds
            maxToasts: 3
        },

        state: {
            pollTimer: null,
            lastNotificationId: null,
            activeToasts: 0
        },

        /**
         * Initialize the notification system
         */
        init: function() {
            this.setupBellIcon();
            this.setupDropdown();
            this.setupEventHandlers();
            this.startPolling();
            this.loadInitialNotifications();
        },

        /**
         * Setup notification bell icon HTML if not exists
         */
        setupBellIcon: function() {
            if ($('#notification-bell').length === 0) {
                // Inject bell icon into navbar (adjust selector based on your layout)
                const bellHtml = `
                    <li class="dropdown notifications-menu" id="notification-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="notification-bell">
                            <i class="fa fa-bell-o"></i>
                            <span class="label label-warning notification-count" style="display: none;">0</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">You have <span class="notification-total">0</span> notifications</li>
                            <li>
                                <ul class="menu notification-list">
                                    <!-- Notifications will be loaded here -->
                                </ul>
                            </li>
                            <li class="footer">
                                <a href="/notifications">View all</a>
                                <a href="#" class="mark-all-read-btn pull-right">Mark all as read</a>
                            </li>
                        </ul>
                    </li>
                `;
                
                // Try to insert after messages-menu or user-menu (adjust based on your navbar structure)
                const $navbar = $('.navbar-custom-menu .navbar-nav, .navbar-nav');
                if ($navbar.length) {
                    $(bellHtml).insertBefore($navbar.find('.user-menu, li:last-child'));
                }
            }
        },

        /**
         * Setup dropdown behavior
         */
        setupDropdown: function() {
            $('#notification-dropdown').on('show.bs.dropdown', () => {
                this.loadRecentNotifications();
            });
        },

        /**
         * Setup event handlers for notification actions
         */
        setupEventHandlers: function() {
            const self = this;

            // Mark individual notification as read
            $(document).on('click', '.notification-item', function(e) {
                const $item = $(this);
                const notificationId = $item.data('id');
                const isUnread = $item.hasClass('unread');
                const url = $item.find('a').attr('href');

                if (isUnread && notificationId) {
                    e.preventDefault();
                    self.markAsRead(notificationId, function() {
                        // Navigate after marking as read
                        if (url && url !== '#') {
                            window.location.href = url;
                        }
                    });
                }
            });

            // Mark all as read
            $(document).on('click', '.mark-all-read-btn', function(e) {
                e.preventDefault();
                self.markAllAsRead();
            });

            // Delete notification
            $(document).on('click', '.delete-notification-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const notificationId = $(this).closest('.notification-item').data('id');
                if (notificationId) {
                    self.deleteNotification(notificationId);
                }
            });

            // Dismiss toast notification
            $(document).on('click', '.toast-notification .close', function() {
                $(this).closest('.toast-notification').fadeOut(300, function() {
                    $(this).remove();
                    self.state.activeToasts--;
                });
            });
        },

        /**
         * Load initial notifications
         */
        loadInitialNotifications: function() {
            this.updateUnreadCount();
        },

        /**
         * Start polling for new notifications
         */
        startPolling: function() {
            const self = this;
            
            this.state.pollTimer = setInterval(function() {
                self.checkForNewNotifications();
            }, this.config.pollInterval);
        },

        /**
         * Stop polling
         */
        stopPolling: function() {
            if (this.state.pollTimer) {
                clearInterval(this.state.pollTimer);
                this.state.pollTimer = null;
            }
        },

        /**
         * Update unread count badge
         */
        updateUnreadCount: function() {
            const self = this;
            
            $.ajax({
                url: this.config.unreadCountUrl,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    const count = response.count || 0;
                    self.renderUnreadCount(count);
                },
                error: function(xhr) {
                    console.error('Failed to load unread count:', xhr);
                }
            });
        },

        /**
         * Render unread count badge
         */
        renderUnreadCount: function(count) {
            const $badge = $('.notification-count');
            const $total = $('.notification-total');
            
            if (count > 0) {
                $badge.text(count).show();
                $total.text(count);
            } else {
                $badge.hide();
                $total.text('0');
            }
        },

        /**
         * Load recent notifications for dropdown
         */
        loadRecentNotifications: function(limit = 5) {
            const self = this;
            
            $.ajax({
                url: this.config.recentNotificationsUrl,
                method: 'GET',
                data: { limit: limit },
                dataType: 'json',
                success: function(response) {
                    self.renderNotificationList(response.notifications || []);
                },
                error: function(xhr) {
                    console.error('Failed to load notifications:', xhr);
                    $('.notification-list').html('<li class="text-center text-danger">Failed to load notifications</li>');
                }
            });
        },

        /**
         * Render notification list in dropdown
         */
        renderNotificationList: function(notifications) {
            const $list = $('.notification-list');
            $list.empty();

            if (notifications.length === 0) {
                $list.html('<li class="text-center text-muted">No notifications</li>');
                return;
            }

            notifications.forEach(notification => {
                const unreadClass = notification.is_read ? '' : 'unread';
                const $item = $(`
                    <li class="notification-item ${unreadClass}" data-id="${notification.id}">
                        <a href="${notification.action_url || '#'}">
                            <i class="${notification.icon_class || 'fa fa-info-circle'}"></i>
                            <strong>${this.escapeHtml(notification.title)}</strong>
                            <p>${this.escapeHtml(notification.message)}</p>
                            <small class="text-muted">${notification.time_ago}</small>
                        </a>
                    </li>
                `);
                $list.append($item);
            });
        },

        /**
         * Check for new notifications
         */
        checkForNewNotifications: function() {
            const self = this;
            
            $.ajax({
                url: this.config.recentNotificationsUrl,
                method: 'GET',
                data: { limit: 1 },
                dataType: 'json',
                success: function(response) {
                    const notifications = response.notifications || [];
                    
                    if (notifications.length > 0) {
                        const latestNotification = notifications[0];
                        
                        // Check if this is a new notification
                        if (!self.state.lastNotificationId || 
                            latestNotification.id !== self.state.lastNotificationId) {
                            
                            // Show toast for new notification
                            if (!latestNotification.is_read) {
                                self.showToast(latestNotification);
                            }
                            
                            self.state.lastNotificationId = latestNotification.id;
                        }
                    }
                    
                    // Update unread count
                    self.updateUnreadCount();
                },
                error: function(xhr) {
                    console.error('Failed to check for new notifications:', xhr);
                }
            });
        },

        /**
         * Show toast notification
         */
        showToast: function(notification) {
            // Limit number of active toasts
            if (this.state.activeToasts >= this.config.maxToasts) {
                return;
            }

            const $toast = $(`
                <div class="toast-notification ${notification.priority || 'info'}" data-id="${notification.id}">
                    <button type="button" class="close">&times;</button>
                    <div class="toast-icon">
                        <i class="${notification.icon_class || 'fa fa-info-circle'}"></i>
                    </div>
                    <div class="toast-content">
                        <strong>${this.escapeHtml(notification.title)}</strong>
                        <p>${this.escapeHtml(notification.message)}</p>
                    </div>
                </div>
            `);

            // Add click handler to navigate
            $toast.on('click', function(e) {
                if (!$(e.target).hasClass('close')) {
                    if (notification.action_url && notification.action_url !== '#') {
                        window.location.href = notification.action_url;
                    }
                }
            });

            // Append to container (create if doesn't exist)
            if ($('.toast-container').length === 0) {
                $('body').append('<div class="toast-container"></div>');
            }
            $('.toast-container').append($toast);

            this.state.activeToasts++;

            // Auto-dismiss after duration
            setTimeout(() => {
                $toast.fadeOut(300, function() {
                    $(this).remove();
                    NotificationManager.state.activeToasts--;
                });
            }, this.config.toastDuration);

            // Mark as read after showing
            if (notification.id) {
                this.markAsRead(notification.id);
            }
        },

        /**
         * Mark notification as read
         */
        markAsRead: function(notificationId, callback) {
            const self = this;
            const url = this.config.markReadUrl.replace('{id}', notificationId);
            
            $.ajax({
                url: url,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function() {
                    // Update UI
                    $(`.notification-item[data-id="${notificationId}"]`).removeClass('unread');
                    self.updateUnreadCount();
                    
                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                error: function(xhr) {
                    console.error('Failed to mark notification as read:', xhr);
                }
            });
        },

        /**
         * Mark all notifications as read
         */
        markAllAsRead: function() {
            const self = this;
            
            $.ajax({
                url: this.config.markAllReadUrl,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    // Update UI
                    $('.notification-item').removeClass('unread');
                    self.renderUnreadCount(0);
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'All notifications marked as read');
                    }
                },
                error: function(xhr) {
                    console.error('Failed to mark all as read:', xhr);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to mark all as read');
                    }
                }
            });
        },

        /**
         * Delete notification
         */
        deleteNotification: function(notificationId) {
            const self = this;
            const url = this.config.deleteUrl.replace('{id}', notificationId);
            
            if (!confirm('Delete this notification?')) {
                return;
            }
            
            $.ajax({
                url: url,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function() {
                    // Remove from UI
                    $(`.notification-item[data-id="${notificationId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if list is empty
                        if ($('.notification-list .notification-item').length === 0) {
                            $('.notification-list').html('<li class="text-center text-muted">No notifications</li>');
                        }
                    });
                    
                    self.updateUnreadCount();
                },
                error: function(xhr) {
                    console.error('Failed to delete notification:', xhr);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to delete notification');
                    }
                }
            });
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        NotificationManager.init();
    });

    // Expose to window for debugging
    window.NotificationManager = NotificationManager;

})(jQuery);
