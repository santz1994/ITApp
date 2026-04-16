## Database Structure:
- Design a database structure that supports the new modular design of the application, ensuring that all necessary tables and relationships are properly defined to support the new features and functionalities. The database structure should be optimized for performance and scalability, allowing for efficient data storage and retrieval while maintaining data integrity and security.
- The database structure should include tables for users, roles, permissions, tickets, meeting room bookings, assets, purchase requests, and any other relevant entities needed to support the application's features and functionalities. Each table should have appropriate fields and relationships defined to ensure data consistency and integrity across the application.
- Implement database indexing and optimization strategies to enhance query performance and ensure efficient data retrieval, especially as the application scales and handles larger volumes of data.
- Ensure that the database structure is flexible and adaptable to accommodate future enhancements and changes in the application's requirements, allowing for easy modifications and additions to the schema as needed.

# Database fields

1. Users Table:
- id (primary key)
- username
- email
- password
- first_name
- last_name
- role_id (foreign key)
- last_login_at
- is_active
- portal_preferences (JSON field to store user preferences for bilingual toggle and role badge display)
- profile_picture (URL or file path to the user's profile picture)
- created_at
- updated_at

2. Roles Table:
- id (primary key)
- name (e.g., Guest, User, Receptionist, Human Resources, Administrator, Director, Developer)
- access_level (integer representing the access level hierarchy)
- description (optional field to provide a brief description of the role)
- created_at
- updated_at

3. Permissions Table:
- id (primary key)
- name (e.g., view_tickets, manage_meeting_rooms, approve_purchase_requests, etc.)
- description (optional field to provide a brief description of the permission)
- created_at
- updated_at

4. Role_Permissions Table (pivot table for many-to-many relationship between roles and permissions):
- role_id (foreign key)
- permission_id (foreign key)
- created_at
- updated_at

5. Tickets Table:
- id (primary key)
- user_id (foreign key)
- title
- description
- status (e.g., open, in_progress, resolved, closed)
- created_at
- updated_at
- priority (e.g., low, medium, high)
- category (e.g., hardware, software, network, etc.)
- assigned_agent_id (foreign key referencing users table for ticket assignment)
- SLA_due_date (datetime field to track the service level agreement deadline for ticket resolution)
- status_history (JSON field to track the history of status changes for the ticket, including timestamps and user actions)
- resolution_notes (text field to store notes and details about the resolution of the ticket)

6. Meeting Room Bookings Table:
- id (primary key)
- user_id (foreign key)
- room_id (foreign key)
- start_time
- end_time
- status (e.g., pending, approved, rejected)
- created_at
- updated_at

7. Assets Table:
- id (primary key)
- name
- category
- asset_type
- brand
- purchase_date
- building
- location
- department
- status (e.g., in_use, available, under_maintenance, retired)
- created_at
- updated_at
- code (generated based on the defined code format for asset labeling)
- maintenance_schedule (e.g., monthly, quarterly, annually)
- ticket_history (JSON field to track the history of tickets related to the asset, including ticket IDs, statuses, and timestamps)
- maintenance_history (JSON field to track the history of maintenance activities for the asset, including dates, types of maintenance, and technicians involved)
- disposal_history (JSON field to track the history of asset disposal, including dates, methods of disposal, and reasons for disposal)
- lending_history (JSON field to track the history of asset lending, including dates, borrowers, and expected return dates)
- return_history (JSON field to track the history of asset returns, including dates and conditions of the asset upon return)
- QR_code (unique identifier for the asset, used for quick access to asset information and tracking)
- RFID_tag (optional field for assets that utilize RFID technology for tracking and management)
- warranty_expiration_date (datetime field to track the expiration date of the asset's warranty)
- supplier (optional field to store information about the asset's supplier or vendor)
- cost (optional field to store the cost of the asset for budgeting and financial tracking purposes)
- depreciation_schedule (optional field to track the depreciation of the asset over time for accounting purposes)
- location_history (JSON field to track the history of asset locations, including dates and new locations)
- assigned_user_id (foreign key referencing users table for tracking which user is currently assigned to the asset)
- maintenance_status (e.g., scheduled, in_progress, completed) to track the current maintenance status of the asset
- maintenance_notes (text field to store notes and details about the maintenance activities performed on the asset)
- disposal_status (e.g., pending, approved, completed) to track the current disposal status of the asset
- disposal_notes (text field to store notes and details about the disposal process of the asset)
- lending_status (e.g., pending, approved, completed) to track the current lending status of the asset
- lending_notes (text field to store notes and details about the lending process of the asset)
- return_status (e.g., pending, approved, completed) to track the current return status of the asset
- return_notes (text field to store notes and details about the return process of the asset)
- image (URL or file path to an image of the asset for visual reference)

8. Purchase Requests Table:
- id (primary key)
- user_id (foreign key)
- item_name
- category
- quantity
- justification
- status (e.g., pending, approved, rejected)
- created_at
- updated_at
- approval_history (JSON field to track the history of approvals for the purchase request, including approver IDs, statuses, and timestamps)
- vendor (optional field to store information about the vendor for the purchase request)
- estimated_cost (optional field to store the estimated cost of the purchase for budgeting purposes)
- actual_cost (optional field to store the actual cost of the purchase after approval and procurement)
- delivery_date (optional field to track the expected delivery date of the purchased items)
- receipt_image (URL or file path to an image of the purchase receipt for record-keeping and auditing purposes)
- purchase_notes (text field to store notes and details about the purchase request, including any special instructions or considerations for procurement)
- purchase_history (JSON field to track the history of purchase requests made by the user, including item names, statuses, and timestamps)

9. Asset Categories Table:
- id (primary key)
- name (e.g., Asset, Sparepart, Consumable, Tools, License, Vendor etc.)
- description (optional field to provide a brief description of the asset category)
- created_at
- updated_at

10. Asset Types Table:
- id (primary key)
- name (e.g., Laptop, Desktop, Printer, Network Device, etc.)
- description (optional field to provide a brief description of the asset type)
- created_at
- updated_at

11. Locations Table:
- id (primary key)
- name (e.g., Building A, Building B, Floor 1, Floor 2, etc.)
- description (optional field to provide a brief description of the location)
- created_at
- updated_at

12. Departments Table:
- id (primary key)
- name (e.g., IT, HR, Finance, etc.)
- description (optional field to provide a brief description of the department)
- created_at
- updated_at

13. Meeting Rooms Table:
- id (primary key)

