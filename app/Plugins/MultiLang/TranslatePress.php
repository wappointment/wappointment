<?php

namespace Wappointment\Plugins\MultiLang;

use Wappointment\Plugins\Contract\PluginMultilang;

class TranslatePress implements PluginMultilang
{
    public function languages()
    {
        return trp_get_languages();
    }
}
