# Wappointment API Testing

## v3 booking tests

Run the focused slot-rule and React-widget suites from the plugin root:

```bash
php vendor/bin/pest --do-not-cache-result tests/Unit
npx --no-install jest --config jest.widget.config.js --runInBand
npm run build
```

Use PHP 8.2-8.4 with the currently locked Pest dependencies. Install the existing
Composer/npm lockfiles first if dependencies are not already restored.

The booking integration suite uses a **disposable** database called `booking_test`
and refuses to run unless WordPress identifies the environment as `local`. It deletes
the booking fixture rows between tests: never point it at a real site.
The following isolated Compose project has no fixed container names and does not
touch the existing client-test stack. Its database lives in tmpfs.

```bash
docker compose -p wappointment-booking-tests -f tests/docker/booking.compose.yml up -d --build --wait
docker compose -p wappointment-booking-tests -f tests/docker/booking.compose.yml exec wordpress \
  wp core install --url=http://127.0.0.1:18089 --title='Booking tests' \
  --admin_user=booking-admin --admin_password=disposable-booking-test \
  --admin_email=admin@example.invalid --skip-email --allow-root
docker compose -p wappointment-booking-tests -f tests/docker/booking.compose.yml exec wordpress \
  wp plugin activate wappointment --allow-root
docker compose -p wappointment-booking-tests -f tests/docker/booking.compose.yml exec \
  -e WAPPOINTMENT_WP_ROOT=/var/www/html wordpress \
  php /var/www/html/wp-content/plugins/wappointment/vendor/bin/pest \
  --do-not-cache-result --cache-directory=/tmp/wappo-pest \
  --configuration=/var/www/html/wp-content/plugins/wappointment/phpunit.xml \
  /var/www/html/wp-content/plugins/wappointment/tests/Integration/BookingTest.php
docker compose -p wappointment-booking-tests -f tests/docker/booking.compose.yml down -v
```

The suite covers public versus administrator permissions, UTC persistence and buffers,
forged inputs, duplicate/concurrent bookings, rescheduling, cancellation deadlines,
manual approval, active-booking limits, customer-data preservation, idempotent schema
bootstrap, and non-destructive refusal of multi-service setup edits.
Change `WAPPOINTMENT_BOOKING_PORT` and the installation URL together if port 18089
is already in use.

The unit suite also enforces one injected UseCase per invokable controller,
one controller/UseCase and distinct RequestData/ResponseData pair per API operation,
matching controller/UseCase signatures, `execute()` on UseCases, and `handle()`
on Actions. Controllers and UseCases remain independent of WordPress HTTP classes.
The contract suite tests required/unknown fields, nested arrays, optional versus null,
numeric-string normalization, dates/emails, response failures and JSON object/list
serialization. The widget suite also verifies exact booking/manage/availability bodies
and changed-fields-only settings submissions.
The integration suite resolves the complete dependency graph for every controller
and exercises the existing client CRUD and settings endpoints over authenticated HTTP,
checking their original success statuses/envelopes and the unified error envelope.
It also tests URL/body identifier conflicts, unknown query/body fields, JSON shape,
default settings compatibility, retained extension settings, and invalid Action output
becoming a server error rather than a success or client error. That last test deliberately
logs a response-contract violation. The jobs response test creates a legacy jobs-table
fixture because background-job installation is not part of the booking baseline yet.

## Setup

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```

2. **Start test environment and create database snapshot:**
   ```bash
   npm run test:setup
   ```
   
   This will:
   - Start Docker containers (WordPress + MySQL)
   - Install WordPress
   - Activate the plugin
   - Create a database snapshot for tests

## Running Tests

**Run all API tests:**
```bash
npm run test:api
```

**Run specific test groups:**
```bash
docker compose -f docker-compose.test.yml exec test-runner vendor/bin/pest --group=clients
docker compose -f docker-compose.test.yml exec test-runner vendor/bin/pest --group=settings
```

**Update snapshots:**
```bash
npm run test:api:snapshots -- -u
```

**Run specific test file:**
```bash
docker compose -f docker-compose.test.yml exec test-runner vendor/bin/pest tests/Api/ClientsTest.php
```

## Test Structure

```
tests/
├── Pest.php                    # Pest configuration & helpers
├── Api/
│   ├── ClientsTest.php        # Client API endpoint tests
│   └── SettingsTest.php       # Settings API endpoint tests
├── docker/
│   ├── create-snapshot.sh     # Database snapshot generator
│   ├── db-snapshot.sql        # WordPress database snapshot
│   └── php.ini                # PHP configuration for tests
└── .pest/
    └── snapshots/             # Response snapshots (auto-generated)
```

## Test Helpers

### `apiRequest(endpoint, method, data)`
Makes authenticated API requests to WordPress REST API.

```php
$response = apiRequest('/clients', 'POST', ['name' => 'Test', 'email' => 'test@example.com']);
```

### Custom Expectations

**`toBeSuccessfulApiResponse()`**
```php
expect($response)->toBeSuccessfulApiResponse();
```

**`toBeApiError(status)`**
```php
expect($response)->toBeApiError(404);
```

**`toMatchSnapshot()`**
```php
expect($response['body'])->toMatchSnapshot();
```

## Docker Services

- **wordpress** - WordPress 6.4 with PHP 8.2 (http://localhost:8080)
- **mysql** - MySQL 8.0 database
- **test-runner** - PHP CLI container for running Pest tests

## Useful Commands

**View logs:**
```bash
docker compose -f docker-compose.test.yml logs -f wordpress
```

**Access WordPress CLI:**
```bash
docker compose -f docker-compose.test.yml exec wordpress wp --allow-root
```

**Recreate database snapshot:**
```bash
tests/docker/create-snapshot.sh
```

**Clean up:**
```bash
npm run test:stop
docker volume rm wappointment-plugin_mysql_data wappointment-plugin_wordpress_data
```
