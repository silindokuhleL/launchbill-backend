# Laravel Backend Rules

These rules apply to Laravel REST API projects in this portfolio system.

## Required Stack

- Laravel REST API.
- MySQL as the default relational database.
- Redis for queues, cache, rate limiting, and background work.
- Spatie Laravel Permission for RBAC.
- Spatie Media Library when files, images, documents, or user uploads are part of the project.
- PayFast for South African payment proof where it fits the product.
- PHPUnit for backend test coverage.

## Architecture Rules

- Controllers must stay thin.
- Controllers may validate, authorize, call services, and return resources.
- Core functionality must live in services, actions, jobs, policies, or domain classes.
- Always use Laravel API Resources to return frontend data.
- Always add relationships on related models.
- Shared behavior should be extracted into reusable traits, services, actions, or support classes.
- Avoid duplicating tenant, logging, response, and permission logic across controllers.

## Tenant Rules

- Tenant-aware projects must include tenant stubs early.
- Tenant-scoped models must be query-safe.
- Super admin roles must not be forced into a tenant.
- Tenant roles and global roles must be clearly separated.
- Seeders must create at least one tenant, tenant users, tenant roles, and sample tenant data.
- Tests must prove tenant data cannot leak across tenants.

## Data And Seeders

- Every core feature needs a migration.
- Every core feature needs a seeder or factory-backed demo data path.
- Seeders must create realistic dummy data for local testing.
- Seeders must include leaders or owner-level users where the product needs account ownership.
- Factories should support PHPUnit tests and demo seeders.

## Logging And Auditability

- Log important requests and state changes.
- Billing, auth, permissions, tenant switching, webhook processing, and admin actions must be auditable.
- Request logging must avoid storing secrets, passwords, tokens, and sensitive payment data.
- Use structured logs where possible.
- Consider a reusable audit service or trait for important model events.

## Notifications

- Send email notifications for important user-facing events.
- Queue email notifications when they are not needed inside the request-response path.
- Notification copy should be clear, short, and useful.

## Backend Testing

- Use PHPUnit for core backend functionality.
- Cover auth, RBAC, tenants, services, resources, payments, notifications, and webhooks.
- Add tests for each service that owns business logic.
- Add tests for each core API resource response shape.

## Cleanup Rule

- Check and remove or replace default demo files such as `ExampleController` before the project is considered portfolio-ready.

