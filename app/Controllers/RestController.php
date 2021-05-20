<?php

namespace Wappointment\Controllers;

use Wappointment\WP\Helpers as WPHelpers;

abstract class RestController
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
                    if (is_array($errors_field)) {
                        foreach ($errors_field as $error) {
                            $this->setError($error, $field);
                        }
                    } else {
                        $this->setError($errors_field);
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

    protected function registerPagination($request)
    {
        \Wappointment\ClassConnect\Paginator::currentPageResolver(function ($pageName = 'page') use ($request) {
            return (int)$request->input($pageName);
        });
    }

    public function tryExecute($param)
    {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            $this->turnDebugOn();
        }

        try {
            $args = $param->get_attributes()['args'];

            if (empty($args['wparams']) || empty($args['wparams']['method'])) {
                throw new \WappointmentException('There is no method defined', 1);
            }

            $methodName = $args['wparams']['method'];
            $request = WPHelpers::requestGet($param->get_params());

            if ($methodName == 'index' || !empty($args['wparams']['paginated'])) {
                $this->registerPagination($request);
            }

            // wrap request with validated data

            if (!empty($args['wparams']['hint'])) {
                if (class_exists($args['wparams']['hint'])) {
                    $class = $args['wparams']['hint'];
                } else {
                    $class = '\\Wappointment\\Validators\\HttpRequest\\' . $args['wparams']['hint'];
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
        if (is_string($error)) {
            $this->errors[$bag][] = $error;
        } else {
            $this->errors[$bag] = $error;
        }
    }
    private function hasOneErrorOnly()
    {
        if (count($this->errors) > 1 || count($this->errors[$this->arrayKeyFirst($this->errors)]) > 1) {
            return false;
        }
        return $this->errors[$this->arrayKeyFirst($this->errors)][0];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function arrayKeyFirst(array $arr)
    {
        foreach ($arr as $key => $unused) {
            return $key;
        }
        return null;
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
