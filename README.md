# Appointment Bookings for WordPress

## v3 development status

**This branch is not production-ready and does not yet have free-feature parity with v2.**
The migration targets one free plugin (`wappointment-plugin`) and one paid plugin
(`wappointment-premium`). Complete the free booking flow before implementing Premium;
Premium will initially contain the former Services features. Licensing and automatic
Premium updates are deferred.

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

Each Data contract exposes a JSON-Schema-shaped `schema()` consumed by the shared PHP
validator. Unknown structural fields, missing required values, wrong types, malformed
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
OpenAPI generator. An exporter can consume the Data schemas and controller signatures
later; no OpenAPI tooling is added here.

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

### Data and legacy plugins

Existing `{$wpdb->prefix}wappo_*` tables, row IDs, UTC timestamps, JSON options, and
`wappointment_settings` remain in place. The bootstrap creates missing tables only;
it does not rebuild existing tables, delete add-on data, or reset settings.
Stored appointment end times continue to include the buffer. Public bookings do not
overwrite existing customer names or private custom fields. This is not yet a certified
upgrade/migration path: older schemas and populated v2 installations need migration testing.

Legacy paid-plugin boot hooks are disabled in v3, with an administrator notice.
Their source repositories and stored data are not removed. Bookings that require
unported features are rejected explicitly rather than ignoring their rules.
The single-service setup editor refuses to overwrite multi-service/calendar/location
setups, multiple durations, custom fields, or conditional availability.

### Remaining release blockers

- Calendar synchronization (ICS and the existing connected-calendar service), automatic
  video-meeting creation, and recurring/conditional availability.
- Notification templates, organizer notifications, reminders, summaries, mail transports
  other than the basic WordPress mail path, and cancellation/reschedule/approval messages.
- Full admin calendar behavior, punctual availability editing, manual/recurrent booking
  tools, booking-widget appearance/text settings, and save-to-calendar downloads.
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
