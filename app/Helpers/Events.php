<?php

namespace Wappointment\Helpers;

class Events
{
    public static function dispatch($eventClassName, $args)
    {

        try {
            $eventInstance = self::getEventInstance($eventClassName, apply_filters('wappointment_' . $eventClassName, $args));
            $eventInstance->callWPAction();
            return self::getDispatcherInstance()->dispatch($eventInstance::NAME, $eventInstance);
        } catch (\WappointmentException $e) {
            return false;
        }
    }

    public static function listens($eventName, $listenerClassName, $full_namespace = false)
    {
        $listenerInstance = self::getListenerInstance($listenerClassName, $full_namespace);
        self::getDispatcherInstance()->addListener($eventName, [$listenerInstance, 'handle']);
    }

    public static function getDispatcherInstance()
    {
        static $dispatcher = false;
        if ($dispatcher === false) {
            $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher;
        }
        return $dispatcher;
    }

    public static function getListenerInstance($listenerClassName, $full_namespace = false)
    {
        return self::getCustomInstance($listenerClassName, 'Listeners', false, $full_namespace);
    }

    public static function getEventInstance($eventClassName, $args)
    {
        return self::getCustomInstance($eventClassName, 'Events', $args);
    }

    public static function getCustomInstance($className, $type = 'Events', $args = false, $full_namespace = false)
    {
        $className = $full_namespace ? $className : '\\Wappointment\\' . $type . '\\' . $className;
        if (!class_exists($className)) {
            throw new \WappointmentException('Cannot load ' . $type . ' instance ' . $className, 1);
        }

        if (empty($args)) {
            return new $className;
        }

        return new $className($args);
    }
}
