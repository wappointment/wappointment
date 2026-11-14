<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\System\Data;
use Wappointment\System\Optional;

class MailSettingsData extends Data
{
    public function __construct(
        public readonly string|Optional $method = Optional::Missing,
        public readonly string|Optional $from_address = Optional::Missing,
        public readonly string|Optional $from_name = Optional::Missing,
        public readonly string|Optional $mgdomain = Optional::Missing,
        public readonly string|Optional $mgkey = Optional::Missing,
        public readonly string|Optional $host = Optional::Missing,
        public readonly string|Optional $port = Optional::Missing,
        public readonly string|Optional $username = Optional::Missing,
        public readonly string|Optional $password = Optional::Missing,
        public readonly bool|Optional $wpmail_html = Optional::Missing,
        public readonly bool|Optional $attachments_off = Optional::Missing,
    ) {}
}
