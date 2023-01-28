<?php

namespace Wappointment\Plugins\MultiLang;

abstract class AbstractMultilang
{
    public function locale()
    {
        return get_locale();
    }

    public function lang()
    {
        return $this->localeToLang($this->locale());
    }

    public function localeToLang($locale)
    {
        return substr($locale, 0, 2);
    }

    public function multilang()
    {
        return true;
    }
}
