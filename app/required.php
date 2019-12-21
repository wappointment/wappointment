<?php

if (!defined('WAPPOINTMENT_SITE')) define('WAPPOINTMENT_SITE', 'https://wappointment.com');
define('WAPPOINTMENT_DB_FORMAT', 'Y-m-d H:i');



register_activation_hook(WAPPOINTMENT_FILE, ['WappointmentLv', 'activating']);



/**
 * Simple widget insertion in php code useful for custom made versions
 *
 * @param mixed $attr
 * @param boolean $return
 * @return void
 */
function wappointment_booking_widget($attr, $return = false)
{
    $button_title = (is_array($attr) && isset($attr['button'])) ? $attr['button'] : (!empty($attr) ? $attr : 'Book now!');
    $widget = \Wappointment\WP\Widget::baseHtml($button_title);
    if ($return) {
        return $widget;
    }
    echo $widget;
}
/**
 * @method string getMessage()
 * @method \Throwable|null getPrevious()
 * @method mixed getCode()
 * @method string getFile()
 * @method int getLine()
 * @method array getTrace()
 * @method string getTraceAsString()
 */
class WappointmentException extends \Exception
{
}
class WappointmentValidationException extends \Exception
{
    public $validationErrors = [];
    public function __construct($errorMessage, $code = 0, $previous = null,  $validationsExceptions = [])
    {
        $this->validationErrors = $validationsExceptions;
        parent::__construct($errorMessage, $code, $previous);
    }
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}

use Wappointment\ClassConnect\Arr;
use Wappointment\ClassConnect\Collection;
use Wappointment\ClassConnect\HigherOrderTapProxy;

class WappointmentLv
{
    public static function blank($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }

    public static function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    public static function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_merge([$class => $class], class_parents($class)) as $class) {
            $results += self::trait_uses_recursive($class);
        }

        return array_unique($results);
    }

    public static function collect($value)
    {
        return new Collection($value);
    }

    public static function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return self::value($default);
                }

                $result = Arr::pluck($target, $key);

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return self::value($default);
            }
        }

        return $target;
    }

    public static function filled($value)
    {
        return !self::blank($value);
    }

    public function head($array)
    {
        return reset($array);
    }

    public static function last($array)
    {
        return end($array);
    }

    public static function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTapProxy($value);
        }

        $callback($value);

        return $value;
    }

    public static function transform($value, callable $callback, $default = null)
    {
        if (self::filled($value)) {
            return $callback($value);
        }

        if (is_callable($default)) {
            return $default($value);
        }

        return $default;
    }

    public static function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += self::trait_uses_recursive($trait);
        }

        return $traits;
    }

    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    public static function starts_with($lookin, $lookfor)
    {
        return strpos($lookin, $lookfor) === 0;
    }
    public static function windows_os()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }

    public static function function_exists($function)
    {
        return function_exists($function);
    }

    public static function blogname()
    {
        return self::quotedString(get_option('blogname'), ENT_QUOTES);
    }
    public static function quotedString($string)
    {
        return html_entity_decode($string, ENT_QUOTES);
    }
    public static function activating()
    {
        if (version_compare(PHP_VERSION, WAPPOINTMENT_PHP_MIN) < 0) {
            die('Your website\'s PHP version(' . PHP_VERSION . ') is lower to our minimum requirement ' . WAPPOINTMENT_PHP_MIN . '. Contact us at <a href="https://wappointment.com/support?php_too_low=1" target="_blank">https://wappointment.com/support</a> for help.');
        }
    }
}
