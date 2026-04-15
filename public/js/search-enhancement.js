/**
 * Enhanced Search Component - Phase 3 Priority 2
 * Features: Autocomplete, Recent Searches, Filters, Better Results Display
 */

(function($) {
    'use strict';

    /**
     * Enhanced Search Class
     */
    class EnhancedSearch {
        constructor(options) {
            this.options = $.extend({
                inputSelector: '#global-search',
                autocompleteContainer: '.search-autocomplete',
                minSearchLength: 2,
                debounceDelay: 300,
                maxRecent: 10,
                apiEndpoint: '/api/search'
            }, options);

            this.searchTimeout = null;
            this.recentSearches = this.loadRecentSearches();
            this.currentQuery = '';
            this.activeFilter = 'all';
            this.keyboardIndex = -1;

            this.init();
        }

        /**
         * Initialize component
         */
        init() {
            this.setupDOM();
            this.bindEvents();
            this.initKeyboardShortcuts();
        }

        /**
         * Setup DOM elements
         */
        setupDOM() {
            const $input = $(this.options.inputSelector);
            
            // Wrap input if not already wrapped
            if (!$input.parent().hasClass('enhanced-search-container')) {
                $input.wrap('<div class="enhanced-search-container"></div>');
            }

            const $container = $input.parent();

            // Add clear button if not exists
            if ($container.find('.enhanced-search-clear').length === 0) {
                $container.append('<span class="enhanced-search-clear" style="display:none;"><i class="fa fa-times-circle"></i></span>');
            }

            // Add autocomplete dropdown if not exists
            if ($container.find('.search-autocomplete').length === 0) {
                $container.append('<div class="search-autocomplete"></div>');
            }

            this.$input = $input;
            this.$container = $container;
            this.$autocomplete = $container.find('.search-autocomplete');
            this.$clearBtn = $container.find('.enhanced-search-clear');
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            const self = this;

            // Input events
            this.$input.on('input', function() {
                self.handleInput();
            });

            this.$input.on('focus', function() {
                const query = $(this).val().trim();
                if (query.length >= self.options.minSearchLength) {
                    self.performSearch(query);
                } else {
                    self.showRecentSearches();
                }
            });

            this.$input.on('blur', function() {
                // Delay to allow click events on autocomplete items
                setTimeout(function() {
                    self.$autocomplete.removeClass('active');
                }, 200);
            });

            // Clear button
            this.$clearBtn.on('click', function() {
                self.clearSearch();
            });

            // Keyboard navigation
            this.$input.on('keydown', function(e) {
                if (self.$autocomplete.hasClass('active')) {
                    self.handleKeyboardNavigation(e);
                }
            });

            // Close autocomplete when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.enhanced-search-container').length) {
                    self.$autocomplete.removeClass('active');
                }
            });
        }

        /**
         * Handle input changes
         */
        handleInput() {
            const query = this.$input.val().trim();

            // Show/hide clear button
            this.$clearBtn.toggle(query.length > 0);

            // Clear previous timeout
            clearTimeout(this.searchTimeout);

            if (query.length < this.options.minSearchLength) {
                if (query.length === 0) {
                    this.showRecentSearches();
                } else {
                    this.$autocomplete.removeClass('active');
                }
                return;
            }

            // Debounce search
            const self = this;
            this.searchTimeout = setTimeout(function() {
                self.performSearch(query);
            }, this.options.debounceDelay);
        }

        /**
         * Perform autocomplete search
         */
        performSearch(query) {
            const self = this;
            this.currentQuery = query;

            // Show loading state
            this.$autocomplete.html(this.getLoadingHTML()).addClass('active');

            $.ajax({
                url: this.options.apiEndpoint,
                method: 'GET',
                data: {
                    q: query,
                    type: this.activeFilter,
                    per_page: 10
                },
                success: function(response) {
                    self.displayAutocomplete(response);
                    self.saveRecentSearch(query);
                },
                error: function(xhr) {
                    self.$autocomplete.html(self.getErrorHTML()).addClass('active');
                }
            });
        }

        /**
         * Display autocomplete results
         */
        displayAutocomplete(response) {
            if (response.total_count === 0) {
                this.$autocomplete.html(this.getNoResultsHTML(response.query)).addClass('active');
                return;
            }

            let html = '';
            const results = response.results;

            // Display tickets
            if (results.tickets && results.tickets.length > 0) {
                html += this.renderAutocompleteSection('Tickets', results.tickets);
            }

            // Display assets
            if (results.assets && results.assets.length > 0) {
                html += this.renderAutocompleteSection('Assets', results.assets);
            }

            // Display users
            if (results.users && results.users.length > 0) {
                html += this.renderAutocompleteSection('Users', results.users);
            }

            // Display locations
            if (results.locations && results.locations.length > 0) {
                html += this.renderAutocompleteSection('Locations', results.locations);
            }

            // Display knowledge base
            if (results.knowledge_base && results.knowledge_base.length > 0) {
                html += this.renderAutocompleteSection('Knowledge Base', results.knowledge_base);
            }

            // Add footer
            html += '<div class="search-autocomplete-footer">';
            html += '<a href="/search?q=' + encodeURIComponent(this.currentQuery) + '">View all ' + response.total_count + ' results</a>';
            html += '</div>';

            this.$autocomplete.html(html).addClass('active');
            this.bindAutocompleteEvents();
            this.keyboardIndex = -1;
        }

        /**
         * Render autocomplete section
         */
        renderAutocompleteSection(title, items) {
            let html = '<div class="search-autocomplete-header">' + title + '</div>';

            items.forEach(function(item, index) {
                html += '<div class="search-autocomplete-item" data-url="' + item.url + '" data-index="' + index + '">';
                html += '<div class="search-autocomplete-icon ' + item.entity_type + '">';
                html += '<i class="fa ' + this.getEntityIcon(item.entity_type) + '"></i>';
                html += '</div>';
                html += '<div class="search-autocomplete-content">';
                html += '<div class="search-autocomplete-title">' + item.title + '</div>';
                html += '<div class="search-autocomplete-subtitle">' + item.subtitle + '</div>';
                html += '</div>';
                
                // Add status badge if available
                if (item.status) {
                    html += '<span class="search-autocomplete-badge" style="background:' + item.status_color + ';color:#fff;">' + item.status + '</span>';
                }
                
                html += '</div>';
            }.bind(this));

            return html;
        }

        /**
         * Get entity icon class
         */
        getEntityIcon(type) {
            const icons = {
                ticket: 'fa-ticket',
                asset: 'fa-desktop',
                user: 'fa-user',
                location: 'fa-map-marker',
                knowledge_base: 'fa-book'
            };
            return icons[type] || 'fa-circle';
        }

        /**
         * Show recent searches
         */
        showRecentSearches() {
            if (this.recentSearches.length === 0) {
                this.$autocomplete.removeClass('active');
                return;
            }

            let html = '<div class="search-recent">';
            html += '<div class="search-recent-header">';
            html += '<span class="search-recent-title">Recent Searches</span>';
            html += '<a href="#" class="search-recent-clear" id="clearRecentSearches">Clear</a>';
            html += '</div>';

            this.recentSearches.forEach(function(search) {
                html += '<div class="search-recent-item" data-query="' + search + '">';
                html += '<i class="fa fa-history"></i>';
                html += '<span>' + search + '</span>';
                html += '</div>';
            });

            html += '</div>';

            this.$autocomplete.html(html).addClass('active');
            this.bindRecentSearchesEvents();
        }

        /**
         * Bind autocomplete events
         */
        bindAutocompleteEvents() {
            const self = this;

            this.$autocomplete.find('.search-autocomplete-item').on('click', function() {
                const url = $(this).data('url');
                if (url) {
                    window.location.href = url;
                }
            });

            this.$autocomplete.find('.search-autocomplete-item').on('mouseenter', function() {
                self.$autocomplete.find('.search-autocomplete-item').removeClass('keyboard-active');
                $(this).addClass('keyboard-active');
                self.keyboardIndex = $(this).data('index') || 0;
            });
        }

        /**
         * Bind recent searches events
         */
        bindRecentSearchesEvents() {
            const self = this;

            this.$autocomplete.find('.search-recent-item').on('click', function(e) {
                e.preventDefault();
                const query = $(this).data('query');
                self.$input.val(query);
                self.performSearch(query);
            });

            this.$autocomplete.find('#clearRecentSearches').on('click', function(e) {
                e.preventDefault();
                self.clearRecentSearches();
            });
        }

        /**
         * Handle keyboard navigation
         */
        handleKeyboardNavigation(e) {
            const $items = this.$autocomplete.find('.search-autocomplete-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.keyboardIndex = Math.min(this.keyboardIndex + 1, $items.length - 1);
                this.updateKeyboardSelection($items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.keyboardIndex = Math.max(this.keyboardIndex - 1, 0);
                this.updateKeyboardSelection($items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const $active = $items.filter('.keyboard-active');
                if ($active.length > 0) {
                    const url = $active.data('url');
                    if (url) {
                        window.location.href = url;
                    }
                }
            } else if (e.key === 'Escape') {
                this.$autocomplete.removeClass('active');
                this.$input.blur();
            }
        }

        /**
         * Update keyboard selection
         */
        updateKeyboardSelection($items) {
            $items.removeClass('keyboard-active');
            const $active = $items.eq(this.keyboardIndex);
            $active.addClass('keyboard-active');

            // Scroll into view
            if ($active.length > 0) {
                const container = this.$autocomplete[0];
                const item = $active[0];
                const containerHeight = container.clientHeight;
                const itemTop = item.offsetTop;
                const itemBottom = itemTop + item.clientHeight;

                if (itemBottom > container.scrollTop + containerHeight) {
                    container.scrollTop = itemBottom - containerHeight;
                } else if (itemTop < container.scrollTop) {
                    container.scrollTop = itemTop;
                }
            }
        }

        /**
         * Clear search
         */
        clearSearch() {
            this.$input.val('');
            this.$clearBtn.hide();
            this.$autocomplete.removeClass('active');
            this.$input.focus();
            this.showRecentSearches();
        }

        /**
         * Initialize keyboard shortcuts
         */
        initKeyboardShortcuts() {
            const self = this;

            $(document).on('keydown', function(e) {
                // Ctrl+K or Cmd+K to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    self.$input.focus();
                    self.$input.select();
                }
            });
        }

        /**
         * Save recent search
         */
        saveRecentSearch(query) {
            if (!query || query.trim().length === 0) return;

            // Remove if already exists
            const index = this.recentSearches.indexOf(query);
            if (index > -1) {
                this.recentSearches.splice(index, 1);
            }

            // Add to beginning
            this.recentSearches.unshift(query);

            // Limit to maxRecent
            if (this.recentSearches.length > this.options.maxRecent) {
                this.recentSearches = this.recentSearches.slice(0, this.options.maxRecent);
            }

            // Save to localStorage
            localStorage.setItem('qutyit_recent_searches', JSON.stringify(this.recentSearches));
        }

        /**
         * Load recent searches
         */
        loadRecentSearches() {
            try {
                const saved = localStorage.getItem('qutyit_recent_searches');
                return saved ? JSON.parse(saved) : [];
            } catch (e) {
                return [];
            }
        }

        /**
         * Clear recent searches
         */
        clearRecentSearches() {
            this.recentSearches = [];
            localStorage.removeItem('qutyit_recent_searches');
            this.$autocomplete.removeClass('active');
        }

        /**
         * Get loading HTML
         */
        getLoadingHTML() {
            return '<div class="search-loading"><i class="fa fa-spinner fa-spin search-loading-spinner"></i><div class="search-loading-text">Searching...</div></div>';
        }

        /**
         * Get error HTML
         */
        getErrorHTML() {
            return '<div class="search-empty-state"><i class="fa fa-exclamation-triangle search-empty-icon"></i><div class="search-empty-title">Search Error</div><div class="search-empty-text">Unable to perform search. Please try again.</div></div>';
        }

        /**
         * Get no results HTML
         */
        getNoResultsHTML(query) {
            return '<div class="search-empty-state">' +
                   '<i class="fa fa-search search-empty-icon"></i>' +
                   '<div class="search-empty-title">No results found</div>' +
                   '<div class="search-empty-text">No results for "' + query + '"</div>' +
                   '<ul class="search-empty-suggestions">' +
                   '<li>Try different keywords</li>' +
                   '<li>Check your spelling</li>' +
                   '<li>Use more general terms</li>' +
                   '</ul>' +
                   '</div>';
        }
    }

    /**
     * jQuery plugin
     */
    $.fn.enhancedSearch = function(options) {
        return this.each(function() {
            if (!$.data(this, 'enhancedSearch')) {
                $.data(this, 'enhancedSearch', new EnhancedSearch($.extend({
                    inputSelector: this
                }, options)));
            }
        });
    };

    /**
     * Auto-initialize on document ready
     */
    $(document).ready(function() {
        if ($('#global-search').length > 0) {
            new EnhancedSearch({
                inputSelector: '#global-search'
            });
        }
    });

})(jQuery);
