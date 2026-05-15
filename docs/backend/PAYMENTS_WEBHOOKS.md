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

- Verify provider signature.
- Store webhook event before processing.
- Ignore duplicate provider event IDs.
- Process subscription and invoice changes inside database transactions where needed.
- Log failures clearly.
- Never trust frontend payment status.

## Portfolio Proof

The case study should explain how webhooks protect the system from fake payment states and keep billing records aligned with the provider.
