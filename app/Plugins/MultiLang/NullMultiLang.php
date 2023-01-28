<?php

namespace Wappointment\Plugins\MultiLang;

use Wappointment\Plugins\Contract\PluginMultilang;

class NullMultiLang extends AbstractMultilang implements PluginMultilang
{
    public function languages()
    {
        return false;
    }

    public function multilang()
    {
        return false;
    }
}
