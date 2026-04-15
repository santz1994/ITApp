/**
 * Notification UI Component - Phase 3 Priority 2
 * Features: Dropdown, Badge, Mark as Read, Real-time Updates
 */

(function($) {
    'use strict';

    /**
     * Notification UI Class
     */
    class NotificationUI {
        constructor(options) {
            this.options = $.extend({
                bellSelector: '#notification-bell',
                dropdownSelector: '#notification-dropdown',
                badgeSelector: '#notification-badge',
                refreshInterval: 60000, // 1 minute
                apiEndpoint: '/api/notifications',
                markReadEndpoint: '/api/notifications/{id}/read',
                markAllReadEndpoint: '/api/notifications/mark-all-read'
            }, options);

            this.unreadCount = 0;
            this.notifications = [];
            this.currentTab = 'all';
            this.refreshTimer = null;
            this.isAuthorized = true; // Track authorization status
            this.fetchInProgress = false; // Prevent duplicate requests

            this.init();
        }

        /**
         * Initialize component
         */
        init() {
            this.setupDOM();
            this.bindEvents();
            // Defer initial fetch to improve page load
            setTimeout(() => {
                this.fetchNotifications();
                this.startAutoRefresh();
            }, 500);
        }

        /**
         * Setup DOM elements
         */
        setupDOM() {
            const $bell = $(this.options.bellSelector);
            
            if ($bell.length === 0) {
                console.error('Notification bell element not found');
                return;
            }

            this.$bell = $bell;
            this.$badge = $(this.options.badgeSelector);
            this.$dropdown = $(this.options.dropdownSelector);

            // Create dropdown if not exists
            if (this.$dropdown.length === 0) {
                this.createDropdown();
                this.$dropdown = $(this.options.dropdownSelector);
            }
        }

        /**
         * Create dropdown HTML
         */
        createDropdown() {
            const html = `
                <div id="notification-dropdown" class="notification-dropdown">
                    <div class="notification-dropdown-header">
                        <div>
                            <h4 class="notification-dropdown-title">Notifications</h4>
                            <span class="notification-dropdown-count">
                                <span id="notification-unread-count">0</span> unread
                            </span>
                        </div>
                        <button class="notification-mark-all" id="mark-all-read">
                            <i class="fa fa-check-double"></i> Mark all as read
                        </button>
                    </div>
                    <div class="notification-tabs">
                        <div class="notification-tab active" data-tab="all">
                            All <span class="notification-tab-badge" id="tab-all-count">0</span>
                        </div>
                        <div class="notification-tab" data-tab="unread">
                            Unread <span class="notification-tab-badge" id="tab-unread-count">0</span>
                        </div>
                    </div>
                    <div class="notification-dropdown-body" id="notification-list">
                        <!-- Notifications will be loaded here -->
                    </div>
                    <div class="notification-dropdown-footer">
                        <a href="/notifications" class="notification-view-all">
                            View All Notifications <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            `;

            this.$bell.parent().css('position', 'relative').append(html);
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            const self = this;

            // Toggle dropdown
            this.$bell.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.$dropdown.toggleClass('active');
                
                if (self.$dropdown.hasClass('active')) {
                    self.fetchNotifications();
                }
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#notification-bell, #notification-dropdown').length) {
                    self.$dropdown.removeClass('active');
                }
            });

            // Tab switching
            $(document).on('click', '.notification-tab', function() {
                $('.notification-tab').removeClass('active');
                $(this).addClass('active');
                self.currentTab = $(this).data('tab');
                self.renderNotifications();
            });

            // Mark all as read
            $(document).on('click', '#mark-all-read', function(e) {
                e.preventDefault();
                self.markAllAsRead();
            });

            // Mark single notification as read
            $(document).on('click', '.notification-mark-read', function(e) {
                e.stopPropagation();
                const id = $(this).closest('.notification-item').data('id');
                self.markAsRead(id);
            });

            // Notification item click
            $(document).on('click', '.notification-item', function() {
                const url = $(this).data('url');
                const id = $(this).data('id');
                
                // Mark as read first
                self.markAsRead(id);
                
                // Navigate to URL
                if (url) {
                    setTimeout(function() {
                        window.location.href = url;
                    }, 200);
                }
            });
        }

        /**
         * Fetch notifications from API
         */
        fetchNotifications() {
            // Prevent duplicate requests
            if (this.fetchInProgress || !this.isAuthorized) {
                return;
            }

            const self = this;
            this.fetchInProgress = true;

            $.ajax({
                url: this.options.apiEndpoint,
                method: 'GET',
                data: {
                    per_page: 20,
                    recent_days: 30
                },
                success: function(response) {
                    self.fetchInProgress = false;
                    
                    // Handle Laravel paginated response structure
                    // Response structure: { notifications: { data: [...], total: X }, unread_count: Y }
                    if (response.notifications && response.notifications.data) {
                        self.notifications = response.notifications.data;
                    } else if (Array.isArray(response.notifications)) {
                        self.notifications = response.notifications;
                    } else if (Array.isArray(response.data)) {
                        self.notifications = response.data;
                    } else {
                        self.notifications = [];
                    }
                    
                    self.unreadCount = response.unread_count || 0;
                    self.updateBadge();
                    self.updateTabCounts();
                    self.renderNotifications();
                },
                error: function(xhr) {
                    self.fetchInProgress = false;
                    
                    // If unauthorized, stop auto-refresh and hide bell to avoid noisy console errors
                    if (xhr && xhr.status === 401) {
                        console.warn('Notifications unauthorized (401). Stopping auto-refresh and hiding notification UI.');
                        self.isAuthorized = false;
                        self.stopAutoRefresh();
                        if (self.$bell) { 
                            self.$bell.addClass('hidden'); 
                        }
                        if (self.$dropdown) { 
                            self.$dropdown.remove(); 
                        }
                        return;
                    }

                    console.error('Failed to fetch notifications:', xhr);
                    self.showError();
                }
            });
        }

        /**
         * Update notification badge
         */
        updateBadge() {
            this.$badge.text(this.unreadCount);
            
            if (this.unreadCount > 0) {
                this.$badge.removeClass('hidden');
                this.$bell.addClass('has-unread');
            } else {
                this.$badge.addClass('hidden');
                this.$bell.removeClass('has-unread');
            }

            // Update unread count in header
            $('#notification-unread-count').text(this.unreadCount);
        }

        /**
         * Update tab counts
         */
        updateTabCounts() {
            // Ensure notifications is always an array
            if (!Array.isArray(this.notifications)) {
                console.warn('notifications is not an array, resetting to empty array', this.notifications);
                this.notifications = [];
            }
            
            const unreadCount = this.notifications.filter(n => !n.is_read).length;
            const allCount = this.notifications.length;

            $('#tab-all-count').text(allCount);
            $('#tab-unread-count').text(unreadCount);
        }

        /**
         * Render notifications list
         */
        renderNotifications() {
            const $list = $('#notification-list');
            
            // Ensure notifications is always an array
            if (!Array.isArray(this.notifications)) {
                console.warn('notifications is not an array in renderNotifications, resetting to empty array');
                this.notifications = [];
            }
            
            // Filter notifications based on current tab
            let filtered = this.notifications;
            if (this.currentTab === 'unread') {
                filtered = filtered.filter(n => !n.is_read);
            }

            if (filtered.length === 0) {
                $list.html(this.getEmptyStateHTML());
                return;
            }

            let html = '';
            filtered.forEach(notification => {
                html += this.renderNotificationItem(notification);
            });

            $list.html(html);
        }

        /**
         * Render single notification item
         */
        renderNotificationItem(notification) {
            const isUnread = !notification.is_read;
            const iconClass = this.getNotificationIconClass(notification.type);
            const timeAgo = this.formatTimeAgo(notification.created_at);
            
            return `
                <div class="notification-item ${isUnread ? 'unread' : ''}" 
                     data-id="${notification.id}" 
                     data-url="${notification.action_url || ''}">
                    <div class="notification-icon ${iconClass}">
                        <i class="fa ${this.getNotificationIcon(notification.type)}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">
                            <i class="fa fa-clock"></i> ${timeAgo}
                        </div>
                    </div>
                    <button class="notification-mark-read" title="${isUnread ? 'Mark as read' : 'Read'}">
                        <i class="fa ${isUnread ? 'fa-circle' : 'fa-check'}"></i>
                    </button>
                </div>
            `;
        }

        /**
         * Get notification icon class
         */
        getNotificationIconClass(type) {
            const classes = {
                'ticket_overdue': 'danger',
                'ticket_assigned': 'ticket',
                'asset_assigned': 'asset',
                'warranty_expiring': 'warning',
                'system_alert': 'system'
            };
            return classes[type] || 'system';
        }

        /**
         * Get notification icon
         */
        getNotificationIcon(type) {
            const icons = {
                'ticket_overdue': 'fa-exclamation-triangle',
                'ticket_assigned': 'fa-ticket',
                'asset_assigned': 'fa-laptop',
                'warranty_expiring': 'fa-shield-alt',
                'system_alert': 'fa-info-circle'
            };
            return icons[type] || 'fa-bell';
        }

        /**
         * Format time ago
         */
        formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);

            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
            if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
            
            return date.toLocaleDateString();
        }

        /**
         * Mark notification as read
         */
        markAsRead(id) {
            const self = this;
            const endpoint = this.options.markReadEndpoint.replace('{id}', id);

            $.ajax({
                url: endpoint,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    // Update local state
                    const notification = self.notifications.find(n => n.id == id);
                    if (notification) {
                        notification.is_read = true;
                        self.unreadCount = Math.max(0, self.unreadCount - 1);
                        self.updateBadge();
                        self.updateTabCounts();
                        self.renderNotifications();
                    }
                },
                error: function(xhr) {
                    console.error('Failed to mark notification as read:', xhr);
                }
            });
        }

        /**
         * Mark all notifications as read
         */
        markAllAsRead() {
            const self = this;

            $.ajax({
                url: this.options.markAllReadEndpoint,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    // Update local state
                    self.notifications.forEach(n => n.is_read = true);
                    self.unreadCount = 0;
                    self.updateBadge();
                    self.updateTabCounts();
                    self.renderNotifications();
                },
                error: function(xhr) {
                    console.error('Failed to mark all notifications as read:', xhr);
                }
            });
        }

        /**
         * Start auto-refresh
         */
        startAutoRefresh() {
            // Don't start auto-refresh if unauthorized
            if (!this.isAuthorized) {
                return;
            }

            const self = this;
            
            // Clear any existing timer
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
            }
            
            this.refreshTimer = setInterval(function() {
                // Check authorization before each fetch
                if (self.isAuthorized) {
                    self.fetchNotifications();
                } else {
                    self.stopAutoRefresh();
                }
            }, this.options.refreshInterval);
        }

        /**
         * Stop auto-refresh
         */
        stopAutoRefresh() {
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
                this.refreshTimer = null;
            }
        }

        /**
         * Get empty state HTML
         */
        getEmptyStateHTML() {
            return `
                <div class="notification-empty">
                    <i class="fa fa-bell-slash notification-empty-icon"></i>
                    <div class="notification-empty-title">No notifications</div>
                    <div class="notification-empty-text">
                        ${this.currentTab === 'unread' ? 'All caught up!' : 'You don\'t have any notifications yet'}
                    </div>
                </div>
            `;
        }

        /**
         * Show error
         */
        showError() {
            $('#notification-list').html(`
                <div class="notification-empty">
                    <i class="fa fa-exclamation-triangle notification-empty-icon"></i>
                    <div class="notification-empty-title">Error Loading Notifications</div>
                    <div class="notification-empty-text">Please try again later</div>
                </div>
            `);
        }

        /**
         * Destroy component
         */
        destroy() {
            this.stopAutoRefresh();
            this.$bell.off('click');
            this.$dropdown.remove();
        }
    }

    /**
     * jQuery plugin
     */
    $.fn.notificationUI = function(options) {
        return this.each(function() {
            if (!$.data(this, 'notificationUI')) {
                $.data(this, 'notificationUI', new NotificationUI($.extend({
                    bellSelector: this
                }, options)));
            }
        });
    };

    /**
     * Auto-initialize on document ready with deferred loading
     * Use setTimeout to defer non-critical initialization and improve DOMContentLoaded performance
     */
    $(document).ready(function() {
        // Defer notification initialization to not block DOMContentLoaded
        setTimeout(function() {
            if ($('#notification-bell').length > 0) {
                new NotificationUI();
            }
        }, 100); // 100ms delay allows page to render first
    });

    // Expose to window for external access
    window.NotificationUI = NotificationUI;

})(jQuery);
