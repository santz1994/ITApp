<!-- REQUIRED JS SCRIPTS -->

<!-- Custom JS script -->
<script src="{{ asset('/js/all.js') }}" type="text/javascript"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- Enhanced UX JavaScript -->
<script src="{{ asset('/js/enhanced-ux.js') }}" type="text/javascript"></script>

<!-- Modern UI/UX Enhancements V2 JavaScript -->
<script src="{{ asset('/js/ui-enhancements-v2.js') }}" type="text/javascript"></script>

<!-- Main header enhancements (custom) -->
<script src="{{ asset('/js/mainheader-enhancements.js') }}" type="text/javascript"></script>

<!-- Critical action confirmation and submit-guard -->
<script src="{{ asset('/js/critical-action-modal.js') }}" type="text/javascript"></script>

<!-- Frontend Core (theme + shared UI behaviors) -->
<script type="text/javascript">
window.ITAPP_FRONTEND_CONFIG = {
    preferenceUpdateUrlTemplate: '{{ route("api.portal-preferences.update", ["key" => "__KEY__"]) }}'
};
</script>
<script src="{{ asset('/js/frontend-core.js') }}" type="text/javascript"></script>

<!-- AdminLTE Initialization Script -->
<script type="text/javascript">
$(document).ready(function() {
    // Initialize AdminLTE
    if (typeof $.AdminLTE !== 'undefined') {
        // Activate layout features
        $.AdminLTE.layout.activate();
        
        // Activate push menu (sidebar toggle)
        $.AdminLTE.pushMenu.activate('[data-toggle="offcanvas"]');
        
        // Activate tree menu for sidebar with proper configuration
        if ($.AdminLTE.tree) {
            $.AdminLTE.tree('.sidebar-menu');
        }
        
        // Fix layout on window resize
        $(window).resize(function(){
            $.AdminLTE.layout.fix();
            $.AdminLTE.layout.fixSidebar();
        });
        
        // Handle responsive behavior for different screen sizes  
        function handleResponsiveLayout() {
            var windowWidth = $(window).width();
            
            // Mobile/Tablet breakpoint (768px)
            if (windowWidth <= 767) {
                $('body').removeClass('sidebar-mini sidebar-collapse')
                        .addClass('sidebar-collapse');
            } else if (windowWidth >= 768 && windowWidth <= 991) {
                // Tablet landscape
                $('body').addClass('sidebar-mini sidebar-collapse');
            } else {
                // Desktop - maintain current state but ensure proper classes
                if (!$('body').hasClass('sidebar-mini')) {
                    $('body').addClass('sidebar-mini');
                }
            }
        }
        
        // Run on page load
        handleResponsiveLayout();
        
        // Run on window resize
        $(window).resize(function(){
            handleResponsiveLayout();
        });
        
        // Handle sidebar toggle button
        $('[data-toggle="offcanvas"]').click(function(e) {
            e.preventDefault();
            
            var windowWidth = $(window).width();
            
            if (windowWidth > 767) {
                // Desktop behavior
                if ($('body').hasClass('sidebar-collapse')) {
                    $('body').removeClass('sidebar-collapse').trigger('expanded.pushMenu');
                } else {
                    $('body').addClass('sidebar-collapse').trigger('collapsed.pushMenu');
                }
            } else {
                // Mobile behavior
                if ($('body').hasClass('sidebar-open')) {
                    $('body').removeClass('sidebar-open').trigger('collapsed.pushMenu');
                } else {
                    $('body').addClass('sidebar-open').trigger('expanded.pushMenu');
                }
            }
        });
        
        // Close sidebar when clicking content on mobile
        $('.content-wrapper').click(function() {
            if ($(window).width() <= 767 && $('body').hasClass('sidebar-open')) {
                $('body').removeClass('sidebar-open');
            }
        });
    }
});
</script>

<!-- Optionally, you can add Slimscroll and FastClick plugins.
      Both of these plugins are recommended to enhance the
      user experience. Slimscroll is required when using the
      fixed layout. -->
