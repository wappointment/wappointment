=== Appointments Bookings - Wappointment ===
Contributors: wappointment, benheu
Tags: appointment scheduling, appointment booking, appointment booking calendar, appointment booking system, appointments booking calendar
Requires at least: 4.7
Tested up to: 5.4
Requires PHP: 7.0
Stable tag: 1.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Appointment booking system for personal coaches, teachers, therapists and service professionals of all kind

== Description ==

A simple and reliable **[appointment booking system](https://wappointment.com/?utm_source=wp-repo&utm_medium=link&utm_campaign=readme)**, for personal coaches, teachers, therapists and service professionals of all kind.
Get booked 24/7 with a really **intuitive booking form**.

It has been designed for any professional providing services to clients (personal coach, estate agent, language teachers, therapists, health professionals, beauty and wellness professionals, fitness professionals, etc…)

Wappointment is free and will remain **free forever**.

https://www.youtube.com/watch?v=jUkiyejbuzg

== The Benefits ==

= Save Time Automate your Bookings =
Convert visitors into customers with a simple call to action. **Get booked 24/7**, Wappointment is basically a **booking assistant which never sleeps**.

= Avoid Double Bookings =
Keep your availability updated using our powerful centralized system . 
Your availability gets refreshed whenever something change in your schedule: 
    * when a new client books you
    * when a client cancels his appointment
    * when you manually set time slots during which you are busy or free 
    * when a new event gets created on your synched personal calendar (Google Calendar, Ical, Outlook Calendar)

= Simplify your Booking Process =
Our **user friendly booking form** gives your clients a quick overview of your availability, making the booking process a breeze.

= Reduce No-Shows =
Your clients receive **appointment confirmations and reminders**. 
Quickly define when and how many of them do they receive (1 day before appointment, 1 hour before appointment).

== The features ==
* Unlimited bookings
* User-friendly and intuitive interfaces with no coding involved

= Availability Setup as an Admin =
* Set your recurrent availability within seconds
* Set your punctual availability and block your non-bookable time (non working days and hours, busy times, holidays, etc) in just few clicks
* Select the timezone from which you operate

= Appointments Settings as an Admin =
* Manage your appointments through a comprehensive Admin panel
* Define how you provide the appointments: by Phone, by Skype or in Person
* Hand-pick the countries you will allow for a phone appointment
* Change the duration of your appointment
* Set the appointments' approval mode: automatic or manual 
* Set how far in advance an appointment can be booked 
* Allow clients to cancel and reschedule appointments
* Book an appointment on behalf of your customer
* Connect your personal calendar to the booking system and automatically block time where you are busy
* Set the date and time format 

= Make the Booking Widget Blend =
* Quickly customize colors and texts for your booking widget 
* 4-steps booking process, each step is editable

= Booking an Appointment as a Customer =
* Clients book you within seconds, from their mobile phone, tablet or desktop computer
* The available booking slots are displayed in your client's timezone, no more confusion for your international clients
* Client receive a confirmation and as many reminders as you've setup
* Clients can easily save your appointment to their personal calendar

= Appointments' Confirmations Reminders and Notifications =
* Receive email notifications when clients book, reschedule or cancel an appointment
* Receive daily and weekly notifications
* Customize and personalize your confirmations and reminders sent to your clients

= Have a Question? =
Our plugin is free, and easy to install. Try it first :)
And for any question or doubt, you can reach us:
* Straight from the plugin in *Wappointment > Help*
* Here on the [WordPress' forum](https://wordpress.org/support/plugin/wappointment/) 
* From our contact page on [wappointment.com](https://wappointment.com/support?utm_source=wp-repo&utm_medium=link&utm_campaign=readme).

== Frequently Asked Questions ==

**Can I customize the look and feel of the booking form?**

We provide a very simple editor in which you can change the texts, colors and few other parameters of each step of the booking process. We plan on adding several templates in the future, meanwhile simply use css to make it exactly the way you want.

**Can I set the duration of my appointment?**

Of course. You decide the duration of your appointments whether it's 5 minutes, 10 minutes, ..., 4 hours long. It's all up to you.

**Can I set a limit to when an appointment can be cancelled or rescheduled?**

You can set both values in hours from the settings page *Wappointment > Settings > General* 

**Why do reminders go out late sometimes?**

It depends on your website's configuration. The most reliable solution is to setup a cron task manually on your server(check your host's documentation) and disable WP cron (DISABLE_WP_CRON)

**Why is the booking form opening my schedule for the next 2months only?**

By default your schedule is opened for the next 60 days, but you can change that value in *Wappointment > Settings > General > Weekly Availability*. You decide which is the value your schedule should be made available for bookings, this said for performance reason we recommend keeping it as low as possible. Just figure what value is right for your activity.

**Why does nobody receive my confirmations or reminders emails?**

Your emails most likely go straight to SPAM or don't event reach your inbox. *Change the email sending method* in *Wappointment > Settings > Confirmations & Reminders* just go for the easy and reliable solution, [create a free account at SendGrid (100emails/day are free)](https://signup.sendgrid.com/) and configure Wappointment with the *SendGrid API*

**Can I sync multiple calendar besides of my Google calendar?**

Sure you can, we allow up to 4 calendars in the ics format to be synched from. It can be personal calendar(gmail, Outlook, iCal, etc..) or from external applications handling part of your schedule

**How often my Google calendar is being checked for sync?**

Every 5 minutes we download your calendar and check for changes, we don't do it more often as it could be a heavy task depending on how big is your calendar.

**I want my 25min appointments to start on the dot at 00 and 30, how can I do that?**

We've created a buffer time for that particular case, you can define it in the advanced settings. In your case, set your appointment's duration to 25min and set the buffer time to 5min this way each reservation will take a whole 30min in your availability.

== Installation ==

= Minimum Requirements =

* WordPress 4.7(or greater)
* PHP version 7.0(or greater)
* MySQL version 5.5(or greater) or MariaDB 10.0(or greater)

We recommend you to always keep your softwares updated to the latest version available.
It requires work on your end but keeps your site safe and optimized.

== Screenshots ==

1. Weekly Availability. First step of our initial setup wizard, simply drag and drop your recurrent availability.
2. Service Setup. Describe the appointment and how you provide it: By Phone, By Skype or In Person.
3. Booking Widget Editor. Quickly change all colors and texts of the customers' frontend Booking widget.
4. Calendar Weekly View. Overview of your appointments and availability(punctual and recurrent).
5. Calendar Admin Action. Book an appointment on behalf of a customer, create a punctual time where you are free or busy.
6. Frontend Booking Widget. Your customers start the booking process by clicking a booking button.
7. Settings Panel. Modify all that you had setup in the wizard, manage the confirmations and reminders, etc...
8. Help Page. Have a question? Simply ask for it straight in there.

== Changelog ==


= 1.4.2 - 2020-04-28 =
* Improved Booking form style and animation
* Refactored email sending transport
* Fixed calendar synch list
* Fixed booking form relative size to container
* Fixed Timezone appearance in booking form
* Fixed Added tips to select simplify the email sending method choice
* Fixed visual issue on iPhone

= 1.4.1 - 2020-04-27 =
* Added Week view in booking form instead of full month
* Added day section when selecting a slot (morning, afternoon, evening)
* Added shortcode options large to have the booking form fill up the whole space where inserted
* Added shortcode option Auto open calendar so that when inserted in your booking page it show the available slots immediately
* Added localization of dates in the frontend booking form, auto display in your client's browser language
* Added phone input requirement
* Improved style of the selected day in booking form
* Fixed bug when booking error message appearing

= 1.4.0 - 2020-04-15 =
* Added Buffer Time, time to prepare next appointment not included in appointment's duration, but removed from availability
* Added possibility to Sync more than 1 external calendar to calculate your availability
* Added tag replacement on subject of booking reminder or confirmation
* Added possibility to disconnect a calendar
* Fixed issue on calendar sync
* Fixed tag replacement in email reminders when using WP Mail
* Fixed issue on phone input missing flags
* Fixed issue on booking appointment from admin dashboard


= 1.3.2 - 2020-04-09 =
* Tested and compatible with WP 5.4
* Fixed styling issue rescheduling form
* Updated addons page
* Fixed rescheduling issue

= 1.3.1 - 2020-03-30 =
* Added Booking form auto-select first day with available slots
* Fixed checkboxes issue in Booking Form Editor
* Fixed month availability minor issue

= 1.3.0 - 2020-03-24 =
* Added SendGrid API for email sending
* Added MailGun Area (EU, US) for email reminders
* Added header logo selection for email reminders
* Corrected rendering issues for emails
* Corrected bug for shortcode insertion of booking module
* Corrected booking module appearing on reschedule  and cancel page
* Dropped vue-form-generator

= 1.2.4 - 2020-02-07 =
* Fixed PHP 7.0 issue with frontend validation
* Fixed PHP 7.0 issue with swiftmailer
* Fixed errors catcher on install
* Fixed dragging issue for regular availability
* corrected addons page styling
* Allow only text version email for WP mail

= 1.2.3 - 2020-01-10 =
* Fixed style frontend booking module

= 1.2.2 - 2020-01-09 =
* Reverting package to PHP 7.0 compatibility

= 1.2.1 - 2020-01-08 =
* Fixed compatibility issue with PHP 7
* Patching
* Corrected a few minor visual issues

= 1.2.0 - 2019-12-14 =
* Your Booking button can float in the bottom right corner of your page now
* Clearer backend calendar to manage your appointment better
* Fixed bug staff selection on ms or prefixed site
* Fixed bug client updating contact info when taking appointment
* And tons of quick refactoring and interfaces corrections for more coherence

= 1.1.1 - 2019-10-07 =
* Fixed shortcode bug inserted in page or post

= 1.1.0 - 2019-10-04 =
* Added Set the staff taking the appointments from your WordPress users list
* Added Set the staff image within few clicks
* Added back to original timezone when previewing calendar in a different Timezone
* Fixed cron bug on low traffic sites
* Fixed Backend interfaces not working for subfolders WordPress installations
* Fixed a few bugs on admin calendar
* Fixed availability regeneration when updating weekly availability
* Fixed a few strings corrections

= 1.0.2 - 2019-09-17 =
* Fixed MailGun API connection for emails delivery
* Fixed booking widget editing title was not working
* Added activation hook checking PHP version
* Cleaned up third party messages appearing

= 1.0.1 - 2019-09-11 =
* Fixed installation process halting while mistaking a lack of MySQL user permissions

= 1.0.0 - 2019-08-21 =
* Hello WordPress!

Maintained at https://github.com/wappointment/wappointment

