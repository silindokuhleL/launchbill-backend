# LaunchBill Queues And Jobs

## Queue Goals

Use background jobs for tasks that should not slow down API responses.

## Suggested Jobs

- Send welcome email.
- Send invoice paid email. Current foundation sends this inline from webhook processing; move to a queued job when production mail delivery is configured.
- Send payment failed email. Current foundation sends this inline from webhook processing; move to a queued job when production mail delivery is configured.
- Sync payment provider subscription state.
- Generate billing report.
- Process webhook event.
- Write audit log if not handled inline.

## Queue Rules

- Jobs should be idempotent where payment state is involved.
- Webhook processing jobs should safely handle duplicate events.
- Failed jobs should have useful error messages.
- Queue behavior should be documented in deployment notes.

## Portfolio Proof

Show that the project understands asynchronous work, not just request-response CRUD.
