<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

/**
 * Values for the legacy `[model:key]` email tag vocabulary (master `Lists/email_tags.php`
 * and `Lists/email_links.php`). Unknown tags are kept literally, as legacy did, so
 * add-on tags that v3 does not provide stay visible instead of disappearing. Values are
 * plain text; callers escape them for the output they produce.
 */
final class EmailTags
{
    /** Legacy tags rendered by v3. `order:summary` is listed but resolves empty until orders migrate (F40). */
    public const SUPPORTED = [
        'client:name', 'client:email', 'client:phone',
        'service:name', 'service:address',
        'appointment:duration', 'appointment:location', 'appointment:starts',
        'staff:name', 'order:summary',
        'appointment:linkAddEventToCalendar', 'appointment:linkRescheduleEvent', 'appointment:linkCancelEvent',
        'appointment:linkNew', 'appointment:linkNewStaff', 'appointment:linkView',
    ];

    private const PATTERN = '/\[([A-Za-z_]+):([A-Za-z0-9_\-]+)\]/';

    /** @param array<string, string> $values keyed `model:key` */
    public function __construct(private array $values) {}

    public function value(string $tag): ?string
    {
        return array_key_exists($tag, $this->values) ? $this->values[$tag] : null;
    }

    /** True when the string is exactly one tag, e.g. a link mark whose href is `[appointment:linkNew]`. */
    public static function isTag(string $value): bool
    {
        return preg_match('/^' . trim(self::PATTERN, '/') . '$/', $value) === 1;
    }

    /** Plain-text replacement, used for subjects. */
    public function replacePlain(string $text): string
    {
        return (string) preg_replace_callback(self::PATTERN, function (array $match): string {
            $value = $this->value($match[1] . ':' . $match[2]);
            return $value === null ? $match[0] : self::singleLine($value);
        }, $text);
    }

    /** HTML-escaped text with tags replaced by escaped values. */
    public function replaceHtml(string $text): string
    {
        $html = '';
        $offset = 0;
        if (preg_match_all(self::PATTERN, $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                [$whole, $position] = $match[0];
                $html .= Html::text(substr($text, $offset, $position - $offset));
                $value = $this->value($match[1][0] . ':' . $match[2][0]);
                $html .= $value === null ? Html::text($whole) : nl2br(Html::text($value), false);
                $offset = $position + strlen($whole);
            }
        }
        return $html . Html::text(substr($text, $offset));
    }

    private static function singleLine(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $value));
    }
}
