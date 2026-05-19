# LaunchBill Queues And Jobs

## Queue Goals

Use background jobs for tasks that should not slow down API responses.

## Suggested Jobs

- Send welcome email.
- Send invoice paid email. Implemented as `SendPaymentSucceededEmail`.
- Send payment failed email. Implemented as `SendPaymentFailedEmail`.
- Sync payment provider subscription state.
- Generate billing report.
- Process webhook event.
- Write audit log if not handled inline.

## Implemented Jobs

- `SendPaymentSucceededEmail` sends `PaymentSucceededMail` to the invoice customer.
- `SendPaymentFailedEmail` sends `PaymentFailedMail` to the invoice customer.
- `BillingNotificationService` dispatches these jobs from PayFast webhook processing.
- Missing customer email addresses are skipped inside the job.

## Queue Rules

- Jobs should be idempotent where payment state is involved.
- Webhook processing jobs should safely handle duplicate events.
- Failed jobs should have useful error messages.
- Queue behavior should be documented in deployment notes.

## Portfolio Proof

Show that the project understands asynchronous work, not just request-response CRUD.
