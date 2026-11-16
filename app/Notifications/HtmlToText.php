<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

/**
 * Plain-text alternative of a rendered email, following legacy master
 * `Messages\ConvertHtmlToText`: headings and blocks become line breaks, list items get
 * a `* ` bullet, and links become `text ( url )`.
 */
final class HtmlToText
{
    public static function convert(string $html): string
    {
        $html = (string) preg_replace('# +#', ' ', $html);
        $html = str_replace(["\n", "\r", "\t"], '', $html);
        $text = (string) preg_replace(
            [
                '#< *a[^>]*> *< *img[^>]*> *< *\/ *a *>#isU',
                '#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU',
                '/<style\b[^>]*>(.*?)<\/style>/s',
                '#< *(h1|h2)[^>]*>#Ui',
                '#< *li[^>]*>#Ui',
                '#< */ *(li|td|tr|div|p)[^>]*> *< *(li|td|tr|div|p)[^>]*>#Ui',
                '#< */? *(br|p|h1|h2|legend|h3|li|ul|ol|h4|h5|h6|tr|td|div|blockquote|pre|hr)[^>]*>#Ui',
                '/< *a[^>]*href *= *"([^#][^"]*)"[^>]*>(.*)< *\/ *a *>/Uis',
            ],
            ["", "", "", "\n\n", "\n* ", "\n", "\n", '${2} ( ${1} )'],
            $html
        );
        $text = function_exists('wp_strip_all_tags') ? wp_strip_all_tags($text) : strip_tags($text);
        $text = html_entity_decode(str_replace('&nbsp;', ' ', $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);
        $text = (string) preg_replace('#[ \t]+#', ' ', $text);
        $text = (string) preg_replace('#[ \t]*\n[ \t]*#', "\n", $text);
        $text = (string) preg_replace("#\n{3,}#", "\n\n", $text);
        return trim($text);
    }
}
