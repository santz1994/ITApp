// Main application JavaScript

// Bootstrap
require('bootstrap');

// Global jQuery
window.$ = window.jQuery = require('jquery');

// AdminLTE initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AdminLTE features
    if (typeof AdminLTE !== 'undefined') {
        AdminLTE.init();
    }
    
    // Custom theme configuration
    document.documentElement.style.setProperty('--primary-color', '#007bff');
    document.documentElement.style.setProperty('--secondary-color', '#6c757d');
    document.documentElement.style.setProperty('--success-color', '#28a745');
    document.documentElement.style.setProperty('--danger-color', '#dc3545');
    document.documentElement.style.setProperty('--warning-color', '#ffc107');
    document.documentElement.style.setProperty('--info-color', '#17a2b8');
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
});

// Global functions for common tasks
window.showNotification = function(message, type = 'success') {
    const alertClass = `alert-${type}`;
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert notification at top of main content
    const mainContent = document.querySelector('.content-wrapper .content');
    if (mainContent) {
        mainContent.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            const alert = mainContent.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
};

// Export/Import confirmation dialogs
window.confirmExport = function(type) {
    return confirm(`Are you sure you want to export all ${type} data?`);
};

window.confirmImport = function() {
    return confirm('Are you sure you want to import this data? This action cannot be undone.');
};

// Print functions
window.printElement = function(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print</title>
                    <link rel="stylesheet" href="/css/bootstrap.min.css">
                    <style>
                        body { font-family: Arial, sans-serif; }
                        @media print {
                            .no-print { display: none !important; }
                        }
                    </style>
                </head>
                <body>
                    ${element.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
};