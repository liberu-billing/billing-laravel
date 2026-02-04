# Liberu Billing

[Managed web hosting and billing for Laravel applications — liberu.co.uk](https://www.liberu.co.uk)

[![Install](https://github.com/liberu-billing/billing-laravel/actions/workflows/install.yml/badge.svg)](https://github.com/liberu-billing/billing-laravel/actions/workflows/install.yml) [![Tests](https://github.com/liberu-billing/billing-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/liberu-billing/billing-laravel/actions/workflows/tests.yml) [![Docker](https://github.com/liberu-billing/billing-laravel/actions/workflows/main.yml/badge.svg)](https://github.com/liberu-billing/billing-laravel/actions/workflows/main.yml) [![Codecov](https://codecov.io/gh/liberu-billing/billing-laravel/branch/main/graph/badge.svg)](https://codecov.io/gh/liberu-billing/billing-laravel)

![](https://img.shields.io/badge/PHP-8.4-informational?style=flat&logo=php&color=4f5b93) ![](https://img.shields.io/badge/Laravel-12-informational?style=flat&logo=laravel&color=ef3b2d) ![](https://img.shields.io/badge/Filament-4.0-informational?style=flat&color=fdae4b) ![](https://img.shields.io/badge/Jetstream-5-purple.svg) ![](https://img.shields.io/badge/Livewire-3.5-informational?style=flat&color=fb70a9) ![](https://img.shields.io/badge/JavaScript-ECMA2020-informational?style=flat&color=F7DF1E) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A modular, production-ready billing and invoicing application built on Laravel and Filament. It provides extensible billing features, secure authentication, and an admin panel for managing customers, invoices, and payments.

Key features

- Secure authentication (Jetstream)
- Modular architecture for extensions and integrations
- Admin UI with Filament
- Invoice generation, reminders and payment handling
- Seedable demo data and automated tests

Quick start

1. Copy environment file and configure your database and mail settings:

```powershell
copy .env.example .env
```

2. Install PHP dependencies, generate app key and run migrations:

```powershell
composer install --no-scripts
php artisan key:generate
php artisan migrate --seed
```

3. Frontend (optional):

```powershell
npm install
npm run build
```

4. Run locally (built-in server):

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Notes

- The included `setup.sh` automates these steps on Unix-like systems; on Windows run the commands above.
- Review `.env.example` before overwriting an existing `.env` file.

Using Docker / Sail

- Build the Docker image:

```powershell
docker build -t billing-laravel .
```

- Run the container:

```powershell
docker run -p 8000:8000 billing-laravel
```

- Or use Laravel Sail:

```powershell
./vendor/bin/sail up
```

Related projects

| Project | Description |
|---|---|
| [accounting-laravel](https://github.com/liberu-accounting/accounting-laravel) | Accounting tools compatible with Liberu apps |
| [automation-laravel](https://github.com/liberu-automation/automation-laravel) | Automation workflows and background jobs |
| [liberu-billing/billing-laravel](https://github.com/liberu-billing/billing-laravel) | This repository — billing and invoicing for Laravel |
| [boilerplate](https://github.com/liberusoftware/boilerplate) | Base Laravel starter used across projects |
| [browser-game-laravel](https://github.com/liberu-browser-game/browser-game-laravel) | Example game project built on Laravel |
| [cms-laravel](https://github.com/liberu-cms/cms-laravel) | Content management system |
| [control-panel-laravel](https://github.com/liberu-control-panel/control-panel-laravel) | Admin/control panel components |
| [crm-laravel](https://github.com/liberu-crm/crm-laravel) | CRM features and integrations |
| [ecommerce-laravel](https://github.com/liberu-ecommerce/ecommerce-laravel) | E‑commerce storefront and checkout |
| [genealogy-laravel](https://github.com/liberu-genealogy/genealogy-laravel) | Family-tree and genealogy tools |
| [maintenance-laravel](https://github.com/liberu-maintenance/maintenance-laravel) | Maintenance and status utilities |
| [real-estate-laravel](https://github.com/liberu-real-estate/real-estate-laravel) | Real-estate listings and management |
| [social-network-laravel](https://github.com/liberu-social-network/social-network-laravel) | Social network features and examples |

Contributing

Contributions are welcome. Please open issues for discussion or submit pull requests with tests and clear descriptions of changes.

License

This project is licensed under the MIT License — see the LICENSE file for details.

Contributors

<a href="https://github.com/liberu-billing/billing-laravel/graphs/contributors"><img src="https://contrib.rocks/image?repo=liberu-billing/billing-laravel" alt="Contributors"/></a>
