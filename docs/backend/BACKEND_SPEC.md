# LaunchBill Backend Spec

## Goal

Build a Laravel REST API that owns billing logic, subscriptions, payments, permissions, and reporting data for the LaunchBill frontend.

## Backend Responsibilities

- Authenticate users.
- Authorize actions with roles and permissions.
- Manage customers.
- Manage subscription plans.
- Manage subscriptions.
- Handle payment provider webhooks.
- Store invoice and payment records.
- Dispatch notification and billing jobs.
- Expose dashboard metrics.
- Return consistent JSON responses.

## Core Domains

- Auth and users.
- Teams or accounts.
- Roles and permissions.
- Plans.
- Customers.
- Subscriptions.
- Invoices.
- Payments.
- Webhooks.
- Notifications.
- Audit logs.
- Admin portal.
- Theme customization.
- Media and uploads.

## Suggested Laravel Packages

- Laravel Sanctum for API auth.
- Spatie Laravel Permission for RBAC.
- Spatie Media Library if invoice files, customer documents, or branded assets are uploaded.
- PayFast for South African payment proof.
- Laravel Queue with Redis for background jobs.
- PHPUnit for backend tests.

## Non-Negotiable Rules

- Backend validates every request.
- Backend checks permissions for protected actions.
- Backend never trusts payment status from the frontend.
- Webhooks must be verified before processing.
- Subscription state changes must be logged.
- Sensitive provider secrets stay in environment variables.
- Controllers must stay thin and call services for business logic.
- API Resources must return data to the frontend.
- Every related model must define useful relationships.
- Every core feature must have migrations, factories, and seeders.
- Tenant stubs must exist early so tenant-aware functionality does not get bolted on later.
- Global roles such as super admin must not be forced into a tenant.
- Default demo files such as `ExampleController` must be checked and removed or replaced before the project is portfolio-ready.

## Backend Structure

Use a reusable backend structure:

- Controllers for HTTP entry points.
- Requests for validation.
- Resources for frontend responses.
- Services for business logic.
- Actions for focused operations where useful.
- Policies for authorization.
- Jobs for queued work.
- Notifications for email.
- Traits for shared model behavior.
- Seeders and factories for realistic test/demo data.

## Admin Portal

The backend must support an admin management portal with:

- Global super admin access.
- Tenant/account management.
- User and role management.
- Billing visibility.
- Audit log visibility.
- Theme customization management.

## Theme Customization

Tenant-aware projects should support theme customization through backend-owned settings:

- Brand name.
- Primary color.
- Logo or brand image through Spatie Media Library when uploads are needed.
- Account-level display settings.

The frontend can render the theme, but the backend owns the stored customization data.

## Logging Strategy

LaunchBill must log:

- Auth events.
- Permission failures.
- Tenant switching or tenant context decisions.
- Admin actions.
- Billing state changes.
- PayFast webhook events.
- Queue job failures.

Logs must avoid secrets, passwords, tokens, and sensitive payment data.
