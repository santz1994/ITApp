/**
 * Global Search Component
 * Provides unified search across tickets, assets, users, locations, and knowledge base
 */

(function($) {
    'use strict';

    /**
     * Initialize global search
     */
    window.GlobalSearch = {
        init: function() {
            this.setupSearchModal();
            this.setupKeyboardShortcut();
            this.setupQuickSearch();
        },

        /**
         * Setup search modal
         */
        setupSearchModal: function() {
            // Add search modal HTML if not exists
            if ($('#globalSearchModal').length === 0) {
                $('body').append(this.getModalHTML());
            }

            // Handle search form submission
            $('#globalSearchForm').on('submit', function(e) {
                e.preventDefault();
                GlobalSearch.performSearch();
            });

            // Handle search input changes (debounced)
            let searchTimeout;
            $('#globalSearchInput').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    GlobalSearch.performSearch();
                }, 500);
            });

            // Handle entity type filter change
            $('#searchEntityType').on('change', function() {
                GlobalSearch.performSearch();
            });

            // Clear search
            $('#clearSearchBtn').on('click', function() {
                $('#globalSearchInput').val('');
                $('#searchResults').html('');
                $('#globalSearchInput').focus();
            });
        },

        /**
         * Setup keyboard shortcut (Ctrl+K or Cmd+K)
         */
        setupKeyboardShortcut: function() {
            $(document).on('keydown', function(e) {
                // Ctrl+K or Cmd+K
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    $('#globalSearchModal').modal('show');
                    setTimeout(function() {
                        $('#globalSearchInput').focus();
                    }, 300);
                }
            });
        },

        /**
         * Setup quick search autocomplete
         */
        setupQuickSearch: function() {
            if ($('#quickSearchInput').length > 0) {
                $('#quickSearchInput').autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: '/api/quick-search',
                            data: { q: request.term },
                            success: function(data) {
                                response(data.results.map(function(item) {
                                    return {
                                        label: item.label,
                                        value: item.label,
                                        url: item.url,
                                        type: item.type
                                    };
                                }));
                            }
                        });
                    },
                    minLength: 2,
                    select: function(event, ui) {
                        window.location.href = ui.item.url;
                    }
                });
            }
        },

        /**
         * Perform search via AJAX
         */
        performSearch: function() {
            const query = $('#globalSearchInput').val().trim();
            const type = $('#searchEntityType').val();

            if (query.length < 2) {
                $('#searchResults').html('<div class="text-center text-muted p-3">Type at least 2 characters to search...</div>');
                return;
            }

            // Show loading
            $('#searchResults').html('<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Searching...</div>');

            $.ajax({
                url: '/api/search',
                method: 'GET',
                data: {
                    q: query,
                    type: type,
                    per_page: 20
                },
                success: function(response) {
                    GlobalSearch.displayResults(response);
                },
                error: function(xhr) {
                    $('#searchResults').html('<div class="alert alert-danger">Search failed. Please try again.</div>');
                }
            });
        },

        /**
         * Display search results
         */
        displayResults: function(response) {
            const results = response.results;
            let html = '';

            if (response.total_count === 0) {
                html = '<div class="text-center text-muted p-3">No results found for "' + response.query + '"</div>';
                $('#searchResults').html(html);
                return;
            }

            // Display results by type
            if (results.tickets && results.tickets.length > 0) {
                html += this.renderEntitySection('Tickets', results.tickets, 'ticket');
            }

            if (results.assets && results.assets.length > 0) {
                html += this.renderEntitySection('Assets', results.assets, 'asset');
            }

            if (results.users && results.users.length > 0) {
                html += this.renderEntitySection('Users', results.users, 'user');
            }

            if (results.locations && results.locations.length > 0) {
                html += this.renderEntitySection('Locations', results.locations, 'location');
            }

            if (results.knowledge_base && results.knowledge_base.length > 0) {
                html += this.renderEntitySection('Knowledge Base', results.knowledge_base, 'knowledge_base');
            }

            $('#searchResults').html(html);
        },

        /**
         * Render a section of results for an entity type
         */
        renderEntitySection: function(title, items, type) {
            let html = '<div class="search-section mb-3">';
            html += '<h4 class="search-section-title">' + title + ' (' + items.length + ')</h4>';
            html += '<div class="list-group">';

            items.forEach(function(item) {
                html += '<a href="' + item.url + '" class="list-group-item list-group-item-action">';
                html += '<div class="row">';
                html += '<div class="col-md-1 text-center">';
                html += '<i class="fa ' + item.icon + ' fa-2x text-muted"></i>';
                html += '</div>';
                html += '<div class="col-md-11">';
                html += '<div class="d-flex w-100 justify-content-between">';
                html += '<h5 class="mb-1">' + item.title + '</h5>';
                
                if (item.status) {
                    html += '<span class="label label-' + item.status_color + '">' + item.status + '</span>';
                }
                
                html += '</div>';
                
                if (item.subtitle) {
                    html += '<p class="mb-1 text-muted"><small>' + item.subtitle + '</small></p>';
                }
                
                if (item.description) {
                    html += '<p class="mb-1">' + item.description + '</p>';
                }

                // Additional metadata based on type
                if (type === 'ticket' && item.priority) {
                    html += '<small class="text-muted">Priority: ' + item.priority + ' | Created by: ' + item.created_by + '</small>';
                } else if (type === 'asset' && item.location) {
                    html += '<small class="text-muted">Location: ' + item.location + ' | Assigned to: ' + item.assigned_to + '</small>';
                } else if (type === 'knowledge_base' && item.views) {
                    html += '<small class="text-muted">' + item.views + ' views | ' + item.helpful_percentage + '% helpful</small>';
                }

                html += '</div>';
                html += '</div>';
                html += '</a>';
            });

            html += '</div>';
            html += '</div>';

            return html;
        },

        /**
         * Get modal HTML
         */
        getModalHTML: function() {
            return `
                <div class="modal fade" id="globalSearchModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title">
                                    <i class="fa fa-search"></i> Global Search
                                    <small class="text-muted">(Ctrl+K)</small>
                                </h4>
                            </div>
                            <div class="modal-body">
                                <form id="globalSearchForm">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <input type="text" 
                                                       id="globalSearchInput" 
                                                       class="form-control input-lg" 
                                                       placeholder="Search tickets, assets, users, locations..." 
                                                       autofocus>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <select id="searchEntityType" class="form-control input-lg">
                                                    <option value="all">All Types</option>
                                                    <option value="ticket">Tickets</option>
                                                    <option value="asset">Assets</option>
                                                    <option value="user">Users</option>
                                                    <option value="location">Locations</option>
                                                    <option value="knowledge_base">Knowledge Base</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" id="clearSearchBtn" class="btn btn-default btn-lg" title="Clear search">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <div id="searchResults" class="search-results" style="max-height: 500px; overflow-y: auto;">
                                    <div class="text-center text-muted p-3">
                                        <i class="fa fa-search fa-3x"></i>
                                        <p>Start typing to search...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        GlobalSearch.init();
    });

})(jQuery);
