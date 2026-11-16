<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

/** A stored email template that cannot be rendered as written. */
class InvalidTemplateException extends \InvalidArgumentException {}
