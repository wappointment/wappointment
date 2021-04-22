<?php

namespace Wappointment\ClassConnect;

if (defined('WP_ELOQUENT')) {
    class Model extends \Wappointment\WPEloquent\Model
    {
    }
} else {
    class Model extends \Illuminate\Database\Eloquent\Model
    {
    }
}
