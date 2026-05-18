# LaunchBill Database Schema

## Core Tables

### users

- id
- name
- email
- password
- email_verified_at
- timestamps

### accounts

- id
- name
- owner_id
- billing_email
- status
- theme_primary_color
- theme_logo_media_id
- timestamps

### account_user

- id
- account_id
- user_id
- role_id
- timestamps

### plans

- id
- account_id
- name
- slug
- description
- price_cents
- currency
- billing_interval
- trial_days
- features
- is_active
- sort_order
- timestamps
- soft deletes

### customers

- id
- account_id
- name
- email
- company_name
- phone
- provider_customer_id
- status
- billing_address
- notes
- timestamps
- soft deletes

### subscriptions

- id
- account_id
- customer_id
- plan_id
- provider_subscription_id
- status
- quantity
- unit_price_cents
- currency
- starts_at
- trial_ends_at
- current_period_starts_at
- current_period_ends_at
- canceled_at
- ended_at
- metadata
- timestamps
- soft deletes

### invoices

- id
- account_id
- customer_id
- subscription_id
- provider_invoice_id
- number
- amount_due_cents
- amount_paid_cents
- currency
- status
- issued_at
- due_at
- paid_at
- voided_at
- line_items
- metadata
- timestamps
- soft deletes

### payments

- id
- account_id
- invoice_id
- provider_payment_id
- amount
- currency
- status
- failure_reason
- paid_at
- timestamps

### webhook_events

- id
- provider
- provider_event_id
- type
- payload
- processed_at
- failed_at
- failure_reason
- timestamps

### audit_logs

- id
- account_id
- user_id
- action
- subject_type
- subject_id
- metadata
- timestamps

### request_logs

- id
- account_id
- user_id
- method
- path
- status_code
- ip_address
- duration_ms
- metadata
- timestamps

## Schema Rules

- Use foreign keys for ownership relationships.
- Index provider IDs used by webhooks.
- Index status columns used by dashboards.
- Store money as integer minor units.
- Store provider payloads only where useful for debugging.
- Keep request logs free of secrets, passwords, tokens, and sensitive payment data.
