# launchbill-backend

Laravel REST API for LaunchBill, an AI-assisted SaaS subscription billing platform.

## Purpose

LaunchBill backend owns authentication, tenant-aware accounts, RBAC, PayFast billing, invoices, webhooks, request logging, audit logs, email notifications, queues, seeders, and REST API responses for the Next.js frontend.

## Core Standards

- Laravel REST API.
- MySQL.
- Redis.
- Spatie Permission for RBAC.
- Spatie Media Library where uploads are needed.
- PayFast payments.
- API Resources for frontend responses.
- Services for business logic.
- PHPUnit for core functionality.
- Queue-backed notifications and billing work.
- AI features that respect permissions, tenants, and auditability.

## Documentation

- `AGENTS.md`
- `docs/backend/BACKEND_SPEC.md`
- `docs/backend/API_ENDPOINTS.md`
- `docs/backend/DATABASE_SCHEMA.md`
- `docs/backend/AUTH_RBAC.md`
- `docs/backend/PAYMENTS_WEBHOOKS.md`
- `docs/backend/QUEUES_AND_JOBS.md`
- `docs/backend/TEST_CHECKLIST.md`
- `docs/ai-features.md`
- `docs/project-checklist.md`

