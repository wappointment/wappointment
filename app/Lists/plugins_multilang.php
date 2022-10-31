<?php

return [
  'contract' => Wappointment\Plugins\Contract\PluginMultilang::class,
  'implementations' => [
    'translatepress-multilingual/index.php' => Wappointment\Plugins\MultiLang\TranslatePress::class,
    'default' => Wappointment\Plugins\MultiLang\NullMultiLang::class
  ]
];
