# Master Data Import System - User Guide

## Overview
The unified import system allows you to import all master data types in a single Excel file with multiple sheets. Each sheet represents a different data type.

## Access
- **URL:** http://192.168.1.87/imports
- **Required Role:** Admin or Super Admin

## How to Use

### Step 1: Download Template
1. Navigate to http://192.168.1.87/imports
2. Click the **"Download Excel Template"** button
3. Save the file: `master-data-template-YYYY-MM-DD.xlsx`

### Step 2: Fill in Your Data
Open the template file and fill in the sheets you need:

#### **Divisions Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Division name | IT Department |
| Description | No | Description | Information Technology |

#### **Locations Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Location name | Head Office |
| City | No | City name | Jakarta |
| Address | No | Full address | Jl. Sudirman No. 123 |

#### **Users Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Full name | John Doe |
| Email | Yes | Email address | john@quty.co.id |
| Password | No | Password (default: 123456) | MyPassword123 |
| Division | No | Division name (must exist) | IT Department |
| Location | No | Location name (must exist) | Head Office |
| Phone | No | Phone number | 081234567890 |
| Role | No | Role name (default: User) | User, Admin, Super Admin |

**Notes:**
- Email must be unique
- If password is empty, default is `123456`
- All notification preferences are enabled by default
- Division and Location must be created first or exist in database

#### **Roles Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Role identifier | admin |
| Display Name | No | Friendly name | Administrator |
| Description | No | Role description | Full system access |

#### **Permissions Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Permission identifier | create-asset |
| Display Name | No | Friendly name | Create Asset |
| Description | No | Permission description | Can create assets |

#### **Asset_Types Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Asset type name | Laptop |
| Description | No | Description | Portable computers |

#### **Manufacturers Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Manufacturer name | Dell |
| Email | No | Contact email | sales@dell.com |
| Phone | No | Contact phone | 021-12345678 |
| Address | No | Company address | Jakarta |

#### **Asset_Models Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Model name | Latitude 5420 |
| Model Number | No | Model number | LAT-5420 |
| Manufacturer | No | Manufacturer name (must exist) | Dell |
| Asset Type | No | Asset type name (must exist) | Laptop |

#### **Suppliers Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Supplier name | PT ABC |
| Email | No | Contact email | sales@abc.com |
| Phone | No | Contact phone | 021-98765432 |
| Address | No | Company address | Jakarta |
| Contact Person | No | Contact person name | John Smith |

#### **Statuses Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Status name | Available |
| Description | No | Status description | Asset is available |

#### **Warranty_Types Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Warranty type name | Standard Warranty |
| Duration (Months) | No | Duration in months | 12 |

#### **Ticket_Priorities Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Priority name | High |
| Color | No | Hex color code | #dc3545 |

#### **Ticket_Statuses Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Status name | Open |
| Color | No | Hex color code | #17a2b8 |

#### **Ticket_Types Sheet**
| Column | Required | Description | Example |
|--------|----------|-------------|---------|
| Name | Yes | Type name | Hardware |
| Description | No | Type description | Hardware issues |

### Step 3: Upload and Import
1. Go back to http://192.168.1.87/imports
2. Click **"Choose File"** and select your filled Excel file
3. Click **"Upload and Import"**
4. Wait for processing (usually takes a few seconds)
5. Review the import results

## Import Results

After import, you will see a results table showing:
- **Sheet Name** - Which data type was imported
- **Imported** - Number of records successfully imported/updated
- **Skipped** - Number of empty or invalid rows skipped

## Important Rules

### Data Dependencies
Import in this order for best results:
1. **Divisions** and **Locations** (no dependencies)
2. **Roles** and **Permissions** (no dependencies)
3. **Users** (depends on Divisions, Locations, Roles)
4. **Asset Types** and **Manufacturers** (no dependencies)
5. **Asset Models** (depends on Manufacturers, Asset Types)
6. **Suppliers**, **Statuses**, **Warranty Types** (no dependencies)
7. **Ticket Types**, **Ticket Priorities**, **Ticket Statuses** (no dependencies)

### Update vs Create
- If a record with the same unique identifier exists, it will be **updated**
- If not, a new record will be **created**
- Unique identifiers:
  - Users: email
  - Divisions: name
  - Locations: name
  - Roles: name
  - Asset Models: name + model_number
  - All others: name

### Sheet Names
Sheet names are case-insensitive but must match exactly:
- ✅ "Divisions" or "divisions"
- ✅ "Asset_Types" or "asset_types"
- ❌ "Division" (singular)
- ❌ "AssetTypes" (no underscore)

### Empty Rows
- Rows with empty required fields are automatically skipped
- No need to delete empty rows from template

### File Size
- Maximum file size: 10 MB
- Supported formats: .xlsx, .xls

## Examples

### Example 1: Import Only Users
1. Download template
2. Delete all sheets except "Users" and "Divisions"
3. Fill in Divisions sheet with departments
4. Fill in Users sheet with employee data
5. Upload

### Example 2: Complete Setup
1. Download template
2. Fill ALL sheets with your organization's data
3. Make sure related data exists (e.g., Division before User)
4. Upload once - all data imported!

### Example 3: Update Existing Data
1. Download template
2. Fill sheets with updated information
3. Use same names/emails as existing records
4. Upload - existing records will be updated

## Troubleshooting

### "Skipped" Count is High
- Check for empty rows in your Excel sheets
- Ensure required fields are filled
- Verify related records exist (e.g., Division for User)

### Import Errors
- Check error messages displayed after import
- Common issues:
  - Invalid email format
  - Duplicate emails for users
  - Referenced division/location doesn't exist
  - Invalid data types

### Some Data Not Imported
- Ensure sheet names match exactly
- Check column order matches template
- Don't add/remove/reorder columns

## Tips

1. **Start Small**: Import Divisions and Locations first, then Users
2. **Test First**: Try with 2-3 rows before bulk import
3. **Backup**: Always backup your database before large imports
4. **Review Template**: Check example rows in template for correct format
5. **Keep Template**: Save the original template for future imports

## Support

For issues or questions:
- Check error messages in import results
- Verify data format matches template examples
- Contact IT Administrator if problems persist

---

**Last Updated:** December 5, 2025  
**System Version:** IT Quty v2.0  
**Import System:** Unified Excel Import
