<p align="center">
  <img src="public/favicon.svg" alt="ChamaPro" width="72" height="72" />
</p>

<h1 align="center">ChamaPro</h1>

<p align="center">
  <strong>Modern chama management for treasurers, secretaries, and members.</strong><br>
  Contributions, loans, meetings, ledger accounting, SMS, and M-Pesa — in one secure portal.
</p>

<p align="center">
  <a href="#features">Features</a> ·
  <a href="#tech-stack">Tech stack</a> ·
  <a href="#getting-started">Getting started</a> ·
  <a href="#testing">Testing</a> ·
  <a href="DEPLOYMENT.md">Deployment</a>
</p>

---

## Overview

**ChamaPro** is a multi-tenant Laravel application for managing Kenyan *chamas* (savings groups). Each group operates in an isolated workspace with role-based access for chairpersons, treasurers, secretaries, and members.

The portal covers the full financial lifecycle: recording contributions and shares, issuing and repaying loans, running meetings, posting to a double-entry ledger, sending templated SMS reminders, and exporting reports.

## Features

| Area | Capabilities |
|------|--------------|
| **Members** | Onboarding, statuses, guarantors, activity history |
| **Contributions** | Types, frequencies, individual & bulk entry, eligibility rules |
| **Loans** | Products, applications, disbursements, guarantors, repayments |
| **Banking & ledger** | Bank/cash accounts, journal entries, chart of accounts |
| **Meetings** | Scheduling, attendance, expenses, net cash-in summaries |
| **Fines & welfare** | Fine types, welfare contributions and disbursements |
| **Shares & dividends** | Share purchases, dividend runs and allocations |
| **SMS** | Templates with placeholders, multi-provider gateways, send history |
| **M-Pesa** | STK push integration (Safaricom Daraja) |
| **Reports** | PDF and Excel exports |
| **Admin** | Super-admin panel for groups, subscriptions, and SMS providers |

### SMS placeholders

Templates support dynamic fields such as `{name}`, `{group_name}`, `{contributions_due}`, `{loan_balance}`, `{unpaid_fines}`, and more. A dummy log driver writes outbound messages to `storage/logs/sms.log` for local development.

## Tech stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.4, Laravel 13 |
| Frontend | Inertia.js v3, React 19 |
| Styling | Bootstrap 5, SCSS design tokens |
| Auth | Laravel Fortify (portal + admin) |
| Permissions | Spatie Laravel Permission (per group) |
| Routes | Laravel Wayfinder (typed TS route helpers) |
| Testing | Pest 4 |
| Exports | DomPDF, Maatwebsite Excel |

## Requirements

- PHP 8.4+ with `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`
- Composer 2.x
- Node.js 20+
- MySQL 8.0+ (or SQLite for local development)

## Getting started

### 1. Clone and install

```bash
git clone <repository-url> chamapro
cd chamapro

composer install
npm install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database connection in `.env`, then migrate and seed:

```bash
php artisan migrate
php artisan db:seed
```

Or use the Composer setup script:

```bash
composer run setup
```

### 3. Run the dev server

```bash
composer run dev
```

This starts the PHP server, queue worker, log tail, and Vite dev server together.

Alternatively, run them separately:

```bash
php artisan serve
npm run dev
```

Visit the landing page at `/` or sign in at `/portal/login`.

### Demo credentials

After seeding with `DemoGroupSeeder`:

| Role | Email | Password |
|------|-------|----------|
| Demo group owner | `demo@chamapro.com` | `password` |
| Chairperson | `chair@demo.com` | `password` |
| Super admin | `admin@chamapro.com` | `password` |

Super admin panel: `/admin/login`

### Useful Artisan commands

```bash
php artisan wayfinder:generate --with-form   # Regenerate typed frontend routes
php artisan db:seed --class=RolesAndPermissionsSeeder --force  # Sync permissions to all groups
vendor/bin/pint --dirty                      # Format changed PHP files
```

## Project structure

```
app/
├── Features/          # Domain modules (Contributions, Loans, Sms, …)
│   ├── */Controllers/
│   ├── */Models/
│   ├── */Services/
│   └── */Requests/
├── Http/              # Shared middleware & controllers
├── Models/            # Core User model
├── Policies/          # Authorization policies
└── Support/           # Group context, helpers

resources/js/
├── pages/portal/      # Chama portal (Inertia + React)
├── pages/admin/       # Super-admin panel
└── layouts/           # Portal, admin, and auth layouts
```

## Permissions

Permissions are scoped per `group_id` via Spatie. Key SMS permissions:

- `sms.view` — view templates and message history
- `sms.send` — send messages to members
- `sms.manage` — create and edit templates

Run `RolesAndPermissionsSeeder` after adding new permissions to sync them across existing groups.

## Testing

```bash
php artisan test --compact
```

Run a specific file or filter:

```bash
php artisan test --compact tests/Feature/SmsMessagingTest.php
php artisan test --compact --filter=contribution
```

## Deployment

See **[DEPLOYMENT.md](DEPLOYMENT.md)** for production server requirements, environment variables, Nginx configuration, queue workers, and optimization steps.

## License

MIT
