# LaunchBill API Endpoints

Base path: `/api/v1`

## Auth

- `POST /auth/register` creates an owner user, account, and API token.
- `POST /auth/login` returns a Sanctum bearer token and authenticated user summary.
- `POST /auth/logout` revokes the current bearer token.
- `GET /auth/me` returns the authenticated user summary.

## Dashboard

- `GET /dashboard/summary` returns selected-account dashboard metrics for revenue, customers, plans, subscriptions, invoices, and payments.
- `GET /dashboard/revenue`
- `GET /dashboard/subscriptions`

Dashboard summary response fields include:

- `account`
- `revenue.total_revenue_cents`
- `revenue.pending_revenue_cents`
- `revenue.failed_revenue_cents`
- `revenue.outstanding_invoice_cents`
- `revenue.active_mrr_cents`
- `customers`
- `plans`
- `subscriptions`
- `invoices`
- `payments`

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

- `GET /customers` lists customers for the selected account.
- `POST /customers` creates a customer for the selected account.
- `GET /customers/{customer}` returns one selected-account customer.
- `PATCH /customers/{customer}` updates one selected-account customer.
- `DELETE /customers/{customer}` archives one selected-account customer.

Customer payload fields:

- `name`
- `email`
- `company_name`
- `phone`
- `provider_customer_id`
- `status`
- `billing_address`
- `notes`

## Subscriptions

- `GET /subscriptions` lists subscriptions for the selected account with customer and plan summaries.
- `POST /subscriptions` creates a subscription for the selected account.
- `GET /subscriptions/{subscription}` returns one selected-account subscription.
- `POST /subscriptions/{subscription}/cancel` cancels one selected-account subscription.
- `POST /subscriptions/{subscription}/resume` resumes one selected-account subscription.

Subscription payload fields:

- `customer_id`
- `plan_id`
- `provider_subscription_id`
- `status`
- `quantity`
- `unit_price_cents`
- `currency`
- `starts_at`
- `trial_ends_at`
- `current_period_starts_at`
- `current_period_ends_at`
- `metadata`

## Invoices

- `GET /invoices` lists invoices for the selected account with customer and subscription summaries.
- `GET /invoices/{invoice}` returns one selected-account invoice.

Invoice response fields include:

- `customer_id`
- `subscription_id`
- `provider_invoice_id`
- `number`
- `amount_due_cents`
- `amount_paid_cents`
- `currency`
- `status`
- `issued_at`
- `due_at`
- `paid_at`
- `voided_at`
- `line_items`
- `metadata`

## Payments

- `GET /payments` lists payments for the selected account with customer and invoice summaries.
- `GET /payments/{payment}` returns one selected-account payment.

Payment response fields include:

- `invoice_id`
- `customer_id`
- `provider`
- `provider_payment_id`
- `amount_cents`
- `currency`
- `status`
- `failure_reason`
- `paid_at`
- `failed_at`
- `refunded_at`
- `metadata`

## Team And Roles

- `GET /team/members`
- `POST /team/members`
- `PATCH /team/members/{user}`
- `DELETE /team/members/{user}`
- `GET /roles`
- `POST /roles`
- `PATCH /roles/{role}`

## Webhooks

- `POST /webhooks/payfast` accepts PayFast ITN callbacks, verifies the signature, stores the event, ignores duplicate `pf_payment_id` values, and updates matching invoices/payments.
- `POST /webhooks/stripe` if Stripe is added later.

PayFast webhook response fields include:

- `provider`
- `provider_event_id`
- `type`
- `status`
- `duplicate`
- `processed_at`
- `failed_at`
- `failure_reason`

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
