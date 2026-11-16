<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

/**
 * Minimal first-party HTML frame for notification emails. Legacy master used the
 * Foundation for Emails template inlined by Emogrifier; v3 adds no Composer packages, so
 * it writes the few styles that matter inline: centered card, optional logo, link color
 * and text direction. The content inside the frame carries the stored template.
 */
final class EmailLayout
{
    /**
     * @param array{rtl?: bool, logo_url?: ?string, link_color?: string, footer_html?: string, site_url?: string} $options
     */
    public static function wrap(string $content, string $subject, array $options = []): string
    {
        $rtl = !empty($options['rtl']);
        $direction = $rtl ? 'rtl' : 'ltr';
        $align = $rtl ? 'right' : 'left';
        $linkColor = self::color($options['link_color'] ?? '');
        $content = self::styleLinks($content, $linkColor);
        $footer = self::styleLinks($options['footer_html'] ?? '', $linkColor);

        $logo = '';
        $logoUrl = isset($options['logo_url']) && is_string($options['logo_url']) ? Html::url($options['logo_url']) : null;
        if ($logoUrl !== null) {
            $image = '<img src="' . $logoUrl . '" alt="" style="max-width:200px;height:auto;border:0">';
            $site = Html::url($options['site_url'] ?? '');
            $logo = '<tr><td style="padding:24px 24px 0;text-align:center">'
                . ($site !== null ? '<a href="' . $site . '">' . $image . '</a>' : $image)
                . '</td></tr>';
        }

        return '<!DOCTYPE html><html dir="' . $direction . '"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . Html::text($subject) . '</title></head>'
            . '<body style="margin:0;padding:0;background:#f3f3f3">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f3f3">'
            . '<tr><td align="center" style="padding:24px 12px">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" dir="' . $direction . '" '
            . 'style="max-width:580px;background:#ffffff;border-radius:6px;font-family:Helvetica,Arial,sans-serif;'
            . 'font-size:16px;line-height:1.5;color:#333333;text-align:' . $align . '">'
            . $logo
            . '<tr><td style="padding:24px">' . $content . '</td></tr>'
            . ($footer !== '' ? '<tr><td style="padding:0 24px 24px;font-size:13px;color:#777777">' . $footer . '</td></tr>' : '')
            . '</table></td></tr></table></body></html>';
    }

    private static function color(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $color) === 1 ? $color : '#6664cb';
    }

    private static function styleLinks(string $html, string $color): string
    {
        return (string) preg_replace('/<a (?![^>]*style=)/', '<a style="color:' . $color . '" ', $html);
    }
}
