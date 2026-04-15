# Master Data Export System - User Guide

## Overview
Export all master data from the system in a single Excel file with multiple sheets. Perfect for backup, reporting, or migrating data.

## Access
- **Main Page:** http://192.168.1.87/exports
- **Direct Export:** http://192.168.1.87/exports/download
- **Templates:** http://192.168.1.87/exports/templates
- **Required Role:** Admin or Super Admin

## How to Export

### Method 1: From Overview Page
1. Navigate to http://192.168.1.87/exports
2. Click **"Export All Master Data"** button
3. File downloads automatically as `master-data-export-YYYY-MM-DD-HHMMSS.xlsx`

### Method 2: Direct Download
1. Go directly to http://192.168.1.87/exports/download
2. File downloads immediately

## Exported Data

The export file contains 14 sheets with all master data:

### 1. Divisions Sheet
- All company divisions
- Includes: Name, Description

### 2. Locations Sheet
- All office locations
- Includes: Name, City, Address

### 3. Users Sheet
- All system users
- Includes: Name, Email, Division, Location, Phone, Role
- **Note:** Passwords are NOT exported for security

### 4. Roles Sheet
- All user roles
- Includes: Name, Display Name, Description

### 5. Permissions Sheet
- All system permissions
- Includes: Name, Display Name, Description

### 6. Asset_Types Sheet
- All asset type categories
- Includes: Name, Description

### 7. Manufacturers Sheet
- All equipment manufacturers
- Includes: Name, Email, Phone, Address

### 8. Asset_Models Sheet
- All asset models
- Includes: Name, Model Number, Manufacturer, Asset Type

### 9. Suppliers Sheet
- All vendors/suppliers
- Includes: Name, Email, Phone, Address, Contact Person

### 10. Statuses Sheet
- All asset statuses
- Includes: Name, Description

### 11. Warranty_Types Sheet
- All warranty types
- Includes: Name, Duration (Months)

### 12. Ticket_Priorities Sheet
- All ticket priorities
- Includes: Name, Color (hex code)

### 13. Ticket_Statuses Sheet
- All ticket statuses
- Includes: Name, Color (hex code)

### 14. Ticket_Types Sheet
- All ticket categories
- Includes: Name, Description

## Use Cases

### Data Backup
- Regular backups of all master data
- Export before major changes
- Archive historical data

### Reporting
- Review all master data in one place
- Create custom reports in Excel
- Share data with stakeholders

### Data Migration
- Export from current system
- Modify data in Excel
- Re-import to update records

### Audit & Review
- Review user assignments
- Check data completeness
- Verify relationships between data

## File Format

- **Format:** Excel (.xlsx)
- **Sheets:** 14 sheets (one per data type)
- **Headers:** First row contains column names
- **Data:** Starting from row 2

## Import Back

The exported file can be re-imported:

1. Export all data
2. Modify in Excel (add, update, or remove data)
3. Go to http://192.168.1.87/imports
4. Upload the modified file
5. System will update existing records and create new ones

## Important Notes

### Security
- **Passwords:** User passwords are NOT exported
- **API Tokens:** User API tokens are NOT exported
- Users will need to reset passwords if creating from export

### Data Relationships
The export maintains relationships:
- Users linked to Divisions and Locations
- Asset Models linked to Manufacturers and Types
- All relationships exported by name

### Empty Data
- If a data type has no records, the sheet will only contain headers
- Empty sheets can be safely ignored

### File Size
- File size depends on amount of data
- Typically 50KB - 5MB
- Large datasets (1000+ users) may take longer to generate

## Comparison: Export vs Template

| Feature | Export | Template |
|---------|--------|----------|
| Contains Data | ✅ Yes (all current data) | ❌ No (sample data only) |
| Purpose | Backup/Report | Import guide |
| File Size | Varies | Small (~20KB) |
| Use For | Exporting | Learning format |

## Quick Actions

### Regular Backup Schedule
Recommended schedule:
- **Daily:** If data changes frequently
- **Weekly:** For normal operations
- **Monthly:** Minimum for data safety
- **Before Updates:** Before system changes

### Automated Backups
Consider setting up automated exports:
```bash
# Run export via command (if implemented)
php artisan masterdata:export
```

## Troubleshooting

### Export Takes Too Long
- Large datasets (5000+ records) may take 10-30 seconds
- Be patient, don't refresh the page
- File will download when ready

### Missing Data in Export
- Ensure you have admin/super-admin role
- Check if data exists in database
- Verify relationships (e.g., user has division)

### Cannot Open File
- Ensure you have Excel or compatible software
- Use LibreOffice or Google Sheets as alternatives
- Check file is not corrupted (try re-downloading)

### Want to Export Specific Data Only
- Export all data
- Open in Excel
- Delete unwanted sheets
- Save as new file

## Advanced Usage

### Bulk Updates
1. Export all data
2. Modify multiple records in Excel
3. Re-import to apply changes
4. Much faster than editing one by one

### Data Analysis
1. Export to Excel
2. Use pivot tables for analysis
3. Create charts and reports
4. Share insights with team

### Cross-System Sync
1. Export from System A
2. Modify for System B format
3. Import to System B
4. Keep systems in sync

## Support

For issues with export:
- **File Issues:** Contact IT Administrator
- **Missing Data:** Verify database access
- **Format Questions:** Refer to IMPORT_GUIDE.md

## Quick Reference

| Page | URL | Purpose |
|------|-----|---------|
| Overview | /exports | Main export page |
| Export Data | /exports/download | Download all data |
| Templates | /exports/templates | Download import template |
| Import | /imports | Upload and import data |

---

**Last Updated:** December 5, 2025  
**System Version:** IT Quty v2.0  
**Export System:** Unified Excel Export
