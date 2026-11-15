<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Attributes\In;
use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Data\DefaultAvailabilityData;
use Wappointment\Http\Data\MailSettingsData;
use Wappointment\Http\Data\ServiceSettingsData;
use Wappointment\System\Optional;

class SaveSettingsRequestData extends RequestData
{
    public function __construct(
        public readonly bool|Optional $wappointment_allowed = Optional::Missing,
        public readonly bool|Optional $allow_cancellation = Optional::Missing,
        public readonly bool|Optional $allow_rescheduling = Optional::Missing,
        public readonly bool|Optional $weekly_summary = Optional::Missing,
        public readonly bool|Optional $daily_summary = Optional::Missing,
        public readonly bool|Optional $notify_new_appointments = Optional::Missing,
        public readonly bool|Optional $notify_pending_appointments = Optional::Missing,
        public readonly bool|Optional $notify_canceled_appointments = Optional::Missing,
        public readonly bool|Optional $notify_rescheduled_appointments = Optional::Missing,
        public readonly bool|Optional $mail_status = Optional::Missing,
        public readonly bool|Optional $show_welcome = Optional::Missing,
        public readonly bool|Optional $force_ugly_permalinks = Optional::Missing,
        public readonly bool|Optional $allow_staff_cf = Optional::Missing,
        public readonly bool|Optional $services_sold = Optional::Missing,
        public readonly bool|Optional $max_active_per_staff = Optional::Missing,
        public readonly bool|Optional $autofill = Optional::Missing,
        public readonly bool|Optional $onsite_enabled = Optional::Missing,
        public readonly bool|Optional $cache = Optional::Missing,
        public readonly bool|Optional $alt_port = Optional::Missing,
        public readonly bool|Optional $forceemail = Optional::Missing,
        public readonly bool|Optional $allow_refreshavb = Optional::Missing,
        public readonly bool|Optional $calendar_handles_free = Optional::Missing,
        public readonly bool|Optional $calendar_ignores_free = Optional::Missing,
        public readonly bool|Optional $invoice = Optional::Missing,
        public readonly bool|Optional $wp_remote = Optional::Missing,
        public readonly bool|Optional $frontend_weekstart = Optional::Missing,
        public readonly bool|Optional $availability_fluid = Optional::Missing,
        public readonly bool|Optional $more_st = Optional::Missing,
        public readonly bool|Optional $big_prices = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $buffer_time = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $scheduler_mode = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $front_page = Optional::Missing,
        #[In([0, 1])] public readonly int|Optional $approval_mode = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $week_starts_on = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $hours_before_booking_allowed = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $hours_before_cancellation_allowed = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $hours_before_rescheduling_allowed = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $weekly_summary_day = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $weekly_summary_time = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $daily_summary_time = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $booking_page = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $max_active_bookings = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $video_link_shows = Optional::Missing,
        #[Min(0), Max(23)] public readonly int|float|Optional $refreshavb_at = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $clean_pending_every = Optional::Missing,
        #[Min(1), Max(1440)] public readonly int|Optional $starting_each = Optional::Missing,
        #[Min(0)] public readonly int|float|Optional $tax = Optional::Missing,
        public readonly string|Optional $date_format = Optional::Missing,
        public readonly string|Optional $time_format = Optional::Missing,
        public readonly string|Optional $date_time_union = Optional::Missing,
        public readonly string|Optional $email_footer = Optional::Missing,
        public readonly string|Optional $email_link_color = Optional::Missing,
        public readonly string|Optional $email_notifications = Optional::Missing,
        public readonly string|Optional $reschedule_link = Optional::Missing,
        public readonly string|Optional $cancellation_link = Optional::Missing,
        public readonly string|Optional $save_appointment_text_link = Optional::Missing,
        public readonly string|Optional $new_booking_link = Optional::Missing,
        public readonly string|Optional $currency = Optional::Missing,
        public readonly string|Optional $invoice_seller = Optional::Missing,
        public readonly string|Optional $invoice_num = Optional::Missing,
        public readonly string|Optional $jitsi_url = Optional::Missing,
        public readonly string|Optional $collate = Optional::Missing,
        public readonly string|Optional $charset = Optional::Missing,
        public readonly bool|int|string|Optional $email_logo = Optional::Missing,
        public readonly bool|int|string|Optional $activeStaffId = Optional::Missing,
        public readonly bool|int|string|Optional $clean_last_check = Optional::Missing,
        /** @var list<string>|Optional */
        #[CollectionOf('string')] public readonly array|Optional $calendar_roles = Optional::Missing,
        /** @var list<string>|Optional */
        #[CollectionOf('string')] public readonly array|Optional $payments_order = Optional::Missing,
        /** @var list<string>|Optional */
        #[CollectionOf('string')] public readonly array|Optional $invoice_client = Optional::Missing,
        /** @var bool|list<int>|Optional */
        #[CollectionOf('int')] public readonly bool|array|Optional $servicesDefault = Optional::Missing,
        public readonly ServiceSettingsData|Optional $service = Optional::Missing,
        public readonly MailSettingsData|Optional $mail_config = Optional::Missing,
        public readonly DefaultAvailabilityData|Optional $regavDefault = Optional::Missing,
    ) {}

    protected function validate(): void
    {
        if ($this->toArray() === []) {
            throw new \InvalidArgumentException('No settings supplied.');
        }
    }
}
