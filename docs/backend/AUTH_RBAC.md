# LaunchBill Auth And RBAC

## Auth Goal

Users should authenticate through the API and only access resources permitted by their account role.

## Suggested Roles

- Super Admin: manages platform-level settings.
- Account Owner: manages billing, team, plans, and subscriptions for their account.
- Billing Manager: manages customers, invoices, payments, and subscriptions.
- Viewer: reads dashboard and billing data only.

## Permission Groups

- `dashboard.view`
- `plans.manage`
- `customers.manage`
- `subscriptions.manage`
- `invoices.view`
- `payments.view`
- `team.manage`
- `roles.manage`
- `audit.view`

## Rules

- Every protected endpoint requires authentication.
- Every account-owned resource must be scoped to the active account.
- Role checks must happen on the backend.
- Frontend can hide controls for UX, but backend must enforce permissions.

