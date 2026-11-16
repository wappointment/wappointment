<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

/** Escaping for generated email HTML. Uses WordPress escaping when it is loaded. */
final class Html
{
    public static function text(string $value): string
    {
        return function_exists('esc_html') ? esc_html($value) : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function attr(string $value): string
    {
        return function_exists('esc_attr') ? esc_attr($value) : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** A safe href, or null when the URL uses a scheme that does not belong in an email link. */
    public static function url(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || preg_match('#^(https?:|mailto:|tel:)#i', $url) !== 1) {
            return null;
        }
        if (function_exists('esc_url')) {
            $escaped = esc_url($url, ['http', 'https', 'mailto', 'tel']);
            return $escaped !== '' ? $escaped : null;
        }
        return htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
