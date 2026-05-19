# LaunchBill Backend Project Checklist

## Planning

- [ ] Confirm Laravel version.
- [ ] Confirm PayFast payment flow.
- [ ] Confirm tenant strategy.
- [ ] Confirm AI feature endpoints.
- [ ] Confirm deployment target.

## Foundation

- [x] Scaffold Laravel REST API.
- [x] Configure MySQL environment defaults.
- [x] Configure Redis package support and environment defaults.
- [x] Configure queue foundation.
- [x] Configure mail defaults.
- [x] Configure PHPUnit.
- [x] Configure request logging.
- [x] Configure audit logging.

## Auth, Tenants, And RBAC

- [x] Add authentication.
- [x] Add Spatie Permission.
- [x] Add tenant stubs.
- [x] Add global super admin role.
- [x] Add tenant owner role.
- [x] Add account/team relationships.
- [x] Add seeders for tenants, users, roles, and dummy data.
- [x] Add tenant isolation tests.

## Billing

- [x] Add plans.
- [x] Add customers.
- [x] Add subscriptions.
- [x] Add invoices.
- [x] Add payments.
- [x] Add PayFast webhook endpoint.
- [x] Add webhook event storage.
- [x] Add duplicate webhook protection.
- [ ] Add email notifications.

## Architecture

- [x] Add services for core business logic.
- [x] Add API Resources for frontend responses.
- [x] Add form requests for validation.
- [x] Add policies for authorization.
- [ ] Add jobs for queued work.
- [x] Add traits for reusable model behavior where useful.
- [x] Remove or replace `ExampleController`.

## AI

- [ ] Add billing summary assistant endpoint.
- [ ] Add payment failure explanation endpoint.
- [ ] Add admin activity insight endpoint.
- [ ] Add AI audit logging.
- [ ] Add AI permission tests.

## Quality

- [x] PHPUnit auth tests.
- [x] PHPUnit RBAC tests.
- [x] PHPUnit tenant tests.
- [x] PHPUnit billing tests.
- [x] PHPUnit webhook tests.
- [ ] PHPUnit service tests.
- [x] API docs updated.
- [ ] README updated.
