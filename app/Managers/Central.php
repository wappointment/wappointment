<?php

namespace Wappointment\Managers;

use Wappointment\Services\VersionDB;
// @codingStandardsIgnoreFile
/**
 * Singleton to replace specific core classes with addons classes
 */
class Central
{
    protected $services = [
        'Service' => [
            'class' => \Wappointment\Services\Service::class,
            'implements' => \Wappointment\Services\ServiceInterface::class
        ],
        'Client' => [
            'class' => \Wappointment\Models\Client::class,
        ],
        'ServiceModel' => [
            'class' => \Wappointment\Models\Service::class,
        ],
        'AppointmentModel' => [
            'class' => \Wappointment\Models\Appointment::class,
        ],
        'CustomFields' => [
            'class' => \Wappointment\Services\CustomFields::class,
        ],
        'CalendarModel' => [
            'class' => \Wappointment\Models\Calendar::class,
        ],
    ];

    public function __construct()
    {
        $this->setOverride();
        if (VersionDB::atLeast(VersionDB::CAN_CREATE_SERVICES)) {
            $this->services['Service']['class'] = \Wappointment\Services\Services::class;
        }
    }

    /**
     * Class is statically generated when instance or get is called satically
     *
     * @return void
     */
    public static function instance()
    {
        static $manager_instance = null;
        if (is_null($manager_instance)) {
            $manager_instance = new self;
        }
        return $manager_instance;
    }

    public static function get($serviceName)
    {
        return static::instance()->getService($serviceName)['class'];
    }

    public function has($serviceName)
    {
        if (!isset($this->services[$serviceName])) {
            throw new \WappointmentException("Service '" . $serviceName . "' is not registered", 1);
        }
        return true;
    }


    public function replace($serviceName, $class)
    {
        if ($this->has($serviceName)) {
            $this->set($serviceName, $class);
        }
    }

    public function getService($serviceName)
    {
        if ($this->has($serviceName)) {
            return $this->services[$serviceName];
        }
    }

    protected function set($serviceName, $class)
    {
        $service = $this->getService($serviceName);

        if (isset($service['implements']) && !in_array($service['implements'], class_implements($class))) {
            throw new \WappointmentException("Central: " . $serviceName . ' class ' . $class . ' not implementing ' . $service['implements'], 1);
        }
        $this->services[$serviceName]['class'] = $class;
    }

    private function setOverride()
    {
        foreach ($this->services as $serviceKey => $serviceDefinition) {
            $this->services[$serviceKey] = apply_filters('wappointment_central_service_' . strtolower($serviceKey), $serviceDefinition);
        }
    }
}
