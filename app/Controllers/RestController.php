<?php

namespace Wappointment\Controllers;

use Wappointment\WP\Helpers as WPHelpers;

class RestController
{
    private $errors = [];

    /**
     * field validation
     */
    protected function isTrueOrFail($result)
    {
        if ($result === true) {
            return ['message' => 'Data is saved'];
        } else {
            //$resultkey
            if (empty($result)) {
                $this->setError('There was no identifiable response');
            } else {
                foreach ($result as $field => $errors_field) {
                    foreach ($errors_field as $error) {
                        $this->setError($error, $field);
                    }
                }
            }
        }
        return false;
    }

    public function turnDebugOn()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    public function tryExecute($param)
    {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            $this->turnDebugOn();
        }

        try {
            $methodName = $param->get_attributes()['args']['method'];
            if (empty($methodName)) {
                throw new \WappointmentException('There is no method defined', 1);
            }
            $request = WPHelpers::requestGet($param->get_params());

            // wrap request with validated data

            if (!empty($param->get_attributes()['args']['hint'])) {
                if (class_exists($param->get_attributes()['args']['hint'])) {
                    $class = $param->get_attributes()['args']['hint'];
                } else {
                    $class = '\\Wappointment\\Validators\\HttpRequest\\' . $param->get_attributes()['args']['hint'];
                }

                $request = new $class($request);
            }
            if (!method_exists($this, $methodName)) {
                throw new \WappointmentException('Method ' . $methodName . ' does not exist', 1);
            }
            $result = $this->$methodName($request);
        } catch (\WappointmentValidationException $e) {
            $this->setError($e->getMessage());
            $this->setError($e->getValidationErrors(), 'validations');
        } catch (\Exception $e) {
            if (WP_DEBUG) {
                $this->setError($e->getMessage(), get_class($this));
            } else {
                $this->setError($e->getMessage());
            }
        }
        if (!empty($this->getErrors())) {
            if ($this->hasOneErrorOnly()) {
                return WPHelpers::restError($this->hasOneErrorOnly(), 500);
            }
            $errors = $this->getErrors();

            return WPHelpers::restError($errors['default'][0], 500, $errors);
        }

        return $result;
    }

    protected function setError($error, $bag = 'default')
    {
        if (is_string($error)) $this->errors[$bag][] = $error;
        else  $this->errors[$bag] = $error;
    }
    private function hasOneErrorOnly()
    {
        if (count($this->errors) > 1 || count($this->errors[array_key_first($this->errors)]) > 1) return false;
        return $this->errors[array_key_first($this->errors)][0];
    }
    public function getErrors()
    {
        return $this->errors;
    }

    public function prepareErrors()
    {
        $errors_string = '';

        foreach ($this->getErrors() as $error_key => $errors) {
            foreach ($errors as $error) {
                $errors_string .= $error . "\n";
            }
        }
        return $errors_string;
    }
}
