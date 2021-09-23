<?php

namespace Wappointment\Helpers;

class Foundation
{

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
