# UI/UX Enhancements V2 - Developer Guide

## Overview

This guide covers the new modern UI/UX enhancements (V2) added to the ITQuty Laravel application. These improvements provide a consistent, professional user experience with modern JavaScript utilities and CSS components.

## Table of Contents

1. [Installation](#installation)
2. [Toast Notifications](#toast-notifications)
3. [Loading States](#loading-states)
4. [Enhanced Modals](#enhanced-modals)
5. [Form Enhancements](#form-enhancements)
6. [DataTable Improvements](#datatable-improvements)
7. [Utility Functions](#utility-functions)
8. [CSS Components](#css-components)

---

## Installation

### Files Added

- **CSS:** `public/css/ui-enhancements-v2.css`
- **JavaScript:** `public/js/ui-enhancements-v2.js`

### Integration

Files are automatically included in the main layout via:
- `resources/views/layouts/partials/htmlheader.blade.php` (CSS)
- `resources/views/layouts/partials/scripts.blade.php` (JavaScript)

---

## Toast Notifications

### Usage

```javascript
// Success notification
showToast('success', 'Success!', 'Operation completed successfully');

// Error notification
showToast('error', 'Error!', 'Something went wrong');

// Warning notification
showToast('warning', 'Warning!', 'Please be careful');

// Info notification
showToast('info', 'Information', 'This is important');
```

### Parameters

- **type:** `success`, `error`, `warning`, `info`
- **title:** Toast title (required)
- **message:** Toast message (optional)
- **duration:** Auto-dismiss time in milliseconds (default: 5000, 0 = no auto-dismiss)

### Example

```javascript
// Toast with custom duration (2 seconds)
showToast('success', 'Saved', 'Your changes have been saved', 2000);

// Toast that doesn't auto-dismiss
showToast('warning', 'Important', 'Please read this carefully', 0);
```

---

## Loading States

### Full Page Loading Overlay

```javascript
// Show loading overlay
showLoading('Processing your request...');

// Hide loading overlay
hideLoading();
```

### Example

```javascript
$('#saveButton').on('click', function() {
    showLoading('Saving data...');
    
    $.ajax({
        url: '/api/save',
        method: 'POST',
        data: formData,
        success: function(response) {
            hideLoading();
            showToast('success', 'Saved', 'Data saved successfully');
        },
        error: function() {
            hideLoading();
            showToast('error', 'Error', 'Failed to save data');
        }
    });
});
```

### Button Loading State

```javascript
const $btn = $('#saveButton');

// Enable loading state
$btn.buttonLoading(true);

// Disable loading state (restores original text)
$btn.buttonLoading(false);
```

### Example

```javascript
$('#submitForm').on('click', function() {
    const $btn = $(this);
    $btn.buttonLoading(true);
    
    setTimeout(() => {
        $btn.buttonLoading(false);
        showToast('success', 'Complete', 'Form submitted');
    }, 2000);
});
```

---

## Enhanced Modals

### Confirmation Dialog

```javascript
confirmAction({
    title: 'Delete Item?',
    message: 'Are you sure you want to delete this item?',
    confirmText: 'Yes, Delete',
    cancelText: 'Cancel',
    confirmClass: 'btn-danger',
    onConfirm: function() {
        // Action to perform on confirmation
        deleteItem();
    }
});
```

### Parameters

- **title:** Modal title (default: "Are you sure?")
- **message:** Modal message (default: "This action cannot be undone.")
- **confirmText:** Confirm button text (default: "Confirm")
- **cancelText:** Cancel button text (default: "Cancel")
- **confirmClass:** Bootstrap class for confirm button (default: "btn-danger")
- **onConfirm:** Callback function when confirmed

### Example

```javascript
$('.delete-button').on('click', function() {
    const itemId = $(this).data('id');
    
    confirmAction({
        title: 'Delete Record?',
        message: 'This will permanently delete the record. Continue?',
        confirmText: 'Delete',
        onConfirm: function() {
            $.ajax({
                url: '/api/records/' + itemId,
                method: 'DELETE',
                success: function() {
                    showToast('success', 'Deleted', 'Record deleted successfully');
                    location.reload();
                }
            });
        }
    });
});
```

---

## Form Enhancements

### Auto-Save Forms

```javascript
$('#myForm').autoSave({
    interval: 30000, // Save every 30 seconds
    url: '/api/autosave',
    onSave: function(response) {
        console.log('Auto-saved:', response);
    },
    onError: function(error) {
        console.error('Auto-save failed:', error);
    }
});
```

### Enhanced Validation

Add `data-validate="true"` attribute to enable automatic validation:

```html
<form data-validate="true">
    <div class="form-group">
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <input type="email" name="email" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### AJAX Form Submission

Add `data-ajax="true"` attribute for AJAX form handling:

```html
<form data-ajax="true" action="/api/save" method="POST">
    @csrf
    <div class="form-group">
        <input type="text" name="title" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success">Save</button>
</form>
```

**Or manually initialize:**

```javascript
$('#myForm').ajaxForm({
    onSuccess: function(response) {
        showToast('success', 'Saved', response.message);
        if (response.redirect) {
            window.location.href = response.redirect;
        }
    },
    onError: function(xhr) {
        showToast('error', 'Error', 'Failed to save');
    },
    showLoading: true,
    showSuccess: true
});
```

### Floating Labels

```html
<div class="form-floating">
    <input type="text" class="form-control" id="name" placeholder="Full Name">
    <label for="name">Full Name</label>
</div>

<div class="form-floating">
    <select class="form-control" id="role">
        <option value="">Choose...</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
    </select>
    <label for="role">Role</label>
</div>
```

### Custom Checkboxes/Radios

```html
<!-- Custom Checkbox -->
<div class="custom-checkbox">
    <input type="checkbox" id="terms">
    <label for="terms">I agree to terms</label>
</div>

<!-- Custom Radio -->
<div class="custom-radio">
    <input type="radio" id="option1" name="options">
    <label for="option1">Option 1</label>
</div>
```

---

## DataTable Improvements

### Enhanced DataTable Initialization

```javascript
const table = initEnhancedDataTable('#myTable', {
    pageLength: 25,
    order: [[0, 'desc']],
    // Additional DataTables options...
});
```

**Features:**
- Export buttons (Excel, CSV, PDF, Print)
- Column visibility toggle
- Responsive design
- Search highlighting
- Enhanced styling

### Bulk Selection

```javascript
// Initialize bulk selection
initBulkSelect('#myTable');
```

**HTML Structure:**

```html
<table id="myTable">
    <thead>
        <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><input type="checkbox" class="select-item"></td>
            <td>John Doe</td>
            <td>john@example.com</td>
        </tr>
    </tbody>
</table>

<div class="bulk-actions" style="display: none;">
    <span class="selected-count">0</span> items selected
    <button class="btn btn-danger">Delete Selected</button>
</div>
```

---

## Utility Functions

### Format Number

```javascript
const formatted = formatNumber(1234567.89);
// Output: "1,234,567.89"
```

### Format Currency

```javascript
const price = formatCurrency(1234567.89, 'Rp');
// Output: "Rp 1,234,567.89"
```

### Copy to Clipboard

```javascript
copyToClipboard('Text to copy');
// Shows success toast automatically
```

### Debounce

```javascript
const debouncedSearch = debounce(function(query) {
    // Perform search
    console.log('Searching for:', query);
}, 500);

$('#searchInput').on('input', function() {
    debouncedSearch($(this).val());
});
```

### Generate Random ID

```javascript
const uniqueId = generateId('item');
// Output: "item_x7k9m2p4q"
```

---

## CSS Components

### Enhanced Stat Cards

```html
<div class="stat-card stat-card-primary">
    <div class="stat-icon">
        <i class="fa fa-users"></i>
    </div>
    <div class="stat-content">
        <div class="stat-value">1,234</div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-footer">
        <i class="fa fa-arrow-up"></i> 12% from last month
    </div>
</div>
```

**Available colors:**
- `stat-card-primary` (blue)
- `stat-card-success` (green)
- `stat-card-warning` (orange)
- `stat-card-danger` (red)
- `stat-card-info` (cyan)

### Enhanced Badges

```html
<span class="badge badge-primary">Primary</span>
<span class="badge badge-success">Success</span>
<span class="badge badge-warning">Warning</span>
<span class="badge badge-danger">Danger</span>
<span class="badge badge-info">Info</span>
```

### Enhanced Buttons

```html
<button class="btn btn-primary">
    <i class="fa fa-save"></i> Save
</button>

<button class="btn btn-success">
    <i class="fa fa-check"></i> Approve
</button>

<!-- Loading state is automatic with .buttonLoading() -->
```

---

## Best Practices

### 1. Error Handling

Always show feedback to users:

```javascript
$.ajax({
    url: '/api/endpoint',
    method: 'POST',
    data: data,
    success: function(response) {
        showToast('success', 'Success', response.message);
    },
    error: function(xhr) {
        let message = 'An error occurred';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        showToast('error', 'Error', message);
    }
});
```

### 2. Loading States

Show loading during async operations:

```javascript
$('#submitButton').on('click', function() {
    const $btn = $(this);
    $btn.buttonLoading(true);
    
    performAsyncOperation()
        .then(() => {
            $btn.buttonLoading(false);
            showToast('success', 'Complete', 'Operation finished');
        })
        .catch(() => {
            $btn.buttonLoading(false);
            showToast('error', 'Failed', 'Operation failed');
        });
});
```

### 3. Confirmation Dialogs

Always confirm destructive actions:

```javascript
$('.delete-btn').on('click', function() {
    confirmAction({
        title: 'Delete Item?',
        message: 'This action cannot be undone.',
        confirmClass: 'btn-danger',
        onConfirm: function() {
            // Perform delete
        }
    });
});
```

### 4. Form Validation

Enable automatic validation:

```html
<form data-validate="true">
    <!-- Form fields with required attributes -->
</form>
```

---

## Browser Support

- Chrome: ✅ Latest
- Firefox: ✅ Latest
- Safari: ✅ Latest
- Edge: ✅ Latest
- IE11: ⚠️ Limited (basic functionality)

---

## Troubleshooting

### Toast not appearing

Ensure jQuery is loaded before `ui-enhancements-v2.js`:

```html
<script src="/plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="/js/ui-enhancements-v2.js"></script>
```

### DataTable buttons not working

Include required CDN libraries:

```html
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
```

### Form validation not working

Add `data-validate="true"` attribute to form:

```html
<form data-validate="true">
```

---

## Examples

See `resources/views/demo-enhancements.blade.php` for comprehensive examples of all components.

---

## Support

For questions or issues:
1. Check this documentation
2. Review the demo page
3. Inspect browser console for errors
4. Contact the development team

---

**Last Updated:** November 21, 2025  
**Version:** 2.0  
**Author:** D-Riz
