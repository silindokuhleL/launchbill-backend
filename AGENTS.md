# AGENTS.md

These instructions apply to the LaunchBill backend repository.

## Project Identity

LaunchBill backend is a Laravel REST API for an AI-assisted SaaS billing platform. It must be built as a reusable, tenant-aware, service-driven backend that proves production-quality Laravel architecture.

## Required Reading

Before backend work, read:

- `docs/agent-rules/agent-rules.md`
- `docs/agent-rules/branching-and-commits.md`
- `docs/agent-rules/api-contract-rules.md`
- `docs/agent-rules/laravel-backend-rules.md`
- `docs/agent-rules/testing-rules.md`
- `docs/ai/ai-system-rules.md`
- `docs/backend/BACKEND_SPEC.md`

## Backend Rules

- Keep controllers thin.
- Put core business logic in services, actions, jobs, policies, and reusable support classes.
- Always return frontend-facing data through API Resources.
- Always add relationships for related models.
- Every core feature needs migrations, factories, and seeders.
- Every tenant-aware feature must respect tenant boundaries.
- Global roles such as super admin must not be forced into a tenant.
- Log important request, auth, permission, billing, tenant, webhook, and admin events.
- Never log secrets, passwords, tokens, or sensitive payment data.

## AI Rules

- AI features must respect permissions and tenant scope.
- AI must not decide payment truth.
- AI must not bypass RBAC.
- AI output must be auditable where it affects important records.
- AI-generated suggestions must be editable before saving.

## Workflow

- Start from `master`.
- Pull before branching.
- Use focused task branches.
- Make small commits.
- Run relevant checks before merging.

