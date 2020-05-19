# Appointments for WordPress
![Appointment Bookings for WordPress](https://ps.w.org/wappointment/assets/banner-1544x500.gif)

**Contributors:** wappointment, benheu  
**Tags:** appointment scheduling, appointment booking, appointment booking calendar, appointment booking system, appointments booking calendar  
**Requires at least:** 4.7  
**Tested up to:** 5.3  
**Requires PHP:** 7.0  
**Stable tag:** 1.2.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

## Description 

A simple and reliable **[appointment booking system](https://wappointment.com/?utm_source=wp-repo&utm_medium=link&utm_campaign=readme)**, for personal coaches, teachers, therapists and service professionals of all kind.

Wappointment is free and will remain **free forever**.

https://www.youtube.com/watch?v=jUkiyejbuzg


## The Benefits


### Avoid Double Bookings
Keep your availability updated using our powerful centralized system . 
Your availability gets refreshed whenever something changes in your schedule: 

* when a new client books you
* when a client cancels his appointment
* when you manually set time slots during which you are busy or free 
* when a new event gets created on your synched personal calendar (Google Calendar, Ical, Outlook Calendar)


### Simplify Your Booking Process 
Our **user friendly booking form** gives your clients a quick overview of your availability, making the booking process a breeze.


### Reduce No-Shows 
Your clients receive **appointment confirmations and reminders**. 
Quickly define when and how many of them do they receive (1 day before appointment, 1 hour before appointment).



##  The features 
* Unlimited bookings
* User-friendly and intuitive interfaces with no coding involved


### Availability Setup as an Admin 
* Set your recurrent availability within seconds
* Set your punctual availability and block your non-bookable time (non working days and hours, busy times, holidays, etc) in just few clicks
* Select the timezone from which you operate


### Appointments Settings as an Admin 
* Manage your appointments through a comprehensive Admin panel
* Define how you provide the appointment by Phone, by Skype or in Person
* Hand-pick the countries you will allow for a phone appointment
* Change appointment's duration
* Set the appointments' approval mode: automatic or manual 
* Set how far in advance an appointment can be booked 
* Allow clients to cancel and reschedule appointments
* Book an appointment on behalf of your customer
* Connect your personal calendar to the booking system and automatically block time where you are busy
* Set the date and time format 


### Make the Booking Widget Blend 
* Quickly customize colors and texts for your booking widget 
* 4-steps booking process, each step is editable


### Booking an Appointment as a Customer 
* Clients book you within seconds, from their mobile phone, tablet or desktop computer
* The available booking slots are displayed in your client's timezone, no more confusion for your international clients
* Client receive a confirmation and as many reminders as you've setup
* Clients can easily save your appointment to their personal calendar


### Appointments' Confirmations Reminders and Notifications 
* Receive email notifications when clients book, reschedule or cancel an appointment
* Receive daily and weekly notifications
* Customize and personalize your confirmations and reminders sent to your clients


### Have a Question? 
Our plugin is free, and easy to install. Try it first :)
And for any question or doubt, you can reach us:

* Straight from the plugin in *Wappointment > Help*
* Here on the [WordPress' forum](https://wordpress.org/support/plugin/wappointment/) 
* From our contact page on [wappointment.com](https://wappointment.com/support?utm_source=wp-repo&utm_medium=link&utm_campaign=readme).


## Frequently Asked Questions 

**Can I customize the look and feel of the booking form?**

We provide a very simple editor in which you can change the texts, colors and few other parameters of each step of the booking process. We plan on adding several templates in the future, meanwhile simply use css to make it exactly the way you want.

**Can I set the duration of my appointment?**

Of course. You decide the duration of your appointments whether it's 5 minutes, 10 minutes, ..., 4 hours long. It's all up to you.

**Can I set time limits for cancelling and rescheduling appointments?**

You decided when clients can cancel and reschedule their appointments in the settings page *Wappointment > Settings > General* 

**Can I sync multiple calendars besides of my Google calendar?**

Sure you can, we allow up to 4 calendars in the ics format to be synched from. It can be personal calendar(Google, Outlook, iCal, etc..) or from external applications handling part of your schedule

**How often my Google calendar is being checked for sync?**

Every 5 minutes we download your calendar and check for changes, we don't do it more often as it could be a heavy task depending on how big is your calendar.

**I need 10 minutes to prepare between 2 appointments, how do I proceed?**

You can set buffer time for that particular case, you can define it in the *Wappointment > Settings > Advanced*. When someone books you, you will become unavailable during the time of the appointment + buffer time

**Why do reminders go out late sometimes?**

It depends on your website's configuration. The most reliable solution is to setup a cron task manually on your server(check your host's documentation) and disable WP cron (DISABLE_WP_CRON)

**Why clients can book me 2 months ahead only?**

By default your schedule is opened for the next 60 days, but you can change that value in *Wappointment > Settings > General > Weekly Availability*. For performance reason we recommend keeping it as low as possible. Just figure which value is right for your activity.

**Why does nobody receive my confirmations or reminders emails?**

Your emails most likely go straight to SPAM or don't event reach your inbox. *Change the email sending method* in *Wappointment > Settings > Confirmations & Reminders* just go for the easy and reliable solution, [create a free account at SendGrid (100emails/day are free)](https://signup.sendgrid.com/) and configure Wappointment with the *SendGrid API*


## Installation 


### Minimum Requirements 

* WordPress 4.7(or greater)
* PHP version 7.0(or greater)
* MySQL version 5.5(or greater) or MariaDB 10.0(or greater)

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
