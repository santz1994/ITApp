/**
 * DataTable Enhancements & Empty State Protection
 * 
 * Purpose: Provide consistent DataTable initialization with:
 * - Empty state handling (no JavaScript errors when table is empty)
 * - Responsive configuration
 * - Export buttons (Excel, CSV, PDF, Copy)
 * - Professional styling
 * - Graceful degradation
 * 
 * Usage:
 * <script src="{{ asset('js/datatable-enhancements.js') }}"></script>
 * <script>
 *   $(document).ready(function() {
 *     initEnhancedDataTable('#myTable', {
 *       pageLength: 25,
 *       exportFileName: 'MyData'
 *     });
 *   });
 * </script>
 */

/**
 * Initialize an enhanced DataTable with empty state protection
 * 
 * @param {string} selector - jQuery selector for the table
 * @param {object} options - Custom options to override defaults
 * @returns {object|null} - DataTable instance or null if initialization fails
 */
function initEnhancedDataTable(selector, options = {}) {
    // Check if jQuery and DataTables are loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded. DataTable cannot be initialized.');
        return null;
    }
    
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables plugin is not loaded. Please include datatables.net library.');
        return null;
    }
    
    // Check if table exists
    var $table = $(selector);
    if ($table.length === 0) {
        console.warn('Table with selector "' + selector + '" not found.');
        return null;
    }
    
    // Check if table has any rows (excluding header)
    var rowCount = $table.find('tbody tr').length;
    var hasData = rowCount > 0;
    
    // Default configuration with performance optimizations
    var defaultConfig = {
        responsive: options.responsive !== false,
        dom: options.showButtons === false ? 'l<"clear">frtip' : 'l<"clear">Bfrtip', // Conditional buttons for faster init
        pageLength: options.pageLength || 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        deferRender: true,
        scroller: false,
        processing: options.processing !== false,
        autoWidth: false,
        stateSave: options.stateSave !== false, // Default disabled for performance
        stateDuration: 300, // 5 minutes state duration
        search: options.search || { smart: true }, // Allow disabling smart search
        buttons: options.showButtons === false ? [] : [
            {
                extend: 'excel',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: options.exportFileName || 'Export',
                exportOptions: options.exportOptions || {
                    columns: ':visible:not(.no-export)'
                }
            },
            {
                extend: 'csv',
                text: '<i class="fa fa-file-text-o"></i> CSV',
                className: 'btn btn-info btn-sm',
                title: options.exportFileName || 'Export',
                exportOptions: options.exportOptions || {
                    columns: ':visible:not(.no-export)'
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: options.exportFileName || 'Export',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: options.exportOptions || {
                    columns: ':visible:not(.no-export)'
                }
            },
            {
                extend: 'copy',
                text: '<i class="fa fa-copy"></i> Copy',
                className: 'btn btn-default btn-sm',
                exportOptions: options.exportOptions || {
                    columns: ':visible:not(.no-export)'
                }
            },
            {
                extend: 'colvis',
                text: '<i class="fa fa-columns"></i> Columns',
                className: 'btn btn-default btn-sm'
            }
        ],
        language: {
            lengthMenu: options.lengthMenuText || "Show _MENU_ entries per page",
            info: options.infoText || "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: options.infoEmptyText || "No entries to show",
            infoFiltered: options.infoFilteredText || "(filtered from _MAX_ total entries)",
            search: "Quick Search:",
            zeroRecords: options.zeroRecordsText || 
                '<div class="empty-state">' +
                '<i class="fa fa-inbox fa-3x text-muted"></i>' +
                '<h4>No Data Available</h4>' +
                '<p class="text-muted">There are no records to display at this time.</p>' +
                (options.emptyStateCTA || '') +
                '</div>',
            emptyTable: options.emptyTableText ||
                '<div class="empty-state">' +
                '<i class="fa fa-inbox fa-3x text-muted"></i>' +
                '<h4>No Data Available</h4>' +
                '<p class="text-muted">This table is currently empty. ' + 
                (options.createButtonText ? 'Click the "' + options.createButtonText + '" button above to add your first record.' : '') +
                '</p>' +
                '</div>',
            paginate: {
                first: '<i class="fa fa-angle-double-left"></i>',
                previous: '<i class="fa fa-angle-left"></i>',
                next: '<i class="fa fa-angle-right"></i>',
                last: '<i class="fa fa-angle-double-right"></i>'
            },
            loadingRecords: '<i class="fa fa-spinner fa-spin"></i> Loading...',
            processing: '<i class="fa fa-spinner fa-spin"></i> Processing...'
        },
        // Column definitions can be overridden
        columnDefs: options.columnDefs || [],
        // Order can be customized
        order: options.order || [[0, 'asc']],
        // Draw callback for custom updates (optimized)
        drawCallback: function(settings) {
            // Batch all non-critical updates
            if (options.countBadgeSelector || typeof options.customDrawCallback === 'function') {
                setTimeout(function() {
                    if (options.countBadgeSelector) {
                        var info = this.api().page.info();
                        $(options.countBadgeSelector).text(
                            info.recordsDisplay + (options.countBadgeText || ' Records')
                        );
                    }
                    
                    if (typeof options.customDrawCallback === 'function') {
                        options.customDrawCallback.call(this, settings);
                    }
                }.bind(this), 0);
            }
        },
        // Init complete callback (minimal blocking)
        initComplete: function(settings, json) {
            // Only log in debug mode to save time
            if (options.debug) {
                console.log('DataTable initialized successfully for ' + selector);
            }
            
            // Defer all styling updates
            setTimeout(function() {
                $(selector + '_wrapper').addClass('datatable-enhanced-wrapper');
                
                if (typeof options.customInitComplete === 'function') {
                    options.customInitComplete.call(this, settings, json);
                }
            }.bind(this), 0);
        }
    };
    
    // Merge custom options with defaults
    var config = $.extend(true, {}, defaultConfig, options.datatableOptions || {});
    
    // Initialize DataTable with error handling
    try {
        var table = $table.DataTable(config);
        
        return table;
        
    } catch (error) {
        console.error('Error initializing DataTable for ' + selector + ':', error);
        
        // Show fallback message in table
        var $tbody = $table.find('tbody');
        if ($tbody.find('tr').length === 0) {
            $tbody.html(
                '<tr>' +
                '<td colspan="100" class="text-center">' +
                '<div class="alert alert-warning">' +
                '<i class="fa fa-exclamation-triangle"></i> ' +
                'Unable to initialize data table. Please refresh the page or contact support.' +
                '</div>' +
                '</td>' +
                '</tr>'
            );
        }
        
        return null;
    }
}

/**
 * Safely destroy a DataTable instance
 * 
 * @param {object|string} tableOrSelector - DataTable instance or selector
 */
function destroyDataTable(tableOrSelector) {
    try {
        if (typeof tableOrSelector === 'string') {
            var $table = $(tableOrSelector);
            if ($table.length && $.fn.DataTable.isDataTable($table)) {
                $table.DataTable().destroy();
            }
        } else if (tableOrSelector && typeof tableOrSelector.destroy === 'function') {
            tableOrSelector.destroy();
        }
    } catch (error) {
        console.error('Error destroying DataTable:', error);
    }
}

/**
 * Reload DataTable data with empty state protection
 * 
 * @param {object} table - DataTable instance
 */
function safeReloadTable(table) {
    if (!table || typeof table.ajax === 'undefined') {
        return; // Silently fail for performance
    }
    
    try {
        table.ajax.reload(null, false); // false = keep current page
    } catch (error) {
        console.error('Error reloading DataTable:', error);
    }
}

/**
 * Add filter capability to DataTable with status badges
 * 
 * @param {object} table - DataTable instance
 * @param {string} filterValue - Value to filter
 */
function filterDataTable(table, filterValue) {
    if (!table || typeof table.search === 'undefined') {
        return; // Silently fail for performance
    }
    
    try {
        table.search(filterValue).draw();
    } catch (error) {
        console.error('Error filtering DataTable:', error);
    }
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initEnhancedDataTable,
        destroyDataTable,
        safeReloadTable,
        filterDataTable
    };
}
