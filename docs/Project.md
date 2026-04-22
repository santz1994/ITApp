## Tasks :

### TODO Backlog (2026-04-21)

- Undo Main Portal dashboard expansion and keep Main Portal as module-navigation-only hub (no KPI widgets, approval center cards, or role snapshot panels on hub canvas).
- UI/UX & Frontend improvements:
    - Redesign the user interface to align with the new modular structure of the application, ensuring that it is intuitive and user-friendly.
    - Ensure that the design is responsive and accessible across different devices and screen sizes.
    - Use modern design principles and a consistent visual language to enhance the overall user experience and make the application more visually appealing.
    - Use modal dialogs for critical actions (e.g., ticket resolution, meeting cancellation, purchase request approval) to provide clear confirmation prompts and prevent accidental actions.
    - Implement a consistent design language and style guide across all modules to enhance the overall user experience and maintain a cohesive look and feel throughout the application.
    - Button and link updates to ensure that all navigation paths are functional and lead to the correct pages, especially with the new modular structure in place.
    - Update the sidebar and navigation menus to reflect the new structure and provide easy access to all modules and features based on user roles and permissions.
        a. Implement a user context panel on the main portal to display relevant information about the logged-in user, such as their role, last login time, and account status.
        b. Add role-focused operational snapshots on the main portal to provide users with quick access to key metrics and actions relevant to their role (e.g., open tickets for IT support staff, upcoming meetings for receptionists, pending approvals for administrators).
    - Theme and styling updates to enhance the visual appeal of the application, including color schemes, typography, and layout adjustments to improve readability and user engagement.
    - Dark and light mode toggle to allow users to switch between different themes based on their preferences and improve accessibility for users with different visual needs.
    - Standardize the design of summary cards, tables, buttons, modals, and pagination across all modules to create a cohesive user experience and make it easier for users to understand and interact with the data presented.
    - Use standardized icons and visual cues to enhance usability and provide clear indications of actions, statuses, and important information.
    - Refactor all CSS and JavaScript code to match the new design and structure of the application, ensuring that frontend code is organized and maintainable. Use modular JavaScript, CSS preprocessors, and an asset build optimization process. Standardize frontend libraries and frameworks to reduce technical debt and support future scalability.
    - No CSS or JavaScript inline in Blade templates; all styles and scripts should be organized in separate files and properly linked to views for better maintainability, readability, caching, and asset optimization.

## Progress Update (2026-04-21)

### Phase 1: Database Operations & Cleanup (COMPLETED)
- Executed comprehensive database table cleanup to remove legacy and unused tables:
    - Dropped 7 redundant tables: `permission_role`, `role_user`, `role_permissions` (legacy Entrust, replaced by Spatie), `tickets_entries`, `pcspecs`, `push_subscriptions`, `meeting_room_lcd_settings`
    - Created pre-cleanup safety backup: `database/pre_phase1_cleanup_20260421_131623.sql`
    - Verified data migration safety: confirmed 154 permission records migrated from `role_permissions` to `role_has_permissions` before drop
    - Migration executed successfully: `2026_04_21_131700_cleanup_redundant_legacy_tables.php` (batch 110, 118ms)
    - Database reduced from 72 to 65 tables while preserving all core module functionality
- Identified unused code artifacts for future cleanup (pending user approval):
    - Models: `Pcspec.php`, `TicketsEntry.php`, `MeetingRoomLcdSetting.php`
    - Controllers: `PcspecsController.php`, `TicketsEntriesController.php`
    - Views: `resources/views/pcspecs/`
    - API references to `TicketsEntry` full-text search (refactor to use `Ticket` model directly)
- Created comprehensive cleanup documentation: `docs/Phase1-Cleanup-Recommendations.md`

### Newly Implemented in Code
- Stabilized pending migration execution on restored `itapp` database by hardening non-idempotent user-column migrations:
    - Updated `2025_12_05_070838_add_location_id_to_users_table` to skip when `users.location_id` already exists.
    - Updated `2026_04_16_142957_add_portal_preferences_to_users_table` to skip when `users.portal_preferences` already exists.
    - Added safe down-guards so rollback paths only drop columns when present.
- Completed pending migration backlog execution (7 migrations) after backup gate verification:
    - `2025_12_05_070838_add_location_id_to_users_table`
    - `2026_04_16_142957_add_portal_preferences_to_users_table`
    - `2026_04_16_160000_align_core_tables_with_project_structure`
    - `2026_04_16_170000_extend_modular_database_structure`
    - `2026_04_17_100000_create_normalized_asset_forms_tables`
    - `2026_04_17_111000_align_role_levels_and_add_meeting_overlap_index`
    - `2026_04_20_130000_normalize_roles_to_project_hierarchy`
- Hardened frontend modularization baseline to reduce inline implementation debt in shared layouts:
    - Replaced inline style usage in `mainheader` theme-toggle/user-menu area with reusable CSS classes (`nav-theme-item-compact`, `nav-user-chevron`, `inline-logout-form`) in `public/css/frontend-core.css`.
    - Replaced sidebar inline section-header styles with reusable classes (`sidebar-section-header`, `sidebar-section-subheader`) to align with no-inline-CSS directive.
    - Replaced inline monthly-report click handler in sidebar (`onclick`) with declarative action hook (`data-action="open-monthly-report-modal"`) and centralized JS binding in `public/js/frontend-core.js`.
- Moved portal layout shell styling into dedicated stylesheet:
    - Added `public/css/portal-layout.css` and linked it from `layouts/portal`.
    - Removed inline `<style>` block from `layouts/portal` and replaced hidden test-helper inline style with utility class (`itapp-test-helpers-hidden`).
- Enforced clean separation for behavior wiring:
    - Added `bindActionTriggers()` in frontend core script so critical UI actions can be bound without inline JavaScript in Blade templates.

### Validation
- Database safety gate validation completed before migration mutation:
    - Fresh backup artifact created and verified: `database/pre_migrate_pending_20260421_130602.sql`.
- Migration status validation completed:
    - `php artisan migrate` -> all 7 pending migrations applied successfully.
    - `php artisan migrate:status` -> `PENDING_COUNT=0`.
- Syntax/diagnostics validation completed for edited files:
    - `php -l resources/views/layouts/partials/mainheader.blade.php` -> `No syntax errors detected`.
    - `php -l resources/views/layouts/partials/sidebar.blade.php` -> `No syntax errors detected`.
    - `php -l resources/views/layouts/portal.blade.php` -> `No syntax errors detected`.
- Focused regression validation completed:
    - `./vendor/bin/phpunit tests/Feature/MainPortalTest.php` -> `OK (6 tests, 41 assertions)`.
    - `./vendor/bin/phpunit tests/Feature/SidebarWorkspaceContextTest.php` -> `OK (3 tests, 24 assertions)`.

### In Progress / Next Batch
- Continue migrating remaining inline CSS/JS from module-specific Blade surfaces into modular asset files (`public/css/*`, `public/js/*`) to fully satisfy the no-inline rule across all modules.

## Progress Update (2026-04-20)

## Progress Update (2026-04-22)

### Newly Implemented in Code
- Continued no-inline frontend modularization on Main Portal hub surface:
    - Removed inline `<style>` block and inline `<script>` block from `resources/views/portal/index.blade.php`.
    - Added dedicated portal hub assets: `public/css/portal-index.css` and `public/js/portal-index.js`.
    - Replaced inline module card CSS-variable style (`style="--card-index: ..."`) with declarative hook (`data-card-index`) and runtime assignment in external JS.
    - Added declarative portal preferences endpoint hook (`data-portal-preferences-url`) consumed by external portal JS.
- Preserved visible portal context labels in server-rendered HTML after script extraction:
    - Added heading block with `data-i18n` markers for `portal_label`, `modules_title`, and `portal_subtitle`.

### Validation
- Syntax/diagnostics validation completed:
    - `php -l resources/views/portal/index.blade.php` -> `No syntax errors detected`.
- Focused regression validation completed:
    - `./vendor/bin/phpunit tests/Feature/MainPortalTest.php` -> `OK (6 tests, 41 assertions)`.
    - `./vendor/bin/phpunit tests/Feature/SidebarWorkspaceContextTest.php` -> `OK (3 tests, 24 assertions)`.
- No-inline verification on portal hub view completed:
    - `resources/views/portal/index.blade.php` no longer contains inline `style=` attributes or inline `<style>/<script>` blocks.

### Newly Implemented in Code
- **SQL Roles Hierarchy Alignment**: Aligned `roles` table to exact LV 0-10 hierarchy mapping as required by Project.md:
    - Executed pending migration `2026_04_17_111000_align_role_levels_and_add_meeting_overlap_index.php` to set `access_level` values.
    - Applied column comment to `roles.access_level` documenting LV mapping: `0=Guest,1=User,2=Receptionist,3=Human Resources,8=Director,9=Administrator,10=Developer`.
    - Verified all 7 canonical roles exist with correct access levels: guest (0), user (1), receptionist (2), human-resources (3), director (8), administrator (9), developer (10).
    - Ensured existing user-role assignments are preserved via `model_has_roles` and legacy `role_user` pivot tables.
- Restored local runtime stability after critical database engine corruption impacted Laravel session + core tables:
    - Root incident presented as SQLSTATE runtime failure on missing `sessions` table during request bootstrap.
    - Validated broader table-engine corruption symptoms on `migrations`, `users`, and `tickets` before executing recovery path.
    - Generated pre-repair backup artifacts before structural recovery operations:
        - `database/pre_session_repair_20260420_071959.sql`
        - `database/pre_restore_empty_itapp_20260420_072533.sql`
    - Executed database-first recovery flow using backup-clone strategy (`itapp_recover` -> rebuild `itapp`) instead of partial table patching to minimize hidden corruption risk.
    - Confirmed post-restore health with table-count verification, `CHECK TABLE sessions`, and HTTP route checks (`302/200` auth flow restored).
- Hardened `docs/Database.md` to become an end-to-end schema reference for current ITSM scope:
    - Added explicit System module SQL coverage (`sessions`, `jobs`, `failed_jobs`, `job_batches`, `cache`, `cache_locks`, `menus`, `menu_user`, `notification_settings`).
    - Added ITSM completeness coverage for `ticket_watchers`, `media`, `licenses`, `asset_has_licenses`, and `meeting_room_display_settings` in SQL + migration + relationship sections.
    - Added `users.deleted_at` soft-delete guidance and legacy notification-column transition notes toward normalized `notification_settings`.
    - Added normalization roadmap section documenting phased PK strategy (`INT UNSIGNED` legacy compatibility -> `BIGINT UNSIGNED` standardization), notification policy, depreciation policy, and documentation governance rules.
    - Aligned media migration documentation with SQL contract (`created_by` index/FK and documented defaults) to prevent cross-section drift.
- Implemented Smart ITSM phase-0 intake recommendations with API v1 contract and strict layering:
    - Added versioned endpoint `POST /api/v1/tickets/smart-intake` in protected API route group.
    - Added `TicketIntelligenceController` (thin controller), `StoreSmartTicketIntakeRequest` validation, and `SmartTicketIntakeResource` response wrapper (`success`, `data`, `message`).
    - Added `SmartTicketIntakeService` heuristic engine for ticket type/priority inference from subject + description (ID/EN keywords).
    - Added `TicketIntelligenceRepository` data-access layer for ticket master lookup and published Knowledge Base suggestion queries.
    - Added `KnowledgeBaseSuggestionResource` output mapping for knowledge suggestion cards (author/meta/views/helpful counters).
    - Added safe fallback integration in `TicketService::createTicket()` so missing `ticket_priority_id`/`ticket_type_id` can use smart recommendations without breaking existing flow.
    - Added focused feature suite `SmartTicketIntakeApiTest` to validate auth guard, recommendation output, KB suggestion payload, and Asia/Jakarta metadata.
    - Integrated Smart Suggest runtime on `tickets/create` UI so users can trigger recommendation directly from subject/description and auto-fill ticket type + hidden priority payload before submit.
    - Added authenticated web endpoint `POST /tickets/smart-intake` for create-page runtime calls to avoid session mismatch on API guard while keeping the same service/resource contract.
    - Added focused regression coverage in `TicketManagementTest` for Smart Suggest hooks and authenticated web-route recommendation response.
- Implemented Smart ITSM predictive maintenance risk analysis API (phase-0.5 baseline):
    - Added versioned endpoint `GET /api/v1/assets/{asset}/maintenance-risk` in protected API route group.
    - Added `AssetMaintenanceIntelligenceController` (thin controller), `GetAssetMaintenanceRiskRequest` validation, and `AssetMaintenanceRiskResource` response wrapper (`success`, `data`, `message`).
    - Added `PredictiveMaintenanceService` heuristic scoring engine using asset age, useful-life fallback, warranty horizon, maintenance frequency, pending maintenance, and status signals.
    - Added `AssetMaintenanceRiskRepository` data-access layer to load asset maintenance context via `maintenanceLogs` relation.
    - Added focused feature suite `AssetMaintenanceRiskApiTest` to validate critical risk path, low-risk path, metadata/timezone payload, and guest-auth guard.
- Expanded bilingual coverage to Budget Management non-core module surfaces (`/budgets`, `/budgets/{budget}/edit`, `/budgets/{budget}`):
    - Added EN/ID runtime language toggle controls (`budgetIndexLanguageEnglish`, `budgetEditLanguageEnglish`, `budgetShowLanguageEnglish` with matching Indonesian IDs) using the shared portal preference storage key (`itapp.portal.preferences.v1.user.{id}`).
    - Added `data-i18n`, `data-i18n-placeholder`, and `data-i18n-title` markers across index table/form labels, edit action panels, and show-page utilization/links panels to keep runtime transitions deterministic.
    - Implemented runtime localization hooks and behavior contracts: `window.budgetIndexLabel`, `window.budgetIndexDataTableLanguage`, `window.budgetIndexRefreshRuntimeText`, `window.budgetEditLabel`, and `window.budgetShowLabel`.
    - Localized Budget DataTable runtime strings and create/edit/delete interaction prompts, including submit loading states and delete confirmation text.
    - Added focused regression suite `BudgetManagementBilingualTest` to cover toggle visibility, marker presence, and language-switch behavior hooks across index/edit/show surfaces.
- Expanded bilingual coverage to Supplier Management non-core module surfaces (`/suppliers`, `/suppliers/{supplier}/edit`, `/suppliers/{supplier}`):
    - Added EN/ID runtime language toggle controls (`supplierIndexLanguageEnglish`, `supplierEditLanguageEnglish`, `supplierShowLanguageEnglish` with matching Indonesian IDs) using the shared portal preference storage key (`itapp.portal.preferences.v1.user.{id}`).
    - Added `data-i18n`, `data-i18n-placeholder`, and `data-i18n-title` markers across supplier listing/table labels, create/edit form guidance, and supplier detail statistics/asset+invoice panels.
    - Implemented runtime localization hooks and behavior contracts: `window.supplierIndexLabel`, `window.supplierIndexDataTableLanguage`, `window.supplierIndexRefreshRuntimeText`, `window.supplierEditLabel`, and `window.supplierShowLabel`.
    - Localized Supplier DataTable runtime strings, create/edit validation prompts, delete confirmation prompt, and submit-loading button states.
    - Added focused regression suite `SupplierManagementBilingualTest` to cover toggle visibility, marker presence, and language-switch behavior hooks across index/edit/show surfaces.

### Validation
- **Roles Hierarchy Validation**: Verified `roles.access_level` column comment and data integrity:
    - `SELECT id, name, access_level FROM roles ORDER BY access_level` returns correct LV mapping.
    - Column comment now reflects LV hierarchy as per Project.md.
    - Total of 7 distinct roles with correct access levels (0,1,2,3,8,9,10).
- Database recovery validation completed:
    - Core runtime tables verified present and healthy (`sessions`, `users`, `migrations`).
    - Request flow revalidated successfully after cache clear (no SQLSTATE/Symfony exception marker in response path).
- Documentation integrity validation completed:
    - Cross-section consistency checks passed for SQL schema, migration snippets, and Eloquent relationship references.
- Smart ITSM phase-0 validation completed:
    - `./vendor/bin/phpunit tests/Feature/SmartTicketIntakeApiTest.php` -> `OK (3 tests, 25 assertions)`.
    - Regression check: `./vendor/bin/phpunit --filter ticket_create_edit_and_show_pages_include_language_switch_behavior_hooks tests/Feature/TicketManagementTest.php` -> `OK (1 test, 18 assertions)`.
    - UI hook regression: `./vendor/bin/phpunit --filter ticket_create_page_shows_bilingual_toggle_and_runtime_markers tests/Feature/TicketManagementTest.php` -> `OK (1 test, 14 assertions)`.
    - Behavior hook regression (updated with Smart Suggest assertions): `./vendor/bin/phpunit --filter ticket_create_edit_and_show_pages_include_language_switch_behavior_hooks tests/Feature/TicketManagementTest.php` -> `OK (1 test, 20 assertions)`.
    - Authenticated web-route recommendation regression: `./vendor/bin/phpunit --filter authenticated_user_can_request_smart_ticket_intake_from_web_route tests/Feature/TicketManagementTest.php` -> `OK (1 test, 14 assertions)`.
- Smart ITSM predictive maintenance baseline validation completed:
    - `./vendor/bin/phpunit tests/Feature/AssetMaintenanceRiskApiTest.php` -> `OK (3 tests, 27 assertions)`.
- Budget module bilingual validation completed:
    - `./vendor/bin/phpunit tests/Feature/BudgetManagementBilingualTest.php` -> `OK (5 tests, 46 assertions)`.
- Supplier module bilingual validation completed:
    - `./vendor/bin/phpunit tests/Feature/SupplierManagementBilingualTest.php` -> `OK (5 tests, 46 assertions)`.

### In Progress / Next Batch
- Expand bilingual coverage for remaining non-core module surfaces so all runtime labels/modals/placeholders are consistently backed by `data-i18n` markers.
- Add focused regression coverage for remaining language-switch behavior hooks to keep EN/ID transitions deterministic across modules.
- Extend Smart ITSM after phase-0 endpoint baseline:
    - Integrate predictive maintenance risk endpoint into module dashboards and technician workflow surfaces.
    - Add embedding-ready adapter interface for future semantic KB matching while keeping keyword pipeline as default open-source fallback.
- Align real migration backlog with `docs/Database.md` normalization roadmap before next schema-change batch.

## Progress Update (2026-04-17)

- Implemented "Hub-and-Spoke" navigation architecture for the Main Portal:
    - Main Portal now serves as the central Hub. The global sidebar has been removed from this surface to maximize workspace focus.
    - Users select a module card (Spoke) to enter a dedicated workspace where a module-specific sidebar dynamically renders based on LV access level.
- Built Main Portal backend using strict enterprise layers:
    - `MainPortalController`: Handles HTTP request and view rendering.
    - `MainPortalService`: Processes user role, permissions, and delegates data gathering.
    - `MainPortalRepository`: Executes DB queries to fetch role-scoped KPI metrics (Open Tickets, Pending Meetings, Pending Purchases) based on LV 0-10 hierarchy.
    - Global visibility (LV 9 Admin, LV 10 Developer) returns organization-wide metrics, while lower levels receive user-scoped metrics.
- Developed Cyber-Industrial Main Portal UI (`resources/views/portal/index.blade.php`):
    - Utilizes a new dedicated layout (`layouts.portal`) with no sidebar.
    - Implemented interactive, role-aware module cards (IT Support, Assets Management, Meeting Room, User Management, Settings, Profile).
    - Applied Cyber-Industrial CSS styling with hover states, pulse glows, and dynamic LV badges (e.g., LV 10 Matrix Glitch, LV 9 Crimson Warning).
    - Integrated real-time KPI metric badges directly into the module cards.
- Embedded Bilingual (ID/EN) architecture into the new portal hub:
    - Added EN/ID language toggle controls in the portal header.
    - Replaced static text nodes with `data-i18n` markers for seamless runtime language switching (e.g., `mod_it_support`, `mod_assets_desc`).

### Newly Implemented in Code
- Repaired database governance to follow Project.md as source of truth (LV hierarchy + modular forms + LCD readiness):
    - Added migration `2026_04_17_111000_align_role_levels_and_add_meeting_overlap_index.php`.
    - Role hierarchy alignment now enforces Project LV mapping in `roles.access_level`:
        - `guest=0`, `user=1`, `receptionist=2`, `human resources=3`, `director=8`, `administrator=9`, `developer=10`.
    - Updated MySQL column comment for `roles.access_level` to LV-based mapping (`0/1/2/3/8/9/10`) to remove legacy 1..5 interpretation.
    - Added explicit LCD overlap index for real-time room-status polling:
        - `idx_meeting_room_lcd_overlap` on `meeting_room_bookings` (`room_id`, `status`, `start_datetime`, `end_datetime`) with safe fallback to `room_name` when needed.
- Implemented normalized Form Management database foundation for Phase 18 (Asset Handover/Lending/Return/Disposal):
    - Added migration `2026_04_17_100000_create_normalized_asset_forms_tables.php`.
    - Created `asset_forms` (header/workflow state), `asset_form_items` (line items), and `asset_form_approvals` (immutable action trail).
    - Added relational constraints and workflow indexes to support create/approve/reject/complete flows and future printable form surfaces.
- Stabilized migration compatibility for focused PHPUnit execution:
    - Patched MySQL-specific alignment migrations to skip MySQL-only backfill SQL while running SQLite-based test migrations.
    - This prevents non-functional test failures from legacy `UPDATE ... JOIN` and `NOW()` SQL paths in test runtime.
- Added focused feature tests for new schema work:
    - `FormManagementSchemaTest` validates table/column presence and parent-child FK cascade definition.
    - `RoleAndMeetingSchemaAlignmentTest` validates LV10 role level persistence and LCD overlap index presence.
    - Focused execution confirmed green:
        - `FormManagementSchemaTest` (`2 tests, 8 assertions`)
        - `RoleAndMeetingSchemaAlignmentTest` (`2 tests, 5 assertions`)
- Repaired `docs/Database.md` to match Project.md requirements:
    - Updated role access-level comment to LV hierarchy (0/1/2/3/8/9/10).
    - Added normalized form-management table definitions (`asset_forms`, `asset_form_items`, `asset_form_approvals`).
    - Added LCD overlap index guidance in performance-index section.
- Expanded bilingual coverage to User Management module index page (`/admin/users`):
    - Added EN/ID toggle controls (`usersIndexLanguageEnglish`, `usersIndexLanguageIndonesian`) and runtime language persistence using the same per-user portal preference storage key.
    - Added `data-i18n` and `data-i18n-placeholder` markers for table headers, quick-create panel labels/placeholders, and helper/action text.
    - Added runtime localization hooks for DataTable labels/export button captions and delete confirmation/alert messages.
    - Added focused feature test suite `UserManagementBilingualTest` with coverage for marker presence and language-switch runtime hooks.
    - Focused execution confirmed green: `UserManagementBilingualTest` (`2 tests, 17 assertions`).
- Expanded bilingual coverage to User Management create/edit pages:
    - `/admin/users/create` now includes EN/ID toggle controls (`userCreateLanguageEnglish`, `userCreateLanguageIndonesian`) and runtime language persistence using the shared portal preference key.
    - `/admin/users/{user}/edit` now includes EN/ID toggle controls (`userEditLanguageEnglish`, `userEditLanguageIndonesian`) and matching runtime language persistence.
    - Added `data-i18n` markers for create/edit section headers and action labels, plus localized runtime feedback for password strength, password matching, and submit-loading states.
    - Expanded `UserManagementBilingualTest` coverage for create/edit marker presence and language-switch behavior hooks.
    - Focused execution confirmed green: `UserManagementBilingualTest` (`5 tests, 47 assertions`).
- Expanded bilingual coverage to System Roles page (`/system/roles`):
    - Added EN/ID toggle controls (`systemRolesLanguageEnglish`, `systemRolesLanguageIndonesian`) and per-user language persistence via shared portal preference storage key.
    - Added `data-i18n`, `data-i18n-placeholder`, and `data-i18n-title` markers across KPI cards, section headers, table labels, quick actions, and create/edit role modals.
    - Implemented runtime localization dictionary + behavior hooks (`window.systemRolesLabel`, `window.systemRolesDataTableLanguage`, `window.systemRolesRefreshRuntimeText`) including localized confirm/error prompts and DataTable runtime labels.
    - Added focused feature test suite `SystemRolesBilingualTest` with marker and language-switch behavior-hook coverage.
    - Focused execution confirmed green: `SystemRolesBilingualTest` (`2 tests, 17 assertions`) and regression `UserManagementBilingualTest` (`7 tests, 64 assertions`).
- Hardened Main Portal Hub-and-Spoke implementation to match architecture intent:
    - Added dedicated hub layout `layouts.portal` and switched `portal/index` to use it, removing the global sidebar from the Main Portal hub surface.
    - Added spoke context query wiring (`workspace`) in `MainPortalService` for module cards, quick links, and approval-center action links.
    - Enhanced sidebar rendering to be workspace-aware (query + route-name fallback), so module pages render focused sidebar groups while preserving role/permission gates.
    - Added focused portal regression coverage for no-sidebar hub layout and spoke-context module links.
    - Focused execution confirmed green: `MainPortalTest` (`5 tests, 30 assertions`) and `MainPortalApprovalCenterScopeTest` (`2 tests, 10 assertions`).
- Expanded Hub-and-Spoke sidebar UX with workspace bilingual persistence:
    - `resources/views/layouts/partials/sidebar.blade.php` now resolves active language from authenticated user `portal_preferences.language` and renders sidebar header labels in EN/ID accordingly.
    - Added `data-i18n` markers for sidebar header/runtime keys (`sidebar.navigation`, `sidebar.workspace`, `sidebar.main_portal`, and workspace label keys) to keep runtime localization hooks consistent with existing bilingual pages.
    - Workspace labels now provide bilingual dictionaries for all supported workspace keys (`it_support`, `meeting_room`, `assets_management`, `purchase_request`, `user_management`, `settings`, `profile`).
- Added focused sidebar workspace regression suite:
    - New `SidebarWorkspaceContextTest` verifies workspace-specific sidebar focus for IT Support and Meeting Room spokes and validates Indonesian header rendering via `portal_preferences.language=id`.
    - Sidebar URL assertions are now host-agnostic using route helpers (no hardcoded `localhost` in feature assertions).
    - Focused execution confirmed green: `SidebarWorkspaceContextTest` (`3 tests, 24 assertions`) and regression `MainPortalTest` (`5 tests, 30 assertions`).
- Simplified Main Portal UI to enforce module-picker-only behavior:
    - `portal/index` now renders only module navigation cards plus EN/ID language toggle; dashboard widgets/summaries/approval-center/personalization surfaces are removed from the hub screen.
    - Maintained bilingual runtime behavior for module title/subtitle and portal labels using `portal_preferences.language` persistence via portal preference API.
    - Updated portal regression assertions to validate "module navigation only" semantics for both standard users and admins.
    - Focused execution confirmed green: `MainPortalTest` (`5 tests, 30 assertions`).
- Refined Main Portal visual language with Cyber-Industrial hub styling while preserving module-only navigation intent:
    - Updated hub surface to dark cyber-industrial card system (`cyber-card`) with module accent hovers (`mod-it`, `mod-assets`, `mod-meeting`, `mod-admin`) and admin-card edge marker.
    - Added dedicated bilingual portal dictionary (`portalTranslations`) with ID/EN keys for welcome copy and module titles/descriptions.
    - Added language toggle behavior using local storage key `portal_lang` with API sync to `portal_preferences.language` for persistence consistency.
    - Added live WIB clock (`Asia/Jakarta`) on the hub header (`#wib-clock`) to keep timezone visibility explicit in portal UI.
    - Focused execution confirmed green: `MainPortalTest` (`5 tests, 30 assertions`), `SidebarWorkspaceContextTest` (`3 tests, 24 assertions`), and `MainPortalApprovalCenterScopeTest` (`2 tests, 10 assertions`).
- Cleaned Main Portal visual residue from legacy AdminLTE scaffolding:
    - `layouts.portal` no longer renders global `mainheader`, `contentheader`, `controlsidebar`, or footer blocks, so hub screen is now a standalone module-picker canvas.
    - Removed duplicated in-page page-header component from `portal/index`; replaced with compact in-canvas kicker label (`Main Portal`) + welcome/subtitle section.
    - Added focused layout assertions to guarantee hub HTML excludes legacy top header/content header/control-sidebar markers.
    - Focused execution confirmed green: `MainPortalTest` (`5 tests, 33 assertions`).
- Enhanced standalone Main Portal UX while keeping module-picker scope:
    - Added compact utility strip on top of hub canvas with user identity, LV role badge, and sign-out action (`#portal-utility-bar`, `#portal-logout-action`) without restoring global header/sidebar.
    - Reused service-provided `primaryRoleBadge` metadata for runtime EN/ID role label switching in portal language toggle flow.
    - Refined module grid rendering with staggered card reveal animation and improved mobile spacing/stacking for utility strip and module cards.
    - Focused execution confirmed green: `MainPortalTest` (`5 tests, 36 assertions`), `SidebarWorkspaceContextTest` (`3 tests, 24 assertions`), and `MainPortalApprovalCenterScopeTest` (`2 tests, 10 assertions`).
- Added dynamic viewport adaptation for Main Portal across device sizes:
    - Module grid now uses adaptive CSS Grid (`auto-fit` + `minmax`) with runtime card-width/gap tuning via viewport presets (`compact`, `mobile`, `tablet`, `wide`, `ultra-wide`).
    - Implemented runtime viewport hook `setupDynamicViewport()` with `ResizeObserver` fallback handling, writing active preset to `data-portal-screen` for deterministic responsive styling.
    - Added focused regression coverage to ensure dynamic viewport hooks are present in rendered portal HTML.
    - Focused execution confirmed green: `MainPortalTest` (`6 tests, 41 assertions`), `SidebarWorkspaceContextTest` (`3 tests, 24 assertions`), and `MainPortalApprovalCenterScopeTest` (`2 tests, 10 assertions`).
- Refined Main Portal module grid so cards render rata (aligned/equalized):
    - Enforced equal-height row behavior using `grid-auto-rows: 1fr` and `display:flex` on each grid item wrapper.
    - Standardized card internals (`box-header`, title/icon row, body flow, action anchoring) so CTA button stays aligned at bottom across all module cards.
    - Applied controlled description clamp and spacing normalization to prevent uneven card growth from variable copy length.
    - Browser validation confirms row-level height parity (`diff: 0` on every row) with focused suite still green: `MainPortalTest` (`6 tests, 41 assertions`).

## Progress Update (2026-04-16)

### Newly Implemented in Code
- Elevated user account `daniel@quty.co.id` to Developer (LV 10) with compatibility-safe role mapping:
    - Created missing `developer` role (`guard_name = web`) in `roles` table.
    - Assigned Developer role to user in both `model_has_roles` (Spatie) and legacy `role_user` mapping tables.
    - Preserved existing `super-admin` role assignment to avoid legacy access regression while enabling LV 10 badge/runtime checks.
    - Cleared permission cache with `php artisan permission:cache-reset`.
    - Verified assignment by SQL joins showing user roles now include `super-admin` and `developer`.
    - Safety procedure completed: pre-change DB backup created and validated at `database/pre_role_update_20260416_150456.sql` before mutation.
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
    - Added bilingual toggle visibility assertions (`EN` / `ID`) in module feature test.
- Added dedicated Form Request classes for Purchase Request approval actions:
    - `ApprovePurchaseRequestRequest`
    - `RejectPurchaseRequestRequest`
    - `FulfillPurchaseRequestRequest`
    - `AssetRequestController` approve/reject/fulfill actions now use validated payloads from dedicated request classes.
- Added feature tests for approval transition guard edge cases:
    - New test suite: `PurchaseRequestApprovalWorkflowTest`
    - Guard coverage added for invalid transitions (`fulfilled -> approve`, `fulfilled -> reject`, `rejected -> fulfill`)
    - Validation coverage added for mandatory rejection reason (`admin_notes` required)
- Added positive-path workflow tests for Purchase Request approvals:
    - Success coverage added for approve, reject, and fulfill transitions (including payload and persistence assertions).
    - Sequential feature run confirmed green for:
        - `MainPortalTest` (2 tests, 10 assertions)
        - `PurchaseRequestPortalTest` (4 tests, 13 assertions)
        - `PurchaseRequestApprovalWorkflowTest` (7 tests, 25 assertions)
        - `MainPortalApprovalCenterScopeTest` (2 tests, 10 assertions)
- Added compact Portal Personalization setting on Main Portal:
    - Per-user preferences persist in browser local storage (scoped by user id).
    - Users can reorder module cards and toggle module visibility from a compact modal.
    - Quick Access shortcuts can be pinned/unpinned based on daily workflow.
- Added bilingual toggle indicator (EN/ID) directly in portal header:
    - Language preference is persisted in the same personalization profile.
    - Main portal labels, key headings, quick links, and module title/subtitle text now switch dynamically.
- Expanded bilingual coverage into Purchase Request module page:
    - Added EN/ID toggle controls on `/purchase-requests` header area.
    - Summary cards, status breakdown, quick actions, and table labels now switch dynamically.
    - Module language preference reuses per-user portal preference storage key for consistency.
- Expanded bilingual coverage into IT Support and Meeting Room module summary pages:
    - Added EN/ID toggle controls on `/tickets` page.
    - IT Support summary cards, tabs, advanced filters, table labels, and bulk action toolbar labels now switch dynamically.
    - Added EN/ID toggle controls on `/meeting-room-bookings` page.
    - Meeting Room summary cards, tabs, table labels, and empty-state labels now switch dynamically.
    - Both module pages reuse the same per-user portal language preference storage key for consistency.
- Added focused bilingual visibility assertions for newly expanded pages:
    - `TicketManagementTest::ticket_index_page_shows_bilingual_toggle_controls`
    - `MeetingRoomBookingTest::meeting_booking_index_shows_bilingual_toggle_controls`
    - Focused execution for both tests is green (2 tests, 6 assertions).
- Expanded bilingual coverage deeper into interaction flows (IT Support + Meeting Room):
    - Tickets page now localizes bulk-operation modal titles, labels, CTA buttons, validation alerts, delete confirmation prompts, loading text, and generic error prefixes.
    - Tickets DataTable runtime UI now localizes export button labels and search/info/length runtime labels based on active EN/ID preference.
    - Meeting Room page now localizes approval/rejection modal copy, monthly report modal labels/CTA text, finish/cancel/delete confirmation prompts, and runtime validation/error alerts.
    - Meeting Room enhanced DataTable runtime labels (length/info/search/loading/processing) and export button captions now follow the active EN/ID preference.
- Added bilingual content-marker assertions for Purchase Request module:
    - `PurchaseRequestPortalTest` now asserts key `data-i18n` markers and language toggle control IDs are rendered in module HTML.
    - This closes the pending test item for bilingual content keys in Purchase Request module.
- Added focused runtime bilingual interaction coverage for Tickets and Meeting Room module index pages:
    - `TicketManagementTest::ticket_index_page_includes_runtime_bilingual_interaction_markers`
    - `MeetingRoomBookingTest::meeting_booking_index_includes_runtime_bilingual_interaction_markers`
    - Focused sequential execution confirmed green for toggle + runtime marker coverage:
        - Tickets: `2 tests, 11 assertions`
        - Meeting Room: `2 tests, 12 assertions`
- Expanded bilingual coverage to module-level create form/action pages:
    - `/tickets/create` now includes EN/ID toggle, localized section labels/help text/placeholders, and localized runtime feedback for template apply + submit loading state.
    - `/meeting-room-bookings/create` now includes EN/ID toggle, localized requester/detail fields, duration/conflict notice text, and localized runtime validation/schedule-loading messages.
    - `/asset-requests/create` (Purchase Request creation flow) now includes EN/ID toggle, localized key form labels/placeholders/actions, and localized runtime validation alerts.
    - All three forms reuse the same per-user portal language preference storage key for consistency.
- Added focused bilingual feature coverage for create form surfaces:
    - `TicketManagementTest::ticket_create_page_shows_bilingual_toggle_and_runtime_markers`
    - `MeetingRoomBookingTest::meeting_booking_create_page_shows_bilingual_toggle_and_runtime_markers`
    - `PurchaseRequestPortalTest::test_asset_request_create_page_shows_bilingual_toggle_and_runtime_markers`
    - Focused execution confirmed green: `3 tests, 30 assertions`.
- Expanded bilingual coverage to module-level edit form/action pages:
    - `/tickets/{ticket}/edit` now includes EN/ID toggle, localized key section/action labels, and localized runtime messages for status-change warnings, char counter, Select2 placeholders, and submit loading state.
    - `/meeting-room-bookings/{id}/edit` now includes EN/ID toggle, localized key section/action labels, and localized runtime messages for time-format validation, duration labels, and submit processing state.
    - `/asset-requests/{id}/edit` now includes EN/ID toggle, localized key section/action labels, localized runtime validation alerts, and a safe fallback for priority options when view data is incomplete.
    - Edit pages reuse the same per-user portal language preference storage key for consistency.
- Added focused bilingual feature coverage for edit form surfaces:
    - `TicketManagementTest::ticket_edit_page_shows_bilingual_toggle_and_runtime_markers`
    - `MeetingRoomBookingTest::meeting_booking_edit_page_shows_bilingual_toggle_and_runtime_markers`
    - `PurchaseRequestPortalTest::test_asset_request_edit_page_shows_bilingual_toggle_and_runtime_markers`
    - Focused execution confirmed green: `3 tests, 30 assertions`.
- Expanded bilingual coverage to module-level show/approval detail pages:
    - `/tickets/{ticket}` now includes EN/ID toggle, localized tab/section/action labels, and localized resolve/reopen confirmation prompts.
    - `/meeting-room-bookings/{id}` now includes EN/ID toggle, localized key section/action labels, and localized runtime confirmation + async processing/error messages for cancel/finish/delete/approve/extend/quick-edit flows.
    - `/asset-requests/{id}` now includes EN/ID toggle, localized detail/admin-action/modal labels, and localized approve/reject/fulfill confirmation prompts.
    - Show/approval pages reuse the same per-user portal language preference storage key for consistency.
- Added focused bilingual feature coverage for show/approval surfaces:
    - `TicketManagementTest::ticket_show_page_shows_bilingual_toggle_and_runtime_markers`
    - `MeetingRoomBookingTest::meeting_booking_show_page_shows_bilingual_toggle_and_runtime_markers`
    - `PurchaseRequestPortalTest::test_asset_request_show_page_shows_bilingual_toggle_and_runtime_markers`
    - Focused execution confirmed green: `3 tests, 30 assertions`.
- Expanded bilingual coverage to Assets Management module index page (outside ticket/meeting/purchase core flows):
    - `/assets` now includes EN/ID toggle, localized summary/filter/table labels, localized DataTable runtime strings, and localized delete confirmation prompt.
    - Runtime language switching now refreshes DataTable button captions and language metadata after toggle, not only static text markers.
    - Assets index now exposes explicit language behavior hooks (`assetDataTableLanguage`, `assetRefreshRuntimeText`, `assetDeleteConfirm`) to keep runtime transitions predictable and testable.
- Added focused bilingual feature + behavioral coverage for Assets module:
    - `AssetManagementTest::asset_index_page_shows_bilingual_toggle_and_runtime_markers`
    - `AssetManagementTest::asset_index_page_includes_language_switch_behavior_hooks`
    - Focused execution confirmed green: `2 tests, 17 assertions`.
- Expanded bilingual coverage to Assets Management create/edit/show surfaces:
    - `/assets/create` now includes EN/ID toggle, localized section/action labels, and localized runtime submit-loading + serial-number feedback messages.
    - `/assets/{asset}/edit` now includes EN/ID toggle, localized section/action labels, and localized runtime update-loading + serial-number feedback messages.
    - `/assets/{asset}` now includes EN/ID toggle, localized tab/section/quick-action labels, and reusable show-page language runtime helper.
    - Create/edit/show pages reuse the same per-user portal language preference storage key for consistency.
- Added focused bilingual feature + behavioral coverage for Assets create/edit/show surfaces:
    - `AssetManagementTest::asset_create_page_shows_bilingual_toggle_and_runtime_markers`
    - `AssetManagementTest::asset_edit_page_shows_bilingual_toggle_and_runtime_markers`
    - `AssetManagementTest::asset_show_page_shows_bilingual_toggle_and_runtime_markers`
    - `AssetManagementTest::asset_create_and_edit_pages_include_language_switch_behavior_hooks`
    - Focused execution confirmed green: `4 tests, 39 assertions`.
- Added focused bilingual behavior-hook coverage for core create/edit/show interaction pages (Ticket + Meeting + Purchase Request):
    - `TicketManagementTest::ticket_create_edit_and_show_pages_include_language_switch_behavior_hooks`
    - `MeetingRoomBookingTest::meeting_booking_create_edit_and_show_pages_include_language_switch_behavior_hooks`
    - `PurchaseRequestPortalTest::test_asset_request_create_edit_and_show_pages_include_language_switch_behavior_hooks`
    - Focused execution confirmed green: `3 tests, 51 assertions`.
- Main Portal now includes Approval Center widget (tickets/meeting/purchase):
    - Ticket Action Queue (unassigned open tickets)
    - Meeting Approval Queue (pending meeting approvals)
    - Purchase Approval Queue (pending purchase requests)
    - One-click queue actions to jump directly into each module workflow
- Approval Center scope and routing are now role-aware:
    - Director/management users now receive division-scoped queue counts for supervised users.
    - Admin/super-admin/developer users retain global approval-center visibility.
    - Ticket queue links now resolve to role-safe routes to prevent restricted dead-end navigation.
- Main Portal now includes service-driven LV role badges with cyber-industrial visual states:
    - Added dedicated service `UserRoleBadgeService` to map role names into LV hierarchy metadata (level, icon, EN/ID labels, effect, color tokens).
    - `MainPortalService` now injects role-badge metadata (`primaryRoleBadge`, `roleSetBadges`) while preserving Controller + Service + Repository separation.
    - `portal/index` header now renders primary role badge + role set badges using LV color/effect styles (LV0 gray, LV1 steel slate, LV2 neon cyan pulse, LV3 emerald glow, LV8 cyber gold, LV9 crimson warning glow, LV10 violet/green glitch).
    - Bilingual runtime switch now updates role badge labels dynamically for EN/ID using existing per-user portal preference storage.
    - Focused portal regression execution confirmed green:
        - `MainPortalTest` (`3 tests, 20 assertions`)
        - `MainPortalApprovalCenterScopeTest` (`2 tests, 10 assertions`)
- Reduced repetitive feature-test bootstrap noise:
    - Suppressed migration echo output during unit tests for index/DDL helper migrations.
    - Suppressed non-critical migration info logs during unit tests.
    - Focused PHPUnit output is now cleaner while preserving test assertions.
- Migrated PHPUnit configuration schema:
    - `phpunit.xml` now uses current schema mapping (no deprecation warning shown in focused test runs).
- Codebase cleanup completed for clearly unused artifacts:
    - Removed `resources/views/debug-view-test.blade.php`
    - Removed `public/test_dynamic_form.html`
    - Removed `resources/views/Meeting/lcd-dashboard.blade.php.old`
    - Removed temporary cache files `bootstrap/cache/pacDE90.tmp` and `bootstrap/cache/pacDE91.tmp`
    - Removed stale environment backup artifact `.env.old`
- Portal Personalization now persists server-side for cross-device consistency:
    - Added `portal_preferences` JSON column to `users` table via migration
    - Created `PortalPreferenceService` for preference management with validation
    - Updated `MainPortalService` to integrate with portal preferences
    - Added API endpoints (`PortalPreferenceController`) for CRUD operations
    - Updated portal JavaScript to use API instead of localStorage
    - Preferences include language, module order, hidden modules, and quick links
    - Maintains backward compatibility with local storage fallback
- Resolved runtime boot failure for local environment (`MissingAppKeyException`):
    - Root cause identified: `APP_KEY` was empty in `.env`
    - Generated fresh application key via `php artisan key:generate --force`
    - Cleared framework caches via `php artisan config:clear` and `php artisan cache:clear`
    - Verified Laravel bootstrap health via `php artisan --version` (Laravel 10.49.1)

### In Progress / Next Batch
- Expand bilingual coverage to remaining module pages outside current ticket/meeting/purchase core flows.
- Expand focused bilingual behavioral tests (language switch + runtime text transitions) to remaining non-core modules beyond ticket/meeting/purchase/assets surfaces.

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
        a. Ensure that all features and functionalities are accessible and usable on mobile devices, tablets, and desktops.
    - Implement a dark mode toggle to allow users to switch between light and dark themes for better accessibility and user preference. (Pending need my approval)
3. Integrate a notification system to keep users informed about important updates and events.
    - Implement email notifications for critical events such as ticket updates, meeting room bookings, and asset maintenance reminders. (Pending need my approval)
    - Use in-app notifications to provide real-time updates to users while they are using the application.
    - Notify users about upcoming meetings, ticket updates, and asset maintenance schedules through in-app notifications. This will provide real-time updates to users while they are using the application and help them stay informed about important events and deadlines.
4. Enhance security measures to protect user data and prevent unauthorized access.
    - Implement role-based access control to restrict access to sensitive features and data based on user roles and permissions.
    - Use encryption to protect sensitive data, such as user passwords and personal information.
5. Optimize performance to ensure fast loading times and smooth user experience.
    - Implement caching strategies to reduce server load and improve response times.
    - Optimize database queries to enhance performance and reduce latency.
    - Implement pagination for data-heavy views to improve load times and user experience when dealing with large datasets (e.g., tickets, assets, purchase requests).
    - Use asynchronous processing for long-running tasks (e.g., report generation, bulk updates) to keep the application responsive and provide feedback to users about task progress and completion.
6. Database setup and restore:
    - Set up a local MySQL database using the credentials specified in the `.env` file.
    - Create the target database (`itapp`) if it does not already exist.
    - Import the provided SQL dump file (`database/backup_itquty_2026-04-15_122739.sql`) into the local MySQL database to restore the application data.
    - Change the database structure to match the new modular design of the application, ensuring that all necessary tables and relationships are properly defined to support the new features and functionalities. But modification the backup file to match the new structure of the database, and then import it to the local MySQL database.
7. UI/UX & Frontend improvements:
    - Redesign the user interface to align with the new modular structure of the application, ensuring that it is intuitive and user-friendly.
    - Ensure that the design is responsive and accessible across different devices and screen sizes.
    - Using modern design principles and a consistent visual language to enhance the overall user experience and make the application more visually appealing.
    - Use modal dialogs for critical actions (e.g., ticket resolution, meeting cancellation, purchase request approval) to provide clear confirmation prompts and prevent accidental actions.
    - Implement a consistent design language and style guide across all modules to enhance the overall user experience and maintain a cohesive look and feel throughout the application.
    - Button and link updates to ensure that all navigation paths are functional and lead to the correct pages, especially with the new modular structure in place.
    - Update the sidebar and navigation menus to reflect the new structure and provide easy access to all modules and features based on user roles and permissions.
        a. Implement a user context panel on the main portal to display relevant information about the logged-in user, such as their role, last login time, and account status.
        b. Add role-focused operational snapshots on the main portal to provide users with quick access to key metrics and actions relevant to their role (e.g., open tickets for IT support staff, upcoming meetings for receptionists, pending approvals for administrators).
    - Theme and styling updates to enhance the visual appeal of the application, including color schemes, typography, and layout adjustments to improve readability and user engagement.
    - Dark and light mode toggle to allow users to switch between different themes based on their preferences and improve accessibility for users with different visual needs.
    - Standardize the design of summary cards, tables, buttons, modals, and pagination across all modules to create a cohesive user experience and make it easier for users to understand and interact with the data presented.
    - Use standardized icons and visual cues to enhance the usability of the application and provide clear indications of actions, statuses, and important information.
    - Refractor all css and javascript code to match the new design and structure of the application, ensuring that all frontend code is organized and maintainable. Use modern frontend development practices and tools to enhance the performance and maintainability of the frontend codebase. This includes using modular JavaScript, leveraging CSS preprocessors, and implementing a build process for optimizing assets. Standardize the use of frontend libraries and frameworks to ensure consistency and reduce technical debt in the codebase. So that the frontend code is clean, efficient, and maintainable, providing a solid foundation for future enhancements and scalability of the application.
    - No CSS or JavaScript inline in Blade templates; all styles and scripts should be organized in separate files and properly linked to the views. This will improve the maintainability and readability of the codebase, as well as enhance the performance of the application by allowing for better caching and optimization of assets.
8. Implement additional features based on user feedback and requirements, such as:
    - A compact "Portal Personalization" setting to allow users to customize their portal experience by rearranging module order and adding quick links to frequently used features.
    - A bilingual toggle indicator (Indonesian/English) in the portal header to allow users to switch between languages easily.
    - An approval-center widget that aggregates pending tickets, meeting room bookings, and purchase requests for authorized users, allowing them to review and take action with one click.
9. Backend improvements:
    - Refactor the backend code to improve readability, maintainability, and scalability, following best practices and design patterns.
    - Implement a service layer to handle business logic and a repository layer for data access, ensuring a clear separation of concerns and easier maintenance.
    - Add comprehensive test coverage for the new features and functionalities to ensure reliability and facilitate future development.
    - Implement a dedicated workflow service for handling purchase request approvals, separating the business logic from controller actions and ensuring a more modular and maintainable codebase.
        a. Add API endpoints for managing portal personalization settings, allowing users to save their preferences for module order and quick links.
        b. Implement a notification system to send email notifications for critical events such as ticket updates, meeting room bookings, and asset maintenance reminders.
10. Codebase cleanup:
    - Remove any unused or redundant code, files, and assets to improve maintainability and reduce clutter in the codebase.
    - Ensure that all remaining code is well-documented and follows consistent coding standards for better readability and collaboration among developers.
    - Remove any temporary or development-only code and files.
    - Remove any code that is no longer relevant or has been replaced by new implementations, ensuring that the codebase remains clean and efficient.
        - Ensure that all code is properly organized and structured according to the new modular design of the application, making it easier for developers to navigate and understand the codebase.
        - Conduct a thorough review of the codebase to identify and remove any dead code, unused variables, or unnecessary comments that may have accumulated during development.
        - Use automated tools and linters to help identify and clean up any code quality issues, ensuring that the codebase remains maintainable and scalable for future development.
11. Ticketing:
    - Ticket auto-assignment based on asset category and technician expertise to streamline the support process and ensure that tickets are handled by the most qualified staff.
    - Implement a ticket escalation process for unresolved tickets to ensure timely resolution and customer satisfaction.
    - Add a knowledge base feature to allow support staff to document solutions and best practices for common issues, improving efficiency and consistency in handling support requests.
        a. Implement a ticket prioritization system to help support staff manage their workload and address critical issues first.
        b. Add a feature to allow users to track the status of their tickets and receive notifications about updates or resolutions.
        c. Integrate the ticketing system with the assets management module to track which assets are associated with support tickets and their maintenance schedules.
12. Meeting Room:
    - Implement a calendar view for meeting room schedules to provide a visual representation of bookings and availability.
    - Add a feature to allow users to check meeting room availability in real-time through an LCD display, providing convenient access to this information.
    - Implement a booking approval workflow for meeting rooms that require authorization, ensuring proper management of resources and preventing conflicts.
        a. Add a feature to allow users to view and manage their meeting room bookings, including the ability to cancel or reschedule bookings as needed.
        b. Integrate the meeting room booking system with calendar applications (e.g., Google Calendar, Outlook) to allow users to sync their bookings and receive reminders.
13. Assets Management:
    - Implement a comprehensive asset tracking system that includes inventory management, maintenance scheduling, and disposal processes.
    - Add a feature to generate and print labels for assets, including QR codes or barcodes for easy tracking and management.
    - Implement an asset import/export feature to allow bulk management of asset data using CSV or Excel files.
    - Add a QR code scanning functionality to quickly access asset information and update asset status using a mobile device or scanner.
        a. Integrate the assets management module with the ticketing system to track which assets are associated with support tickets and their maintenance schedules, providing a holistic view of asset performance and support history.
        b. Implement a maintenance scheduling feature that allows users to set up regular maintenance tasks for assets, receive notifications about upcoming maintenance, and track the history of maintenance activities for each asset.
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
        a. Roles: Guest, User, Receptionist, Human Resources, Administrator (IT Support Staff), Director (Management), Developer (IT Programmer Staff)
        b. Access level :
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
            "Wah, tiket saya langsung dibalas oleh seseorang dengan badge merah [LV 9 Administrator] atau badge ungu [LV 10 Developer]!"
            Ini memberikan ketenangan bagi pembuat tiket bahwa masalah mereka ditangani oleh ahlinya.
        3. Approval Logs (Purchase Request / Handover Asset):
        Di dalam riwayat dokumen, berikan stempel badge pada nama yang menyetujui. Misalnya, Approved by Feti [Badge Emas: LV 8 Prime]. Ini membuat rekam jejak audit (audit trail) menjadi sangat jelas secara visual.
        4. Halaman Profile:
        Sediakan area khusus di tengah halaman profil yang menampilkan versi besar dan detail dari badge mereka, lengkap dengan animasi penuhnya.
17. Implementasi AI: (Pending need my approval - Just for Administrator, Director and Developer use only)
    - Tambahkan fitur AI untuk meningkatkan pengalaman pengguna dan efisiensi operasional, seperti chatbot untuk dukungan otomatis, analitik prediktif untuk wawasan kinerja, dan alat otomatisasi untuk menyederhanakan proses.
    - Fitur AI akan diimplementasikan dalam fase proyek yang akan datang, dengan fungsionalitas spesifik yang akan ditentukan berdasarkan kebutuhan dan umpan balik pengguna. (Pending need my approval) (AI features will be implemented in the future phase of the project, and specific functionalities will be determined based on user needs and feedback) (Just for Administrator, Director and Developer use only)
18. Form management:
    - Implement a comprehensive form management system to handle various types of forms related to asset handover, lending, return, and disposal processes.
    - Allow administrators to create, manage, update, and print forms for asset handover, lending, return, and disposal processes, providing a streamlined workflow for managing company assets and ensuring proper documentation of asset transactions.
        a. Integrate the form management system with the assets management module to ensure that all asset-related transactions are properly documented and tracked, providing a holistic view of asset performance and history.
        b. Implement features for users to view and manage their forms, including the ability to submit new forms, track the status of existing forms, and receive notifications about updates or approvals related to their forms.
19. Database structure update:
    - Modify the existing database structure to align with the new modular design of the application, ensuring that all necessary tables and relationships are properly defined to support the new features and functionalities.
    - This may involve creating new tables for modules such as Assets Management, Purchase Request, and User Management, as well as updating existing tables to accommodate changes in data requirements and relationships between modules.
    - Ensure that the database structure is optimized for performance and scalability, allowing for efficient data retrieval and management as the application grows and evolves.
    - Drop any redundant or obsolete tables that are no longer needed in the new structure to maintain a clean and efficient database schema.
20. User Support and Documentation:
    - Implement a user support system that includes a comprehensive FAQ section and a contact support feature to assist users with any issues or questions they may have while using the application.
    - The FAQ section will provide answers to common questions and troubleshooting tips, while the contact support feature will allow users to submit inquiries directly to the support team for personalized assistance.
21. Update README.md file to include comprehensive documentation of the project, including setup instructions, feature descriptions, and contribution guidelines to facilitate collaboration and onboarding of new developers. CONTRIBUTING.md file will also be created to provide clear guidelines for contributing to the project, including coding standards, pull request process, and issue reporting. LICENSE.md file will be added to specify the terms under which the project is licensed, ensuring proper usage and distribution of the codebase.


## Structure of the application:
# LOGIN PAGE
- User Login (Username/Email and Password) additionally with "Remember Me" option for persistent login sessions.
    - Forgot Password (Allow users to reset their password through email verification)
    - User Registration (Allow new users to create an account with email verification for added security)

# MAIN PORTAL
User will pick the module that they want to access from the main portal, and the features that they can access will depend on their role and permissions. The main portal will also display relevant information and metrics based on the user's role, such as open tickets for IT support staff, upcoming meetings for receptionists, and pending approvals for administrators. So main portal will be the central hub for users to navigate to different sections of the application and access the features that are relevant to their role and responsibilities, no sidebar menu, just a dashboard with cards for each module and quick links to important features within those modules. Then after user click the card or quick link, they will be directed to the respective module page where they can access the features and functionalities of that module based on their role and permissions. The main portal will also provide a personalized experience for users, allowing them to customize their dashboard with widgets and shortcuts to frequently used features, enhancing their productivity and efficiency within the application.

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