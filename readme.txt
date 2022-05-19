=== Appointment Bookings for Zoom GoogleMeet and more - Wappointment  ===
Contributors: wappointment, benheu
Tags: appointment scheduling, appointment booking, booking calendar, booking form, zoom
Requires at least: 4.7
Tested up to: 5.9
Requires PHP: 7.0
Stable tag: 2.4.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Get clients to quickly book a meeting with you by Zoom, GoogleMeet, phone or at your office

== Booking plugin for WordPress ==

**Appointment booking calendar** for personal coaches, teachers, therapists and service professionals of all kind.
Get booked 24/7 with the most **intuitive booking form**.

Convert visitors into customers with a simple call to action.

The perfect [Calendly alternative for WordPress](https://wappointment.com).

https://www.youtube.com/watch?v=jUkiyejbuzg

Try the [demo of the booking calendar](https://demo.wappointment.com)

== Scheduling system headache free ==

= Provide your appointments the way you like =
* as a video Meeting over Zoom, GoogleMeet, Jitsi or Skype
* over the Phone
* or at your office

= Sync bookings with Google Calendar =
* Setup a 2-way sync quickly with Google Calendar
* 1-way sync is available with any .ICS Calendar, Microsoft Outlook, Apple Ical, etc ...

= Avoid Double Bookings =
Keep your availability updated using our powerful centralized system. 
Your availability gets refreshed whenever something changes in your schedule: 

* when a new client books you
* when a client cancels his appointment
* when you manually create new time slots during which you are busy or free 
* when a new event gets created on your synched personal calendar (Google Calendar, Apple iCal, Outlook Calendar)

= Simplify your Booking Process =
Our **user friendly booking form** gives your clients a quick overview of your availability, making the booking process a breeze.

= Reduce No-Shows =
Your clients receive **appointment confirmations and reminders**. 
Quickly define when and how many of them do they receive (1 day before appointment, 1 hour before appointment).

== Make it simple for your customers ==
* Clients book you within seconds, from their mobile phone, tablet or desktop computer
* The available booking slots are displayed in your client's timezone, no more confusion for your international clients
* Clients receive a confirmation and as many reminders as you wish
* Clients can easily save your appointment to their personal calendar

== Manage your schedule simply ==
* Unlimited bookings
* User-friendly and intuitive interfaces with no coding involved

= Availability Setup =
* Set your recurrent availability within seconds
* Set your punctual availability and block your non-bookable time (non working days and hours, busy times, holidays, etc) in just few clicks
* Select the timezone from which you operate

= Appointments Settings =
* Set the duration of your meeting 5 min, 10 min, 15 min , 60 min etc ...
* Set the appointments' approval mode: automatic or manual 
* Set how far in advance an appointment can be booked 
* Allow clients to cancel and reschedule appointments
* Book an appointment on behalf of your customer
* Connect your personal calendar to the booking system and automatically block times during which you are busy
* Change the date and time format 

= Customizing the appearance =
* Quickly customize colors and texts for your booking form 
* 4-steps booking process, each step is fully editable

= Appointments' Confirmations Reminders and Notifications =
* Receive email notifications when clients book, reschedule or cancel an appointment
* Receive daily and weekly notifications
* Customize and personalize your confirmations and reminders sent to your clients

= Advanced options for edge use-cases =
* Limit/Maximum active bookings per client
* Force user account's email for logged in users
* Open new slots in new day at a specific time e.g." new slots everyday at 11pm"

= Have a Question? =
Our plugin is free, and easy to install. Try it first :)
And for any question or doubt, you can reach us:

* Straight from the plugin in *Wappointment > Help*
* Here on the [WordPress' forum](https://wordpress.org/support/plugin/wappointment/) 
* From [our contact page](https://wappointment.com/support?utm_source=wp-repo&utm_medium=link&utm_campaign=readme).

== Frequently Asked Questions ==

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

== Installation ==

= Minimum Requirements =

* WordPress 4.7(or greater)
* PHP version 7.0(or greater)
* MySQL version 5.6(or greater) or MariaDB 10.0(or greater)

Always keep your softwares updated.
It requires work on your end but keeps your site safe and optimized.

== Screenshots ==

1. Booking an appointment from a mobile phone
2. Monitoring upcoming appointments and modifying availability
3. Receiving branded email reminders on mobile phone
4. Editing booking widget's style through simple interfaces
5. Weekly Availability. First step of our initial setup wizard, simply drag and drop your recurrent availability.
6. Service Setup. Describe the appointment and how you provide it: By Phone, By Skype or At a location.

== Changelog ==

= 2.4.9 - 2022-04-13 =
* fixed issue admin notif double client data
* fixed issue deleted service
* fixed behalf booking when deleted client
* fixed behalf booking
* fixed translated strings

= 2.4.8 - 2022-03-11 =
* added dirt detector in availability json response
* added fluid availability option
* fixed issue 3001 when using Zoom without app option
* fixed issue not available slot when available
* fixed today's slot picking strange value
* fixed issue start day of the week in admin calendar
* fixed issue php8

= 2.4.7 - 2022-03-01 =
* added manager role
* added RTL emails
* fixed avoid display issue when a calendar staff is deleted
* fixed calendar ICS parse exdate on recurrent event
* fixed min start for availability

= 2.4.6 - 2022-02-16 =
* added option to specify custom Jitsi Server URL
* added wp_remote option to avoid CURL errors
* fixed issue i18n date conversion in cancel and reschedule page
* fixed issue save to calendar button
* fixed auto cancel job issues
* refactored bits and pieces

= 2.4.5 - 2022-01-24 =
* corrected fatal error

= 2.4.4 - 2022-01-18 =
* added staff appointments shortcode
* added health panel for scheduled tasks
* added Jitsi in video meeting providers
* fixed bug Zoom Browser with google meet activated
* fixed issue ics attachments and save to calendar button fixed issue pay on site

= 2.4.3 - 2022-01-10 =
* added TOS and privacy policy link in payment step
* added storing user details per order
* added extended data to email receipt
* added translatable strings
* fixed force user email issues
* fixed issue behalf booking
* fixed issue add to calendar button in booking confirmation
* fixed custom fields issue display in calendar and emails
* fixed issue "no idn encoder found" while sending email

= 2.4.2 - 2021-12-20 =
* Remove SQL modes to avoid issue on certain servers

= 2.4.1 - 2021-12-18 =
* Fixed issue with twilio

= 2.4.0 - 2021-12-16 =
* Added compatibility with fluent SMTP plugin
* Added default availability and default assign services for new staff
* Added option to open Zoom meeting straight in the browser without the app
* Added mark as paid and cancel button in orders listing
* Added option to differentiate free event from Outlook
* Added cancel, reschedule and join meeting links to [wap_history] page
* Added allow to cancel 30 minutes before appointments take place
* Added pending appointment admin notification
* Added many translatable strings
* Fixed cache issue, staff listing in Settings > Calendars & Staff was dissappearing
* Improved, plugin is lighter
* Improved installation issue detection
* Lot of improvements and refactoring

= 2.3.0 - 2021-09-19 =
* added ability to sell services on site
* fixed reply to email for admin notification
* fixed calendar sync from external ics and recurring events

= 2.2.6 - 2021-08-16 =
* fixed issue broken booking form when using data privacy link
* added option to force email from loggedin user hidding the email field
* added option to open slots for a new day from a specific time

= 2.2.5 - 2021-08-07 =
* fixed Security vulnerability reported by researcher Renos Nikolaou @r3n_hat
* added popup option for Booking widget shortcode
* improved popup system no more overlay from 3rd party themes or plugins
* fixed issue staff and service selection broken in specific use case
* added compatibility with plugin WP HTML Mail
* added option to show the meeting link only x minutes before the appointment starts
* fixed issue on save to calendar button
* added warning for PHP 8 users
* fixed exception for disappearing Sunday 1st in booking widget

= 2.2.4 - 2021-06-27 =
* Added cancel and reschedule link in auto generated event in Google calendar
* Improved js event trigger on appointment confirmation for 3rd party integrations
* Added enable/disable auto-fill option of booking form for logged in users
* Added a "Wide format" for Services with long descriptions
* Fixed bug with cache ON and Calendars & Staff listing empty, impossible to create new staff
* Increased timeout limit on .ICS calendar sync
* Added link in email editor for making a new appointment with same staff
* Added option on booking widget editor to hide the staff name
* Added compatibility for unix socket for DB connection
* Added compatibility with Local by FlyWheel (Long overdue DB connection issue)

= 2.2.3 - 2021-05-29 =
* added Limit active bookings per client, in order to avoid over booking from one single person
* fixed availability calculation issue with big busy slots

= 2.2.2 - 2021-05-25 =
* fixed calendar synch not being refreshed automatically
* Reminder emails contain now direct link to Zoom Meeting or Google Meet for video appointments
* fixed shortcode issue with shortcode remover plugins
* fixed issue user account with different roles not appearing in Settings > Calendars & Staff

= 2.2.1 - 2021-05-17 =
* disabling Wappointment's cache by default
* added hook after booking
* fixed style on button in booking form relative size
* fixed issue undefined email when editing Staff

= 2.2.0 - 2021-05-10 =
* Added staff selection page in booking form 
* First booking widget screen can now be a staff selection page using a shortcode attribute 
* Optimization made on calendars availability check requests
* Added Staff field, for extra information describing your staff(to be used in emails and SMS reminders)
* Added staff permissions to allow staff to modify their own availability
* added appointment history shortcode for logged in users [wap_history]
* Admin notifications emails are now translatable with LocoTranslate or other translations system alike
* Improved overall usability in the backend interfaces

= 2.1.5 - 2021-04-17 =
* Added centered option for the booking widget in shortcode mode
* Added email reminder duplicate option
* Improved shortcode generator
* Improved staff/Calendar creation
* Improved service creation
* Improved booking widget appearance
* Correction on calendar sync timezone handling issue
* Remove buffer from total duration in attached ics and in appointment's title
* Fixed missing service information in Wappointment > Calendar for each appointment
* Fixed missing translation on booking widget

= 2.1.4 - 2021-04-09 =
* Fixed issue when modifying calendar on save, the custom avatar selection was lost
* Improved booking widget editor for payment selection in addons
* Fixed booking cancel sequence from the backend
* Improved css hiding external nag 99% efficient
* Improved foreign key handling on installation
* Allow calendar delete

= 2.1.3 - 2021-04-02 =
* Fixed confirm button in pending appointment email 
* Fixed Edit Cancel / Reschedule page in Settings > Advanced
* Fixed client delete issue

= 2.1.2 - 2021-03-31 =
* Fixed triple email notifications sent
* Fixed issue installation on WordPress Multisite (db foreign keys name issue)

= 2.1.1 - 2021-03-27 =
* Fixed problem to replace calendar/staff image
* Fixed improper service duration appearing in reschedule process of appointment
* Fixed missing translatable strings in booking widget editor
* Fixed multiduration when locked bookable service not being editable
* Fixed false duration in header compact mode
* Fixed issue calendar synch and non standard timezones
* Fixed issue emails not sending when "WP PGP Encrypted Emails" installed
* Fixed issue backend calendar page not showing with out orf range value of regav
* Fixed issue with booking confirmation message initial import

= 2.1.0 - 2021-03-19 =
* Can create up to 3 services
* Can create up to 2 calendars
* Lot of interfaces improvements and bug fixes

= 2.0.3 - 2021-03-01 =
* Improved calendar synch 
* Fixed issue with availability calculation on edge case
* Hide button on preload of booking form
* Improved Google - Zoom connection for better performances
* Added missing confirmation message for video meetings
* Fixed bug on Zoom, Google meet link in appointment confirmation
* Fixed edge case admin calendar not loading events

= 2.0.2 - 2021-01-31 =
* Added js hook for analytics on booking confirmation
* Added missing editable text for appointment viewing
* Fixed calendar loading unlimited loop error with recurrent event
* Fixed error when trying to Connect account to Zoom and Google Calendar

= 2.0.1 - 2021-01-20 =
* Added Save to calendar button for Outlook Live
* Added Join Zoom Google Meet Meeting link straight in the admin email confirmation
* Fixed SMTP encryption issue SSL and TLS to send booking confirmation
* Fixed issue ics in emails Zoom Google Meet meeting room information is missing
* Fixed issue ics in booking confirmation step for iCal and Outlook

= 2.0.0 - 2021-01-09 =
* Added Zoom Integration
* Added Google Calendar 2-way Sync
* Added Google Meet Integration
* Improvement on Email editor
* Phone countries can be ordered with Drag and Drop now
* Style corrections
* Client erase fixed
* Fixed issue with sunday appointments not showing in the admin calendar for some timezones
* Fixed issue on email headers, corrected double headers on site's method
* Compatible with WP Mail SMTP plugin

= 1.9.5 - 2020-11-23 =
* Fixed corrupted version

= 1.9.4 - 2020-11-23 =
* Corrected Appointments not showing in Admin Calendar on some websites

= 1.9.3 - 2020-11-21 =
* Fixed DB tables install issue on foreign key client_id
* added colors preference in Admin calendar
* added client delete button
* setting your weekly availability works on tablets now
* usability improved in the booking widget editor
* automatic cleanup of weekly availability invalid values

= 1.9.2 - 2020-10-31 =
* corrected default behaviour on mobile, full screen mobile is OFF if auto-open is ON
* added shortcode "pop_off" parameters to turn off pop behaviour and to force it too "pop"
* added exception on timezone detection for "AUS Eastern Standard Time"

= 1.9.1 - 2020-10-27 =
* corrected issue with update information

= 1.9.0 - 2020-10-26 =
* Added precision mode on Weekly Availability, you can now set your weekly schedule each 10min, 15min, 20min, 30min
* Added display preferences in Admin Calendar View
* Pref1: You can now set the starting time and end time showing on screen (e.g.: from 8am til 11pm)
* Pref2: You can now set the size of the interval for selection
* mobile booking form is now full screen always for better usability
* Improved Iphone scheduling form
* fixed styling issues in booking form distorter button or header compact mode with long service name
* fixed unkown eastern standard time on calendar sync

= 1.8.3 - 2020-10-09 =
* fixed styling issues in booking form
* fixed unavailable booking slots close to current time appearing when they shouldn't
* fixed usability issues in Booking widget editor

= 1.8.2 - 2020-10-05 =
* fixed various styling issue in the booking form
* fixed broken email preview for appointment reminders
* fixed large calendar version not expanding fully
* reduced size of calendar buttons days
* corrected calendar refresh in booking form
* fixed account switch bug re showing update page
* corrected error when booking appointment really close to starting time "left greater than right"
* set min width of floating booking form to 320px
* fixed missing error message in booking form
* added feedback script on deactivation 

= 1.8.1 - 2020-09-25 =
* Backward issue resolved

= 1.8.0 - 2020-09-25 =
* Added client basic listing
* Booking form widget design improvements
* Booking form mobile improvements
* Added staff name 1-click edit
* Improved booking widget editor, made clearer
* Improved icons, colors and styling management
* refactored email generation and tag system
* Global refactoring

= 1.7.1 - 2020-09-08 =
* Corrected bug on active staff switch
* Added update page information

= 1.7.0 - 2020-09-07 =
* Added prefilled booking form when user is logged in
* Improved design of booking form
* Added rescheduled emails when rescheduling from the backend
* Added security plugin conflict detection
* Corrected bug when discarding active staff

= 1.6.0 - 2020-08-10 =
* Refactored form generator latest version extendable
* added possibility to include more than one email for admin notifications
* fixed redirect issue on plugin setup
* fixed installation interrupted error with MySQL 5.6 due to unique keys length
* Fixed issue with email notifications
* fixed issue while updating reminder

= 1.5.5 - 2020-08-02 =
* fixed new bug DB_CHARSET and DB_COLLATE in v1.5.4

= 1.5.4 - 2020-07-31 =
* Improving email test when sending test appointment (cron trigger)
* Fixed Booking Widget Editor
* Fixed issue no available slots when little booking activity
* Fixed installation issue DB_CHARSET missing

= 1.5.3 - 2020-07-22 =
* added Dates Localized when supported in booking widget
* fixed Calendar Synch issue
* fixed issue when instaling on local by FlyWheel
* added fix for cached websites, somehow could break frontend booking form
* Corrected style issue
* Fixed Save appointment to personal calendar button 
* code refactor booking form and bug fixes

= 1.5.2 - 2020-06-19 =
* Added option to turn the cancellation rescheduling page into a proper WordPress page so you can control the style
* Added client's phone number to Admin notifications when present
* Fixed edge case when permalinks are not turned on or server badly configured
* Fixed issue with recurring event on calendar sync
* Fixed issue dealing with failed jobs
* Fixed non standard timezone recognition when syncing calendar 

= 1.5.1 - 2020-06-05 =
* Fixed update notifications
* Corrected addons update issue
* improved style compatibility booking form
* improved weekly and daily appointments notifications for admins
* improved error detection on installation
* added spread the word section

= 1.5.0 - 2020-05-29 =
* Improved wizard and onboarding
* Creating a booking page during onboarding is now a breeze
* Added .ics files to every emails containing appointment informations
* Now you can send prettier appointment confirmation emails using WPmail
* Refactored portions of code

= 1.4.4 - 2020-05-18 =
* Added option for data protection link in booking form
* Added plenty of UI improvements and text corrections
* Corrected issues in MultiSite during installation
* Corrected calculation of today's first available slot(increased precision)
* Corrected wizard back button
* Corrected bug on phone field in the frontend

= 1.4.3 - 2020-05-08 =
* Refactored bits of code
* Corrected style in frontend booking form
* Async availability requests made lighter
* Fixed Calendar admin view when buffer time is set
* Fixed slots appearing in today's date when not supposed to
* Fixed bug publish unpublish of reminder drops the email header
* Fixed bug loading media gallery before needing it when editing reminders
* Fixed bug at the end of the day showing calendar slot of tomorrow in today

= 1.4.2 - 2020-05-04 =
* Improved Booking form style and animation
* Refactored email sending transport
* Improved scheduling system avoiding double event trigger
* Fixed calendar synch list
* Fixed today's slots in booking form
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

