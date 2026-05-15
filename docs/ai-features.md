# LaunchBill Backend AI Features

LaunchBill should be an AI-assisted billing platform, not just a billing CRUD app.

## Planned AI Features

### Billing Summary Assistant

Summarizes account billing health:

- Active subscriptions.
- Failed payments.
- Recent invoices.
- Revenue movement.
- Suggested follow-up actions.

Backend responsibilities:

- Scope data to the authenticated account.
- Redact sensitive billing details.
- Log AI summary generation.
- Return summary text through a protected API endpoint.

### Payment Failure Explanation Assistant

Helps admins understand failed payment patterns and draft customer follow-up messages.

Backend responsibilities:

- Read failed payment and invoice records.
- Keep payment provider secrets out of prompts.
- Queue expensive AI work where needed.
- Store generated drafts only if the user saves them.

### Admin Activity Insight Assistant

Summarizes important account, tenant, and admin activity for super admins.

Backend responsibilities:

- Enforce super admin permissions.
- Use audit logs and request logs safely.
- Avoid exposing one tenant's data to another tenant.

## Safety Requirements

- AI must never decide whether a payment succeeded.
- AI must never create, cancel, or resume subscriptions without normal backend authorization.
- AI must never bypass tenant scoping.
- AI prompts must exclude secrets, passwords, tokens, and sensitive payment payloads.

