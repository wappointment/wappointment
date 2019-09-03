<?php

namespace Wappointment\Installation;

class MethodsRunner
{
    private $errors = [];

    public function __construct()
    {
        $this->checkAll();
    }

    private function checkAll()
    {
        $class = new \ReflectionClass($this);
        //start all the traits init methods
        foreach ($class->getMethods(\ReflectionMethod::IS_PROTECTED) as $method) {
            $methodName = $method->name;

            if (\WappointmentLv::starts_with($methodName, 'can')) {
                try {
                    $this->$methodName();
                } catch (\WappointmentException $e) {
                    $this->setError($e->getMessage(), $class->name);
                }
            }
        }
    }

    private function setError($error, $bag = 'default')
    {
        $this->errors[$bag][] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
