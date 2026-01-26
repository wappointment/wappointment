# Wappointment API Testing

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
