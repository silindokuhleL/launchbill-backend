# LaunchBill Backend Project Checklist

## Planning

- [ ] Confirm Laravel version.
- [ ] Confirm PayFast payment flow.
- [ ] Confirm tenant strategy.
- [ ] Confirm AI feature endpoints.
- [ ] Confirm deployment target.

## Foundation

- [ ] Scaffold Laravel REST API.
- [ ] Configure MySQL.
- [ ] Configure Redis.
- [ ] Configure queues.
- [ ] Configure mail.
- [ ] Configure PHPUnit.
- [ ] Configure request logging.
- [ ] Configure audit logging.

## Auth, Tenants, And RBAC

- [ ] Add authentication.
- [ ] Add Spatie Permission.
- [ ] Add tenant stubs.
- [ ] Add global super admin role.
- [ ] Add tenant owner role.
- [ ] Add account/team relationships.
- [ ] Add seeders for tenants, users, roles, and dummy data.
- [ ] Add tenant isolation tests.

## Billing

- [ ] Add plans.
- [ ] Add customers.
- [ ] Add subscriptions.
- [ ] Add invoices.
- [ ] Add payments.
- [ ] Add PayFast webhook endpoint.
- [ ] Add webhook event storage.
- [ ] Add duplicate webhook protection.
- [ ] Add email notifications.

## Architecture

- [ ] Add services for core business logic.
- [ ] Add API Resources for frontend responses.
- [ ] Add form requests for validation.
- [ ] Add policies for authorization.
- [ ] Add jobs for queued work.
- [ ] Add traits for reusable model behavior where useful.
- [ ] Remove or replace `ExampleController`.

## AI

- [ ] Add billing summary assistant endpoint.
- [ ] Add payment failure explanation endpoint.
- [ ] Add admin activity insight endpoint.
- [ ] Add AI audit logging.
- [ ] Add AI permission tests.

## Quality

- [ ] PHPUnit auth tests.
- [ ] PHPUnit RBAC tests.
- [ ] PHPUnit tenant tests.
- [ ] PHPUnit billing tests.
- [ ] PHPUnit webhook tests.
- [ ] PHPUnit service tests.
- [ ] API docs updated.
- [ ] README updated.

