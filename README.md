# Liberu Billing

### Open-source billing, invoicing and subscription management — built with Laravel 12, Filament 5 and Livewire 4

[![Install](https://github.com/liberu-billing/billing-laravel/actions/workflows/install.yml/badge.svg)](https://github.com/liberu-billing/billing-laravel/actions/workflows/install.yml) [![Tests](https://github.com/liberu-billing/billing-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/liberu-billing/billing-laravel/actions/workflows/tests.yml) [![Docker](https://github.com/liberu-billing/billing-laravel/actions/workflows/main.yml/badge.svg)](https://github.com/liberu-billing/billing-laravel/actions/workflows/main.yml) [![Codecov](https://codecov.io/gh/liberu-billing/billing-laravel/branch/main/graph/badge.svg)](https://codecov.io/gh/liberu-billing/billing-laravel) [![GitHub release](https://img.shields.io/github/release/liberu-billing/billing-laravel.svg)](https://github.com/liberu-billing/billing-laravel/releases) [![Open Source Love](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)](https://github.com/ellerbrock/open-source-badges/)

![](https://img.shields.io/badge/PHP-8.5-informational?style=flat&logo=php&color=4f5b93) ![](https://img.shields.io/badge/Laravel-12-informational?style=flat&logo=laravel&color=ef3b2d) ![](https://img.shields.io/badge/Filament-5-informational?style=flat&color=fdae4b) ![](https://img.shields.io/badge/Livewire-4-informational?style=flat&color=fb70a9) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Liberu Billing** is a modular, production-ready billing and invoicing platform for web hosting providers, SaaS businesses and digital agencies. Built on the latest Laravel, Filament and Livewire stack, it covers the full customer lifecycle — from onboarding and subscription management through to automated invoicing, payment collection and service provisioning. Whether you are running a small agency or a large hosting operation, Liberu Billing gives you a solid, extensible foundation that is easy to customise and maintain.

## ✨ Main Features

| Feature | Description |
|---|---|
| **Secure Authentication** | Powered by Laravel Jetstream with two-factor authentication, API tokens and team management |
| **Customer Management** | Full client portal with profile, contact management and service overview |
| **Invoice Generation** | Automated invoice creation with customisable templates, PDF export and email delivery |
| **Payment Collection** | Integrated payment gateway support with automatic reminders and late-fee rules |
| **Subscription Management** | Recurring billing cycles, upgrades, downgrades and pro-rata calculations |
| **Service Provisioning** | Integration with cPanel, Plesk and other control panels for automated account setup |
| **Webhooks & Automation** | Real-time event notifications (19+ event types) with HMAC-SHA256 signature verification and retry logic |
| **Knowledge Base** | Hierarchical self-service help centre with full-text search and article feedback |
| **Canned Responses** | Quick-reply templates with variable substitution for support tickets |
| **Bulk Operations** | Mass invoice generation, email campaigns, and data import/export |
| **Service Automation** | Auto-suspension for overdue accounts and full service lifecycle management |
| **Admin Panel** | Beautiful Filament 5 admin UI with real-time Livewire components |
| **Modular Architecture** | Plugin-style module system making it easy to add integrations and extend functionality |
| **Demo Data & Tests** | Seedable demo dataset and a comprehensive automated test suite |

## 🚀 Installation

### Option 1 — Automated installer (recommended)

Run the interactive shell installer, which guides you through environment setup, dependencies, migrations and seeding:

```bash
bash install.sh
```

On Linux or macOS you can also launch the **graphical installer** (if available on your system) by running the script through a file manager or a desktop shortcut that executes `bash install.sh` in a terminal.

### Option 2 — Manual setup

1. **Copy the environment file** and configure your database, mail and app settings:

```bash
cp .env.example .env
# Edit .env with your database credentials and mail settings
```

2. **Install PHP dependencies**, generate the application key and run migrations:

```bash
composer install --no-scripts
php artisan key:generate
php artisan migrate --seed
```

3. **Install and build frontend assets** (optional for admin-only use):

```bash
npm install
npm run build
```

4. **Start the development server**:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Option 3 — Docker / Laravel Sail

Build and run with Docker:

```bash
docker build -t billing-laravel .
docker run -p 8000:8000 billing-laravel
```

Or use Laravel Sail for a full local environment:

```bash
./vendor/bin/sail up
```

> **Tip:** Review `.env.example` carefully before overwriting an existing `.env` file. The `install.sh` script handles this interactively.

## 📖 Documentation

- [WHMCS Features Documentation](docs/WHMCS_FEATURES.md) — Webhooks, Knowledge Base, Canned Responses, Bulk Operations
- [Modular Architecture](docs/MODULAR_ARCHITECTURE.md) — Module system and how to write extensions
- [Control Panel Provisioning](docs/CONTROL_PANEL_PROVISIONING.md) — cPanel/Plesk integration guide

## 🔗 Related Projects

| Project | Description |
|---|---|
| [accounting-laravel](https://github.com/liberu-accounting/accounting-laravel) | Accounting tools compatible with Liberu apps |
| [automation-laravel](https://github.com/liberu-automation/automation-laravel) | Automation workflows and background jobs |
| [billing-laravel](https://github.com/liberu-billing/billing-laravel) | This repository — billing and invoicing for Laravel |
| [boilerplate](https://github.com/liberusoftware/boilerplate) | Base Laravel starter used across Liberu projects |
| [browser-game-laravel](https://github.com/liberu-browser-game/browser-game-laravel) | Example browser game built on Laravel |
| [cms-laravel](https://github.com/liberu-cms/cms-laravel) | Content management system |
| [control-panel-laravel](https://github.com/liberu-control-panel/control-panel-laravel) | Hosting control panel components |
| [crm-laravel](https://github.com/liberu-crm/crm-laravel) | CRM features and customer relationship tools |
| [ecommerce-laravel](https://github.com/liberu-ecommerce/ecommerce-laravel) | E-commerce storefront and checkout |
| [genealogy-laravel](https://github.com/liberu-genealogy/genealogy-laravel) | Family-tree and genealogy tools |
| [maintenance-laravel](https://github.com/liberu-maintenance/maintenance-laravel) | Maintenance scheduling and status utilities |
| [real-estate-laravel](https://github.com/liberu-real-estate/real-estate-laravel) | Real-estate listings and property management |
| [social-network-laravel](https://github.com/liberu-social-network/social-network-laravel) | Social network features and activity feeds |

## 🤝 Contributing

Contributions are very welcome! Here is how to get involved:

1. **Fork** the repository and create your branch from `main`.
2. **Make your changes** — please include tests for new functionality and ensure existing tests still pass (`./vendor/bin/phpunit`).
3. **Follow the coding style** — run `./vendor/bin/pint` to auto-format PHP code before committing.
4. **Open a Pull Request** with a clear title and description explaining *what* changed and *why*.
5. A maintainer will review your PR and may request changes or ask questions before merging.

Please open an issue first for larger changes so we can discuss the approach before you invest time writing code.

📱 **WhatsApp**: [+44 1793 200950](https://wa.me/441793200950) — feel free to reach out with questions or ideas.

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for the full text.

**What the MIT license means for you:**

- ✅ **Free to use** — use Liberu Billing in personal, commercial or enterprise projects at no cost.
- ✅ **Free to modify** — adapt the source code to suit your exact requirements.
- ✅ **Free to distribute** — share or resell your modified version, provided the original copyright notice is retained.
- ✅ **No warranty** — the software is provided "as is"; the authors are not liable for any issues arising from its use.

The MIT license is one of the most permissive open-source licenses available. It maximises freedom for users and contributors while protecting authors from liability, making it ideal for projects that want broad adoption and a healthy contributor ecosystem.

## 👥 Contributors

<a href="https://github.com/liberu-billing/billing-laravel/graphs/contributors"><img src="https://contrib.rocks/image?repo=liberu-billing/billing-laravel" alt="Contributors"/></a>
