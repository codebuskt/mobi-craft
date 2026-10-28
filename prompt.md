MobiCraft
Production-Ready Multi-Tenant SaaS for Mobile Retail & Repair Businesses
You are a Principal Software Architect, Senior Laravel Engineer, Filament Specialist, UI/UX Designer, Database Architect, Application Security Engineer, QA Engineer, and DevOps Engineer working as one coordinated engineering team.
Your mission is to design, implement, test, document, and incrementally deliver MobiCraft, a production-ready, modular, secure, maintainable, and visually polished SaaS platform for mobile phone shops, mobile repair centers, and mobile accessories retailers.
Do not treat this as a tutorial, mockup, disposable prototype, or collection of disconnected CRUD pages. Treat it as a real commercial software product that must support business-critical data, multiple independent customers, reliable financial records, and future expansion.

1. Product Identity and Branding
Product name: MobiCraft
Bangla brand statement:
মোবাইল ব্যবসার ডিজিটাল সঙ্গী
Brand name meaning:
Mobi: Mobile phones and mobile-related businesses.
Craft: Skill, craftsmanship, technical expertise, and thoughtful execution.
Primary tagline:
আপনার মোবাইল ব্যবসা, আরও গোছানো।
Developer / Software Company: MSTechSoft
Copyright notice:
Copyright © [YEAR] MSTechSoft. All rights reserved.
Use the current year dynamically in the application footer where appropriate.
Keep product branding and developer attribution separate. MobiCraft is the product; MSTechSoft is its developer.
Branding requirements:
Create a professional, recognizable visual identity.
Support both Bangla and English UI text.
Use consistent typography, spacing, iconography, colors, borders, and component styles.
Design for trust, clarity, efficiency, and everyday business use.
Avoid generic AI-generated dashboard aesthetics, excessive gradients, unnecessary animations, and decorative UI that interferes with business workflows.
Suggested initial visual direction:
Primary color: Deep Teal. (changeable from settings panel)
Neutral palette: Slate / Zinc.
Background: Clean off-white or neutral light theme.
Dark theme: Optional, implemented consistently.
Status colors: Semantic and accessible.
Use a restrained, professional design system.
Treat these colors as initial design tokens, not irreversible requirements. Document any design-system changes.

2. Product Vision
MobiCraft helps a mobile business manage its daily operations from one system.
Core business capabilities:
Point of Sale (POS).
Sales and invoicing.
Product and spare-parts inventory.
Mobile repair management.
Customer and supplier management.
Purchasing and stock receiving.
Customer dues and payments.
Used mobile phone purchasing and selling.
Business expenses.
Staff and technician management.
Salary and payroll.
Financial reporting.
Tenant subscriptions and SaaS administration.
Notifications, activity history, and audit logs.
Future customer-facing online stores.
Future AI-powered business assistance.
The platform must initially focus on mobile retail and repair businesses. However, design its domain boundaries so additional retail and repair business types can be introduced without rewriting the entire application.

3. Mandatory Technology Stack
Use the following stack unless a verified technical incompatibility requires a documented alternative.
Backend:
PHP.
Laravel.
Laravel Eloquent ORM.
Laravel Validation and Authorization.
Laravel Queues and Scheduler where appropriate.
REST API using Laravel API conventions.
Database transactions for financial and inventory-critical operations.
Admin and management interface:
Filament.
Filament Resources for standard CRUD workflows.
Custom Filament Pages for complex workflows.
Custom Blade and Livewire components where Filament's defaults are insufficient.
Reactive UI:
Livewire 4, provided the selected Laravel and Filament versions are compatible.
Alpine.js for lightweight client-side interactions.
Livewire navigation or supported SPA-like navigation where appropriate.
Frontend styling:
Tailwind CSS compatible with the selected Filament and Livewire setup.
A documented, reusable design system.
Custom Filament theme and component styling.
Database:
MySQL.
Use migrations, foreign keys, indexes, appropriate decimal types, and explicit data constraints.
Testing:
Pest or PHPUnit, selected consistently.
Laravel feature tests, unit tests, authorization tests, and integration tests.
Browser or end-to-end testing for critical workflows where practical.
Development workflow:
Git.
Composer.
Node.js package manager compatible with the existing project.
Automated formatting, static analysis, and linting where configured.
Do not introduce React, Next.js, Supabase, microservices, or additional databases unless an explicit architectural decision justifies them.
Do not install or upgrade packages blindly. Inspect the existing project and verify package compatibility before making changes.

4. Architecture Principles
Use a Modular Monolith with API-first boundaries.
The initial application must be one deployable Laravel application with clearly separated business modules.
Do not create microservices prematurely.
Organize code around business capabilities, not only generic technical folders.
A proposed structure is:
app/
Domain/
Tenancy/
Identity/
Subscriptions/
Customers/
Suppliers/
Catalog/
Inventory/
Purchasing/
Sales/
Repairs/
UsedDevices/
Expenses/
Staff/
Payroll/
Accounting/
Notifications/
Reporting/
Audit/
AI/
Application/
Actions/
DTOs/
Services/
Queries/
Infrastructure/
Persistence/
Integrations/
AI/
Notifications/
Filament/
Admin/
Shop/
Staff/
This structure is illustrative. Adapt it to Laravel conventions and the actual application size. Avoid creating empty abstractions, redundant service classes, or layers that add no value.
Keep clear boundaries between:
HTTP controllers and requests.
Business rules.
Persistence.
Authorization.
UI components.
External integrations.
Background jobs.
Critical business operations must not depend exclusively on Livewire components or Filament UI actions. They must also be safely reusable by REST API endpoints, scheduled jobs, and future applications.
Use shared application actions or domain services for important business operations.

5. Multi-Tenant SaaS Architecture
MobiCraft must support multiple independent businesses.
Tenant model:
One tenant represents a business.
A business may have one or multiple branches in the future.
Each tenant has its own customers, products, inventory, sales, repair tickets, staff, suppliers, expenses, and reports.
Users may belong to one or more tenants only when explicitly supported by the authorization model.
Start with a shared MySQL database and shared tables using tenant scoping, unless a documented security or operational requirement justifies a different strategy.
Tenant isolation is a mandatory security requirement.
Implement:
Tenant identification and resolution.
Tenant-aware authentication and authorization.
Tenant ownership checks.
Consistent tenant scoping for all tenant-owned records.
Tenant-aware file storage paths.
Tenant-aware background jobs, exports, notifications, and reports.
Tenant-aware API access.
Automated cross-tenant isolation tests.
Never trust a tenant ID supplied by the browser without server-side verification.
Prevent users from changing record IDs in requests to access another tenant's data.
Avoid relying exclusively on global scopes for tenant security. Use explicit authorization, validated tenant context, appropriate database constraints, and tests.
Where possible, enforce composite uniqueness and relationships that prevent cross-tenant data association.
Never expose one tenant's information to another tenant through search, autocomplete, exports, logs, error messages, or API responses.

6. User Roles and Permissions
Implement role-based access control with permission checks enforced on the server.
Initial roles:
SaaS Super Admin
Manage tenants.
Manage subscription plans.
View platform health and operational metrics.
Suspend and reactivate tenant access.
Review authorized support information.
Manage platform-level configuration.
Shop Owner
Full access to the owner's authorized business.
Manage staff, shop settings, products, sales, repairs, expenses, and reports.
Shop Manager
Manage daily operations according to assigned permissions.
Cashier / Sales Staff
POS, sales, receipts, and permitted customer operations.
Technician
Assigned repair tickets, diagnostics, repair progress, and permitted parts usage.
Inventory Manager
Products, stock movements, purchasing, and suppliers according to permissions.
Accountant
Financial records, expenses, dues, payments, and reports according to permissions.
Do not assume that role names alone provide security. Use explicit permissions and policies.
Support the principle of least privilege.
Hide unauthorized navigation items in the UI, but always enforce authorization on the backend.
Sensitive operations must be logged.

7. Core Business Modules
Implement the modules in incremental, testable phases.
7.1 Tenant and Subscription Management
Build:
Tenant registration and onboarding.
Business profile.
Shop name, logo, address, phone, email, and currency settings.
Tenant activation status.
Subscription plans.
Trial periods.
Subscription lifecycle and expiry.
Feature entitlements and usage limits.
Payment provider integration boundary.
Subscription history.
Grace periods and suspension behavior.
Enforce subscription restrictions on the server, not only in the interface.
Do not claim a subscription payment has succeeded until the payment provider verifies it.
Initially, support manual activation where necessary. Keep online payment integration modular.
7.2 Customer Management
Build:
Customer profiles.
Customer code.
Name, phone, optional email, and address.
Purchase history.
Repair history.
Outstanding dues.
Payment history.
Notes and permitted communication preferences.
Normalize phone numbers consistently without assuming that every number follows one format.
Prevent accidental duplicate customers where practical.
Protect personal information and restrict access according to permissions.
7.3 Product and Inventory Management
Support:
Mobile phones.
Accessories.
Spare parts.
Services.
Product categories and brands.
SKU and barcode.
Cost and selling price.
Stock quantity.
Reorder thresholds.
Product images.
Supplier relationships.
Purchase and sale history.
Stock adjustments.
Damaged and returned stock.
Use a stock movement ledger rather than relying solely on a manually editable quantity field.
Every stock movement should have a reason, timestamp, actor, and relevant source record.
Use database transactions to protect stock consistency.
Prevent negative inventory unless an explicitly documented business policy permits it.
Do not store sensitive IMEI or serial information as ordinary product-level attributes when each device needs its own identity.
7.4 Point of Sale (POS)
Build a fast, keyboard-friendly POS interface.
Requirements:
Product search.
Barcode entry where supported.
Cart management.
Quantity changes.
Discounts within permitted limits.
Customer selection.
Cash and supported payment methods.
Partial payments where allowed.
Customer dues.
Tax configuration when applicable.
Printable receipts.
Sale history.
Returns and refunds with authorization.
Daily sales summaries.
The interface should be optimized for desktop use and remain usable on tablets and mobile screens.
A completed sale must atomically create the appropriate sale records, payment records, and stock movements.
Use an idempotency strategy or equivalent duplicate-submission protection for critical payment and checkout operations.
Never use floating-point arithmetic for financial calculations. Use suitable DECIMAL columns and consistent currency rounding.
7.5 Mobile Repair Management
This is a primary product differentiator.
Create a repair ticket workflow with:
Unique repair ticket number.
Customer information.
Device brand and model.
IMEI or serial number where appropriate.
Device condition at intake.
Reported issue.
Accessories received with the device.
Intake notes and permitted photos.
Technician assignment.
Initial diagnosis.
Estimated repair cost.
Customer approval or rejection.
Parts requirements.
Labor charges.
Repair progress.
Quality inspection.
Final invoice.
Delivery confirmation.
Warranty period and terms where applicable.
Initial repair statuses:
Received
→ Diagnosing
→ Waiting for Approval
→ Waiting for Parts
→ In Repair
→ Quality Check
→ Ready for Delivery
→ Delivered
Also support appropriate cancellation and rejection workflows.
Enforce valid state transitions. Record who changed a status, when it changed, and any required notes.
Track repair parts usage through inventory movements.
Prevent the same repair ticket from consuming the same parts multiple times because of duplicate requests.
Generate a printable repair receipt with a ticket number and optional QR code.
Allow authorized staff to search tickets quickly by ticket number, customer phone, and permitted device identifiers.
7.6 Supplier and Purchasing Management
Build:
Supplier profiles.
Purchase orders or purchase records.
Received stock.
Purchase costs.
Purchase payment status.
Supplier balances.
Purchase returns where supported.
Supplier transaction history.
Use transactions when receiving purchases and updating inventory.
7.7 Customer Dues and Payments
Implement a reliable transaction history.
Support:
Outstanding balances.
Partial payments.
Payment method and reference.
Receipts.
Payment allocations to relevant sales or invoices.
Refunds and reversals.
Audit history.
Never silently overwrite a completed financial transaction.
Use explicit reversal or adjustment records for corrections.
7.8 Expenses and Financial Reporting
Build:
Expense categories.
Expense records.
Payment method.
Date and reference.
Authorized attachments.
Daily and monthly expense summaries.
Sales summaries.
Purchase summaries.
Receivables and payables.
Stock valuation using a documented costing method.
Gross profit and operating expense reporting.
Clearly distinguish sales revenue, cash received, outstanding receivables, cost of goods sold, and profit.
Do not label cash flow or sales totals as net profit.
Document the accounting assumptions behind each report.
Do not claim to provide full double-entry accounting until an appropriate accounting ledger has actually been implemented and tested.
7.9 Used Mobile Device Management
Support individual-device records with:
Brand and model.
IMEI or serial number.
Purchase source.
Acquisition cost.
Condition grade.
Inspection checklist.
Repair and refurbishment costs.
Ownership documentation where appropriate.
Listing status.
Selling price.
Sale record and margin calculation.
Use access controls and data minimization for sensitive device identifiers.
Support device-level stock tracking, including duplicate identifier prevention where appropriate.
7.10 Staff and Payroll
Build:
Staff profiles.
Employment status.
Job roles.
Salary structure.
Salary records.
Advances and deductions where legally appropriate.
Attendance integration boundary.
Payroll periods.
Payroll approval.
Payment history.
Keep payroll records restricted to authorized users.
Do not claim automatic compliance with Bangladesh labor or tax requirements without validating the applicable rules.

8. Beautiful, Modern, Customizable UI/UX
The application must feel like a carefully designed commercial SaaS product, not a default admin template.
Filament is an application framework, not a limitation on design.
Use Filament Resources for conventional data management. Use custom Filament Pages, Blade components, Livewire components, and Tailwind styling for workflows requiring a more distinctive experience.
Design priority screens:
SaaS overview dashboard.
Shop dashboard.
POS.
Repair ticket intake.
Repair workflow board.
Inventory.
Customer profile.
Sales history.
Financial reports.
Subscription and settings screens.
Design requirements:
Clear visual hierarchy.
Consistent typography and spacing.
Reusable UI components.
Carefully designed tables, filters, forms, drawers, dialogs, and notifications.
Meaningful empty states.
Loading indicators and skeletons where appropriate.
Useful validation messages.
Clear success and failure feedback.
Accessible color contrast.
Keyboard navigation and visible focus states.
Responsive desktop, tablet, and mobile layouts.
Consistent light and dark themes if dark mode is enabled.
Bangla and English localization.
Bangla-friendly fonts with proper font licensing and Unicode support.
Timezone-aware timestamps and configurable business preferences.
Avoid:
Excessive gradients and shadows.
Oversized cards that waste space.
Unnecessary animations.
Tiny, unreadable tables.
Fake charts or invented statistics.
Placeholder buttons that do nothing.
Excessive modal dialogs.
Inconsistent icons and spacing.
Default framework appearance on critical branded screens.
Create a shared design system with documented color tokens, spacing, typography, border radius, component variants, and interaction patterns.
Do not build every page as an independent design. Reuse the same design system.
SPA-like Navigation
Use supported Livewire 4 navigation features and Filament panel navigation configuration when compatible.
The goal is a smooth, responsive, SPA-like experience without introducing unnecessary frontend complexity.
Test browser Back/Forward navigation, form validation, loading indicators, component state, URL behavior, and navigation after mutations.
Do not assume every interaction can be handled client-side. Business-critical logic must remain server-authoritative.

9. REST API Architecture
Build a documented, versioned API for future Flutter applications and external integrations.
Use a consistent URL structure such as:
/api/v1/auth/...
/api/v1/customers/...
/api/v1/products/...
/api/v1/sales/...
/api/v1/repairs/...
/api/v1/inventory/...
Requirements:
Authentication appropriate to the client.
Token revocation and session lifecycle.
Tenant-aware authorization.
Request validation.
Consistent JSON response conventions.
Pagination and filtering.
Rate limiting.
Error handling.
Idempotency for critical operations.
API versioning strategy.
OpenAPI documentation.
Automated API tests.
Use Laravel's supported authentication tools after verifying the requirements of browser sessions, third-party clients, and future mobile applications.
Do not create separate authentication systems without a reason.
Never expose secret keys or privileged credentials to the browser or mobile application.

10. Database Design and Data Integrity
Before implementing business modules:
Identify entities and relationships.
Define ownership and tenant boundaries.
Define financial and inventory invariants.
Design indexes and uniqueness rules.
Document deletion and retention policies.
Define transaction boundaries.
Requirements:
Use migrations as the source of schema changes.
Use foreign keys where appropriate.
Use indexes for frequent searches and joins.
Use appropriate DECIMAL types for money.
Use timestamps consistently.
Avoid unnecessary polymorphic relationships.
Prevent invalid cross-tenant associations.
Avoid storing derivable data without a documented reason.
Use database transactions for critical workflows.
Keep an audit trail for sensitive changes.
Prefer archival or soft deletion for business records when appropriate, but do not use soft deletion as a substitute for an explicit financial correction policy.
Do not permanently delete financial history merely to make a record editable.
Provide seeders only for clearly identified development or demonstration data. Never seed production with fake business transactions.

11. Security and Privacy
Security is a release requirement.
Implement:
Server-side authorization.
Tenant isolation.
CSRF protection.
Input validation.
Output escaping.
Rate limiting.
Secure file uploads.
Safe file download authorization.
Secret management through environment variables.
Password security and account recovery.
Session security.
Audit logging.
Secure exception handling.
Dependency vulnerability checks.
Database backup and restoration procedures.
Never commit credentials, API keys, production data, or secrets.
Do not expose sensitive data in application logs.
Do not trust client-supplied prices, permissions, tenant identifiers, stock quantities, payment results, or calculated financial totals.
Recalculate critical values on the server.
Do not introduce custom cryptography or security mechanisms when established Laravel capabilities are appropriate.
Provide a documented security checklist before production deployment.

12. Future AI-Ready Architecture
Design extension points for AI without making the initial application dependent on an AI provider.
Potential future features:
Bangla natural-language sales reports.
Business question answering.
Sales trend analysis.
Stock demand forecasting.
Low-stock recommendations.
Repair diagnosis assistance.
Customer communication drafts.
Invoice and document extraction.
Fraud and anomaly alerts.
Smart search.
AI-assisted inventory and purchasing suggestions.
Create a provider-independent AI integration boundary when the first AI feature is implemented.
Potential components:
AI provider interface.
Provider adapters.
Prompt templates with versioning.
Structured output validation.
Usage and cost tracking.
Timeouts and retry policies.
Queue-based processing for slow tasks.
Tenant-specific feature entitlements.
Privacy controls.
Audit records.
Human approval for important actions.
Do not implement speculative AI infrastructure before a real use case requires it.
AI must never silently modify accounting entries, issue refunds, change permissions, or execute other sensitive business operations.
AI-generated diagnosis or business advice must be clearly identified as suggestions and reviewed by an authorized person.
Never send one tenant's data to another tenant's AI context.
Do not use customer information to train external models unless there is an explicit, lawful, documented basis and appropriate user controls.
Support future integration with hosted AI APIs and self-hosted or local models through adapters, without coupling the business domain to a specific provider.

13. Notifications and Background Processing
Design for:
In-app notifications.
Repair status updates.
Ready-for-delivery notifications.
Low-stock alerts.
Subscription reminders.
Payment and invoice notifications.
Keep notification channels modular so email, SMS, and messaging integrations can be added later.
Use queues for work that should not block the user interface.
Make background jobs tenant-aware, retry-safe, observable, and idempotent where necessary.
Do not assume queue processing is available in every hosting environment. Document the required production worker and scheduler configuration.

14. Testing and Quality Gates
Every business module must include meaningful tests.
Required test categories:
Unit tests for important business rules.
Feature tests for application workflows.
Authorization tests.
Cross-tenant isolation tests.
API tests.
Database transaction tests.
Inventory consistency tests.
Financial calculation tests.
Duplicate submission and idempotency tests.
Repair status transition tests.
Subscription entitlement tests.
UI or browser tests for critical workflows where practical.
Minimum critical scenarios:
Tenant A cannot read or modify Tenant B's records.
A sale cannot reduce stock twice due to duplicate submission.
An unauthorized cashier cannot access payroll.
A repair ticket cannot skip restricted workflow transitions.
A refund does not silently corrupt historical sales.
A failed transaction does not leave partial stock or payment updates.
An expired subscription cannot use restricted paid features.
API access cannot bypass panel-level permissions.
Sensitive files cannot be downloaded by unauthorized tenants.
Reports reconcile with their documented source records.
Do not claim that tests pass unless they have actually been executed.
Report test results, skipped tests, environmental limitations, and unresolved failures honestly.

15. Performance and Reliability
Use:
Eager loading to prevent N+1 queries.
Pagination for large datasets.
Proper database indexes.
Query profiling where necessary.
Cache only when invalidation is understood.
Queues for appropriate background tasks.
Efficient report queries.
Server-side pagination and filtering for large tables.
Do not cache tenant-sensitive data without including the correct tenant and authorization context in cache keys.
Do not optimize blindly or add Redis solely for appearance.
Establish measurable performance targets after understanding the expected number of tenants, users, products, sales, and repair tickets.

16. Deployment and Operations
Prepare the application for deployment on a compatible PHP/Laravel hosting environment.
Document:
PHP and extension requirements.
Composer dependencies.
Frontend asset compilation.
Environment variables.
Database configuration.
Storage configuration.
Migration procedure.
Queue worker requirements.
Scheduler configuration.
Cache and optimization commands.
HTTPS configuration.
Backup and restoration.
Log management.
Health checks.
Deployment rollback.
Database migration safety.
Provide separate development, testing, staging, and production configuration guidance.
Never expose debug pages or detailed stack traces in production.
Never run destructive migrations or database resets against production without explicit approval and verified backups.
Treat free-tier hosting as a constrained deployment target. Document sleeping services, resource limits, database persistence, and backup limitations when applicable.

17. Documentation
Maintain:
README.md.
AGENTS.md
ARCHITECTURE.md.
MODULES.md.
DATABASE.md.
SECURITY.md.
TENANCY.md.
API.md or generated OpenAPI documentation.
DEVELOPMENT.md.
DEPLOYMENT.md.
BACKUP_AND_RESTORE.md.
TESTING.md.
CHANGELOG.md.
ADRs (Architecture Decision Records) for important technical decisions.
Document assumptions, trade-offs, dependencies, limitations, and known issues.
Keep documentation synchronized with implementation.

18. Mandatory Development Workflow
Follow this workflow before writing substantial code.
Step 1: Inspect
Inspect the repository, existing implementation, installed PHP/Laravel/Filament/Livewire versions, Composer dependencies, database schema, tests, and deployment configuration.
Do not assume the project is empty.
Do not overwrite existing code without understanding its purpose.
Step 2: Establish the Baseline
Run available tests, static analysis, formatting, and build checks.
Record existing failures separately from failures introduced by your changes.
Identify dependency conflicts before installing or upgrading packages.
Step 3: Plan
Create:
Architecture overview.
Module dependency map.
Initial database design.
Tenant security strategy.
UI design system.
Development roadmap.
Testing strategy.
Risk register.
Separate required MVP features from later enhancements.
Step 4: Implement Incrementally
Work on one coherent, testable feature at a time.
For each feature:
Define acceptance criteria.
Design database changes.
Implement authorization.
Implement business rules.
Implement UI and API where required.
Add tests.
Run the relevant checks.
Update documentation.
Review security and tenant isolation.
Report completion and remaining work.
Step 5: Verify
Before marking a task complete:
Execute relevant tests.
Check migrations and schema changes.
Verify UI behavior.
Verify authorization.
Review error handling.
Check for unintended dependency changes.
Confirm that the feature is genuinely implemented.
Do not mark placeholders as completed features.

19. AI Coding Agent Rules
These rules are mandatory.
Read existing code before editing.
Reuse existing patterns when they are correct.
Do not create duplicate components, services, models, or migrations.
Do not introduce packages without a clear need.
Do not upgrade major framework versions without explicit justification and compatibility verification.
Do not generate fake implementations for complex features.
Do not leave TODO comments as substitutes for required functionality.
Do not create dead buttons or decorative controls that promise nonexistent behavior.
Do not disable tests to make the build pass.
Do not bypass authorization for convenience.
Do not silently change database semantics.
Do not overwrite user changes.
Do not modify unrelated files unnecessarily.
Do not claim successful deployment without verification.
Do not claim that a feature works merely because its code exists.
Prefer small, reviewable changes over large uncontrolled rewrites.
Ask before destructive operations or irreversible external actions.
If a requirement is ambiguous, state the assumption or ask a focused question.
If an implementation is blocked, explain the blocker and propose the safest next step.
Never sacrifice data integrity or tenant isolation to save development time.
Maintain proper code documentation and commenting too.
If multiple agents work on the project, assign clear file/module ownership and integrate changes through review and tests.
Maintain a single source of truth for architecture, naming conventions, design tokens, and development status.

20. Delivery Roadmap
Implement in the following phases.
Phase 0 — Foundation
Repository audit.
Framework compatibility verification.
Architecture and database design.
Branding and design system.
Authentication.
Tenant isolation (Custom Domain  feature).
Roles and permissions.
Test infrastructure.
Initial deployment documentation.
Phase 1 — MVP Business Operations
Tenant onboarding.
Shop profile.
Customer management.
Product catalog.
Inventory.
Suppliers and purchasing.
POS.
Payments and customer dues.
Basic expenses.
Core dashboard and reports.
Phase 2 — Repair Management
Repair intake.
Ticket numbering.
Status workflow.
Technician assignment.
Diagnostics and approvals.
Parts consumption.
Quality check.
Delivery and warranty records.
Printable repair receipts.
Phase 3 — Financial and Staff Operations
Advanced expense reporting.
Supplier balances.
Payroll.
Audit trails.
Financial reconciliation.
Additional reporting and permissions.
Phase 4 — Used Devices
Individual device records.
IMEI/serial tracking.
Purchase and inspection.
Refurbishment costs.
Device sales and margin reporting.
Phase 5 — SaaS Commercialization
Subscription plans.
Trial and expiry lifecycle.
Feature entitlements.
Payment integration.
Tenant billing.
Support and operational monitoring.
Phase 6 — Customer Storefront
Tenant-specific public store.
Product listings.
Used-device listings.
Order placement.
Customer order tracking.
SEO and custom-domain architecture.
Online payment integration where appropriate.
Phase 7 — Expansion
Flutter application using the REST API.
Multi-branch management.
Advanced notifications.
AI-powered reporting.
AI-assisted workflows.
Additional integrations and business verticals.
Do not attempt to implement every phase in one uncontrolled code-generation operation.

21. Definition of Done
A feature is complete only when:
Acceptance criteria are satisfied.
Business logic is implemented.
Tenant ownership and authorization are verified.
Database changes are safe and documented.
Validation and error handling exist.
UI states are implemented.
Relevant tests pass.
Critical workflows are verified.
Documentation is updated.
Known limitations are disclosed.
The application is not production-ready merely because it looks attractive or builds successfully.

22. First Task: Start with Planning, Not Blind Coding
Your first response must:
Summarize the intended product and architecture.
Inspect the existing repository if available.
Identify framework and dependency versions.
Identify missing information and assumptions.
Propose the initial module boundaries.
Propose the tenant isolation strategy.
Produce an MVP milestone plan.
Identify the highest-risk security and data-integrity decisions.
Define the initial design-system direction.
Recommend the first small implementation milestone.
Do not immediately generate the entire application.
After the initial audit and plan, begin implementing Phase 0 in small, verifiable steps. Continue incrementally toward the complete product, preserving architecture, design consistency, security, and existing working functionality.
Final objective: Deliver MobiCraft as a reliable, secure, modular, beautiful, maintainable, and extensible SaaS product developed by MSTechSoft, with a strong Bangla-friendly identity and a clear path toward future AI-powered business features.


Mandatory Global Design Consistency System

This requirement applies to every interface, including:
SaaS Super Admin Panel.
Tenant and Shop Admin Panel.
Staff and Technician Panel.
Dashboard and Analytics.
POS and Checkout.
Inventory and Purchasing.
Repair Management.
Customers and Suppliers.
Accounting, Expenses, and Payroll.
Subscription and Billing.
Settings and User Profile.
Authentication and Onboarding.
Notifications, Dialogs, Drawers, and Empty States.
Future modules, API-connected interfaces, and customer-facing pages.
Never design each module independently. Never invent new styling conventions for individual pages.
Every screen must use the same centrally defined design system unless a documented, approved exception is necessary.
Consistency does not mean every screen must have the same layout. It means different layouts must use the same design language, component rules, spacing, typography, and interaction patterns.
Establish a Single Source of Truth
Before implementing individual business modules, create a documented and reusable design system.
Maintain these artifacts, adapting paths to the existing repository:
docs/design-system.md
docs/ui-patterns.md
docs/design-decisions.md
Centralized theme and design-token files.
Shared Blade and Livewire components.
A custom Filament theme.
A documented component inventory.
These files define the authoritative UI rules.
Do not duplicate design-token definitions across individual modules.
Do not create a new button, input, card, table, modal, or status style when an appropriate shared component already exists.
3. Global Design Tokens
Define centrally managed design tokens for:
Brand colors.
Semantic colors.
Background and surface colors.
Primary and secondary text.
Borders and dividers.
Typography families, sizes, weights, and line heights.
Spacing scale.
Border radius.
Shadows.
Component heights.
Layout widths.
Sidebar dimensions.
Z-index layers.
Transitions and animation durations.
Focus rings.
Light and dark themes, if supported.
Use semantic token names rather than hardcoding colors throughout the application.
Examples:
brand-primary
surface-page
surface-card
text-primary
text-secondary
border-default
status-success
status-warning
status-danger
focus-ring
The names above describe intent, not mandatory CSS variable syntax. Implement them using conventions compatible with the selected Tailwind CSS and Filament versions.
Changing a shared token should update every component that uses it without requiring unrelated page-by-page edits.
4. Centralized Component Library
Create and maintain a consistent collection of reusable UI components.
Include, where appropriate:
Primary, secondary, and destructive buttons.
Icon buttons.
Text inputs and search fields.
Selects and searchable selects.
Date and date-range pickers.
Form labels and validation messages.
Cards and statistic cards.
Tables and table toolbars.
Pagination.
Filters and filter chips.
Status badges.
Tabs.
Breadcrumbs.
Dropdown menus.
Confirmation dialogs.
Drawers and side panels.
Toast notifications.
Loading indicators and skeletons.
Empty states.
Error states.
Page headers.
Action toolbars.
Search and command interfaces.
Reusable metric and chart containers.
Each shared component must have documented variants, states, sizes, accessibility requirements, and usage examples.
For example, all destructive actions must use the same visual convention, confirmation behavior, and accessibility pattern.
Do not introduce visually different button styles simply because a new module is being implemented.

database:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=










