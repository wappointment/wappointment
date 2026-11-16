<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Jobs\PermanentJobFailure;
use Wappointment\Notifications\RenderedEmail;

/**
 * WordPress mail adapter. Only the `wpmail` transport is migrated; other stored
 * `mail_config` methods fail explicitly instead of pretending to send. Mail plugins
 * that hook into wp_mail keep working.
 *
 * Format follows legacy master `Transports\WpMail::sendTheRightWay`: plain text unless
 * `mail_config.wpmail_html` is on or a known SMTP plugin is active, in which case the
 * HTML body is sent with the plain-text version as its alternative part. The
 * alternative part is skipped for plugins legacy flagged as conflicting with multipart.
 */
class Mailer
{
    /** Constants that legacy `PluginsDetection::smtpConfigured()` treated as an SMTP plugin. */
    private const SMTP_PLUGINS = ['WPMS_PLUGIN_VER', 'POST_SMTP_VER', 'HAET_MAIL_PATH', 'FLUENTMAIL_PLUGIN_VERSION'];
    /** Legacy `PluginsDetection::createsEmailConflict()`. */
    private const MULTIPART_CONFLICTS = ['WP_PGP_ENCRYPTED_EMAILS_MIN_PHP_VERSION'];

    public function __construct(private Settings $settings) {}

    /**
     * @param list<string> $to
     * @param array{email: string, name: string}|null $replyTo
     */
    public function send(array $to, RenderedEmail $email, ?array $replyTo = null): void
    {
        $config = $this->settings->get('mail_config');
        $config = is_array($config) ? $config : [];
        $method = $config['method'] ?? 'wpmail';
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
        $html = !empty($config['wpmail_html']) || self::anyDefined(self::SMTP_PLUGINS);
        $headers[] = 'Content-Type: ' . ($html ? 'text/html' : 'text/plain') . '; charset=UTF-8';

        $failure = '';
        $capture = static function ($error) use (&$failure): void {
            if ($error instanceof \WP_Error) {
                $failure = $error->get_error_message();
            }
        };
        $alternative = static function ($phpmailer) use ($email): void {
            if (is_object($phpmailer) && property_exists($phpmailer, 'AltBody')) {
                $phpmailer->AltBody = $email->text;
            }
        };
        $withAlternative = $html && !self::anyDefined(self::MULTIPART_CONFLICTS);
        add_action('wp_mail_failed', $capture);
        if ($withAlternative) {
            add_action('phpmailer_init', $alternative);
        }
        try {
            $sent = wp_mail($to, $email->subject, $html ? $email->html : $email->text, $headers);
        } finally {
            remove_action('wp_mail_failed', $capture);
            if ($withAlternative) {
                remove_action('phpmailer_init', $alternative);
            }
        }
        if (!$sent) {
            throw new \RuntimeException('wp_mail did not accept the message' . ($failure !== '' ? ': ' . $failure : '.'));
        }
    }

    /** @param list<string> $constants */
    private static function anyDefined(array $constants): bool
    {
        foreach ($constants as $constant) {
            if (defined($constant)) {
                return true;
            }
        }
        return false;
    }
}
