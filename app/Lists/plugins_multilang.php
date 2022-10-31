<?php

use Wappointment\Plugins\MultiLang\NullMultiLang;
use Wappointment\Plugins\MultiLang\TranslatePress;

return [
  'translatepress-multilingual/index.php' => TranslatePress::class,
  'default' => NullMultiLang::class
];
