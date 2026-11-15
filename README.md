# Appointment Bookings for WordPress

## v3 development status

**This branch is not production-ready and does not yet have free-feature parity with v2.**
The migration targets one free plugin (`wappointment-plugin`) and one paid plugin
(`wappointment-premium`). Complete the free booking flow before implementing Premium;
Premium will initially contain the former Services features. Licensing and automatic
Premium updates are deferred.

See the [master feature audit and v3 migration plan](docs/V3-MIGRATION-PLAN.md) for
the feature inventory, implementation order, and acceptance gates. Legacy API routes
and payloads may be redesigned; the current database schema must remain unchanged.
Both packaged plugins must run without external PHP packages or a Composer autoloader.
Composer development dependencies, including Pest, stay for testing/tooling only;
release ZIPs must exclude vendor files, Composer metadata, tests and development tools.

The v3 implementation uses PHP 8.2+, WordPress 6.2+, native WordPress REST/database APIs,
and React. It does not load the old Illuminate/Vue runtime.

### PHP request architecture

Each operation has one invokable controller with exactly one constructor dependency:
its UseCase in `app/UseCases`. Each API controller accepts its own RequestData and
returns its own ResponseData, matching the UseCase's `execute()` signature.
Controllers only delegate; they never parse WordPress requests, send responses,
catch exceptions, or call repositories or Actions directly.
UseCases coordinate the operation through focused Actions in `app/Actions`.
An Action exposes `handle()`; a UseCase exposes `execute()`. Neither layer depends
on controllers, and UseCases do not depend on REST request/response objects.
Request contracts belong in `app/Http/Requests`, response contracts in
`app/Http/Responses`; database access stays in repositories.

The route-registration Action is the HTTP boundary. It merges URL/query/JSON parameters,
rejects conflicting identifiers, validates input, invokes the controller through the
container, and serializes its ResponseData into a `WP_REST_Response`.
Creation statuses (201) are route metadata, not controller logic.
Success responses keep `{ "success": true, "data": ... }`.
API errors now consistently use `{ "code": "...", "message": "...", "data": { "status": 400 } }`
instead of the older clients/settings error envelopes. WordPress still handles its own
authentication, routing and malformed-JSON errors.

Data contracts use native, Spatie-inspired constructor properties, without Laravel
dependencies or array-backed payloads. Types define the structure; attributes add
constraints that PHP types cannot express. Shared nested Data objects live in
`app/Http/Data`.

```php
class BookingRequestData extends RequestData
{
    public function __construct(
        #[Min(1)] public readonly int $service_id,
        #[Min(1)] public readonly int $calendar_id,
        #[Min(1)] public readonly int $location_id,
        #[Min(5), Max(1440)] public readonly int $duration,
        #[Date] public readonly string $date,
    ) {}
}
```

Actions read `$request->service_id` directly, not generic `text()`/`integer()` helpers.
`Data::from($array)` validates input and hydrates nested Data/`#[CollectionOf(...)]`
items; native constructors also support named arguments and already-typed nested
objects. As with ordinary PHP constructors, direct construction enforces PHP types,
not validation attributes. Responses are validated again at the HTTP boundary.
`toArray()` recursively exports plain PHP values; JSON serialization preserves
declared empty object maps. `#[MapInputName(...)]` maps wire names where needed.

An `Optional::Missing` default means a field can be omitted but cannot be null.
Omitted fields are left out of serialization and updates. Nullable types instead
allow an explicit null; a nullable field with no default is still required.
`#[Map]` declares an intentionally extensible options/settings map rather than a list.
Cross-field rules (availability selection versus secret key, rescheduling-only fields)
live in protected `validate()` hooks on the request DTO.

The shared Data implementation derives `schema()` from types, defaults and attributes;
endpoint classes no longer maintain parallel schema arrays. The existing PHP validator
consumes these generated schemas. Unknown structural fields, missing required values, wrong types, malformed
dates/emails and invalid nested arrays are rejected. Numeric strings are normalized
only where integer parameters are declared (query strings and HTML selects).
JSON objects and lists remain distinct at the HTTP boundary, including empty objects.
Email contracts use native PHP validation rather than WordPress sanitization filters.
Response contract violations are logged server errors (500), never successful or
client-validation responses. Stored extension settings and option maps are explicitly
extensible; the settings UI sends only changed fields to avoid replaying legacy keys.
The settings editor response projects service/calendar/location fields instead of
exposing complete database records.

These are application contracts, not an OpenAPI export or automatically registered
WordPress argument schemas. WordPress provides REST discovery/OPTIONS but no built-in
OpenAPI generator. An exporter can consume the generated schemas and controller
signatures later, but would also need to describe cross-field rules from validation
hooks. No OpenAPI tooling is added here.

For example:
`JobsController::__invoke()` -> `ListJobsUseCase::execute()` -> `ListJobsAction::handle()`.
GET and POST operations use separate controllers/UseCases even when they share a URL.
Booking actions are split by validation, availability, persistence, presentation,
and notification, rather than collected into a multi-operation service.
`tests/Unit/ArchitectureTest.php` enforces these conventions.

Implemented booking baseline:

- Free service/calendar/location setup and weekly availability.
- Live, timezone-aware slots, minimum notice, booking horizon, buffers, busy/free
  exceptions, and existing pending/confirmed appointment conflicts.
- Persisted public bookings with server-side service/location/duration validation,
  active-booking limits, serialized concurrent writes, and transaction rollback.
- Pending versus confirmed bookings, administrator approval/cancellation, and
  secret-link customer cancellation/rescheduling with configurable deadlines.
- Live React booking widgets with independent instance titles, explicit request errors,
  and no fabricated confirmation or email-delivery messages.
- A basic customer confirmation through `wp_mail` when that transport is configured.
  Delivery failure is logged and shown without pretending that the saved booking failed.

Weekly scheduling now has a reproducible, isolated legacy-engine baseline:
15 frozen weekly/DST/busy/free fixtures and 60 duration/buffer/spacing comparisons.
The [characterization instructions](tests/README.md#legacy-weekly-runtime-characterization)
describe exactly which legacy methods execute; this is not full WordPress/lifecycle parity.
Slots use duration plus buffer as their default spacing. `starting_each` applies only
when `more_st` is enabled, and spacing restarts at each remaining free interval after
busy time is subtracted.

Stored weekly schedules follow master's format: `regav.precise: true` means minutes;
an absent/false flag means hours. Both setup editors expose minute values and write
`precise: true` on explicit saves. Reads/bookings do not rewrite stored schedules.
**Earlier v3 test builds wrote unmarked minute schedules.** Those test calendars need
administrator review before reuse: values above 24 without the flag are refused,
not guessed or automatically repaired. Smaller unmarked values follow master's
hour interpretation. Only mark an existing schedule as minutes after confirming its
actual origin; do not bulk-convert populated sites.

Frozen comparisons also cover notice rounding, fluid start times, the daily
availability-refresh cutoff, and the calendar-day booking horizon (including DST).
Minimum notice is rounded down to whole minutes as in the old UI. Fluid availability
starts on the next five-minute boundary. Without fluid availability, today's first
slot advances from its schedule start using the appointment duration, while normal
slot spacing still accounts for buffer. The configured daily refresh hour delays one
booking-horizon day until reached. Out-of-horizon dates return no slots.

### Data and legacy plugins

Existing `{$wpdb->prefix}wappo_*` tables, row IDs, UTC timestamps, JSON options, and
`wappointment_settings` remain in place. Fresh installation now creates the 16 core
tables derived from `master`, including its timestamp/text types, pivot IDs, indexes
and foreign keys. Existing installations are inspected, not rebuilt or completed:
partial/incompatible schemas block setup without changing their tables or records.
An empty database with existing installation/settings markers is not treated as fresh.
Stored appointment end times continue to include the buffer. Public bookings do not
overwrite existing customer names or private custom fields. This is not yet a certified
upgrade path: representative populated v2 installations still need characterization.

**Wappointment > Diagnostics** (administrators and Wappointment managers) displays a read-only schema
report, also available at `GET /wappointment/v1/system/schema`. It reads metadata,
not customer records. Additional extension tables are listed but not certified.
This follows the same Controller -> UseCase -> Action and typed Data architecture.
Schema compatibility does not certify feature parity or production readiness.

The old development marker `3.0.0` no longer authorizes API operations: successful
schema verification records the audited master reference instead. Until verified,
other API operations return `503 wappointment_schema_not_ready`; Diagnostics remains
available. Earlier v3 test databases may therefore need review. They are **not**
automatically converted or reset; preserve any data and use a separate disposable
fixture for a clean installation.

Legacy paid-plugin boot hooks are disabled in v3, with an administrator notice.
Their source repositories and stored data are not removed. Bookings that require
unported features are rejected explicitly rather than ignoring their rules.
The single-service setup editor refuses to overwrite multi-service/calendar/location
setups, multiple durations, custom fields, or conditional availability.

### Current admin permissions

The shared access policy allows site administrators (`manage_options`) and the
existing `wappointment_manager` role to use the current management screens and APIs.
It does not grant managers WordPress administrator privileges or treat
`switch_themes` as permission to manage bookings.

Staff need `wappo_calendar_man` to open Appointments. Listings are scoped in SQL to
all non-deleted calendars whose `wp_uid` is the current WordPress user, before the
100-row limit. Unpublished calendars retain their appointment history.
Confirmation additionally requires `wappo_calendar_confirm`; cancellation requires
`wappo_calendar_cancel`. Both mutations recheck ownership within the transaction;
another user's appointment ID returns 404. Menus, direct page access and React
controls use the same policy; the server always enforces the grants.

Missing legacy staff/manager roles are created using their legacy defaults.
Existing roles and per-user grants/denials are never refreshed or overwritten.
Permissions and appointment/client table prefixes follow the current user/site when
switching blogs on multisite.

Staff with `wappo_clients_man` can use Clients independently of calendar-management
permissions. Listings, name/email/client-field search and pagination counts use
appointment links to all their own non-deleted calendars. Editing additionally
requires `wappo_clients_edit`; deletion requires `wappo_clients_del`. Ownership is
locked and checked during each mutation, including calls made directly to an Action.
Unowned/missing/deleted client IDs return 404. Standalone client creation remains
administrator/manager-only.

As in legacy, a client linked to multiple calendars is a shared record: authorized
staff on either calendar may edit or soft-delete it. Deletion removes the client
from all client lists but keeps their row and every appointment reference. A new
successful public booking with the same email restores that client's original ID,
without overwriting their existing name/private fields; a failed booking rolls back
restoration. Client editing shows options as JSON and rejects invalid/non-object
JSON instead of silently clearing stored values. Legacy empty `[]` option maps are
shown as `{}`, and empty maps are persisted as JSON objects.

**Wappointment > My availability** is a separate own-calendar screen.
`wappo_self_man` grants read access, independently of calendar/client management;
`wappo_self_weekly` additionally permits changing timezone, booking horizon, weekly
hours and busy/free times. One-off and supported recurring intervals can be created,
edited and deleted from the same screen. This `/me` operation remains owner-scoped even
for managers. Missing calendar assignments show an explicit empty state. Busy/free
exception inputs use the browser timezone and are stored in the existing statuses table;
conditional Services rules and recurring rules with excluded dates remain protected.

Managers can also create, edit and delete busy/free intervals for the configured calendar
from **Wappointment > Booking setup**. That editor requests an explicit manager scope,
which requires manager authorization and is kept separate from own-calendar access.

The typed `GET /wappointment/v1/me/calendars` response contains only each owned
calendar's ID, name and supported weekly settings, not account keys/private options.
`POST /wappointment/v1/me/calendars/{id}/weekly-settings` accepts a `settings` object
containing `timezone`, `booking_days` and seven-day `regav` ranges in minutes.
Existing `calendars.options` keys (`timezone`, `avb`, `regav`) are updated under an
ownership-checked transaction; other options, relationships, cached legacy data and
existing appointments are preserved. The current booking engine reads these options
live: changes affect subsequent public slot searches, not existing bookings.

Conditional/unsupported stored schedules are explicitly read-only, with mutation
returning 409 rather than dropping conditions. Malformed stored JSON is a logged
server error. This editor supports basic weekly ranges only; no legacy usermeta is
automatically imported or rewritten.

This is **not complete staff-workflow parity**. Global settings, Booking setup,
Jobs and Diagnostics remain administrator/manager-only. Staff profile/service/
publishing/provider settings, history and remaining calendar actions still need
dedicated scoped operations.

### Remaining release blockers

- Calendar synchronization (ICS and the existing connected-calendar service), automatic
  video-meeting creation, and recurring/conditional availability.
- Notification templates, organizer notifications, reminders, summaries, mail transports
  other than the basic WordPress mail path, and cancellation/reschedule/approval messages.
- Full admin calendar behavior, punctual availability editing, manual/recurrent booking
  tools, booking-widget appearance/text settings, and save-to-calendar downloads.
- Remaining staff profile/service/publishing/provider settings, history and calendar
  workflows; own weekly settings, appointment listing/confirmation/cancellation and
  scoped client management are implemented.
- Country-aware phone validation, complete logged-in/autofill behavior, and the remaining
  advanced scheduling settings (`scheduler_mode`, fluid availability, daily release, etc.).
- Upgrade tests using representative v2 data, existing management links/shortcodes,
  pending background jobs/reminders, multisite, and minimum supported WordPress versions.
- The Premium plugin and the complete former Services feature set.

Use **Wappointment > Booking setup**, add `[wap_widget title="Book now"]` to a
published page, and set its ID in Booking setup to enable confirmation links.
Without a valid booking page, the appointment is saved but the confirmation email
is explicitly reported as not sent. Exercise this baseline on staging only.
See [booking tests](tests/README.md#v3-booking-tests) for reproducible checks.

![Appointment Bookings WordPress](https://ps.w.org/wappointment/assets/banner-1544x500.gif)

**Appointment booking calendar** for personal coaches, teachers, therapists and service professionals of all kind.
Get booked 24/7 with the most **intuitive booking form**.

Convert visitors into customers with a simple call to action.

The perfect [Calendly alternative for WordPress](https://wappointment.com).

[![Watch the video](https://cdn.wappointment.com/images/play-explainer-bg.jpg)](https://www.youtube.com/watch?v=jUkiyejbuzg)

Try the [demo of the booking calendar](https://demo.wappointment.com)

## Scheduling system headache free


### Provide your appointments the way you like
* as a video Meeting over Zoom, GoogleMeet, Jitsi or Skype
* over the Phone
* or at your office


### Sync bookings with Google Calendar
* Setup a 2-way sync quickly with Google Calendar
* 1-way sync is available with any .ICS Calendar, Microsoft Outlook, Apple Ical, etc ...


### Avoid Double Bookings 
Keep your availability updated using our powerful centralized system. 
Your availability gets refreshed whenever something changes in your schedule: 

* when a new client books you
* when a client cancels his appointment
* when you manually create new time slots during which you are busy or free 
* when a new event gets created on your synched personal calendar (Google Calendar, Apple iCal, Outlook Calendar)


## Simplify your Booking Process
Our **user friendly booking form** gives your clients a quick overview of your availability, making the booking process a breeze.


### Reduce No-Shows
Your clients receive **appointment confirmations and reminders**. 
Quickly define when and how many of them do they receive (1 day before appointment, 1 hour before appointment).


### Make it simple for your customers
* Clients book you within seconds, from their mobile phone, tablet or desktop computer
* The available booking slots are displayed in your client's timezone, no more confusion for your international clients
* Clients receive a confirmation and as many reminders as you wish
* Clients can easily save your appointment to their personal calendar


### Manage your schedule simply
* Unlimited bookings
* User-friendly and intuitive interfaces with no coding involved


### Availability Setup 
* Set your recurrent availability within seconds
* Set your punctual availability and block your non-bookable time (non working days and hours, busy times, holidays, etc) in just few clicks
* Select the timezone from which you operate


### Appointments Settings
* Set the duration of your meeting 5 min, 10 min, 15 min , 60 min etc ...
* Set the appointments' approval mode: automatic or manual 
* Set how far in advance an appointment can be booked 
* Allow clients to cancel and reschedule appointments
* Book an appointment on behalf of your customer
* Connect your personal calendar to the booking system and automatically block times during which you are busy
* Change the date and time format 

### Customizing the appearance
* Quickly customize colors and texts for your booking form 
* 4-steps booking process, each step is fully editable

### Appointments' Confirmations Reminders and Notifications
* Receive email notifications when clients book, reschedule or cancel an appointment
* Receive daily and weekly notifications
* Customize and personalize your confirmations and reminders sent to your clients

### Advanced options for edge use-cases
* Limit/Maximum active bookings per client
* Force user account's email for logged in users
* Open new slots in new day at a specific time e.g." new slots everyday at 11pm"

### Have a Question? 
Our plugin is free, and easy to install. Try it first :)
And for any question or doubt, you can reach us:

* Straight from the plugin in *Wappointment > Help*
* Here on the [WordPress' forum](https://wordpress.org/support/plugin/wappointment/) 
* From our contact page on [wappointment.com](https://wappointment.com/support?utm_source=wp-repo&utm_medium=link&utm_campaign=readme).


## Frequently Asked Questions 

**Can I change text and colors of the booking form?**

You can change all the texts, colors and few other parameters of each step of the booking process. We plan on adding several templates in the future, meanwhile simply use css to make it exactly the way you want.

**Can I set time limits for cancelling and rescheduling appointments?**

You can configure when clients can cancel and reschedule their appointments in the settings page *Wappointment > Settings > General* 

**Can I sync multiple calendars besides of my Google calendar?**

Sure you can, we allow up to 4 calendars in the ics format to be synched from. It can be personal calendar(Google, Outlook, iCal, etc..) or from external applications handling part of your schedule

**How often my Google calendar is being checked for sync?**

Every 5 minutes we download your calendar and check for changes, we don't do it more often as it could be a heavy task depending on how big is your calendar.

**Why do reminders go out late sometimes?**

It depends on your website's configuration. The most reliable solution is to setup a cron task manually on your server(check your host's documentation) and disable WP cron (DISABLE_WP_CRON)

**Why does nobody receive my confirmations or reminders emails?**

Your emails most likely go straight to SPAM or don't event reach your inbox. *Change the email sending method* in *Wappointment > Settings > Confirmations & Reminders* just go for the easy and reliable solution, [create a free account at SendGrid (100emails/day are free)](https://signup.sendgrid.com/) and configure Wappointment with the *SendGrid API*

**I need 10 minutes to prepare between 2 appointments, how do I proceed?**

You can set buffer time for that particular case, you can define it in the *Wappointment > Settings > Advanced*. When someone books you, you will become unavailable during the time of the appointment + buffer time


## Installation 


### Minimum Requirements 

* WordPress 6.2(or greater)
* PHP version 8.2(or greater)
* MySQL version 5.6(or greater) or MariaDB 10.0(or greater)

Always keep your softwares updated.
It requires work on your end but keeps your site safe and optimized.


## Screenshots 

### 1. Booking an appointment on mobile phone
![Mobile appointment booking form](https://ps.w.org/wappointment/assets/screenshot-1.gif)

### 2. Viewing appointment and changing quickly availability
![Admin Calendar schedule management](https://ps.w.org/wappointment/assets/screenshot-2.gif)

### 3. Receiving branded email reminders on mobile phone
![Appointment email reminders on mobile phone](https://ps.w.org/wappointment/assets/screenshot-3.gif)

### 4. Editing booking widget's style through simple interfaces
![Booking form editor](https://ps.w.org/wappointment/assets/screenshot-4.gif)

### 5. Weekly Availability. First step of our initial setup wizard, simply drag and drop your recurrent availability.
![Weekly availability](https://ps.w.org/wappointment/assets/screenshot-5.jpg)

### 6. Service Setup. Describe the appointment and how you provide it: By Phone, By Skype or At a location.
![Service Setup](https://ps.w.org/wappointment/assets/screenshot-6.jpg)
