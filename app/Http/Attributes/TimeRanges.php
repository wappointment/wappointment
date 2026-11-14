<?php
declare(strict_types=1);

namespace Wappointment\Http\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class TimeRanges implements Constraint
{
    public function __construct(private ?int $max = null) {}

    public function rules(): array
    {
        $rules = [
            'type' => 'array',
            'items' => [
                'type' => 'array', 'minItems' => 2, 'maxItems' => 2,
                'items' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 1440],
            ],
        ];
        if ($this->max !== null) {
            $rules['maxItems'] = $this->max;
        }
        return $rules;
    }
}
