<?php

namespace Wappointment\Services;

class Forms
{
    protected $fields = [];
    public function __construct($fields)
    {
        $this->fields = $fields;
    }
    public function addField($field, $atLocation = false)
    {
        if ($atLocation !== false) {
            $first_part = array_splice($this->fields, (int) $atLocation);
            $last_part = array_splice($this->fields, (int) $atLocation - count($this->fields));
            $first_part[] = $field;
            $this->fields = array_merge($first_part, $last_part);
        } else {
            $this->fields[] = $field;
        }
    }
    public function removeField($fieldId)
    {
        $this->fields = array_filter($this->fields, function ($value) use ($fieldId) {
            return $value['id'] != $fieldId;
        });
    }
    public function getHtml()
    {
        $formHtml = '';

        foreach ($this->fields as $key => $field) {
            switch ($field['type']) {
                case 'text':
                    $formHtml .= $this->fieldInput($field);
                    break;
                case 'checkbox':
                    $formHtml .= $this->fieldCheckbox($field);
                    break;
                case 'select':
                    $formHtml .= $this->fieldSelect($field);
                    break;

                default:
                    $formHtml .= '<div class="' . (empty($field['class']) ? '' : $field['class']) . '">' . $field['label'] . '</div>';
                    break;
            }
        }
        return $formHtml;
    }

    public function fieldInput($field)
    {
        return '<p>' .
            '<label for="' . $field['id'] . '">' . $field['label'] . '</label>' .
            '<input class="widefat" id="' . $field['id']
            . '" name="' . $field['name'] . '" type="text" value="' . $field['value'] . '">' .
            '</p>';
    }

    public function fieldCheckbox($field)
    {

        $checked = !empty($field['value']) ? ' checked ' : '';
        return '<p>' .
            '<input class="checkbox" type="checkbox" ' . $checked
            . ' id="' . $field['id'] . '" name="' . $field['name'] . '" />' .
            '<label for="' . $field['id'] . '">' . $field['label'] . '</label>' .
            '</p>';
    }

    public function fieldSelect($field)
    {

        $dropdown =  '<p>' .
            '<label for="' . $field['id'] . '">' . $field['label'] . '</label>' .
            '<select class="widefat" id="' . $field['id'] . '" name="' . $field['name'] . '">';

        foreach ($field['values'] as $key => $object) {
            $selected = $field['value'] == $object['value'] ? ' selected ' : '';
            $dropdown .=  '<option value="' . $object['value']
                .  '" ' . $selected . '>' . $object['label'] . '</option>';
        }

        $dropdown .=  '</select>' .
            '</p>';
        return $dropdown;
    }
}
