# Wappointment v3 feature migration plan

## 1. Baseline, scope, and findings

Prepared on 2026-11-14 from a static source audit of:

- Legacy free plugin: local `master`, commit `fcf2cfe296a240421ed91f28ff3672ece04e4786`,
  plugin version **2.7.7**.
- Target: `feat/v3.0.0`, HEAD `b7b70afa3c1dd36e735573c8659c35b27a3a420c`
  **plus the current uncommitted implementation**. HEAD alone does not represent
  the target state described here.
- Legacy inventory: 382 PHP files under `app`, 291 Vue files under `resources/js`,
  the REST route declarations, historical database migrations, WordPress integration,
  configuration/translation lists, and the available test entry points.

`master` was checked out in a separate detached worktree. The active v3 checkout,
uncommitted changes, and running local WordPress site were not switched or modified
for this audit. No production data was inspected and no external provider was contacted.
The local `master` reference was used; it was not fetched from a remote.

**Conclusion: v3 is a working subset, not a feature-equivalent replacement.**
The recent Booking setup and Appointments screens are temporary baseline interfaces,
not replacements for the complete legacy settings experience and admin calendar.
Settings persistence and a visible control do not establish that its behavior works.

This document is an implementation proposal and acceptance checklist. It does not
start the migration, approve a product change, certify live integrations, or claim
every legacy code path has been exercised at runtime.

### Product scope already agreed

1. Finish free-plugin feature parity and safe data migration first.
2. Keep the free plugin as `wappointment-plugin`.
3. Then create `wappointment-premium`, initially containing the former Services features.
4. Do not migrate all paid packages into the first Premium release.
5. Defer Premium licensing/update enforcement.
6. Preserve customer data and clearly report unsupported legacy paid configurations.
7. Work on `feat/v3.0.0` in every repository that is subsequently changed.
8. **Preserve the current database schema. Legacy API compatibility is not required.**
   Routes, request parameters, response shapes and frontend consumers may be redesigned
   together. Database column names do not need to become API property names.
9. **Zero external PHP/Composer dependencies in the packaged plugins**, both free
   and Premium. Composer development dependencies and Pest are allowed and retained
   for testing/tooling only. Release ZIPs must exclude `vendor/`, Composer manifests/
   lockfiles/generated autoloaders, tests and development tooling, and boot without
   Composer. Use PHP/WordPress APIs and first-party runtime code; do not bypass this
   constraint by copying or bundling external PHP libraries. Frontend npm tooling and
   libraries supplied by WordPress itself remain allowed.
10. Use the audited `master` schema as the authoritative database reference.
    Any additional Services-owned storage must be inventoried from its source before
    Premium work; it does not authorize changing existing installations.
11. Support **both single-site and WordPress multisite in v3.0**, including scoped
    activation, blog-specific tables/options/permissions and safe lifecycle behavior.

Core source includes payment, package, staff, and provider extension points. Their
presence is **not proof** that all related features were free. Conversely, an
integration or selection component must not be reclassified as paid just because
its implementation has extension hooks. Unresolved ownership is a release gate,
not permission to remove functionality.

### Evidence notation

Legacy paths below refer to the audited `master` commit, not files necessarily
present in the v3 checkout. They remain retrievable without retaining the worktree:

```bash
git show fcf2cfe296a240421ed91f28ff3672ece04e4786:app/Routes/Main.php
git show fcf2cfe296a240421ed91f28ff3672ece04e4786:resources/js/backend.js
git ls-tree -r --name-only fcf2cfe296a240421ed91f28ff3672ece04e4786 database/migrations
```

Evidence groups:

| Ref | Legacy source anchors | What they establish |
|---|---|---|
| E01 | `app/Routes/Main.php`, `app/Routes/Init.php`, `app/Routes/AbstractRoutes.php` | Public/admin/capability-scoped operations; RESOURCE expansion; POST suffixes for PATCH/PUT/DELETE. |
| E02 | `app/Services/Appointment.php`, `app/Models/Appointment.php`, `app/Models/Appointment/Recurrence.php` | Lifecycle, states, relations, locks, recurrence and management behavior. |
| E03 | `app/Services/Availability.php`, `app/Services/ExternalCalendar.php`, `app/Services/Calendar.php` | Availability regeneration, recurring busy inputs, imported calendar URLs and change detection. |
| E04 | `app/Controllers/EventsController.php`, `resources/js/CalendarAdmin/` | Full calendar interactions, manual bookings, confirm/change/cancel/delete/force-delete. |
| E05 | `resources/js/backend.js`, `resources/js/Views/`, `resources/js/Settings/` | Admin screens, wizard, settings, orders, help and extension UI. |
| E06 | `resources/js/BookingForm/`, `resources/js/Form/`, `resources/js/Fields/`, `resources/js/FormOptional/` | Customer booking steps, calendars, generated fields, conditional content and payments. |
| E07 | `app/WP/Shortcodes.php`, `app/WP/Widget.php`, `app/WP/WidgetAbstract.php`, `app/WP/CustomPage.php` | Embedding, widget instances, shortcode flags, legacy public pages and query links. |
| E08 | `app/WP/AppointmentHistory.php`, `app/WP/StaffHistory.php`, `app/WP/Staff.php`, `app/WP/StaffLegacy.php` | Logged-in history, staff ownership/profile data and old/new staff storage. |
| E09 | `app/Services/Client.php`, `app/Controllers/ClientController.php`, `resources/js/Views/Clients.vue` | Client lookup, booking limits, customer administration. |
| E10 | `app/Services/Reminder.php`, `app/Jobs/`, `app/Events/`, `app/Listeners/`, `app/Messages/` | Lifecycle notifications, reminders, summaries, templates, links and attachments. |
| E11 | `app/System/Scheduler.php`, `app/WP/Scheduler.php`, `app/Services/Queue.php`, `app/Transports/` | Scheduled jobs, worker processing, reservation recovery and delivery integration. |
| E12 | `app/Services/IcsGenerator.php`, `app/Services/Jitsi.php`, `app/Formatters/EventsCalendar.php` | Calendar export and local Jitsi meeting-link behavior. |
| E13 | `app/Controllers/CalendarsController.php`, `resources/js/Settings/StaffCalendarsExternal.vue`, `resources/js/Settings/StaffCalendarsExternalImport.vue`, `resources/js/Settings/StaffCalendarsExternalExport.vue` | Calendar connections/import/export UI and remote-account integration boundaries. |
| E14 | `app/Controllers/OrdersBackController.php`, `app/Controllers/OrderController.php`, `app/Services/Payment.php`, `app/Jobs/ProcessTransaction.php` | Orders, transaction processing, pay-later/provider integration and payment lifecycle. |
| E15 | `app/Lists/widget_settings.php`, `widget_fields.php`, `widget_steps.php`, `widget_translations.php`, `email_tags.php`, `email_links.php`, `translations_*.php` | Configurable appearance/text/steps, template vocabulary and localization. |
| E16 | `app/Installation/`, `database/migrations/`, `app/Config/Database.php`, `app/System/Status.php` | Installation state, schema generations, migration history and old service/calendar imports. |
| E17 | `app/WP/Menus.php`, `app/System/InitBackend.php`, `app/System/Compatibility.php`, `app/WP/Helpers.php` | Role-aware menus, plugin links, old admin slugs, permalink handling and lifecycle. |
| E18 | `app/Lists/addons.php`, `app/Services/Addons.php`, `app/Services/Wappointment/`, `app/Addons/AbstractBoot.php` | Extension identities, legacy boot/entitlement/distribution mechanisms. |
| E19 | `resources/js/Standalone/`, `resources/css/`, `resources/scss/` | Browser locale/date formatting, router translation, styling and responsive presentation. |
| E20 | `cypress/integration/test.spec.js`, `tests/Unit/ExampleTest.php` | Existing tests are not a parity oracle; the booking Cypress coverage identified is a smoke test. |

References to a directory mean a feature spans several collaborators, not that every
file in that directory is a separate product feature.

## 2. Target architecture and migration rules

Retain the architecture already requested and implemented:

```text
WordPress route / permission callback
  -> REST adapter: parse input and hydrate endpoint RequestData
  -> one invokable Controller
  -> one injected UseCase::execute(RequestData): ResponseData
  -> focused Actions::handle(...)
  -> repositories / WordPress adapters / explicit integration interfaces
```

- Every REST operation gets its own controller, RequestData, ResponseData and UseCase.
  Split existing multi-method legacy controllers by operation, not by file translation.
- Native readonly Data constructor properties, validation attributes, nested Data,
  declared collections, and `Optional::Missing` remain the contract style.
- Controllers only delegate. WordPress request/response classes, status selection,
  parameter merging, error conversion and serialization stay at the HTTP boundary.
- UseCases orchestrate Actions; they do not become replacement monolithic Services.
- Reuse existing validated Actions where behavior matches. Do not retain both a
  legacy and a new booking engine behind different UI screens.
- Actions may accept domain Data or explicit values. Preserve DTO structure until
  a persistence/transport boundary actually requires an array.
- Permissions include resource ownership, not only a global `manage_options` check.
- Design consistent v3 routes and success/error contracts without retaining legacy
  payloads, response envelopes, aliases or parameter spellings. Update every consumer
  in the same delivery slice; legacy wire snapshots are not acceptance requirements.
- Choose HTTP methods deliberately for WordPress/hosting support. Existing POST
  `/put`, `/patch`, `/delete` suffixes are implementation history, not a requirement.
  Verify the chosen convention with plain and pretty permalinks.
- Repositories map clean API/domain names and types to the **unchanged existing
  tables, columns, indexes, relationships and stored-value semantics**. Do not rename
  columns, replace pivots, add tables/columns, or rewrite business records to fit DTOs
  without a separately approved schema change.
- No Laravel/Vue runtime migration as a shortcut. Keep PHP 8.2+/React/native WP,
  with no external Composer packages or Composer autoloader in production.
  Keep Pest and other development-only Composer tools isolated from plugin execution.
- Implement ICS/recurrence behavior as focused first-party code using native date/time
  and WordPress facilities. Characterize required formats/rules before implementation;
  unsupported input must fail explicitly, never silently approximate availability.
- Use WordPress HTTP/mail facilities instead of external PHP clients or provider SDKs.
  Keep the native typed Data/container implementations; do not add third-party
  validation, reflection, collection, ORM or dependency-injection packages.
- Domain failures remain explicit; do not copy silent cron failures, fake successful
  mail delivery, or success-shaped database fallbacks.
- Keep non-HTTP entry points thin too: shortcodes, cron and event handlers call
  UseCases/Actions without manufacturing a REST request.

## 3. Feature-by-feature migration backlog

Status definitions:

- **Partial**: some working v3 behavior exists; the full legacy workflow is not migrated.
- **Missing**: no complete equivalent found in the current v3 implementation.
- **Boundary**: core exposes related behavior, but entitlement/provider/add-on scope
  needs a fixture-backed decision before claiming parity.
- **Replace**: deliberately retire an old mechanism while preserving its useful outcome.

Wave names and their dependencies are defined in section 4. Every acceptance entry
is a minimum requirement, in addition to the cross-cutting test gates in section 8.

### A. Data, platform, permissions and onboarding

| ID | Feature and evidence | v3 status | Planned migration / wave | Acceptance |
|---|---|---|---|---|
| F01 | Installation checks, activation state, wizard and update tracking (E05/E16) | Partial: missing-table bootstrap, no equivalent guided wizard | Schema-compatible installation/preflight UseCases and resumable setup screens. W1/W4 | Fresh install creates the current schema; upgrade does not recreate or alter existing tables; existing configuration is not reseeded. |
| F02 | Historical schemas, option/usermeta storage, legacy service/calendar conversion (E08/E16) | Partial: retains tables/options but no certified cutover | Schema inventory, read-only preflight and repositories compatible with current storage. Historical imports inform mapping, not automatic new conversions. W1 | Existing IDs, keys, relationships, timestamps and opaque paid data unchanged by activation/cutover; mismatched schemas reported before writes. |
| F03 | Manager/staff permissions and scoped admin access (E01/E08/E17) | Partial: shared manager policy, scoped appointments/clients/own weekly settings; other self settings/history/actions pending | Capability/ownership policy shared by API, admin and history screens. W1 | Administrator, manager, assigned staff, unrelated staff, subscriber and anonymous matrices tested; menu visibility agrees with server authorization. |
| F04 | Admin navigation, saved links, settings tabs, wizard completion, help/error states (E05/E17/E19) | Partial: Jobs/Clients/Settings plus baseline booking screens | Restore workflow-oriented React admin shell; map old slugs/hash routes; make Calendar primary after W3. W4/W7 | Old bookmarks lead to the right screen; refresh/back/forward work; both permalink styles work; no admin link resolves to frontend. |
| F05 | General, staff and advanced settings (E01/E05/E15) | Partial: typed settings storage/UI does not implement all consumers | Per-setting ledger: stored key/type, clean API property, default, owner, reader/writer and behavioral test. W0/W2-W6 | Every supported setting has an exercised effect; `false`/`0` and unknown stored keys survive; no need to expose legacy setting keys directly in the API. |
| F06 | Site lifecycle, compatibility and diagnostics (E11/E16/E17) | Partial: development warnings; limited schema checks | Safe health/readiness screen, cron/DB/assets checks, activation/deactivation and multisite policy. W1/W7 | Custom prefixes, plain/pretty permalinks, subdirectory install, read-only plugin assets and supported runtime matrix pass; no destructive reset on activation. |

### B. Availability and booking engine

| ID | Feature and evidence | v3 status | Planned migration / wave | Acceptance |
|---|---|---|---|---|
| F07 | Weekly working hours, timezone, booking horizon and minimum notice (E03/E05) | Implemented for the supported free schedule model: source-pinned weekly/DST and notice/horizon rules; past/off-grid selections are rejected explicitly; unsupported schedule forms fail closed | Keep the supported schedule oracle and safe-difference fixtures current. W2 | Supported bookable instants match frozen fixtures; past/off-grid requests are explicitly rejected or documented as intentional differences. |
| F08 | Slot starts, duration, buffer, scheduler mode, fluid/daily availability release rules (E02/E03/E05) | Implemented for supported modes: 145 source-pinned slot comparisons, duration/buffer persistence, adjacency/conflict handling, fluid starts, daily horizon release, and reschedule sequence | Keep slot/lifecycle rules on one availability pipeline; investigate any newly characterized mode before supporting it. W2 | Stored end includes buffer exactly once; adjacent appointments and horizon edges match; stored sequence increments on reschedule for future ICS export. |
| F09 | Punctual busy/free exceptions and recurring busy/free periods (E03/E04). Service/location availability conditions are **not free behavior**: they belong to the Services add-on (W8) | W2 core implemented: own-calendar users and managers can create/list/edit/delete supported busy/free recurrences; recurring intervals affect slots; end dates bound occurrences; overlaps resolve predictably; excluded-date and unsupported legacy rules remain read-only. Full calendar-editor integration is W3. | Maintain recurrence and availability behavior fixtures. W2/W3 | CRUD exceptions changes slots correctly; duplicate/overlapping ranges merge predictably; recurrence and excluded dates remain bounded and DST-safe. |
| F10 | Booking creation, pending/confirmed states, limits and concurrency (E02/E09) | Implemented and integration-tested: persisted pending/confirmed state, active-booking limits, conflict checks, transaction rollback, and serialized slot locking; retries of an occupied slot return 409 without creating another client or appointment | Continue only if broader legacy characterization identifies additional required state transitions. W2 | Verified: conflicting concurrent bookings cannot both persist; failed writes roll back; duplicate retries have an explicit 409 policy. |
| F11 | Public view/cancel/reschedule and action deadlines (E02/E06/E07) | Implemented and integration-tested: emailed hash links load the public management flow; view/cancel/reschedule, disabled/deadline rejection, self-excluding slot checks, collision rejection, buffer retention, and sequence increments | Continue only if broader legacy characterization identifies additional required public lifecycle behavior. W2/W4/W5 | Verified: old links resolve; late/disabled actions fail; rescheduling preserves other bookings and original buffer; sequence advances. |
| F12 | Recurring appointments, children/parents and series edits/deletion (E02/E04/E11) | Deferred paid Group feature: the legacy recurrence generator is gated on `wappointment_group`; v3 rejects unsupported series operations without changing records | Separate Group audit and product approval; do not activate from free W2. W3/W5 only after the paid feature is scheduled. | If approved later: parent/child IDs preserved; repeated generation creates no duplicates; single/series operations are distinct; conflicting occurrences have explicit outcomes. |
| F13 | Locked appointments and safe cancel/delete/force-delete distinctions (E02/E04/E14) | Implemented for ordinary appointments: scoped lock/unlock, cancellation history, normal delete, manager-only force-delete, and order-item unlinking; recurring/provider-linked records fail closed pending their migrations | Keep provider and Group-series operations unavailable until their owners are migrated. W3 | Verified: locks enforced; force actions authorized; cancellation retains history; deletion does not delete unrelated order data. |

### C. Full admin workflows

| ID | Feature and evidence | v3 status | Planned migration / wave | Acceptance |
|---|---|---|---|---|
| F14 | Calendar views, date navigation, timezone, filtering and event detail (E04/E05) | Partial: typed bounded UTC range, overlap inclusion, filters, pagination, week grid and appointment/busy/free event types implemented; external provider events and per-calendar timezone rendering remain unavailable | Complete calendar timezone/event parity and provider integration only with their respective approved owners. W3/W4 | Range, filters and pagination covered; pending/canceled/busy/free display implemented. External events remain unavailable while provider-linked records fail closed. |
| F15 | Book on behalf, client selection and intentional admin overrides (E02/E04/E09) | Partial: authorized staff can book for an existing client through the ordinary availability/conflict pipeline; confirmation notification is sent; no availability override or notification-choice option is exposed | Add any approved admin override only as a distinct, audited operation; preserve public validation and explicit notification behavior. W3 | Anonymous denial and conflict preservation tested. No override is exposed publicly or to staff; notification choice remains outstanding. |
| F16 | Calendar drag/drop, moving/editing appointments and confirmation dialogs (E04) | Partial: authorized transactional rescheduling checks availability/conflicts and preserves duration/buffer; calendar drop opens the timezone-aware editor rather than committing immediately | Complete optimistic interaction/rollback and confirmation UX without weakening server-side concurrency checks. W3/W4 | Reschedule collision, buffer and sequence behavior covered; drag-to-day opens explicit edit; one-step drag confirmation and optimistic rollback remain outstanding. |
| F17 | Free service configuration, relationships, duration/modality definitions and ordering (E01/E05/E06) | Partial: one simplified setup form; rejects richer records | Restore free configuration limits and full supported fields with separate operations; retain richer paid records read-only if unsupported. W2/W4 | No multi-duration/custom metadata loss; order/filter relationships preserved; free limits established by source/fixtures, not current query LIMITs. |
| F18 | Organizer/calendar identity, avatar, timezone, availability defaults and publishing (E05/E08/E13) | Partial: name/timezone/schedule subset | Calendar/profile operations and WP-user mapping; own-resource authorization; defaults/publishing management. W1/W4 | Repositories interpret existing staff/calendar identities without rewriting stored IDs; missing WP users produce actionable states; avatars/defaults/published state persist. |
| F19 | Customer list/search/pagination, create/edit/delete and details (E01/E09) | Partial: scoped CRUD, JSON details/options, soft deletion and repeat-booking restoration implemented; full history/field presentation pending | Complete detail/history/custom-field presentation; retain tested shared-client and soft-delete/reuse semantics. W3/W4 | Search/pagination/permalink cases; historic appointments stay readable; duplicate email and deleted-customer cases deterministic; private fields not overwritten by anonymous bookings. |
| F20 | Custom-field definitions, required/conditional inputs, phone/country validation and terms (E05/E06/E08) | Partial/Boundary: only basic contact fields; richer definitions not ported | Inventory field types/gates; typed definitions and submitted values; free renderer/validation first, premium-only editors later. W2/W4 | Client and server agree; country/phone restrictions enforced; terms/conditional content preserved; unknown stored field values retained, never silently dropped. |

### D. Customer experience and WordPress embedding

| ID | Feature and evidence | v3 status | Planned migration / wave | Acceptance |
|---|---|---|---|---|
| F21 | Service/staff/location/duration selection, first availability and invalidated steps (E06) | Partial: manual basic selectors | Restore free flow variants/preselection/first-available behavior, with explicit entitlement limits. W4 | Changing an upstream choice clears stale slots/contact-dependent state; hidden/preselected choices validated by server; no unsupported option looks bookable. |
| F22 | Month/week day-slot calendar and responsive timezone-aware selection (E06/E19) | Partial: accessible week/month views, organizer-timezone dates, native date input and stale-result invalidation are implemented; slot availability remains server-authoritative | Strengthen locale/RTL/mobile behavior and complete calendar boundary rules. W4 | Keyboard-accessible day buttons and navigation; changing a day invalidates slots; server remains the availability authority. Broader locale/RTL matrix remains outstanding. |
| F23 | `[wap_widget]` flags, filtered/preselected embeds, multiple instances and floating/popup modes (E07/E15) | Partial: supported flags/preselection, filter, unique instance IDs and a basic native widget are implemented; random/staff-page flags and full designer/preview remain unsupported | Normalize supported attributes into typed embed configuration and add basic native widget parity. W4 | Supported embeds resolve catalog relationships; instances remain isolated; unknown/unsupported flags are warned about. |
| F24 | Native WP widget, shortcode designer/generator, booking page/editor previews (E05/E07) | Partial: basic native WordPress widget is registered with title, button text and floating option; designer/editor previews remain missing | Extend native widget settings and implement preview only after parity requirements are characterized. W4 | Existing sidebar settings load; generated embed equals preview; editor/frontend assets do not collide. No assumption of a new Gutenberg block from generic references alone. |
| F25 | Legacy `[wappointment_page]`, `appointmentkey`, `view` routes and saved management links (E07) | Partial: shortcode alias, query-key links, reschedule prefill and ICS download are supported; cancellation remains explicit user confirmation | Keep opaque keys intact and route existing pages through the new widget. W1/W4 | Legacy query links load only the matching appointment; rescheduling preloads its selection; invalid/expired links reveal no private record. |
| F26 | Customer `[wap_history]` and staff `[wap_staff_history]` (E08) | Partial: scoped history repositories, typed UseCases and shortcodes are implemented; generic management links do not expose separate time-gated actions | Add customer-owned and calendar-owned history queries, shortcode adapters and scoped management links. W3/W4 | User sees own history only; logged-out/empty cases safe; labels/timezones are correct; action availability and meeting-link timing still require parity work. |
| F27 | Logged-in autofill, forced account email and customer timezone data (E06/E08/E09) | Partial: authenticated name/email/phone prefill and forced-email read-only UI are implemented; timezone preference persistence is not | Add privacy-safe auth defaults and saved timezone preferences. W4 | Cannot submit another email when forced; anonymous flow works; returning data is not overwritten/leaked; saved timezone choices remain outstanding. |
| F28 | Appearance, text/translation customization, terms/privacy links and conditional modality content (E05/E06/E15) | Partial: supported layout flags and title/button text exist; legacy style/text settings, terms/privacy and modality-specific details remain | Migrate stored widget settings; preserve sanitized custom labels/style and conditional content. W4 | Existing custom text/style survives; HTML/tag content is safe; unrelated location/video/phone details never bleed into another modality. |
| F29 | Confirmation details, add-to-calendar links/downloads and timed meeting visibility (E02/E06/E12/E15) | Partial: status/time/management link and a UTC ICS download are implemented; viewed appointments lack complete provider details and timed meeting-link presentation | Restore complete confirmation/view data and join-link visibility rules. W4/W6 | ICS includes booked duration and stable UID; full timezone/location/provider details and private meeting-link visibility still require parity work. |
| F30 | Localization, RTL, date formats and accessibility across all screens (E06/E15/E19) | Partial: React translation calls, focus return, Escape close, and live error announcements are implemented; locale/RTL matrix remains | Replace old localization dictionaries through a documented migration/translation map; complete accessible shared components. W4/W7 | Locale/RTL/date-format matrix; translated custom content preserved; dialog focus/escape/return-focus works; errors announced; no reliance on color alone. |

### E. Notifications, jobs and integrations

| ID | Feature and evidence | v3 status | Planned migration / wave | Acceptance |
|---|---|---|---|---|
| F31 | Confirmed/pending/canceled/rescheduled customer and organizer notifications (E02/E10) | Partial: only basic synchronous customer confirmation | Domain event -> durable job -> delivery Actions; preserve per-event toggles and recipients. W5 | One intended notification per transition; confirmation versus approval distinguished; failures observable without rolling back an already-saved booking. |
| F32 | Reminder editor, publication/locking, preview, tags, links, HTML/plain text and attachments (E05/E10/E15) | Missing | Reminder CRUD/preview UseCases, template Data and renderer; migrate `lang`/`parent` relationships and per-service/modality rules. W5 | Existing templates render with equivalent vocabulary; timing/publication respected; malformed templates fail visibly; previews use safe fixture data; links and ICS valid. |
| F33 | Reminder timing and daily/weekly summaries (E10/E11) | Missing | Typed scheduled jobs and timezone-aware schedulers with duplicate suppression. W5 | Frozen clock/DST cases; canceled/rescheduled bookings invalidate stale jobs; recipient preferences respected; no duplicate summaries after worker retry/restart. |
| F34 | Queue worker, retries/failed jobs, reservation recovery, cron and pending cleanup (E11/E14/E16) | Missing: Jobs listing is not a worker; jobs table not installed by booking bootstrap | Native WP-cron trigger plus durable repository-backed job dispatcher, restricted job-type registry and operational UI. W1/W5 | Concurrency-safe claiming; timeout recovery; bounded retry/failure state; unsupported legacy jobs quarantined/reported; no arbitrary class execution or dropped pending reminders. |
| F35 | Mail configuraCustom rewrite routetion, sender/footer/logo, HTML/plain text and test email (E05/E10/E11/E15) | Partial: basic `wp_mail` only | Transport adapter inventory from actual mail configuration; port core-supported choices, retaining WP mail plugin interoperability. W5 | Each advertised transport is exercised on staging; credentials protected; test-email failures clear; attachment/HTML preferences and sender options honored. SMTP/Mailgun-like config keys alone are not runtime certification. |
| F36 | Incoming ICS feeds: connect/validate/refresh/disconnect, logs and recurring external events (E03/E13) | Missing: configured sync currently blocks booking | Restore URL import with recurrence/timezone parsing, bounded fetches/change detection and availability invalidation. W6 | Recurring/changed/deleted/all-day external events tested; unchanged feed not duplicated; refresh/disconnect releases correct intervals; failure/staleness policy explicit. This is not provider OAuth write-back. |
| F37 | Outgoing appointment ICS/export and external calendar subscriptions (E07/E12/E13) | Missing/Boundary | Restore confirmed core download/export behavior; establish which subscription/write-back paths belong to remote service or Syncal. W6 | Import into calendar client; identifiers/timezones/escaping correct; authorized feed access and canceled/changed events handled; no false claim of two-way sync. |
| F38 | Connected Wappointment account and automatic meeting/provider synchronization (E01/E13/E17) | Missing/Boundary: core contains connection routes/UI | Explicit remote-service adapter and capability inventory using an authorized test account after free/paid verification. W6 | Connect/disconnect/refresh/provider failure/revoked access tested; no fictitious meeting URL; booking persistence and retry behavior defined. Do not assume Zoom/GoogleMeet are paid-only from names or UI injection points. |
| F39 | Jitsi/local links and manual meeting instructions (E12/E15) | Partial: static other-link/location instructions only | Port local Jitsi behavior and timing/confirmation/email integration independently of remote provider provisioning. W4/W6 | Correct configured host/room, safe links, stable appointment mapping and visibility timing; no provider credentials required for local links. |

### F. Orders, consolidation and operational surfaces

| ID | Feature and evidence | v3 status | Planned migration / wave | Acceptance |
|---|---|---|---|---|
| F40 | Core prices/orders, currency/tax, pay-later, mark-paid/cancel/refund and history (E05/E06/E14/E16) | Missing/Boundary: schema is preserved and appointment deletion unlinks order items, but there is no v3 order listing or lifecycle API/UI | Characterize stored amount/currency/tax semantics and implement safe core order reads and pay-later transitions; provider refunds remain unavailable unless a real provider integration handles them. W1/W3/W4, paid integration after W8 | Existing order/price/appointment relations and amounts retained; integer minor-unit/currency/tax semantics tested; no gateway refund simulated as success. |
| F41 | Paid services, multiple durations/prices and richer service-specific options | Boundary | Dedicated Services-repository audit after free parity; move its actual behavior to the single Premium plugin, not legacy bootstrap code. W8 | Services-only Premium activates alongside free; free alone remains complete; deactivation retains data and displays unsupported configurations explicitly. |
| F42 | Staff expansion, groups/capacity, packages, payments, Syncal, SMS, WooCommerce and Mailchimp (E18) | Boundary: legacy paid boot hooks disabled | Separate feature/repository audits and future milestones; no automatic inclusion in first Premium. Preserve data/dependencies now. W0/W1; implementation deferred beyond W8 | Entitlement inventory explicit; free slot engine never ignores capacity/package/payment constraints; unsupported paid sites do not accept misleading bookings. |
| F43 | Add-on discovery/install/activate/licensing/catalog/renewal UI (E05/E18) | Replace | Remove add-on-product concept in favor of one Premium integration surface. Compatibility notices/readiness replace unsafe legacy execution. W7/W8 | No legacy add-on is loaded by accident; no separate add-on marketplace implied; old data not deleted; licensing/update enforcement remains deferred. |
| F44 | Help/contact, health, feedback, update notices and diagnostics/reset endpoints (E01/E05/E17) | Partial: development/error displays only | Keep useful diagnostics/support; make remote feedback explicit; replace destructive debug routes with deliberate guarded maintenance or omit with documented rationale. W7 | Authorized health output excludes secrets; destructive reset cannot run accidentally; no network feedback without intended user action; no dropped support-critical failure details. |

## 4. Ordered implementation waves and real dependencies

These are delivery slices, not calendar estimates. Each has a demonstration and tests.
There is no defensible time estimate until W0 fixtures establish the difficult cases.

| Wave | Deliverable | Prerequisites | Feature coverage | Exit demonstration |
|---|---|---|---|---|
| W0 | Characterization suite and ownership/configuration ledger | None | Baseline for F01-F44, especially F05/F17/F20/F38/F40-F42 | For each feature: legacy evidence, free/paid/remote owner, input fixtures, output/side effects, target test and disposition. |
| W1 | Schema-preserving cutover, resource authorization, persistence/job foundations | W0 schema/permission fixtures | F01-F03, F06, F18/F25/F34/F40/F42 data foundations | Read-only preflight and staged cutover on the current schema; before/after schema equality; correct scoped access; no data loss. |
| W2 | One complete availability/reservation/lifecycle core | W0 behavior fixtures; W1 repositories and policies | Booking-related F05 settings; supported free behavior in F07-F11; unsupported rich F17/F20 records fail closed | Differential slot/lifecycle suite passes; recurring availability fixtures book correctly; stored Services conditions stay blocked until W8; parallel conflicts handled. Group-series appointments (F12) remain paid roadmap scope; admin lock/delete workflows (F13) are delivered in W3. |
| W3 | Operational administration APIs and admin calendar | W1 + relevant W2 operations | F09/F12-F19/F26; F40 read/admin core | In progress. F13 and the first F14-F16 slice are implemented and covered. W3 is not complete: F12 remains the approved paid Group deferral; F17-F19 are partial; F26 and F40 v3 history/order surfaces are still missing; F14-F16 have the gaps stated above. Do not claim the exit demonstration until those items are implemented or explicitly re-scoped. |
| W4 | Customer workflow, embedding/history and full free configuration UX | W2; W3 APIs for admin/history features; W1 legacy-link resolver | F01/F04/F17-F30; F39 instructions; free F40 UX | In progress. Month/week navigation, signed-in prefill, supported shortcode/native widget embeds, customer/staff history, query-key legacy links, reschedule prefill and basic ICS are implemented. F17-F20 configuration parity, F21 first-available behavior, full F23-F30 parity and end-to-end existing-page evidence remain. |
| W5 | Durable notifications, reminders, summaries and operational queue | W1 queue/storage; W2 lifecycle events | F31-F35 | Booking/approval/change/cancel/reminder/summary flows survive worker restart without duplicate effects. Can proceed alongside W3/W4 after prerequisites. |
| W6 | Calendar and meeting integrations | W1 secrets/config; W2 availability/lifecycle; W5 delivery/event foundations where needed; W0 entitlement decisions | F29, F36-F39 | Staging feed/provider scenarios pass including outages and revocation; no unsupported provider assumed to work. |
| W7 | Free parity certification and upgrade release candidate | W1-W6 | F04/F06/F30/F43/F44 and all free acceptance criteria | Representative current-schema v2 sites cut over without schema changes; links/jobs/permissions preserved; no unexplained parity gaps. |
| W8 | Services-only Premium | W7; dedicated Services source audit | F41/F43; only explicitly approved dependencies | Clean free+Premium install and upgrade from legacy Services; no changes to unrelated paid repositories. |
| Later | Remaining paid consolidation | Individual audits, product approval, dependency ordering | F42 | Per-feature releases into the same Premium plugin; package/WooCommerce overlap resolved before porting it. |

Dependency graph:

```text
W0 -> W1 -> W2 -> W3 --------\
                 |           -> W4 ----\
                 -> W5 ---------------> W7 -> W8 -> later paid milestones
                 -> W6 (also needs    /
                    relevant W5 work)/
```

W0's characterization cases must be checked against `master` on a separate fixture
environment. Source inspection is the basis for this plan, not a substitute for that
runtime oracle. Provider-dependent cases need explicit staging credentials/consent
and cannot be certified by local mocks alone.

### First implementation slice after approval

Do **W0/W1 first**, rather than building more isolated UI:

0. Inventory runtime versus development-only PHP dependencies and any bundled library
   code. Retain the current Pest tests and Composer development setup. Define an
   explicit release-file allowlist and verify that packaged code never references
   Composer/vendor files or development-only classes.
1. Generate a normalized legacy route/config/permission ledger from E01/E15/E17.
2. Capture the authoritative current schema and synthetic representative records.
3. Freeze clock/timezone and record legacy availability/lifecycle outputs for W2.
4. Add read-only schema preflight and resource-ownership policies.
5. Only then complete availability and lifecycle, followed by the full admin calendar.

This makes "feature migrated" measurable and prevents further baseline screens from
being confused with parity.

### Implementation progress: schema foundation (2026-11-14)

The first W0/W1 slice is implemented; the waves as a whole remain incomplete:

- [MasterSchema](../app/Database/MasterSchema.php) describes all 16 core tables from
  the audited migrations, including the migration repository, legacy timestamp/text
  types, pivot IDs, indexes and foreign keys.
- Fresh installation uses that schema, protected by a site/database-specific lock.
  Existing compatible tables are left unchanged; partial/incompatible installations
  are refused rather than silently completed, altered or reset.
- [Schema inspection](../app/Actions/Database/InspectDatabaseSchemaAction.php) reads
  metadata only and returns typed issues. The protected `/system/schema` API and
  **Wappointment > Diagnostics** screen show the result without a repair operation.
  Unknown extension tables remain untouched and are not certified.
- The obsolete `3.0.0` development marker is replaced by the audited schema reference
  after verification. Other API operations return `503 wappointment_schema_not_ready`
  until verified; Diagnostics remains available. Earlier baseline test databases can
  be incompatible and are deliberately not auto-converted.
- Tests cover schema defaults/types/indexes/relations, unchanged records/DDL, corrupt
  or partial state, metadata errors, installation/marker failures, route permissions,
  failure-lock release, and actual multisite blog-prefix switching. The Diagnostics
  screen was also exercised in a browser on the isolated fixture.
- Existing booking regressions pass on a fresh master-shaped schema. This is not
  yet a runtime golden-master comparison or a populated-v2 upgrade certification.
- Validation for this slice: 205 PHP unit tests; 35 single-site schema/booking
  integration tests (the multisite-only case skipped there); 14 schema integration
  tests on real multisite; 18 frontend tests; production bundles and PHP 8.2 syntax.

### Implementation progress: appointment permissions (2026-11-14)

The next bounded F03/W1 slice is implemented without changing the database schema:

- [AccessPolicy](../app/Auth/AccessPolicy.php) is shared by protected REST routes,
  admin menus/rendering and appointment Actions. Administrators (`manage_options`)
  and existing `wappointment_manager` users have site-wide management access; the
  old `switch_themes` heuristic is deliberately not a booking-management grant.
- Staff with `wappo_calendar_man` may list appointments only through their own
  non-deleted `calendars.wp_uid` associations. Filtering occurs in SQL before the
  latest-100 limit and includes history on unpublished calendars.
- Confirmation/cancellation additionally require their respective
  `wappo_calendar_confirm`/`wappo_calendar_cancel` grants. Ownership is checked inside
  the locked transaction; non-owned or missing appointments return 404, with no write.
- Missing legacy staff/manager roles are registered through an Action/UseCase.
  Existing role and individual capability grants/denials are never reset.
- Staff enter Appointments rather than the inaccessible Jobs screen. Server page
  guards, allowed-page bootstrap and separate React action flags agree with the
  policy; stale UI grants never bypass server checks.
- Active-user capabilities and appointment prefixes are resolved at call time.
  Real multisite tests reuse the same objects across switches and verify same-ID
  collisions and writes cannot cross blog boundaries.
- Validation: 207 PHP unit tests; 50 single-site schema/booking/permission integration
  tests (two multisite-only cases skipped); 30 schema/permission tests on real
  multisite; 25 frontend tests; production bundles and PHP 8.2 syntax. Browser checks
  confirm the staff page loads and direct Clients access returns 403.

### Implementation progress: staff clients (2026-11-14)

The next F03/W1 slice restores scoped customer management:

- Client routes and Actions share `AccessPolicy`: `wappo_clients_man` grants listing,
  with `wappo_clients_edit`/`wappo_clients_del` required for mutations. This capability
  tree is independent of `wappo_calendar_man`; users with client-only grants enter
  Clients instead of an inaccessible Appointments page. Creation remains manager-only.
- [ClientRepository](../app/Repositories/ClientRepository.php) scopes the SQL list,
  search and pagination count through owned-calendar appointment relations before
  pagination. It searches name/email/options, escapes wildcard characters and does
  not duplicate clients with multiple appointments. Unpublished calendars and
  canceled historical appointments still establish the legacy client relationship.
- Updates and deletion check/lock the client and ownership relation within the
  existing booking transaction/lock. Unowned/missing/deleted IDs return 404.
  Duplicate emails return an explicit 409; no-op updates succeed and database
  failures are logged server errors, not successful empty lists.
- Shared-client semantics match master: either related authorized staff member may
  edit or soft-delete the shared record. Deletion retains the client row/appointment
  references and hides it from lists on every calendar. Successful repeat booking
  restores the same ID without overwriting existing private fields; failed booking
  rolls restoration back.
- Menus/bootstrap/React controls expose separate create/edit/delete grants. Failed
  saves keep the form open with its values and server error. The options editor now
  displays/validates object JSON rather than coercing it to `[object Object]` or
  silently defaulting malformed stored JSON to an empty object. Legacy empty `[]`
  option maps open as `{}`; new/edited empty maps persist as JSON objects.
- Validation: 207 PHP unit tests; 62 single-site integration tests (two multisite-only
  cases skipped); 40 real multisite schema/permission tests; 37 frontend tests;
  production bundles and PHP 8.2 syntax. Multisite reuses the same client Action and
  repository across switches, including identical IDs and isolated mutations.
  Browser checks exercise scoped listing, restricted controls and a real staff edit.

### Implementation progress: own weekly settings (2026-11-14)

The next F03/F05/F18 slice adds **My availability**, not unrestricted staff access
to the global Settings API:

- Dedicated GET `/me/calendars` and POST `/me/calendars/{id}/weekly-settings`
  operations follow Controller -> UseCase -> Action, with distinct typed request/
  response Data and nested weekly settings. No schema or runtime dependency change.
- `wappo_self_man` permits own-settings viewing; `wappo_self_weekly` additionally
  permits changing organizer timezone, booking horizon and weekly hours. This
  capability tree is independent of appointment/client management. The menu and
  direct page guard use the same policy; a self-only user enters My availability.
- [OwnCalendarSettingsRepository](../app/Repositories/OwnCalendarSettingsRepository.php)
  selects all non-deleted calendars owned through `wp_uid`, including unpublished
  calendars. `/me` always means the current actor, even for managers. It never uses
  caller-supplied user IDs or a global active-calendar option.
- Saves lock and recheck the owned row inside the booking transaction, merge only
  `options.timezone`, `options.avb` and `options.regav`, and retain other option
  objects/lists, keys, private fields, legacy caches and relationships. Ownership
  failures return 404; database failures log and roll back; no-op saves succeed.
- Responses project only supported weekly fields. Conditional or unsupported
  existing schedules have `settings: null` plus an explicit migration problem and
  cannot be overwritten (409). Corrupt JSON is a server error, not empty defaults.
  Missing options use the same UTC/60-day/no-hours defaults as the current engine;
  no old staff usermeta is silently imported or rewritten.
- [WeeklyAvailabilityEditor](../resources/js-react/components/WeeklyAvailabilityEditor.jsx)
  is shared with Booking setup to preserve range/midnight behavior. Own calendar
  forms are independent, show view-only permissions and preserve input after failed
  saves. Empty assignments and unsupported calendars have actionable states.
- Tests save weekly options and then exercise the public availability API: timezone,
  weekly-hour and booking-horizon changes affect slots, while stored appointments
  remain unchanged. Real multisite reuses the same Actions/repository across blog
  switches, including identical calendar IDs and isolated settings mutations.
- Validation: 228 PHP unit tests; 72 single-site integration tests (two multisite-only
  cases skipped); 49 real multisite schema/permission tests; 44 frontend tests;
  production bundles and PHP 8.2 syntax. A self-only browser user loads the page,
  saves and reloads a changed horizon, and sees a conditional calendar as read-only.

**Still pending within F03:** staff profile/service/publishing/provider settings,
history and remaining calendar actions. Global Settings, Booking setup, Jobs and
Diagnostics intentionally remain administrator/manager-only. These slices do not
claim complete staff parity.

Still pending in W0/W1: full setting/capability/entitlement ledger, remaining legacy
runtime availability/lifecycle characterization, remaining resource policies, populated-site
fixtures, complete multisite lifecycle, pending-job cutover and release packaging.
No Premium feature implementation has started.

### Implementation progress: first legacy runtime oracle (2026-11-14)

The first bounded F07/F08/W0/W2 differential slice now executes source-pinned
legacy engine methods instead of treating v3 tests as a legacy oracle:

- [Weekly capture runner](../tests/Characterization/capture-legacy-weekly.php)
  executes the audited master's unmodified weekly generation and Segment
  union/subtraction over synthetic inputs with a frozen Carbon clock. Reflection
  bypasses only the database-backed Availability constructor; this is not a
  complete WordPress bootstrap or database/lifecycle test.
- [Slot capture runner](../tests/Characterization/capture-legacy-slots.cjs)
  executes the original DaySlots conversion/increment methods over captured PHP
  intervals. Source hashes are verified; golden input hashes and byte-identical
  regeneration are checked. Legacy dependencies live in a separate fixture,
  never in v3 runtime or packaging.
- Fifteen weekly cases cover unmarked/explicit hours, precise minutes (including
  small midnight ranges), actual legacy fractional-hour truncation, Paris DST
  dates/transition-spanning windows, midnight endpoints, busy interval splitting,
  extra free time and busy-over-free precedence. Four duration/buffer/spacing
  configurations produce 60 differential slot cases.
- Fixed three measured discrepancies: stored schedule precision, default spacing
  by duration plus buffer unless `more_st` is enabled, and spacing anchored at the
  start of each remaining free interval after busy subtraction. The shared
  [WeeklySchedule](../app/Booking/WeeklySchedule.php) projection is used by public
  availability and both admin settings surfaces. Explicit saves write minutes
  with `precise: true`; reads/bookings do not migrate options or alter schema.
- User-approved ambiguity policy: unmarked/false precision follows master and
  means hours. Earlier unreleased v3 builds wrote unmarked minutes; values above
  24 are refused, not inferred or automatically converted. Smaller unmarked
  values remain hours. These test calendars require origin review; both editors
  protect unsupported schedules from overwrite.
- Disposable real-WordPress tests verify exact public slots, unchanged options
  on reads/bookings, both editor projections/saves, private data retention, and
  unchanged booking/cancel/reschedule/approval/concurrency behavior. Multisite
  checks continue to cover site-specific ownership and weekly-setting writes.
- Validation: 305 PHP unit tests on PHP 8.2, including 60 captured-master slot
  comparisons; byte-identical regeneration of both golden files; 78 single-site
  integration tests (two multisite-only skips); 50 real multisite tests; 44 frontend
  tests; PHP 8.2 and Node syntax checks, plus whitespace validation.

This does **not** close F07/F08 or W0/W2. Next legacy-oracle coverage must include
minimum notice/fluid rounding, booking-horizon boundaries, recurring/conditional
availability and real database lifecycle/limits/notification side effects. Existing
v3 lifecycle tests are regression evidence, not proof of matching legacy behavior.
No populated-v2 cutover or full admin-calendar/Premium implementation is claimed.

### Implementation progress: minimum notice and booking horizons (2026-11-14)

The second F07/F08/W0/W2 differential slice characterizes server and frontend
minimum-start behavior together:

- The source-pinned PHP runner exercises legacy `Availability::daysRegenerating`
  and `generateAvailabilityWithRA` with frozen Carbon, representative schedules,
  and exact master `Settings`/`Availability` hashes. Cases include notice hours and
  fractional minutes, fluid/non-fluid windows, windows closed by notice, overnight
  cutoffs, day horizons, the refresh hour, and a Paris DST day boundary.
- A second source-pinned Node runner executes the original `DatesExtended`,
  `Calendar`, `IncrementTime` and `DaySlots` methods with a small deterministic UTC
  clock shim. Seventeen cases across five spacing/buffer scenarios produce 85
  comparisons of final slot starts, checked directly against v3. Combined with the
  earlier 60 comparisons, this slice now has **145 legacy differential cases**.
- Restored master behavior: fractional notice is truncated to whole minutes;
  fluid starts round up to the next five-minute boundary; non-fluid starts advance
  from the schedule's first start using duration (custom step when enabled), while
  ordinary slot progression uses duration plus buffer. Calendar horizons end on the
  complete local date `now + N days`; before the configured refresh hour, one day is
  withheld, becoming available at the configured hour. Calendar-day addition is
  timezone/DST aware. New v3 output returns no slots before today or after the
  applicable horizon date.
- The candidate availability engine fails closed for invalid stored booking-day
  counts, notice values and refresh hours; the settings RequestData now also caps
  `refreshavb_at` at 23. Schema and runtime dependency sets remain unchanged.
- Public WordPress integration verifies fluid same-day starts, refresh-hour/day
  horizon behavior and unchanged stored calendar options. Frozen golden comparisons
  verify the exact minute and DST boundaries. Existing transaction, cancellation,
  rescheduling, approval, role, ownership and multisite tests remain regression
  checks, not legacy lifecycle comparisons.
- Validation: 400 PHP unit tests on PHP 8.2, including 85 notice/horizon and 60
  weekly/spacing differential cases; byte-identical golden regeneration; 79
  single-site integration tests (two multisite-only skips); 50 real multisite tests;
  44 frontend tests; production bundles and PHP/Node syntax checks.

### Implementation progress: recurring busy/free status expansion (2026-11-14)

- The booking repository now expands persisted daily, weekly, monthly and yearly
  busy/free statuses into the requested calendar window, honoring the stored origin
  timezone, interval, `BYDAY`/`BYMONTHDAY` shape, `until` and excluded dates. It uses
  native PHP date/time APIs; no runtime Composer dependency or schema change was added.
- Malformed recurrence data fails closed with a conflict instead of silently opening
  slots. Conditional status rules still return a conflict; see the finding below.
- Seven frozen-clock fixtures execute the unmodified master `Status::expand` method
  against a minimal cast-on-read model shim, covering daily intervals/exclusions,
  weekly weekdays, a DST transition, monthly day/ordinal-weekday rules and leap-year
  rollover, including legacy month-end overflow behavior. A disposable WordPress API
  integration test verifies recurring busy blocks remove slots and recurring free
  blocks add them in the calendar timezone.
- This restores slot projection only; it does not add status CRUD, the admin calendar
  editor, imported-calendar lifecycle/synchronization, or conditional availability.
- Validation: 413 PHP unit tests (2,447 assertions) and 80 single-site integration
  tests passed; two multisite-only integration tests were skipped. PHP 8.5 reports
  existing `ReflectionMethod::setAccessible()` deprecations during the unit run.

### Finding: availability conditions are Services add-on behavior (2026-11-15)

Earlier revisions of this plan listed conditional availability as free W2 work. A
source audit shows that is wrong; no condition evaluation was added to free.

- **Free master never evaluates conditions.** `Availability::generateAvailabilityWithRA`
  reads only elements 0 and 1 of a weekly range, free statuses are consumed without
  reading `options`, and the only core reference is a delete-popup label in
  `CalendarAdmin/MixinSelection.vue`. `Calendars::regavClean` preserves a third element
  untouched. This comes from reading the audited source, not from an executed oracle.
- **The Services add-on owns the feature** (`wappointment-services` master
  `580383c4012b65c0885bd27ab839b81e8643c8ee`; its `feat/v3.0.0` currently points at
  the same commit). It writes `regav[day][n][2] = {locations: [ids], services: [ids]}`
  only when at least one list is non-empty, stores `options.conditions` on free
  statuses plus a per-staff `punctual_conditions` option, filters slots in its own
  `BookingForm/Calendar.vue`, and validates bookings in `BookingValidateConditions`.
  A slot is bookable when no condition covers it, or when one covering condition
  accepts both the location and the service; an empty list accepts any value.
- **W8 must characterize, not copy, the server matcher.** Its weekly test compares
  hours and minutes independently, its second-day branch ignores minutes, and its
  punctual list is pruned by wall-clock time on read. Decide per defect whether
  Premium reproduces or corrects it, with fixtures.
- **Free v3 keeps blocking stored conditions**, following the agreed rule to block
  unsupported paid rules rather than ignore them. This deliberately differs from
  free master, which would offer restricted windows to every service. A conditional
  weekly range blocks the whole calendar and makes My availability read-only; a
  conditional status blocks only the days it overlaps. Both are tested, and stored
  data is never rewritten.
- **Consequence for upgrades:** a v2 site that used Services conditions cannot take
  bookings on the affected calendar or days under free v3 alone. The preflight
  report must list these calendars, and Premium release 1 must include condition
  evaluation before such sites are cut over.
- Validation: 81 single-site integration tests passed (two multisite-only skips),
  including the new status-condition case.

### Implementation progress: booking lifecycle characterization (2026-11-15)

- Added a 38-scenario database-backed oracle against audited master
  `fcf2cfe296a240421ed91f28ff3672ece04e4786`. The shared case hash and five PHP
  source hashes are pinned; the compact fixture normalizes generated IDs and
  timestamps and reproduces byte-for-byte on the isolated `legacy_oracle` WordPress
  stack. Cron is disabled and queued jobs are recorded without sending mail.
- The fixture covers automatic/manual approval, buffers, overlap/adjacency, booking
  limits, cancel/reschedule deadlines and disabled controls, repeated changes,
  returning/deleted clients, public view, and state/jobs after each step.
- Source-observed differences are documented rather than copied blindly: master
  accepts some off-grid bookings and past/beyond-horizon reschedules; rescheduling
  onto an occupied slot can fail at the database uniqueness constraint. V3 validates
  those selections before writing. Master overwrites a returning client's name and
  deletes canceled appointment rows; v3 intentionally preserves existing client
  data and canceled history.
- V3 `ChangeBookingAction` now increments the stored reschedule sequence while
  preserving the booking's original buffer. Integration coverage confirms pending
  bookings remain cancelable after the deadline, consistent with master; the
  existing soft-cancel state remains for v3 history.
- The lifecycle fixture and unit suite check source/input hashes and captured
  transitions without loading legacy libraries. Validation: 111 focused unit tests
  (189 assertions; PHP 8.5 emits the existing `ReflectionMethod::setAccessible()`
  deprecations), 34 booking integration tests (303 assertions), and byte-identical
  lifecycle fixture regeneration.

### W2 implementation progress: free availability and lifecycle core (2026-11-15)

- Added a v3 integration runner over the same 38 legacy lifecycle scenarios. It
  compares success/failure and persisted booking/client state at every step, with
  explicit allowances for documented behavior changes: rejecting off-grid and
  past/out-of-horizon selections, retaining canceled history, preserving returning
  customer data, and rolling back customer rows from failed bookings.
- That comparison found and fixed two lifecycle defects: minimum notice now applies
  to future dates (not only same-day slots), and canceling multiple appointments at
  the same slot assigns distinct negative canceled states to satisfy the unchanged
  unique index while preserving history.
- Added an HTTP-level recurrence test proving recurring busy intervals reject
  bookings and recurring free intervals permit bookings outside weekly hours.
  Recurrence, end dates, DST, overlaps, unsupported Services conditions, transaction
  rollback, active-booking limits and parallel conflicts are covered by the focused
  unit/integration suites.
- W2 is complete for the supported free availability/reservation/customer lifecycle
  scope. F12 remains the paid Group feature; F13 admin lock/delete operations and
  the full calendar editor are W3 work. F05's complete settings ledger, richer
  F17/F20 configuration UX, W1 cutover certification, W5 notifications and populated
  v2 upgrade tests remain open outside this W2 exit.
- Validation: 145 source-pinned legacy slot comparisons; 38 shared lifecycle
  scenarios (235 assertions in the v3 differential integration test); full PHP unit
  suite, booking and permissions integration suites, booking-widget tests and
  production build. PHP 8.5 emits existing `ReflectionMethod::setAccessible()`
  deprecations.

V3 continues to reject pre-notice slots that a legacy browser edge case might expose;
this is an intentional safe validation difference, not an authorization bypass.

## 5. Fixed database schema and cutover contract

The current database schema is the persistence contract. Do not redesign storage
to match the new API. For example, an API can use `calendarId` while its repository
reads/writes the existing `staff_id` column and its established calendar identity.
Dates, enums, nullable values, JSON options and amounts must retain their stored
meaning even when the API presents richer typed objects.

### Storage inventory

Preserve actual site prefixes; `wp_` is not a contract.

| Data | Source / important shape | Planned treatment |
|---|---|---|
| `wappo_appointments` | UTC start/end, type/status/edit_key, client/staff/service/location IDs, options, recurrent/parent; end includes buffer | Preserve IDs/secrets and canceled/pending state; map old staff identities explicitly; do not subtract/reapply buffer destructively. |
| `wappo_statuses` | Busy/free, recur/muted, source/eventkey, calendar ownership and options | Preserve manual/imported provenance, recurrence and uniqueness; migrate parser output only through characterized conversion. |
| `wappo_clients` | Unique email, options/custom data, soft deletes and timestamps | Preserve opaque fields, historic relations and deleted state; never merge clients solely by a lossy normalization. |
| `wappo_services`, `wappo_locations`, `wappo_calendars` | Options, soft deletes, sort/publish state, WP-user mapping, account key and cached availability | Use existing columns/options; distinguish business configuration from recalculable caches; invalidate caches only after readers understand the inputs. |
| `wappo_service_location`, `wappo_calendar_service` | Historical pivot IDs/foreign keys; not necessarily the new composite-primary-key shape | Retain IDs and relationships; fix v3 fresh-install DDL to match the authoritative schema, not the other way around. |
| `wappo_reminders` | Subject/type/event/locked/published/options, language and parent variants | Retain publication/locking and translation links; render existing vocabulary without requiring stored-template rewrites. |
| `wappo_jobs`, `wappo_failed_jobs` | Queue, payload, appointment reference, attempts/reserved/available/created values; failures | Decode known legacy payloads safely into typed jobs in memory; report/quarantine unsupported payloads using existing storage; prevent double sends/overlapping workers. |
| `wappo_logs` | History, type/options, nullable client references | Retain useful history and define retention/redaction; do not expose private log payloads publicly. |
| `wappo_prices`, `wappo_orders`, `wappo_order_price` | Transaction IDs, integer amounts/tax/currency, paid/refunded/deleted state, quantity and appointment links | Preserve even when paid execution is disabled; build read/admin core before provider mutations. |
| Migration history | `wappo_migrations` and schema/version/install flags (E16) | Read existing versions; record cutover state through existing WordPress options if needed, not a new schema; never mark an unsupported cutover complete. |
| Options and usermeta | `wappointment_settings`, widget settings, staff settings, install/wizard/version/cron flags; exact keys via WP helpers | W0 ledger records exact keys/types per generation; preserve extension-owned keys, nested values, stored widgets, WP roles and page IDs. |
| Pages, embeds and secrets | Hidden custom booking page or regular page, shortcodes, widget instances, legacy query links | Preserve page identity/content/URL routing and edit secrets; add adapters, not a requirement to replace customer pages. |
| Add-on-owned tables/options | Not fully audited here | Retain untouched; audit before any conversion; block execution of unsupported paid rules rather than ignoring them. |

There are **28 historical migration files** in the audited core. They include
data imports and relationship changes, not just CREATE TABLE statements.
The [BookingSchema](../app/Database/BookingSchema.php) facade now delegates to the
initialization UseCase/Action. It creates the complete master-derived core schema
only for an empty, unmarked site, or verifies an existing compatible installation.
It is not a historical-schema converter or a pending-job cutover engine.
Historical migrations are evidence of existing storage generations, not authorization
to apply destructive or structural changes as part of this refactor.

Historical-file coverage (all under `database/migrations`, ordered by filename):

| Migration group | Files | Feature coverage / current-schema relevance |
|---|---:|---|
| 2018 base appointments, jobs, failed jobs, statuses, clients, reminders, logs | 7 | F02/F07-F13/F19/F31-F35: foundational columns, queue fields and references. |
| 2019 appointment/status/log alterations | 3 | F02/F10/F19: nullable references and foreign-key behavior. |
| 2020 client alteration | 1 | F19 soft-delete compatibility. |
| 2021 status alteration | 1 | F09/F36 external-event identity and uniqueness. |
| 2021 services, locations, pivot, appointment relations and two data imports | 6 | F02/F17/F20/F41: stored service/modality semantics; imports are historical evidence, not new cutover operations. |
| 2021 calendars, assignment pivot and calendar import | 3 | F02/F03/F18/F42: WP-user/calendar identity, options and assignments. |
| 2021 staff role | 1 | F03 capabilities/roles in WordPress storage. |
| 2021 prices, orders and order-price pivot | 3 | F40/F42 money and appointment/order associations. |
| 2021 order-price alteration | 1 | F40/F42 quantity. |
| 2022 appointment recurrence alteration | 1 | F12 parent/child fields. |
| 2022 reminder language/parent alteration | 1 | F30/F32 translated reminder relationships. |
| **Total** | **28** | No historical migration is implicitly authorized to run during the schema-preserving refactor. |

### Cutover procedure to implement

1. Require backup and read-only preflight with schema/feature/entitlement detection.
2. Compare the installed schema to the supported current schema, including table
   prefixes, column types/defaults, indexes and relationships. Record historical or
   partially migrated layouts as distinct cases, not automatically supported upgrades.
3. Refuse unsupported/corrupt layouts with a report before modifying rows. Supporting
   older layouts through schema changes requires separate approval, outside this scope.
4. Stop/coordinate legacy cron consumers and temporarily gate mutating bookings during
   cutover. Do not allow old/new workers to send the same reminder.
5. Switch to schema-compatible repositories/readers and record resumable cutover state.
   Existing sites must not execute ALTER/DROP/rebuild operations or automatic business
   data imports. Normal authorized application writes still use existing storage.
6. Preserve unknown JSON and provider/add-on data. Adapt representations in memory;
   do not rename stored fields, regenerate secrets or rewrite records to suit DTOs.
7. Check row counts, IDs, key/relationship integrity, representative amounts/times,
   orphan detection, reminder coverage and booking availability before enabling writes.
8. Rebuild derived caches through the new engine and schedule the new worker once.
9. Assert before/after schema equality and record success only after verification;
   show actionable issues otherwise.
10. Keep a tested database/files backup for rollback. Unchanged schema alone does not
    prove that v2 understands every new job payload or option value written by v3.

## 6. Legacy entry-point disposition map

This maps declared legacy routes to feature groups to check **behavioral coverage**.
It is not a compatibility specification: none of these old API paths, request
payloads, response shapes or transport suffixes must be retained.
E01 is authoritative for exact registration, namespaces and permissions.
RESOURCE routes expand into operations, and legacy modern verbs are generally
transported as POST paths with verb suffixes. Design fresh v3 contracts and update
their React/WordPress consumers together.

| Legacy entry points | Migration destination / decision |
|---|---|
| `GET /ping`, `/pingAdmin`, `/health` | F06/F44 health/readiness contracts, no private public diagnostics. |
| `GET /availability`, `POST /convertdate` | F07-F09/F22 typed availability/date behavior under new contracts. |
| `POST /services/booking`, `POST /booking` | F10/F21 one booking UseCase/API contract, no legacy payload translator required. |
| `POST /appointment`; `PATCH /booking`, `/appointment` | F11/F25 view/reschedule/cancel UseCases; preserve stored action secrets/deadlines, not old REST payloads. |
| `POST /order/confirm` | F40 core order-confirm contract, provider boundary separately gated. |
| `/service` POST; `/services` GET/POST/DELETE; `/services/reorder` | F17 free service operations; F41 premium-only expansions after source ownership audit. |
| `/services/location` GET/POST/DELETE; `/services/custom_fields` GET | F17/F20 modality and field definitions with verified free/paid rules. |
| `/currency` GET/POST | F40 currency configuration; used only by supported order flows. |
| `/calendars` GET/POST/DELETE; `/calendars/reorder`, `/calendars/toggle`, `/calendars/avatar` | F18 free organizer/profile/publishing; paid staff-count expansion F42. |
| `/calendars/services`, `/calendars/customfields` GET/POST, `/calendars/permissions` | F03/F17/F18/F20 ownership, assignment and field data; preserve entitlement constraints. |
| `GET /config/calendar`; `/events/list`, `/events` | F14/F15 calendar range/read/create operations. |
| `/services/booking/admin`, `/client/book` | F15 explicit authorized on-behalf booking using the same engine as public booking. |
| `/events/patch`, `/events/put`, `/events/delete`, `/events/forceDelete` | F12/F13/F16 split reschedule/edit/confirm/cancel/delete/force operations; do not infer semantics from HTTP-like suffix alone. |
| `/status`, `/status/delete` | F09 typed busy/free mutation operations, F03 ownership. |
| `/client` GET/POST, `/client/search`, `/client/delete` | F19 clean customer list/search/detail/mutation APIs; pluralization/pagination/fields may all change. |
| `/settings/{key}`, `/settingsstaff/{key}` GET; `/settings`, `/settingsstaff` POST | F05 setting ledger, typed free config operations and scoped staff permissions. |
| `/reminder` GET/POST/DELETE/PATCH, `/reminderpreview`, `/settings/sendtestemail` | F31-F35 reminder/template/transport operations with no synchronous all-purpose controller. |
| `/calendars/savecal`, `/calendars/refreshcalendars`, `/calendars/disconnect` | F36 incoming ICS subscriptions and freshness diagnostics. |
| `/wappointment/connect`, `/wappointment/disconnect`, `/wappointment/refresh` | F38 account/provider integration; establish actual remote behavior/entitlements. |
| `/events/record` | F38 remote recording capability, not assumed free/local/available. |
| `/orders` GET; `/order/refund`, `/order/paid`, `/order/cancel` | F40 core order listing/admin lifecycle, refund execution requires a working provider. |
| `/viewsdata/{key}`, `/wizard`, `/wizardlater` | F01/F04/F05 explicit bootstrap/setup contracts rather than arbitrary key-shaped data dumps. |
| `/wappointment/contact`, `/send_feedback`, `/wappointment/subscribe`, `/wappointment/sendignore` | F44 explicit support/onboarding/remote preferences; verify necessity/consent before port. |
| `/addons` GET/POST, `/addons/check`, `/addons/clear`, `/addons/install`, `/addons/activate`, `/addons/deactivate` | F43 retire add-on marketplace/installer; replace with Premium readiness/incompatibility status. No legacy arbitrary package activation pipeline. |
| `/app/migrate` | F01/F02 explicit preflight/upgrade workflow with permission and recovery guards. |
| `/freshinstall`, `refreshcache`, `/updatepage`, `/addmanagerrole`, `/addbigprice` | F06/F44 maintenance outcomes only if needed; destructive/debug semantics are not automatically public product requirements. Document removal/replacement. |
| `[wap_widget]`, WP widget identity `wappointment` | F23/F24 compatible embed adapters and migrated instance settings. |
| `[wappointment_page]`, `appointmentkey` and `view` query routes | F25 legacy link resolver and ICS/add-event pages, separate from REST routing. |
| `[wap_history]`, `[wap_staff_history]` | F26 authorized history UseCases and rendering. |
| Legacy admin `wappointment_calendar`, `_orders`, `_clients`, `_settings`, `_addons`, `_help` and Vue routes | F04 redirect/map to supported React screens; `_addons` becomes Premium/readiness, not a marketplace. |
| `wappointment_process_daily`, `_every_hour`, `_every_5min`, `_every_min` | F33/F34/F36 explicit scheduled handlers, no duplicate legacy and v3 consumers. |

No legacy REST aliases or wire-shape compatibility layer are planned. Test the new
contracts directly. Stored public booking links, shortcodes and page identities
remain separate user-facing concerns: they can resolve into the new frontend/API
without recreating the old REST contract. Data repositories must continue to use
the current database schema regardless of endpoint design.

## 7. Free/Premium boundary and replacement decisions

### Preserve in free when verified as legacy free behavior

Booking and management, organizer configuration, calendar administration, availability,
customer management, supported form fields, notifications/reminders, appearance,
embedding/history, local meeting links and core calendar import/export.
Connected remote-service behavior must be audited by actual free entitlement, not
removed solely because a provider is named Zoom/Google/Microsoft.

Free baseline query limits (currently first few services/calendars) are implementation
guards, **not the specification of the commercial limits**. W0 must establish exact
legacy gates before W2/W4 implement supported counts and selectors.

### Premium release 1

Only the former Services feature set and explicitly necessary dependencies, audited
from `wappointment-services` on its `feat/v3.0.0` branch. Define a small feature
registration/entitlement interface that free uses without depending on Premium
classes. Premium registers focused Actions/UseCases/routes/components; it must not
restore the old add-on boot machinery.

### Later paid roadmap, not part of free parity

| Legacy package | Required future audit / migration question |
|---|---|
| Services | Exact service/duration/pricing/custom-field expansions, service/location availability conditions, and overlap with free schemas. Initial Premium scope. |
| Staff | Multiple staff/calendars, assignment and permission expansions; retain free own-calendar behavior. |
| Group | Capacity/multi-attendee semantics, shared slots and concurrency; do not treat group appointments as ordinary single bookings. |
| Packages | Ticket/credit redemption, balances, expiration and order linkage. |
| WooCommerce | Product/order/refund bridge and package duplication; choose a shared entitlement ledger before porting overlapping package behavior. |
| Stripe / PayPal | Gateway lifecycle, webhook verification/idempotency, refunds and reconciliation. |
| Syncal | Establish difference between core read-only ICS/account connectors and paid provider synchronization. |
| Twilio | SMS templates, consent, phone normalization, retries and delivery callbacks. |
| Mailchimp | Subscription consent, field mapping and synchronization. It appears in the core add-on manifest even though not in the original main package list. |

This audit covers the free-plugin `master` and its integration seams, **not a full
implementation audit of each paid repository**. Those need their own scoped audit
before dates or exact Premium behavior can be promised.

### Deliberate replacements requiring documented behavior

- Add-on storefront/licensing/install UI -> single Premium readiness/product surface.
- Multi-method controllers and Laravel containers/ORM -> typed operation-based stack.
- Vue router/build chunks -> React screens while preserving user links/workflows.
- Legacy serialized-job decoding -> known typed job registry, without changing the
  jobs table or blindly executing a class named by an unvalidated payload.
- Destructive debugging shortcuts -> guarded maintenance or explicit retirement.
- No requirement to preserve a legacy defect: any correction changes a characterized
  expectation only with an explanation and regression test, not accidental drift.
- OpenAPI export remains optional follow-up. Generated Data schemas help but do not
  describe cross-field validation hooks or external side effects automatically.

## 8. Verification and release gates

### Characterization and migration fixtures

Build synthetic/anonymized fixtures for:

1. Fresh install matching the current 2.7.7 schema; populated current-schema sites;
   older/partial schema fixtures that must fail read-only preflight explicitly.
2. Existing staff usermeta/current calendar relationships, custom prefixes and multisite.
3. Soft-deleted clients/services/calendars; missing WP users; orphaned relation detection.
4. Pending/confirmed/canceled/locked/recurring bookings with nonzero buffers.
5. Weekly split ranges, precise/conditional availability, recurring manual and imported
   statuses, all-day ICS, DST gaps/overlaps and different browser/organizer timezones.
6. Every retained setting/widget/shortcode variant and old action-link shape.
7. Outstanding reminders/jobs/failed reservations, translated templates and old payloads.
8. Paid configuration present with the corresponding code inactive; free upgrade must
   preserve it and clearly reject unsupported operations, not quietly ignore its rules.
9. Historical order totals/currencies/refunds/quantities and package/Woo references.
10. Interrupted cutover, installation failure, provider timeout and DB write failure;
    no schema-changing statements on a populated current-schema installation.

No fixtures may contain production credentials or real customer personal information.

### Test layers

- **Packaging/dependency gate:** no external runtime packages in `require`; Composer
  `require-dev`, its transitive packages and Pest may remain in the development checkout.
  Inspect each built release ZIP to ensure it excludes `vendor/`, Composer manifests/
  lockfiles/generated autoloaders, tests and development tooling, with no bundled
  external-PHP-library workaround. Install and exercise the actual ZIP on WordPress
  without Composer/vendor available; plugin boot, REST, shortcodes and cron must work.
  `composer install --no-dev` alone is not proof of a clean package.
- **Pure unit:** slot/recurrence/price calculations, cutoff rules, Data/attribute
  hydration, cross-field validation, permission policy and template rendering.
- **Legacy comparison:** same frozen inputs to the legacy and new domain behavior;
  compare slot timestamps, states, durable side effects and normalized user-visible
  results. Differences require an explicit accepted decision.
- **WordPress integration:** new route/status/shape/nonce/permission/ownership contracts,
  before/after schema equality, real persistence, cron and plain/pretty URLs.
- **Concurrency/failure:** competing reservations, admin edits, worker claims, retry
  idempotency, partial DB failures, downtime and interrupted migrations.
- **React/browser:** free wizard -> configure -> publish embed -> book -> approve ->
  notify -> reschedule/cancel -> history; admin calendar/manual booking; mobile/RTL/
  keyboard; old bookmarks; no stale cached JS requirement hidden from upgrade tests.
- **Provider staging:** separate opt-in real ICS/mail/connected-provider tests, with
  observed delivery/provisioning and explicit skip reasons when credentials unavailable.

Current passing unit/integration/widget tests cover the implemented subset. They do
not demonstrate parity; new tests must assert observable legacy requirements rather
than just the new implementation's chosen behavior.
The current `composer.json` declares `pestphp/pest` as a development dependency, which
is permitted. Keep that harness and its test coverage; verify release exclusion rather
than replacing it. Frontend tests may likewise retain npm-based tooling.
See [test instructions](../tests/README.md).

### Definition of done for each feature

1. Named feature ID, source evidence and confirmed free/paid owner.
2. Supported inputs, saved-data forms and side effects documented.
3. Endpoint/controller/UseCase/Data/Action layers implemented consistently.
4. Real UI/shortcode/cron consumer wired; not merely an unused backend endpoint.
5. Unchanged schema and correct repository mapping verified; stored public-link
   adapters covered where needed, without legacy API-shape constraints.
6. Expected output and failure behavior tested, including permissions/ownership.
7. No hidden loss of an existing field/setting/relationship; no simulated integration.
8. Documentation updated and a repeatable demonstration passes.

### Free-release go/no-go

Do not remove the development warning or recommend a production upgrade until:

- All confirmed-free F01-F44 items pass or a specific scope change is approved.
- Unresolved boundary items cannot silently change booking/payment/calendar behavior.
- Representative upgrade and backup/restore drills pass.
- Outstanding jobs and management links continue to function after cutover.
- Free works without Premium or any legacy add-on installed.
- Both release ZIPs pass the packaging/dependency gate and function without Composer
  or external PHP packages; development-only Composer/Pest tooling is not shipped.
- Minimum supported WP/PHP, relevant DB versions and multisite policy are tested.
- Setup, Calendar, Clients, Notifications, Appearance, Integrations and Help have
  coherent user-facing navigation; Jobs is diagnostics, not the primary product screen.

## 9. Confirmed decisions and remaining implementation gates

- **Confirmed:** the audited `master` schema is authoritative, and single-site plus
  multisite support both ship in v3.0. Preserve that schema; fail read-only preflight
  for incompatible layouts rather than changing them without approval. Characterize
  per-site/network activation, blog switching, new-site initialization and removal
  behavior without deleting stored data as a side effect of this refactor.
- Exact free service/staff/duration/custom-field limits and provider entitlements.
  Recommendation: preserve verified existing free behavior and do not infer limits
  from visible Vue components, database columns, or baseline SQL LIMITs.
- New API naming, pagination, response envelope and HTTP-method conventions.
  Recommendation: choose one consistent v3 design and update all consumers together;
  no old request/response compatibility layer. Preserve stored public links/embeds
  separately from wire contracts.
- Remote account/video feature continuity and provider staging access.
  Recommendation: keep the current service contract where supported; no silent swap
  from connected provider functionality to a generic link.
- Legacy job translation, upgrade pause window and retry/delivery guarantees.
  Recommendation: durable known-job translation plus visible quarantine for unknowns.
- Calendar UI library and first-party recurrence/ICS implementation boundaries.
  Recommendation: select frontend components after behavior fixtures/API design;
  PHP parsing and recurrence must use native/WordPress facilities and first-party code,
  not external Composer packages. Define supported rules and explicit failures.
- Handling of genuinely retired/internal diagnostic paths.
  Recommendation: explicit removal/replacement list with safe admin support outcomes.

The remaining items are implementation/research gates, not blockers requiring the
user to design the system. Resolve them from source and tests where possible; request
approval only for product behavior changes or genuinely ambiguous scope. Provider
staging access is needed later for live certification, not to begin local work.
