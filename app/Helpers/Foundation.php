<?php

namespace Wappointment\Helpers;

use Wappointment\Messages\Templates\FoundationEmail;

class Foundation
{
    public static function convertTipTapToHTML($bodyTipTapContentArray)
    {
        $htmlBody = '';
        $foundationEmail = new FoundationEmail();
        foreach ($bodyTipTapContentArray as $key => $keyValuePair) {
            foreach ($keyValuePair as $key => $value) {
                $conversionMethod = 'convert' . ucfirst($key);
                $convertedArray[] = self::$conversionMethod($value);
            }
        }
    }

    protected function convertCustomfield($model, $attribute)
    {
        return [
            'type' => 'customfield',
            'attrs' => [
                'src' => $model,
                'alt' => $attribute,
                'class' => 'customfield',
                'title' => null,
            ],
        ];
    }

    protected static function convertH3($value)
    {
        return [
            'type' => 'heading',
            'attrs' => [
                'level' => 3,
            ],
            'content' => [
                [
                    'type' => 'text',
                    'text' => $value,
                ]
            ]
        ];
    }

    protected static function convertP($value)
    {
        if (empty($value)) {
            return [
                'type' => 'paragraph',
            ];
        }
        return [
            'type' => 'paragraph',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $value,
                ]
            ]
        ];
    }
}
