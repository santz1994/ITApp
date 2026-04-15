## Tasks :

## Progress Update (2026-04-15)

### Approved and Implemented in Code
- Main Portal is now the default post-login landing page for all authenticated users via `/home` (alias: `/portal`).
- New role-aware portal layer implemented using Controller + Service + Repository:
    - `MainPortalController`
    - `MainPortalService`
    - `MainPortalRepository`
- Main Portal UI implemented with responsive module cards and KPI snapshot:
    - IT Support Module
    - Meeting Room
    - Assets Management
    - Purchase Request (mapped to current asset request flow)
    - Profile
    - User Management (authorized roles)
    - Settings (authorized roles)
    - KPI Dashboard (authorized roles)
    - LCD Screen access (authorized roles)
- Dashboard metrics displayed with user context and WIB time:
    - Open Tickets
    - Meetings Today
    - Pending Requests
    - Asset Count (global or personal based on role)
- Sidebar updated to include `Main Portal` for all authenticated users.
- Codebase cleanup completed:
    - Removed unused backup view file: `resources/views/Meeting/receptionist-dashboard-old-backup.blade.php`
- Added feature test coverage for main portal access:
    - Guest redirect behavior
    - Authenticated portal rendering

### In Progress / Next Batch
- Expand module-level dashboards (per role) with deeper KPI widgets.
- Integrate richer user info panel (division, location, latest activity).
- Continue modular refactor for Purchase Request domain (dedicated structure).

### Ideas Added (Pending Your Approval - No Code Yet)
- Add a compact "Portal Personalization" setting (module order and quick links per user).
- Add bilingual toggle indicator (ID/EN) directly in portal header.
- Add approval-center widget (tickets/meeting/purchase) with one-click action queue.

1. Refractor the codebase to improve readability and maintainability.
 - After login use main portal to navigate to different sections of the application.
 - Implement a dashboard to display key metrics and user information.
 - The Structure is change to a modular apllication with clear separation of concerns.
   a. IT Support Module: Handle all IT support related functionalities, including ticket management and user support.
   b. Meeting Room: Manage meeting room bookings, availability, and scheduling.
   c. Assets Management: Track and manage company assets, including inventory and maintenance schedules.
   d. User Management: Handle user accounts, roles, and permissions within the application. (Use by Administrator and authorized roles only)
   e. Purchase Request: Manage purchase requests, approvals, and tracking.
   f. Settings: All application settings and configurations will be managed in this module. (Use by Administrator and authorized roles only)
   g. Profile: Allow users to view and update their profile information, including changing passwords and managing personal settings.
 - Each module will have its own set of features and functionalities, allowing for better organization and scalability
 - Assets Management will have new structure to track and manage company assets, including inventory and maintenance schedules.
 - IT Support Module and Assets Management will have connection to each other to track the assets that are being used for IT support tickets and their maintenance schedules.
 - Purchase Request module will have a new structure to manage purchase requests, approvals, and tracking, allowing for better organization and efficiency in handling procurement processes, and connect with the Assets Management module to track the assets that are being purchased and their maintenance schedules.
 - To display the Meeting room in LCD screen, we will implement a new feature that allows users to view the meeting room schedule and availability on a large display. This will provide a convenient way for employees to check the meeting room status and plan their meetings accordingly. (1 page for LCD screen)
 - Add Chatbox feature to allow users to communicate with support staff in real-time for IT support and meeting room inquiries. This will enhance user experience and provide quick assistance for any issues or questions they may have. (Pending need my approval)
2. Implement a responsive design to ensure the application is accessible on various devices.
 - Use CSS frameworks like Bootstrap or Tailwind CSS to create a responsive layout.
    - Ensure that all features and functionalities are accessible and usable on mobile devices, tablets, and desktops.
3. Integrate a notification system to keep users informed about important updates and events.
    - Implement email notifications for critical events such as ticket updates, meeting room bookings, and asset maintenance reminders. (Pending need my approval)
    - Use in-app notifications to provide real-time updates to users while they are using the application.
4. Enhance security measures to protect user data and prevent unauthorized access.
    - Implement role-based access control to restrict access to sensitive features and data based on user roles and permissions.
    - Use encryption to protect sensitive data, such as user passwords and personal information.
5. Optimize performance to ensure fast loading times and smooth user experience.
    - Implement caching strategies to reduce server load and improve response times.
    - Optimize database queries to enhance performance and reduce latency.


## Structure of the application:

# LOGIN PAGE
- User Login (Username/Email and Password) additionally with "Remember Me" option for persistent login sessions.
    - Forgot Password (Allow users to reset their password through email verification)
    - User Registration (Allow new users to create an account with email verification for added security)

# MAIN PORTAL
A. Common user interface for all modules:
- Portal Menu (Dashboard)
    - IT Support Module
        - Ticket Management
            - Create Ticket
            - View Tickets
        - User Support
            - FAQ
            - Contact Support
    - Meeting Room
        - Book Meeting Room
            - Create Booking
            - View Bookings
            - Update Booking Status
        - View Meeting Room Schedule (Calendar View)
        - View Availability (LCD View)
    - Purchase Request
        - Create Purchase Request
        - View Purchase Requests
    - Form Request
        - Form Handover Asset (Create, and View forms for asset handover processes)
        - Form Asset Lending (Create, and View forms for asset lending processes)
        - Form Asset Return (Create, and View forms for asset return processes)
    - Profile
        - View Profile
        - Update Profile
        - Change Password

B. Director Interface
- Portal Menu (Dashboard)
    - IT Support Module
        - View all tickets created by users under the director's supervision
        - View all user support inquiries and responses for users under the director's supervision
        - View KPI Dashboard for IT support performance metrics related to users under the director's supervision
    - Meeting Room
        - View all meeting room bookings and schedules for users under the director's supervision
    - Assets Management
        - View all assets and their maintenance schedules for users under the director's supervision
    - Purchase Request
        - Director purchase request management (View, approve/reject, and purchase requests)
    - Form Request
        - View all forms for asset handover, lending, and return processes created by users under the director's supervision
    - Profile
        - View Profile
        - Update Profile
        - Change Password

C. Receptionist Interface
- Portal Menu (Dashboard)
    - Meeting Room
        - View and manage all meeting room bookings and schedules for users under the receptionist's supervision. (Can drag and drop to update booking room status)
        - View Availability (LCD View)
    - Profile
        - View Profile
        - Update Profile
        - Change Password
    - User Support
        - FAQ
        - Contact Support
    - LCD Screen
        - View meeting room schedule and availability on a large display for easy reference by employees.

D. Administrator Interface (IT Support Staff)
- Portal Menu (Dashboard)
    - IT Support Module
        - Ticket Management
            - Create Ticket
            - View Tickets
            - Update Ticket Status
        - View Tickets (All tickets created by users in the organization)
        - KPI Dashboard (View key performance indicators related to IT support, such as ticket resolution time and user satisfaction)
        - User Support
            - FAQ
            - Contact Support
    - Meeting Room
        - Book Meeting Room
            - Create Booking
            - View Bookings
            - Update Booking Status
        - View Meeting Room Schedule (Calendar View)
        - View Availability (LCD View)
        - Administrator meeting room management (View, approve/reject, and meeting room bookings)
        - Meeting Rooms Setting (Configure the Meeting room settings such as room capacity, equipment availability, LCD display settings, and booking rules)
    - Assets Management
        - Form Management
            - Form Handover Asset (Create, manage, update, and print forms for asset handover processes)
            - Form Disposal Asset (Create, manage, update, and print forms for asset disposal processes)
            - Form Asset Lending (Create, manage, update, and print forms for asset lending processes)
            - Form Asset Return (Create, manage, update, and print forms for asset return processes of Handover, and Lending)
        - Inventory Management
            - Add Asset
            - View Assets
            - Update Asset Information
        - Maintenance Schedule
            - Create Maintenance Schedule
            - View Maintenance Schedules
        - Print Label (Generate and print labels for assets, including QR codes or barcodes for easy tracking and management)
            - Setting Label (Configure label settings such as label format, content, and printing options)
        - Asset Import/Export (Import and export asset data in bulk using CSV or Excel files for efficient asset management and record-keeping)
        - Setting Asset (Configure asset settings such as asset categories, locations, and maintenance rules)
        - QR Code Scanning (Implement QR code scanning functionality to quickly access asset information and update asset status using a mobile device or scanner)
    - Purchase Request
        - Create Purchase Request
        - View Purchase Requests
        - Update Purchase Request Status
        - Administrator purchase request management (View, approve/reject, and purchase requests)
    - User Management (Use by Administrator and Developer only)
        - Create Roles and Permissions
        - Create User Account
        - View User Accounts
            - Update User Account Information
            - View User Roles and Permissions
            - Update User Account Status (Activate/Deactivate)
            - Delete User Account
    - Settings (Use by Administrator and Developer only)
        - Application Settings Management (Configure application settings and preferences) -> (Low code development settings management, such as managing API integrations, configuring system parameters, and customizing application features without requiring extensive coding knowledge)
    - Profile
        - View Profile
        - Update Profile
        - Change Password

E. Developer Interface (IT Programmer Staff)
- Portal Menu (Dashboard)
    - IT Support Module
        - Ticket Management
            - Create Ticket
            - View Tickets
            - Update Ticket Status
        - View Tickets (All tickets created by users in the organization)
        - KPI Dashboard (View key performance indicators related to IT support, such as ticket resolution time and user satisfaction)
        - User Support
            - FAQ
            - Contact Support
    - Meeting Room
        - Book Meeting Room
            - Create Booking
            - View Bookings
            - Update Booking Status
        - View Meeting Room Schedule (Calendar View)
        - View Availability (LCD View)
        - Administrator meeting room management (View, approve/reject, and meeting room bookings)
        - Meeting Rooms Setting (Configure the Meeting room settings such as room capacity, equipment availability, LCD display settings, and booking rules)
    - Assets Management
        - Form Management
            - Form Handover Asset (Create, manage, update, and print forms for asset handover processes)
            - Form Disposal Asset (Create, manage, update, and print forms for asset disposal processes)
            - Form Asset Lending (Create, manage, update, and print forms for asset lending processes)
            - Form Asset Return (Create, manage, update, and print forms for asset return processes of Handover, and Lending)
        - Inventory Management
            - Add Asset
            - View Assets
            - Update Asset Information
        - Maintenance Schedule
            - Create Maintenance Schedule
            - View Maintenance Schedules
        - Print Label (Generate and print labels for assets, including QR codes or barcodes for easy tracking and management)
            - Setting Label (Configure label settings such as label format, content, and printing options)
        - Asset Import/Export (Import and export asset data in bulk using CSV or Excel files for efficient asset management and record-keeping)
        - Setting Asset (Configure asset settings such as asset categories, locations, and maintenance rules)
        - QR Code Scanning (Implement QR code scanning functionality to quickly access asset information and update asset status using a mobile device or scanner)
    - Purchase Request
        - Create Purchase Request
        - View Purchase Requests
        - Update Purchase Request Status
        - Administrator purchase request management (View, approve/reject, and purchase requests)
    - User Management (Use by Administrator and Developer only)
        - Create Roles and Permissions
        - Create User Account
        - View User Accounts
            - Update User Account Information
            - View User Roles and Permissions
            - Update User Account Status (Activate/Deactivate)
            - Delete User Account
        - Update User Roles Permissions
    - Settings (Use by Administrator and Developer only)
        - Application Settings Management (Configure application settings and preferences)-> (Low code development settings management, such as managing API integrations, configuring system parameters, and customizing application features without requiring extensive coding knowledge)
        - Data Backup and Restore (Manage data backup and restore processes to ensure data integrity and availability)
        - System Logs (View and manage system logs for monitoring and troubleshooting purposes)
        - User Activity Monitoring (Monitor user activities and interactions within the application for security and performance analysis)
        - API Management (Manage API integrations and configurations for seamless connectivity with external systems and services)
        - System Maintenance (Schedule and manage system maintenance tasks to ensure optimal performance and reliability)
        - Chatbox Management (Manage and configure the chatbox feature for real-time communication with support staff)
        - AI Management (Manage and configure AI features and functionalities within the application, such as chatbots, predictive analytics, and automation tools)
    - Profile
        - View Profile
        - Update Profile
        - Change Password

## Additional Features:
- Use Indonesian Region Timezone (WIB) for all date and time-related functionalities within the application. This will ensure that all timestamps, schedules, and notifications are displayed in the correct local time for users in Indonesia, providing a more accurate and relevant user experience.
- Use duo language (Indonesian and English) for the user interface to cater to a wider range of users and enhance accessibility. This will allow users to choose their preferred language for navigating the application and understanding its features, improving overall user experience and satisfaction.
- Implement a chatbox feature to allow users to communicate with support staff in real-time for IT support and meeting room inquiries. This will enhance user experience and provide quick assistance for any issues or questions they may have. (Pending need my approval)
- Implement AI features and functionalities within the application, such as chatbots for automated support, predictive analytics for performance insights, and automation tools to streamline processes. This will enhance the overall user experience and improve efficiency in handling various tasks and requests. (Pending need my approval) (AI features will be implemented in the future phase of the project, and specific functionalities will be determined based on user needs and feedback) (Just for Administrator, Director and Developer use only)
- Implement email notifications for critical events such as ticket updates, meeting room bookings, and asset maintenance reminders. This will ensure that users are promptly informed about important updates and can take necessary actions in a timely manner. (Pending need my approval)
- Notify users about upcoming meetings, ticket updates, and asset maintenance schedules through in-app notifications. This will provide real-time updates to users while they are using the application and help them stay informed about important events and deadlines.
- Implement role-based access control to restrict access to sensitive features and data based on user roles and permissions. This will enhance security measures and ensure that only authorized users can access certain functionalities and information within the application.
- Use encryption to protect sensitive data, such as user passwords and personal information. This will help safeguard user data and prevent unauthorized access, ensuring the privacy and security of user information within the application.
- Implement caching strategies to reduce server load and improve response times. This will enhance the performance of the application and provide a smoother user experience, especially during peak usage times.
- Optimize database queries to enhance performance and reduce latency. This will ensure that the application can handle large volumes of data efficiently and provide fast response times for users, improving overall performance and user satisfaction.

## Guidelines for AI:
- When generating code, ensure that it adheres to the coding guidelines outlined above and follows best practices.
# Coding Guidelines:
- Follow the SOLID principles of object-oriented programming to ensure that the codebase is modular, maintainable, and scalable.
- Use meaningful variable and function names to enhance code readability and maintainability.
- Write clear and concise comments to explain the purpose and functionality of complex code sections.
- Adhere to a consistent coding style and formatting guidelines to improve code readability and maintainability.
- Implement error handling and logging mechanisms to facilitate debugging and troubleshooting.
# Assets Management Guidelines:
- Ensure that all assets are properly categorized and tagged for easy tracking and management.
- Regularly update and maintain asset information to ensure accuracy and completeness.
- Implement a robust asset tracking system to monitor the location, status, and usage of all assets within the organization.
    Categorize assets based on
    - Category (e.g., Asset, SparePart, Software License, Consumable, Vendor, etc.) (Code format: C**)
    - Asset Type (e.g., PC, Laptop, Printer, Network Device, Storage, Monitor, CCTV, , etc.) (Code format: A**)
    - Brand (e.g., Dell, HP, Lenovo, Cisco, etc.) (Code format: B**)
    - Purchasing Date (e.g., dd-mm-yyyy) (Code format: MMYY)
    - Location (e.g., Office, Warehouse, etc.) (Code format: L)
    - Department (e.g., IT, HR, Finance, etc.) (Code format: D)
    - Status (e.g., In Use, Available, Under Maintenance, Retired, etc.)
    - Building (e.g., Building A, Building B, etc.) (Code format: *)
    - Factory (e.g., Factory 1, Factory 2, etc.) (Code format: *)
    - User (e.g., John Doe, Jane Smith, etc.)
    - logs (e.g., Maintenance history, Usage history, Ticket History, etc.)
    - Maintenance Schedule (e.g., Monthly, Quarterly, Annually, etc.)
    - Maintenance History (e.g., Date of maintenance, Type of maintenance, Technician, etc.)
    - Disposal History (e.g., Date of disposal, Method of disposal, Reason for disposal, etc.)
    - Lending History (e.g., Date of lending, Borrower, Expected return date, etc.)
    - Return History (e.g., Date of return, Condition of asset, etc.)
    - QR Code (e.g., Unique identifier for each asset, used for quick access to asset information and tracking)
    Label standard:
    - Factory: [Factory] (Code format: F)
    - Category: [Category] (Code format: C**)
    - Asset Type: [Asset Type] (Code format: A**)
    - Brand: [Brand] (Code format: B**)
    - Purchase Date: [Purchasing Date] (MMYY) (Code format: MMYY)
    - Building: [Building] (Code format: B)
    - Location: [Location] (Code format: L)
    - Department: [Department] (Code format: D)
        - Code format: C**-A**-B**-MMYY-BLD
        C** is Category + [2 Alphabet of the category name]
        A** is Asset Type + [2 Alphabet of the asset type name]
        B** is Brand + [2 Alphabet of the brand name]
        MMYY is Purchase Date
        BLD is Building+Location+Department -> [1 Alphabet of the building name][1 Alphabet of the location name][1 Alphabet of the department name]
    - QR Code: [QR Code] (Unique identifier for each asset, using Code format)
# IT Support Module Guidelines:
- Ensure that all support tickets are properly categorized and prioritized for efficient handling.
- Implement a clear and efficient ticket resolution process to ensure timely resolution of support requests.
- Provide comprehensive documentation and resources for support staff to effectively handle user inquiries and issues.
# Meeting Room Guidelines:
- Ensure that all meeting room bookings are properly scheduled and managed to avoid conflicts and ensure availability.
- Implement clear guidelines for meeting room usage, including booking rules, cancellation policies, and equipment usage protocols.
- Regularly update and maintain meeting room information, including availability, capacity, and equipment details.
# Purchase Request Guidelines:
- Implement a clear and efficient purchase request process to ensure timely handling of procurement requests.
- Ensure that all purchase requests are properly categorized and prioritized for efficient handling.
- Provide comprehensive documentation and resources for procurement staff to effectively manage purchase requests and approvals.