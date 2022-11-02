<?php

namespace Wappointment\Plugins\MultiLang;

use Wappointment\ClassConnect\Collection;
use Wappointment\Plugins\Contract\PluginMultilang;

class TranslatePress extends AbstractMultilang implements PluginMultilang
{
    public function languages()
    {
        return $this->getLanguagesArray();
    }

    private function getLanguagesArray()
    {
        return array_values((new Collection(trp_get_languages()))->map(function ($item, $key) {
            return [
                'locale' => $key,
                'name' => $item
            ];
        })->toArray());
    }

    public static function hackTranslatePress()
    {
        // avoid an issue breaking our email sending when translatePRess is activated
        remove_all_filters('trp_before_translate_content');
    }
}
