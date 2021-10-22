<?php

if (!defined('WAPPOINTMENT_SITE')) define('WAPPOINTMENT_SITE', 'https://wappointment.com');
define('WAPPOINTMENT_DB_FORMAT', 'Y-m-d H:i');

use Wappointment\ClassConnect\Arr;
use Wappointment\ClassConnect\Collection;
use Wappointment\ClassConnect\HigherOrderTapProxy;
use Wappointment\ClassConnect\Carbon;

/**
 * Simple widget insertion in php code useful for custom made versions
 *
 * @param mixed $attr
 * @param boolean $return
 * @return void
 */
function wappointment_booking_widget($attr, $return = false)
{
    $button_title = (is_array($attr) && isset($attr['button'])) ? $attr['button'] : (!empty($attr) ? $attr : __('Book now!', 'wappointment'));
    $widget = \Wappointment\WP\Widget::baseHtml($button_title);
    if ($return) {
        return $widget;
    }
    echo $widget;
}

if (!function_exists('availdd')) {
    function availdd($availability)
    {
        foreach ($availability as $key => $avail) {
            $availability[$key] = [
                0 => Carbon::createFromTimestamp($avail[0])->toDateTimeString(),
                1 => Carbon::createFromTimestamp($avail[1])->toDateTimeString(),
            ];
        }
        dd($availability);
    }
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



class WappointmentLv
{
    public static function isTest()
    {
        return substr(WAPPOINTMENT_SITE, -5) === '.test' || substr(WAPPOINTMENT_SITE, -3) === '.fr' || defined('WAPPOINTMENT_TEST');
    }
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


    public static function minRequirements()
    {
        $fails = [];
        if (!extension_loaded('pdo')) {
            $fails[] = 'PDO extension missing in your PHP configuration';
            if (!defined('WAPPOINTMENT_PDO_FAIL')) {
                define('WAPPOINTMENT_PDO_FAIL', true);
            }
        }

        if (version_compare(PHP_VERSION, WAPPOINTMENT_PHP_MIN) < 0) {
            /* translators: %s - PHP Version number. */
            $fails[] = sprintf(esc_html__('Minimum PHP version required %s', 'wappointment'), WAPPOINTMENT_PHP_MIN);
            if (!defined('WAPPOINTMENT_PHP_FAIL')) {
                define('WAPPOINTMENT_PHP_FAIL', true);
            }
        }
        return $fails;
    }
}


$installed_at = get_option('wappointment_installation_time');

if (empty($installed_at)) {
    $fails_min_req = WappointmentLv::minRequirements();

    function wappo_setup_error_write()
    {
        echo wappo_get_error_message();
    }

    function wappo_get_error_message()
    {
        $fails_min_req = WappointmentLv::minRequirements();
        $messagehtml = '';
        if (!empty($fails_min_req)) {

            $messagehtml = '<div class="notice notice-error"> Error Installing Wappointment';
            foreach ($fails_min_req as $message) {
                $messagehtml .= '<div>' . $message . '</div>';
            }
            $messagehtml .= '<div>You can ask for help here <a href="http://wappointment.com/support" target="_blank">http://wappointment.com/support</a></div></div>';
        }
        return $messagehtml;
    }

    if (defined('WAPPOINTMENT_PHP_FAIL') && strpos($_SERVER['REQUEST_URI'], '/wp-admin/plugins.php') !== false) {
        add_action('admin_notices', 'wappo_setup_error_write');
    }
}
