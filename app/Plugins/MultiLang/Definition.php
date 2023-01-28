<?php

namespace Wappointment\Plugins\MultiLang;

use Wappointment\Plugins\Contract\PluginDefinition;

class Definition implements PluginDefinition
{
    public function contract()
    {
        return \Wappointment\Plugins\Contract\PluginMultilang::class;
    }

    public function implementations()
    {
        return [
          'translatepress-multilingual/index.php' => \Wappointment\Plugins\MultiLang\TranslatePress::class,
          'default' => \Wappointment\Plugins\MultiLang\NullMultiLang::class
        ];
    }
}
