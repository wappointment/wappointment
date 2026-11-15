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
manual approval, active-booking limits, customer-data preservation, own-calendar busy/free
exception CRUD and editing effects on live slots, idempotent schema bootstrap, and
non-destructive refusal of multi-service setup edits. Rescheduling checks include disabled
and late actions, collision with a different appointment, and retention of the original
buffer and sequence; the widget suite opens the emailed `#wappointment=<key>` and legacy
`?appointmentkey=<key>&view=reschedule-event` links, verifies unique widget-instance labels,
checks the generated ICS start/end, location, and UID, exercises first-available search,
browser timezone persistence and popup keyboard focus management. The PHP integration suite
also verifies native widget sanitization, legacy sidebar widget registration and rendering
without rewriting stored instances, customer timezone persistence, and time-gated
separate cancel/reschedule/calendar links in customer history. Customer history also
tests the customizable duration column and excludes the reserved buffer from its value.
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
Admin API URL tests cover pagination/search with both pretty permalinks and
plain `?rest_route=...` URLs so query parameters never become part of the route.
WordPress integration checks also verify that booking submenus register after their
parent menu and resolve to admin page hooks, not frontend paths.
Data-specific tests cover promoted readonly properties, native named construction,
inherited validation attributes, mapped wire names, nested Data/collection hydration,
scalar unions, generated schemas, and `Optional::Missing` versus explicit null.
The architecture suite rejects hand-authored endpoint schemas and generic payload
accessors. Request factories perform validation; direct native constructors enforce
PHP types, and response validation also runs before HTTP serialization.
The integration suite resolves the complete dependency graph for every controller
and exercises the existing client CRUD and settings endpoints over authenticated HTTP,
checking their original success statuses/envelopes and the unified error envelope.
The W3 appointment integration checks cover bounded range/event filtering, appointment
lock and deletion permissions, manager force-delete, order-item unlinking, admin
rescheduling, and on-behalf booking with both anonymous denial and conflict preservation.
The focused admin calendar UI suite can be run with:

```bash
npx --no-install jest --config jest.config.js --runInBand resources/js-react/__tests__/appointments.integration.test.jsx
```

It verifies typed week events, authorized lock/edit controls, timezone-aware rescheduling
fields, client selection, and the on-behalf booking request/confirmation flow. The UI
test does not certify external-calendar events, multi-calendar timezone rendering,
optimistic drag/drop rollback, or the still-unimplemented history/order screens.
The customer widget suite also covers the accessible seven-day date chooser, organizer
timezone labeling, site-configured first-weekday ordering, locale-aware RTL rendering,
privacy-policy links, legacy `new-event` booking links, week navigation, and invalidating
stale availability after a date change. WordPress integration tests also check that the
enabled legacy privacy notice and its custom link render with unsafe HTML and URLs removed;
the legacy notice is informational and does not request consent.
It also tests URL/body identifier conflicts, unknown query/body fields, JSON shape,
default settings compatibility, retained extension settings, and invalid Action output
becoming a server error rather than a success or client error. That last test deliberately
logs a response-contract violation. The jobs response test retains its legacy-shaped
fixture; fresh installation now includes that table, but queue processing is not
implemented by the installer.

## Legacy availability runtime characterization

The committed golden files under [Characterization](Characterization/) come from
audited master `fcf2cfe296a240421ed91f28ff3672ece04e4786`, not hand-authored expected
slot values. The ordinary PHP unit suite consumes them without legacy dependencies.
It checks the input hashes and compares all returned slot starts and duration/buffer
output fields across 15 weekly fixtures and four slot configurations (60 comparisons).

To reproduce them, use a **separate** copy of that master revision with its own
locked Composer dependencies restored. Never check out master over the v3 worktree
or install its runtime dependencies into v3. Set the path explicitly:

```bash
export WAPPOINTMENT_LEGACY_ROOT=/absolute/path/to/isolated-master
php8.3 tests/Characterization/capture-legacy-weekly.php > /tmp/wappo-legacy-weekly.json
cmp tests/Characterization/legacy-weekly.json /tmp/wappo-legacy-weekly.json
node tests/Characterization/capture-legacy-slots.cjs > /tmp/wappo-legacy-slots.json
cmp tests/Characterization/legacy-slots.json /tmp/wappo-legacy-slots.json
```

The PHP runner verifies the legacy Availability/Segment source hashes, bypasses
only the database-backed constructor using reflection, supplies synthetic settings,
freezes Carbon's clock, and executes the original weekly generation and interval
union/subtraction methods. The Node runner verifies the original DaySlots source
hash and executes its actual slot conversion/increment methods over the committed
PHP interval output. No WordPress site, external service, Vue mount or production
database is involved. Both commands must reproduce the golden files byte-for-byte.
The legacy libraries remain outside v3 and are not release dependencies.

The golden files also characterize notice rounding and horizons:

```bash
php8.3 tests/Characterization/capture-legacy-notice-horizon.php > /tmp/wappo-legacy-notice.json
cmp tests/Characterization/legacy-notice-horizon.json /tmp/wappo-legacy-notice.json
WAPPOINTMENT_NOTICE_WINDOWS=/tmp/wappo-legacy-notice.json \
  node tests/Characterization/capture-legacy-notice-slots.cjs > /tmp/wappo-legacy-notice-slots.json
cmp tests/Characterization/legacy-notice-slots.json /tmp/wappo-legacy-notice-slots.json
```

The PHP runner also verifies the master Settings source hash and executes its
unmodified `daysRegenerating` and `generateAvailabilityWithRA` methods against a
frozen clock. The Node runner source-hash-pins the original `DatesExtended`,
`Calendar`, `IncrementTime` and `DaySlots` components; a small UTC Moment-compatible
test shim supplies only clock/timezone arithmetic. It executes the original
minimum-start and slot-generation methods, not a rewritten expected-value formula.

Seventeen cases cover fractional/minute-rounded minimum notice, no-hours and fluid
availability, windows ending before notice, overnight notice, normal horizon
inclusion/exclusion, the exact daily-refresh hour, and a Paris DST calendar-day
horizon, plus explicit rejection of past dates. Five duration/buffer/custom-spacing
configurations produce 85 direct comparisons to original frontend slot starts. The
PHP oracle records availability
windows and effective refresh-day counts separately.

Covered: unmarked/explicit hourly schedules, precise minute schedules (including
small midnight values), legacy fractional-hour truncation, Paris DST dates and
transition-spanning windows, midnight endpoints, busy/free precedence, busy interval
splitting, duration-plus-buffer default spacing, and opt-in custom spacing.
**The availability-only slot oracles above do not cover:** appointment database
selection, booking limits, lifecycle side effects, notifications or populated-site
cutover.
Service/location availability conditions are Services add-on behavior that free
master never evaluates, so there is no free oracle for them; free v3 blocks them.
Existing v3 integration tests still cover its booking/reschedule/cancel/approval,
transaction and concurrency behavior; those are not legacy differential tests.

## Legacy booking lifecycle oracle

The database-backed lifecycle oracle captures 38 scenarios against audited master
`fcf2cfe296a240421ed91f28ff3672ece04e4786`. Its input inventory is
[lifecycle-cases.php](Characterization/lifecycle-cases.php); the source-pinned
result is [legacy-lifecycle.json](Characterization/legacy-lifecycle.json). It
covers approval state, buffers, conflicts, booking limits, cancel/reschedule
deadlines, client restoration, and stored state after each public REST operation.
The runner refuses any database except the disposable `legacy_oracle` database
in a `local` WordPress environment. Cron is disabled; queued jobs are summarized
instead of processed, and no mail or provider calls are sent.

Use a **separate checkout** of audited master with its Composer dependencies
restored. Do not check out master over the v3 worktree or install its runtime
dependencies into v3. Build the master's frontend first so its `dist/manifest.json`
exists (the test site does not load the frontend bundle):

```bash
export WAPPOINTMENT_LEGACY_ROOT=/absolute/path/to/isolated-master
(cd "$WAPPOINTMENT_LEGACY_ROOT" && npm ci && npm run build)
docker compose -p wappo-legacy-oracle -f tests/docker/legacy.compose.yml up -d --build --wait
docker compose -p wappo-legacy-oracle -f tests/docker/legacy.compose.yml exec wordpress \
  wp core install --url=http://localhost --title='Legacy oracle' \
  --admin_user=oracle-admin --admin_password=disposable-oracle-test \
  --admin_email=admin@example.invalid --skip-email --allow-root
docker compose -p wappo-legacy-oracle -f tests/docker/legacy.compose.yml exec wordpress \
  wp plugin activate wappointment --allow-root
docker compose -p wappo-legacy-oracle -f tests/docker/legacy.compose.yml exec wordpress \
  wp eval-file /characterization/capture-legacy-lifecycle.php --allow-root \
  > /tmp/wappo-legacy-lifecycle.json
cmp tests/Characterization/legacy-lifecycle.json /tmp/wappo-legacy-lifecycle.json
docker compose -p wappo-legacy-oracle -f tests/docker/legacy.compose.yml down -v
```

The site must have the plugin's default calendar `1` and physical location `2`;
the runner fails explicitly if either seeded fixture is absent. Never use a
working/demo WordPress database with this oracle. The source and input hashes,
normalized errors, and relative timestamps make the captured JSON reproducible.
It is a master baseline, not a requirement to reproduce legacy defects: for
example, master accepts some off-grid/past reschedule inputs and its occupied-slot
reschedule can hit a database uniqueness error. v3 intentionally validates those
selections more strictly. The PHP unit suite checks the fixture's source/input
hashes and its key lifecycle transitions without loading legacy libraries. The
disposable v3 booking integration suite executes the same 38 scenarios and compares
success/failure plus persisted appointment/client state at each step. Explicitly
accepted differences cover stricter invalid-slot rejection, canceled-history
retention, preserving existing customer data, and rollback of customers from failed
booking attempts. It also checks pending cancellation after its deadline and
monotonic sequence increments over repeated reschedules.

Recurring status occurrences have a separate source-pinned oracle:

```bash
php8.3 tests/Characterization/capture-legacy-recurring-statuses.php > /tmp/wappo-legacy-recurring.json
cmp tests/Characterization/legacy-recurring-statuses.json /tmp/wappo-legacy-recurring.json
```

It executes master `Status::expand` with a frozen Carbon clock and a minimal
cast-on-read model shim. Seven cases compare the exact occurrence start/end timestamps
for daily interval/exclusion/until rules, weekly weekdays, a timezone/DST transition,
monthly day 15/31 and ordinal weekday rules, and leap-year rollover. The v3 unit suite
consumes these committed results without loading legacy libraries. A separate
disposable WordPress integration test verifies recurring busy and free records alter
public slots and that those projected slots are enforced by booking creation. This
does not cover the status editor or imported-calendar lifecycle.
Another integration test stores a free status carrying Services `options.conditions`
and verifies that availability and booking return 409 for the overlapping day only,
with no appointment written and the stored options unchanged.

The isolated WordPress integration suites additionally verify hourly/minute API
projection, unchanged stored options during reads/bookings, explicit `precise: true`
saves through both editors, private-field preservation, rejected unmarked minute
data, busy-boundary anchoring, and the `more_st` toggle. Rejection tests deliberately
leave incompatible earlier v3 test data untouched. Unmarked ranges follow master's
hour interpretation; review earlier v3 minute fixtures rather than guessing a format.

## Schema foundation tests

The PHP unit suite now characterizes the source-derived `master` schema: all 16 core
tables, pivot IDs, timestamp/text types, nullability, defaults, indexes and restrictive
foreign keys. It tests typed report serialization and metadata failure propagation.

Use a **separate disposable** project on port 18090, not a local working/demo site.
The same Docker definition is used, but the project and database are isolated:

```bash
WAPPOINTMENT_BOOKING_PORT=18090 docker compose -p wappo-schema-tests \
  -f tests/docker/booking.compose.yml up -d --build --wait
docker compose -p wappo-schema-tests -f tests/docker/booking.compose.yml exec wordpress \
  wp core install --url=http://127.0.0.1:18090 --title='Schema tests' \
  --admin_user=booking-admin --admin_password=disposable-booking-test \
  --admin_email=admin@example.invalid --skip-email --allow-root
docker compose -p wappo-schema-tests -f tests/docker/booking.compose.yml exec wordpress \
  wp plugin activate wappointment --allow-root
docker compose -p wappo-schema-tests -f tests/docker/booking.compose.yml exec \
  -e WAPPOINTMENT_WP_ROOT=/var/www/html \
  -e WAPPOINTMENT_TEST_HOST=127.0.0.1:18090 wordpress \
  php /var/www/html/wp-content/plugins/wappointment/vendor/bin/pest \
  --do-not-cache-result --cache-directory=/tmp/wappo-pest \
  --configuration=/var/www/html/wp-content/plugins/wappointment/phpunit.xml \
  /var/www/html/wp-content/plugins/wappointment/tests/Integration/DatabaseSchemaTest.php \
  /var/www/html/wp-content/plugins/wappointment/tests/Integration/PermissionsTest.php \
  /var/www/html/wp-content/plugins/wappointment/tests/Integration/BookingTest.php
```

`WAPPOINTMENT_TEST_HOST` must match the installed URL's host and port, particularly
on multisite; change it with `WAPPOINTMENT_BOOKING_PORT` when using another port.
Tests create uniquely prefixed temporary plugin tables and remove only those fixtures.
They verify metadata-only queries, unchanged DDL/records on repeat setup, missing-table
and obsolete-marker refusal, installation-lock release on failure, readiness-marker
write failure, additional-table preservation, REST permissions and the 503 readiness
gate. Intentional blocked-operation tests emit server log messages.

Then convert **only that disposable fixture**, never a real/local working site:

```bash
docker compose -p wappo-schema-tests -f tests/docker/booking.compose.yml exec \
  wordpress wp core multisite-convert --title='Schema multisite tests' --allow-root
docker compose -p wappo-schema-tests -f tests/docker/booking.compose.yml exec \
  wordpress wp plugin activate wappointment --network --allow-root
```

Rerun the same Pest command with `DatabaseSchemaTest.php` and `PermissionsTest.php`
(omit `BookingTest.php`). The schema suite's multisite case
creates a child site and verifies installation/inspection against the active blog's
prefix, reusing the same repository instance across blog switches. It checks that
the parent site's DDL is unchanged, then removes its child-site fixture. This covers
schema scoping, not complete multisite lifecycle or feature certification.

Remove only that disposable stack afterwards:

```bash
docker compose -p wappo-schema-tests -f tests/docker/booking.compose.yml down -v
```

The frontend suite includes Diagnostics loading/error/incompatible/empty states and
verifies that it never offers a repair mutation. A schema mismatch requires review;
do not reset a populated site's data to make a test pass.

## Role, appointment and client ownership tests

The permission suite above uses the same separate disposable fixture. It creates
uniquely named temporary users/calendars/appointments and removes them afterwards;
never run integration tests against the working/demo site on port 18089.

It verifies legacy staff/manager role defaults without administrator escalation or
overwriting existing restrictions; anonymous/subscriber/manager/staff boundaries;
separate confirm/cancel capabilities and their calendar parent grant; SQL ownership
filtering before the latest-100 limit; unpublished/deleted/reassigned/missing
calendars; denied writes leaving other users' appointments unchanged; menu visibility,
direct render rejection and bootstrap operation flags. The historical
`switch_themes` shortcut does not grant management access.

Multisite tests reuse a policy/action/repository across user and blog switches.
They test site membership, identical calendar/appointment IDs in different blogs,
and mutations that change only the active site's owned appointment/client. These checks do
not certify complete network activation/new-site/uninstall or staff workflow parity.
Global settings/setup/jobs/Diagnostics remain blocked for staff until their dedicated
scoped operations are implemented. Clients now have their own scoped capability tree.

React tests cover distinct confirmation/cancellation controls, a grant revoked after
bootstrap, fail-closed page rendering and blocked SPA navigation.

Client tests cover list/search/count scoping before pagination; escaped wildcard and
SQL-like search input; client-only and separate edit/delete grants; missing/deleted/
reassigned calendars; shared clients and deduplication; anonymous/unassigned users;
guessed IDs; manager-only creation; duplicate-email conflicts; no-op updates; and
logged database failures. Soft deletion must retain all appointment rows unchanged
and hide the client for every user. Booking tests verify restoration of the same ID
with private fields intact and rollback of restoration when a booking fails.

Client UI tests cover read-only/details access, distinct operation controls, shared
deletion messaging, denied edit/delete preservation, valid JSON object submission and
explicit malformed/non-object JSON errors. Empty maps persist as objects, including
editing legacy empty `[]` maps. The intentional database-failure test logs its
simulated missing-column error; this is expected rather than a test failure.
The production staff client list, hidden
controls and edit workflow are also checked in a browser on the separate fixture.

## Own weekly settings tests

The same permission suite covers `GET /me/calendars`,
`POST /me/calendars/{id}/weekly-settings`, and own-calendar busy/free status listing,
creation and deletion. It tests self-only menus; separate
`wappo_self_man` and `wappo_self_weekly` grants; actor-owned calendar projection;
no private-option leakage; unassigned users and unowned/missing/deleted/reassigned
IDs; no-op writes; retained nested option objects/lists; unknown/forged parameters;
timezone/horizon/range validation; conditional schedule refusal; malformed stored
JSON; and rollback on a deliberately injected database write failure.
The expected failure cases emit server logs.

Booking integration tests save own settings and then call the public availability
API, checking exact UTC slots, booking-horizon exclusion, closed weekly schedules
and unchanged existing appointments. Multisite tests reuse the same own-settings
Actions/repository across sites with identical calendar IDs and check parent data
remains unchanged after child mutations.

Unit tests validate the typed weekly request/response shape and schedule constraints.
React tests cover independent calendar forms, exact save bodies, shared range-editor
midnight behavior, view-only grants, denied saves retaining input, conditional
read-only states, empty assignments and retryable loading errors. Browser checks use
a synthetic self-only staff account to save/reload its horizon without opening any
global management screen. This is basic weekly-settings coverage, not certification
of conditional availability, old usermeta imports or all staff settings.

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
