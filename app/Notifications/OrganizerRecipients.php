<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

/**
 * Legacy organizer recipients: every `email_notifications` address plus the calendar
 * owner's address. Invalid addresses are dropped and duplicates (compared without case)
 * keep the first spelling.
 */
final class OrganizerRecipients
{
    /**
     * @param mixed $configured the `email_notifications` setting: a comma-separated string or a list
     * @return list<string>
     */
    public static function resolve(mixed $configured, ?string $ownerEmail): array
    {
        $addresses = is_array($configured) ? $configured : explode(',', (string) $configured);
        if ($ownerEmail !== null) {
            $addresses[] = $ownerEmail;
        }
        $valid = [];
        foreach ($addresses as $address) {
            $address = sanitize_email(trim((string) $address));
            if ($address !== '' && is_email($address) && !isset($valid[strtolower($address)])) {
                $valid[strtolower($address)] = $address;
            }
        }
        return array_values($valid);
    }
}
