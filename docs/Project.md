## Tasks :

## Progress Update (2026-04-16)

### Newly Implemented in Code
- Purchase Request approval flow is now handled by a dedicated workflow service:
    - `PurchaseRequestApprovalWorkflowService`
    - `AssetRequestController` approve/reject/fulfill actions now delegate business logic to service layer.
    - Approval/rejection now persist notes consistently to `approval_notes` (legacy-safe).
    - Fulfillment transition now validates allowed status flow and writes audit log snapshots.
- Main Portal expanded with dedicated operational summary widgets:
    - IT Support Summary widget (open, assigned, urgent, resolved/closed + recent tickets table)
    - Meeting Room Summary widget (pending, approved, finished, cancelled/rejected + recent booking table)
    - New repository/service data providers added for ticket + meeting status breakdown and recent meeting list.
- Added module-specific feature tests for Purchase Request portal:
    - Guest redirect behavior for `/purchase-requests`
    - Authenticated portal rendering
    - Role-based visibility (`Review Pending Approvals` shown for admin only)
    - Data scoping verification (standard user sees own requests only, admin sees all)
- Codebase cleanup completed for clearly unused artifacts:
    - Removed `resources/views/debug-view-test.blade.php`
    - Removed `public/test_dynamic_form.html`

### In Progress / Next Batch
- Add dedicated Form Request classes for approval/reject/fulfill actions in Purchase Request domain.
- Add feature tests for approval transition guards (fulfilled/rejected edge cases).
- Add approval-center widget (tickets/meeting/purchase) on Main Portal (pending your approval).

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
- Database setup and restore completed on local MySQL (`localhost:3306`) using `root` (blank password, per current `.env`).
    - Target database created first: `itapp` (matches current `.env` value `DB_DATABASE=itapp`).
    - Pre-import backup artifact verified:
        - `database/pre_import_itapp_20260415_160706.sql`
        - Timestamp: `2026-04-15 16:07:06`
        - Size: `1263` bytes
    - SQL dump imported successfully: `database/backup_itquty_2026-04-15_122739.sql`
    - Post-import validation:
        - Tables: `65`
        - Assets: `156`
        - Users: `93`
        - Tickets: `0`
- Main Portal enhanced with role-focused operational snapshots and richer workspace context:
    - User context panel now shows division, location, building, last login, and account status.
    - Role Focus Snapshot now adapts KPI cards by role (user/receptionist/admin/management).
    - Dynamic quick links prevent dead-end route navigation for role-specific paths.
- Dedicated Purchase Request module hub implemented (Controller + Service + Repository + View):
    - New route: `/purchase-requests` (`purchase-requests.index`)
    - Summary cards: total, pending, approved this month, fulfilled this month
    - Status breakdown + quick actions + recent request table
    - Uses existing `asset_requests` flow for backward compatibility while preparing modular refactor
- Main Portal now includes purchase request snapshot table for cross-module visibility.

### In Progress / Next Batch
- Connect Purchase Request module to dedicated approval workflow service (separate from controller actions).
- Add module-specific feature tests for `/purchase-requests` and role-based visibility.
- Expand IT Support and Meeting Room modules with dedicated summary widgets similar to Purchase Request module.

### Ideas Added (Pending Your Approval - No Code Yet)
- Add a compact "Portal Personalization" setting (module order and quick links per user). (Approve by me)
- Add bilingual toggle indicator (ID/EN) directly in portal header. (Approve by me)
- Add approval-center widget (tickets/meeting/purchase) with one-click action queue. 

## Phase Ideas:
1. Refactor the codebase to improve readability and maintainability.
 - After login use main portal to navigate to different sections of the application.
 - Implement a dashboard to display key metrics and user information.
 - The Structure is change to a modular application with clear separation of concerns.
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
6. Database setup and restore:
    - Set up a local MySQL database using the credentials specified in the `.env` file.
    - Create the target database (`itapp`) if it does not already exist.
    - Import the provided SQL dump file (`database/backup_itquty_2026-04-15_122739.sql`) into the local MySQL database to restore the application data.
    - Change the database structure to match the new modular design of the application, ensuring that all necessary tables and relationships are properly defined to support the new features and functionalities. But modification the backup file to match the new structure of the database, and then import it to the local MySQL database.
7. UI/UX & Frontend improvements:
    - Redesign the user interface to align with the new modular structure of the application, ensuring that it is intuitive and user-friendly.
    - Implement a dashboard on the main portal to display key metrics and user information, providing users with a quick overview of their activities and relevant data.
    - Ensure that the design is responsive and accessible across different devices and screen sizes.
8. Implement additional features based on user feedback and requirements, such as:
    - A compact "Portal Personalization" setting to allow users to customize their portal experience by rearranging module order and adding quick links to frequently used features.
    - A bilingual toggle indicator (Indonesian/English) in the portal header to allow users to switch between languages easily.
    - An approval-center widget that aggregates pending tickets, meeting room bookings, and purchase requests for authorized users, allowing them to review and take action with one click.
9. Backend improvements:
    - Refactor the backend code to improve readability, maintainability, and scalability, following best practices and design patterns.
    - Implement a service layer to handle business logic and a repository layer for data access, ensuring a clear separation of concerns and easier maintenance.
    - Add comprehensive test coverage for the new features and functionalities to ensure reliability and facilitate future development.
10. Codebase cleanup:
    - Remove any unused or redundant code, files, and assets to improve maintainability and reduce clutter in the codebase.
    - Ensure that all remaining code is well-documented and follows consistent coding standards for better readability and collaboration among developers.
11. Ticketing:
    - Ticket auto-assignment based on asset category and technician expertise to streamline the support process and ensure that tickets are handled by the most qualified staff.
    - Implement a ticket escalation process for unresolved tickets to ensure timely resolution and customer satisfaction.
    - Add a knowledge base feature to allow support staff to document solutions and best practices for common issues, improving efficiency and consistency in handling support requests.
12. Meeting Room:
    - Implement a calendar view for meeting room schedules to provide a visual representation of bookings and availability.
    - Add a feature to allow users to check meeting room availability in real-time through an LCD display, providing convenient access to this information.
    - Implement a booking approval workflow for meeting rooms that require authorization, ensuring proper management of resources and preventing conflicts.
13. Assets Management:
    - Implement a comprehensive asset tracking system that includes inventory management, maintenance scheduling, and disposal processes.
    - Add a feature to generate and print labels for assets, including QR codes or barcodes for easy tracking and management.
    - Implement an asset import/export feature to allow bulk management of asset data using CSV or Excel files.
    - Add a QR code scanning functionality to quickly access asset information and update asset status using a mobile device or scanner.
14. Purchase Request:
    - Implement a clear and efficient purchase request process that includes request submission, approval workflows, and tracking of procurement status.
    - Add a feature to allow users to view the status of their purchase requests and receive notifications about updates or approvals.
    - Integrate the purchase request module with the assets management module to track the assets being purchased and their maintenance schedules.
15. User Management:
    - Implement a user management system that allows administrators to create and manage user accounts, roles, and permissions.
    - Add features for users to view and update their profile information, including changing passwords and managing personal settings.
    - Implement role-based access control to ensure that users only have access to features and data relevant to their roles.
    - Add a feature to allow administrators to monitor user activity and manage user sessions for enhanced security and oversight.
    - Implement a user activity monitoring system to track user interactions and identify potential security issues or performance bottlenecks.
    - Add an API management feature to allow administrators to manage API integrations and configurations for seamless connectivity with external systems and services.
    - Implement a system maintenance feature to allow administrators to schedule and manage system maintenance tasks, ensuring optimal performance and reliability of the application.
    - Add a chatbox management feature to allow administrators to manage and configure the chatbox feature for real-time communication with support staff, enhancing user experience and support efficiency.
    - Implement an AI management feature to allow administrators to manage and configure AI features and functionalities within the application, such as chatbots, predictive analytics, and automation tools, providing advanced capabilities and improving overall user experience.
    - All user can have many roles.
        - Roles: Guest, User, Receptionist, Human Resources, Administrator (IT Support Staff), Director (Management), Developer (IT Programmer Staff)
        - Access level :
            - LV 0 : Guest (Unauthenticated users, with access to limited features such as login and registration pages)
            - LV 1 : User (Default role for all authenticated users, with access to basic features and functionalities)
            - LV 2 : Receptionist (Access to meeting room management and user support features)
            - LV 3 : Human Resources (Access to user management and profile features)
            - LV 8 : Director (Management) (Access to view all tickets, meeting room bookings, assets, and purchase requests for users under their supervision, as well as KPI dashboards and user management features)
            - LV 9 : Administrator (IT Support Staff) (Access to IT support module, meeting room management, assets management, purchase request management, user management, and settings)
            - LV 10 : Developer (IT Programmer Staff) (Access to all features and functionalities, including IT support module, meeting room management, assets management, purchase request management, user management, settings, and additional developer tools for managing the application, future enhancements, and debugging)
16. Gamification:
    - Konsep Visual Badge (Warna & Efek)Anda bisa merancang badge ini dengan gaya Cyber-Tech atau Industrial, menggunakan bentuk dasar seperti Hexagon (segi enam) atau Pill (kapsul), dipadukan dengan kode warna khusus.
        LV 0 : Guest / Stranger | Gray / Silver(#9CA3AF) | Static: Desain sederhana tanpa efek, menunjukkan status non-authenticated. | Ikon Siluet manusia dengan tanda tanya atau kunci terbuka.
        LV 1 : User / The Operator | Steel Blue / Slate(#64748B) | Static: Desain matte yang bersih dan solid tanpa efek cahaya, menunjukkan fungsi utilitarian di lantai produksi. | Ikon Gear tunggal atau bentuk kotak solid.
        LV 2 : Receptionist / The Navigator | Neon Cyan(#06B6D4) | Soft Pulse: Efek glow tipis yang berdetak lambat, merepresentasikan sinyal, arah, dan ketersediaan ruangan. | Ikon Radar atau Location Pin dalam bingkai sirkular.
        LV 3 : Human Resources / The Sync Ops | Emerald Green(#10B981) | Border Glow: Bingkai menyala terang, melambangkan status "Aman/Terhubung" untuk urusan personalia dan user data. | Ikon Node jaringan atau siluet manusia digital.
        LV 8 : Director / The Prime | Cyber Gold(#F59E0B) | Metallic Shine: Gradien emas dengan kilapan statis (seperti pantulan cahaya logam), menunjukkan otoritas eksekutif. | Ikon Mahkota minimalis atau Bintang bersudut tajam.
        LV 9 : Administrator / The SysOp | Crimson Red / Orange(#EF4444) | Warning Glow: Warna merah menyala dengan bayangan kuat (heavy box-shadow), menandakan kontrol operasional dan penanganan isu kritis. | Ikon Wrench silang atau tameng pelindung (Shield).
        LV 10 : Developer / The Architect | Deep Violet / Matrix Green | Glitch / RGB Shift: Efek animasi glitch sesekali atau warna yang perlahan bergeser (breathing neon). Menandakan akses absolut ke source code dan database. | Ikon Mata (The All-Seeing Eye) atau logo Infinity (∞).
    - Penempatan Badge dalam UI/UX Aplikasi
        Agar badge ini fungsional dan tidak hanya sekadar kosmetik, letakkan di beberapa area strategis:
        1. Header / Top Navigation Bar:
        Di pojok kanan atas layar, di sebelah foto profil atau nama pengguna, tampilkan badge kecil. Misalnya: [D] Daniel • [Badge: LV 10 Developer]. Ini menjadi pengingat konstan tentang hak akses mereka.
        2. Ticket Management & Chatbox:
        Saat ada balasan di tiket IT Support, badge ini muncul di sebelah nama komentator.
            "Wah, tiket saya langsung dibalas oleh seseorang dengan badge merah [LV 10 Administrator] atau badge ungu [LV 10 Developer]!"
            Ini memberikan ketenangan bagi pembuat tiket bahwa masalah mereka ditangani oleh ahlinya.
        3. Approval Logs (Purchase Request / Handover Asset):
        Di dalam riwayat dokumen, berikan stempel badge pada nama yang menyetujui. Misalnya, Approved by Feti [Badge Emas: LV 8 Prime]. Ini membuat rekam jejak audit (audit trail) menjadi sangat jelas secara visual.
        4. Halaman Profile:
        Sediakan area khusus di tengah halaman profil yang menampilkan versi besar dan detail dari badge mereka, lengkap dengan animasi penuhnya.
17. Implementasi AI: (Pending need my approval - Just for Administrator, Director and Developer use only)
    - Tambahkan fitur AI untuk meningkatkan pengalaman pengguna dan efisiensi operasional, seperti chatbot untuk dukungan otomatis, analitik prediktif untuk wawasan kinerja, dan alat otomatisasi untuk menyederhanakan proses.
    - Fitur AI akan diimplementasikan dalam fase proyek yang akan datang, dengan fungsionalitas spesifik yang akan ditentukan berdasarkan kebutuhan dan umpan balik pengguna. (Pending need my approval) (AI features will be implemented in the future phase of the project, and specific functionalities will be determined based on user needs and feedback) (Just for Administrator, Director and Developer use only)


## Structure of the application:

# LOGIN PAGE
- User Login (Username/Email and Password) additionally with "Remember Me" option for persistent login sessions.
    - Forgot Password (Allow users to reset their password through email verification)
    - User Registration (Allow new users to create an account with email verification for added security)

# MAIN PORTAL
A. Guest Interface
- Access to login and registration pages only, with no access to other features or modules of the application. Guests will be redirected to the login page if they attempt to access any restricted areas of the application.

B. Common user interface for all modules:
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

C. Director Interface
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

D. Receptionist Interface
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

E. Administrator Interface (IT Support Staff)
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

F. Developer Interface (IT Programmer Staff)
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