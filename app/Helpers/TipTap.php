<?php

namespace Wappointment\Helpers;

class TipTap
{
    public static function toHTML($tiptapArray, $parentTag = false)
    {

        $html = '';
        $wrappingtag = self::getWrappingTag($tiptapArray, $parentTag);
        if ($wrappingtag !== false) {
            $html .= '<' . $wrappingtag . '>';
        }

        if (isset($tiptapArray['content'])) {
            foreach ($tiptapArray['content'] as $tiptapRow) {
                $html .= self::toHTML($tiptapRow, $wrappingtag);
            }
        } else {
            if ($tiptapArray['type'] == 'customfield') {
                $tiptapArray['text'] = '[' . $tiptapArray['attrs']['src'] . ':' . $tiptapArray['attrs']['alt'] . ']';
            }

            $html .= self::getMarksWrap($tiptapArray);
        }

        if ($wrappingtag !== false) {
            $html .= '</' . $wrappingtag . '>';
        }
        return $html;
    }

    public static function getWrappingTag($tiptapRow, $parentTag)
    {
        switch ($tiptapRow['type']) {
            case 'heading':
                return 'h' . $tiptapRow['attrs']['level'];
            case 'paragraph':
                return in_array($parentTag, ['li']) ? 'span' : 'p';
            case 'list_item':
                return 'li';
            case 'ordered_list':
                return 'ol';
            case 'bullet_list':
                return 'ul';
            case 'cblockphysical':
            case 'cblockphone':
            case 'cblockskype':
            case 'cblockzoom':
            case 'customfield':
            case 'text':
            case 'doc':
                return false;
        }
    }

    public static function simpleArrayToTipTap($bodyArray)
    {
        return [
            'type' => 'doc',
            'content' => self::toTiptap($bodyArray)
        ];
    }

    protected static function getMarkTag($type)
    {
        switch ($type) {
            case 'bold':
                return 'strong';
                break;
            case 'italic':
                return 'i';
            case 'underline':
                return 'u';
            case 'link':
                return 'a';
            case 'strike':
                return 's';
            default:
                // code...
                break;
        }
    }

    protected static function getMarkAttributes($params)
    {
        $attributes = ' ';
        if (isset($params['attrs'])) {
            if (isset($params['attrs']['href'])) {
                $attributes .= ' href="' . $params['attrs']['href'] . '" target="_blank" ';
            }
        }
        return $attributes;
    }

    protected static function getMarksWrap($tiptapArray)
    {
        $markedString = !empty($tiptapArray['text']) ? $tiptapArray['text'] : '<br/>';
        if (!empty($tiptapArray['marks'])) {
            foreach ($tiptapArray['marks'] as $key => $value) {
                $type = self::getMarkTag($value['type']);
                $markedString = '<' . $type .
                    self::getMarkAttributes($value) . '>' . $markedString . '</' . $type . '>';
            }
        }

        return $markedString;
    }

    protected static function toTiptap($bodyArray)
    {
        $convertedArray = [];
        foreach ($bodyArray as $keyValuePair) {
            foreach ($keyValuePair as $key => $value) {
                $conversionMethod = 'tiptap' . ucfirst($key);
                if (!is_array($value)) {
                    $value = [[
                        'p' => $value
                    ]];
                }
                foreach ($value as $val) {
                    foreach ($val as $typeval => $linevalue) {
                        $convertedArray[] = self::$conversionMethod($linevalue, $typeval);
                    }
                }
            }
        }

        return $convertedArray;
    }

    protected static function tiptapCf($model, $attribute)
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

    protected static function detectTag($value)
    {
        preg_match_all("/\[([^\]]*)\]/", $value, $matches);
        return $matches[1];
    }

    protected static function integrateContent($attributes, $value)
    {
        if (empty($value)) {
            return $attributes;
        }

        $detected_tags = self::detectTag($value);

        //detect custom fields and links
        if (!empty($detected_tags)) {
            $attributes['content'] = self::generateArrayContentTag($detected_tags, $value, $attributes);
        } else {
            $attributes['content'] = [
                [
                    'type' => 'text',
                    'text' => $value,
                ]
            ];
        }
        return $attributes;
    }

    protected static function generateArrayContentTag($detected_tags, $value, $attributes)
    {
        $wrapped_tags = [];
        foreach ($detected_tags as $tag) {
            $wrapped_tags[] = '[' . $tag . ']';
        }
        $tag_replacement = '<detectedtag>';
        $tempstring = str_replace($wrapped_tags, $tag_replacement, $value);

        $arrayString = explode($tag_replacement, $tempstring);
        $newArrayString = [];
        foreach ($arrayString as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $newArrayString[] = [
                'type' => 'text',
                'text' => $value,
            ];

            if (count($detected_tags) > 0) {
                $tag_to_use = array_shift($detected_tags);

                if (strpos($tag_to_use, ':') === false) {
                    //if must be a link since it noest a : separated values
                    preg_match_all(
                        "/(\s+)(\S+)=[\"']?((?:.(?![\"']?\s+(?:\S+)=|[>\"']))+.)[\"']?/",
                        $tag_to_use,
                        $matches
                    );
                    $attributeValues = [];
                    foreach ($matches[2] as $key => $attributeName) {
                        $attributeValues[$attributeName] = $matches[3][$key];
                    }
                    $model_String = !empty($attributeValues['model']) ? $attributeValues['model'] : 'appointment';
                    $newArrayString[] = [
                        'type' => 'text',
                        'marks' => [
                            [
                                'type' => 'link',
                                'attrs' => [
                                    'href' => '[' . $model_String . ':' . $attributeValues['link'] . ']',
                                ],
                            ]
                        ],
                        'text' => $attributeValues['label']
                    ];
                } else {
                    $modelValue = explode(':', $tag_to_use);
                    $data = [
                        'type' => 'customfield',
                        'attrs' => [
                            'src' => $modelValue[0],
                            'alt' => $modelValue[1],
                            'class' => 'customfield',
                            'title' => null,
                        ],
                    ];
                    if (isset($attributes['marks'])) {
                        $data['marks'] = $attributes['marks'];
                    }
                    $newArrayString[] = $data;
                }
            }
        }

        return $newArrayString;
    }

    protected static function typeToOptions($type = 'p')
    {
        if (strlen($type) == 2 && $type[0] == 'h') {
            return [
                'type' => 'heading',
                'attrs' => [
                    'level' => (int)$type[1],
                ]
            ];
        }

        return ['type' => 'paragraph'];
    }
    protected static function tiptapH3($value)
    {
        return self::integrateContent(self::typeToOptions('h3'), $value);
    }

    protected static function tiptapP($value, $type = 'p')
    {
        return self::integrateContent(self::typeToOptions($type), $value);
    }

    protected static function tiptapPb($value, $type = 'p')
    {
        $options = self::typeToOptions($type);
        $options['marks'] = [["type" => "bold"]];
        return self::integrateContent($options, $value);
    }

    protected static function tiptapPhysical($value, $type = 'p')
    {
        return [
            'type' => 'cblockphysical',
            'content' => [
                self::integrateContent(self::typeToOptions($type), $value)
            ]
        ];
    }

    protected static function tiptapPhone($value, $type = 'p')
    {
        return [
            'type' => 'cblockphone',
            'content' => [
                self::integrateContent(self::typeToOptions($type), $value)
            ]
        ];
    }

    protected static function tiptapSkype($value, $type = 'p')
    {
        return [
            'type' => 'cblockskype',
            'content' => [
                self::integrateContent(self::typeToOptions($type), $value)
            ]
        ];
    }

    protected static function tiptapZoom($value, $type = 'p')
    {
        return [
            'type' => 'cblockzoom',
            'content' => [
                self::integrateContent(self::typeToOptions($type), $value)
            ]
        ];
    }
}
