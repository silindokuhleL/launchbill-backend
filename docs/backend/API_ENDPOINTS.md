# LaunchBill API Endpoints

Base path: `/api/v1`

## Auth

- `POST /auth/register` creates an owner user, account, and API token.
- `POST /auth/login` returns a Sanctum bearer token and authenticated user summary.
- `POST /auth/logout` revokes the current bearer token.
- `GET /auth/me` returns the authenticated user summary.

## Dashboard

- `GET /dashboard/summary`
- `GET /dashboard/revenue`
- `GET /dashboard/subscriptions`

## Plans

- `GET /plans` lists plans for the selected account.
- `POST /plans` creates a plan for the selected account.
- `GET /plans/{plan}` returns one selected-account plan.
- `PATCH /plans/{plan}` updates one selected-account plan.
- `DELETE /plans/{plan}` archives one selected-account plan.

Plan payload fields:

- `name`
- `slug`
- `description`
- `price_cents`
- `currency`
- `billing_interval`
- `trial_days`
- `features`
- `is_active`
- `sort_order`

## Customers

- `GET /customers`
- `POST /customers`
- `GET /customers/{customer}`
- `PATCH /customers/{customer}`
- `DELETE /customers/{customer}`

## Subscriptions

- `GET /subscriptions`
- `POST /subscriptions`
- `GET /subscriptions/{subscription}`
- `POST /subscriptions/{subscription}/cancel`
- `POST /subscriptions/{subscription}/resume`

## Invoices

- `GET /invoices`
- `GET /invoices/{invoice}`

## Payments

- `GET /payments`
- `GET /payments/{payment}`

## Team And Roles

- `GET /team/members`
- `POST /team/members`
- `PATCH /team/members/{user}`
- `DELETE /team/members/{user}`
- `GET /roles`
- `POST /roles`
- `PATCH /roles/{role}`

## Webhooks

- `POST /webhooks/payfast`
- `POST /webhooks/stripe` if Stripe is added later.

## Audit

- `GET /audit-logs`

## Admin And Theme

- `GET /admin/accounts`
- `GET /admin/accounts/{account}`
- `PATCH /admin/accounts/{account}`
- `GET /admin/audit-logs`
- `GET /theme`
- `PATCH /theme`
- `POST /theme/logo`
