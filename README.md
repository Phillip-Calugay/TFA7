# Simple POS System - Laravel TFA6

Phillip E. Calugay - IT0049 - TC31

TFA6 replaces the TFA5 controller arrays with a real database, Laravel migrations, repeatable seed data, and Customer/User Eloquent models. The four pages retain the original layout and sample records.

## Requirements and first-time setup

PHP 8.2+ with PDO SQLite enabled and Composer. This project uses Laravel 12, as locked in composer.lock. No Node.js build is needed; CSS is in public/css/pos.css.

Run these commands in Windows Command Prompt for a fresh checkout:

```bat
cd /d C:\xampp\htdocs\TSA6
composer install
copy .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan serve
```

Open http://127.0.0.1:8000. SQLite is the default database in .env.example. The local working copy is already configured and seeded; do not overwrite its .env to start the app.

## Start the existing local project

```bat
cd /d C:\xampp\htdocs\TSA6
php artisan serve
```

If php is not recognized, use C:\xampp\php\php.exe artisan serve. Keep Command Prompt open; Ctrl+C stops the server. With XAMPP Apache running, http://localhost/TSA6/ is also supported.

## Assignment implementation and file locations

| Requirement | Location |
| --- | --- |
| customers table: id, full_name, email, phone, timestamps | database/migrations/2026_10_20_000000_create_pos_account_tables.php |
| users table: unique username, full_name, role, timestamps | database/migrations/0001_01_01_000000_create_users_table.php and 2026_10_30_000000_adapt_users_for_pos.php |
| At least five records per table | database/seeders/PosAccountSeeder.php |
| Seeder entry point | database/seeders/DatabaseSeeder.php |
| Eloquent models and fillable fields | app/Models/Customer.php and app/Models/User.php |
| Eloquent queries ordered by ID | app/Http/Controllers/CustomerController.php and UserController.php |
| Customer/user tables and escaped output | resources/views/customers/index.blade.php and resources/views/users/index.blade.php |
| Home/About and shared navigation | resources/views/pages/ and resources/views/layouts/app.blade.php |
| Page routes | routes/web.php |
| Local database file (excluded from Git) | database/database.sqlite |
| Example database settings | .env.example |
| Database and page tests | tests/Feature/PosPagesTest.php and PosAccountSeederTest.php |

The users adaptation migration renames the default name/email fields to full_name/username, preserves the unique index, removes unused authentication fields, and adds role so the TFA5 Role column remains visible. These are staff listing records; this assignment does not implement login.

The account controllers retrieve Eloquent collections. Blade reads model properties and escapes their values. Empty tables show a clear message. Sample data lives only in the seeder. Repeating php artisan db:seed updates the five samples without duplicating them. Migrations and seeders are the database submission artifacts; a separate SQL export is not required by the document.

| Page | Route |
| --- | --- |
| Home | / |
| About | /about |
| Customer Accounts | /customers |
| User Accounts | /users |

## Validation

```bat
php artisan test
php artisan route:list --except-vendor
php artisan view:cache
php artisan view:clear
```

Tests use a separate in-memory SQLite database. They verify all four pages, navigation, seeded records, database inserts/updates/deletes appearing on the pages, escaped output, empty states, unique usernames, repeatable seeding, and migration rollback/reapply.

## Hosting and submission

Repository: https://github.com/Phillip-Calugay/TFA6

Hosted URL: pending deployment to a PHP/Laravel hosting account. The local address is not a public hosted submission link.

Use a PHP 8.2+ host with PDO SQLite (or configure MySQL in the server .env). Point its document root to public/. Upload the project or deploy from Git, then run:

```sh
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

Set APP_ENV=production, APP_DEBUG=false, and APP_URL to the actual HTTPS address in the server .env. For SQLite ensure database/database.sqlite and the database directory are writable by PHP. Also permit writes to storage/ and bootstrap/cache/. Then run:

```sh
php artisan migrate --seed --force
php artisan optimize
```

Keep .env, the local SQLite database, vendor/, and secrets out of Git. If using MySQL, create the database first and set DB_CONNECTION=mysql plus DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, and DB_PASSWORD. Check /, /about, /customers, /users and the CSS on the public host, then submit both the repository and actual hosted URL.