<?php

namespace Wappointment\Messages;

trait ConvertHtmlToText
{
    protected function convertHtmlToText($html, $fullConvert = true)
    {
        if ($fullConvert) {
            $html = preg_replace('# +#', ' ', $html);
            $html = str_replace(["\n", "\r", "\t"], '', $html);
        }
        $removepictureslinks = "#< *a[^>]*> *< *img[^>]*> *< *\/ *a *>#isU";
        $removeScript = '#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU';
        $removeStyle = '/<style\\b[^>]*>(.*?)<\\/style>/s';
        $removeStrikeTags = '#< *strike(?:(?!< */ *strike *>).)*< */ *strike *>#iU';
        $replaceByTwoReturnChar = '#< *(h1|h2)[^>]*>#Ui';
        $replaceByStars = '#< *li[^>]*>#Ui';
        $replaceByReturnChar1 = '#< */ *(li|td|tr|div|p)[^>]*> *< *(li|td|tr|div|p)[^>]*>#Ui';
        $replaceByReturnChar = '#< */? *(br|p|h1|h2|legend|h3|li|ul|h4|h5|h6|tr|td|div)[^>]*>#Ui';
        $replaceLinks = '/< *a[^>]*href *= *"([^#][^"]*)"[^>]*>(.*)< *\/ *a *>/Uis';
        $text = preg_replace(
            [
                $removepictureslinks, $removeScript, $removeStyle, $removeStrikeTags,
                $replaceByTwoReturnChar, $replaceByStars, $replaceByReturnChar1,
                $replaceByReturnChar, $replaceLinks
            ],
            ['', '', '', '', "\n\n", "\n* ", "\n", "\n", '${2} ( ${1} )'],
            $html
        );
        $text = str_replace(['Â ', '&nbsp;'], ' ', strip_tags($text));
        $text = trim(@html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        if ($fullConvert) {
            $text = preg_replace('# +#', ' ', $text);
            $text = preg_replace('#\n *\n\s+#', "\n\n", $text);
        }
        return $text;
    }
}
