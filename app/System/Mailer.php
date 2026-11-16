<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Jobs\PermanentJobFailure;

/**
 * WordPress mail adapter. Only the `wpmail` transport is migrated; other stored
 * `mail_config` methods fail explicitly instead of pretending to send. Mail plugins
 * that hook into wp_mail keep working.
 */
class Mailer
{
    public function __construct(private Settings $settings) {}

    /**
     * @param list<string> $to
     * @param array{email: string, name: string}|null $replyTo
     */
    public function send(array $to, string $subject, string $body, ?array $replyTo = null): void
    {
        $method = $this->settings->get('mail_config')['method'] ?? 'wpmail';
        if ($method !== 'wpmail') {
            throw new PermanentJobFailure('The configured email transport "' . $method . '" has not been migrated to v3.');
        }
        if ($to === []) {
            throw new PermanentJobFailure('The notification has no valid recipient.');
        }
        $headers = [];
        if ($replyTo !== null && is_email($replyTo['email'])) {
            $name = trim(str_replace(["\r", "\n", '"', '<', '>'], '', $replyTo['name']));
            $headers[] = 'Reply-To: ' . ($name !== '' ? '"' . $name . '" ' : '') . '<' . $replyTo['email'] . '>';
        }
        $failure = '';
        $capture = static function ($error) use (&$failure): void {
            if ($error instanceof \WP_Error) {
                $failure = $error->get_error_message();
            }
        };
        add_action('wp_mail_failed', $capture);
        try {
            $sent = wp_mail($to, $subject, $body, $headers);
        } finally {
            remove_action('wp_mail_failed', $capture);
        }
        if (!$sent) {
            throw new \RuntimeException('wp_mail did not accept the message' . ($failure !== '' ? ': ' . $failure : '.'));
        }
    }
}
