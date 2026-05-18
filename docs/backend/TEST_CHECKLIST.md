# LaunchBill Backend Test Checklist

## Auth

- [ ] User can register.
- [ ] User can login.
- [ ] User can logout.
- [ ] Protected endpoints reject unauthenticated requests.
- [ ] Request logging does not expose passwords or tokens.

## RBAC

- [ ] Owner can manage billing resources.
- [ ] Viewer cannot create or update billing resources.
- [ ] Account scoping prevents cross-account access.
- [ ] Super admin can access global admin functions without belonging to a tenant.
- [ ] Tenant roles cannot access another tenant.

## Plans

- [x] Plan can be listed.
- [x] Plan can be created.
- [x] Plan can be updated.
- [x] Plan can be archived.
- [x] Plan slug is unique per account.
- [x] Plan routes are scoped to the active account.
- [x] Users without `plans.manage` cannot manage plans.
- [ ] Inactive plan cannot be used for new subscription.
- [x] Plan API Resource returns the expected frontend shape.

## Customers

- [ ] Customer can be created.
- [ ] Customer can be updated.
- [ ] Customer list is scoped to account.

## Subscriptions

- [ ] Subscription can be created.
- [ ] Subscription can be cancelled.
- [ ] Subscription status changes correctly after webhook.

## Payments And Webhooks

- [ ] Webhook signature is required.
- [ ] Duplicate webhook event is ignored.
- [ ] Paid invoice updates invoice and payment records.
- [ ] Failed payment stores failure reason.
- [ ] PayFast webhook payload is verified before processing.

## Dashboard

- [ ] Dashboard summary returns scoped account totals.
- [ ] Revenue totals exclude failed payments.
- [ ] Subscription counts reflect current statuses.

## Services And Seeders

- [x] Controllers delegate core behavior to services.
- [ ] Core services have direct unit coverage.
- [x] Every core feature has a seeder or factory-backed demo data path.
- [x] Tenant seeders create tenants, owners, roles, and dummy billing data.
- [x] `ExampleController` is removed or replaced.
