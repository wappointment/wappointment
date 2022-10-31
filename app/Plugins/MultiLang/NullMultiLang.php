<?php

namespace Wappointment\Plugins\MultiLang;

use Wappointment\Plugins\Contract\PluginMultilang;

class NullMultiLang implements PluginMultilang
{
    public function languages()
    {
        return false;
    }
}
