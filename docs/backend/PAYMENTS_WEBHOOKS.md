# LaunchBill Payments And Webhooks

## Payment Provider Recommendation

Start with PayFast because it matches the South African market and strengthens local business relevance. Stripe can be added later if the project needs global subscription proof.

## Payment Features

- Create subscription checkout.
- Track subscription status.
- Track invoices.
- Track payment success.
- Track payment failure.
- Cancel subscription.
- Resume subscription if supported.

## Webhook Events To Handle

PayFast first:

- Payment completed.
- Payment failed.
- Subscription created or activated if supported by the chosen PayFast flow.
- Subscription cancelled if supported by the chosen PayFast flow.
- Recurring billing notification if supported by the chosen PayFast flow.

If Stripe is added later:

- `checkout.session.completed`
- `customer.subscription.created`
- `customer.subscription.updated`
- `customer.subscription.deleted`
- `invoice.paid`
- `invoice.payment_failed`

## Webhook Rules

- Verify the PayFast signature before creating local records.
- Build the PayFast signature from the received fields, excluding `signature`, ignoring empty values, URL-encoding trimmed values, appending `passphrase` when configured, and comparing the lowercase MD5 hash.
- Store webhook event before processing.
- Ignore duplicate provider event IDs and return the stored event response.
- Process subscription and invoice changes inside database transactions where needed.
- Log failures clearly.
- Never trust frontend payment status.

## Current PayFast Endpoint

`POST /api/v1/webhooks/payfast`

Implemented behavior:

- Rejects missing or invalid signatures with `422`.
- Stores accepted events in `webhook_events`.
- Uses `pf_payment_id` as the provider event ID, falling back to `m_payment_id` or a payload hash.
- Uses `m_payment_id` as the local invoice number.
- Uses `custom_int1` or `custom_str1` as the optional account ID scope when PayFast checkout is created.
- Marks `COMPLETE` payments as `succeeded` and updates the matching invoice to `paid`.
- Marks `FAILED` and `CANCELLED` payments as `failed` and stores the failure reason.
- Sends a customer email for successful payments.
- Sends a customer email for failed payments with the failure reason.
- Redacts `merchant_key` and `signature` before storing webhook payload data.

## Payment Email Notifications

Payment email delivery is handled by `BillingNotificationService`.

- `PaymentSucceededMail` is sent to the invoice customer after a PayFast `COMPLETE` webhook.
- `PaymentFailedMail` is sent to the invoice customer after a PayFast `FAILED` or `CANCELLED` webhook.
- Duplicate webhook events do not send duplicate emails.
- Missing customer email addresses are skipped.
- Production delivery depends on the configured Laravel mailer. Local defaults use the `log` mailer.

Required PayFast environment values:

- `PAYFAST_MERCHANT_ID`
- `PAYFAST_MERCHANT_KEY`
- `PAYFAST_PASSPHRASE`

## Portfolio Proof

The case study should explain how webhooks protect the system from fake payment states and keep billing records aligned with the provider.
