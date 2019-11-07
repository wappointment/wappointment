<?php

namespace Wappointment\Installation;

use Wappointment\WP\Helpers as WPHelpers;

class AbstractProcess
{
    protected $errors = [];
    protected $steps = [];
    protected $key = '';
    protected $complete = false;
    protected $currentStep = '';

    public function __construct()
    {

        if (!$this->isUpToDate()) {
            $errors_bag = $this->testSystem();

            if (empty($errors_bag)) {
                $this->currentStep = $this->loadCurrentStep();

                $this->runStep();
            } else {
                $this->errors = $errors_bag;
            }

            //once we ran the installation test and the instalation process we check that all is fine
            if (!empty($this->errors)) {
                throw new \WappointmentValidationException("Installation interrupted", 1, null, $this->errors);
            }
        }
    }

    public function result()
    {
        if (!empty($this->errors)) {
            return $this->errors;
        }
        return true;
    }

    protected function testSystem()
    {
        return (new Check())->getErrors();
    }

    protected function isUpToDate()
    {
        return true;
    }


    protected function runStep()
    {
        if (empty($this->currentStep)) {
            $this->setNextStep();
        }
        $currentStep = $this->currentStep;

        $runningStepObject = new $currentStep();

        $errors_step = $runningStepObject->getErrors();
        if (empty($errors_step)) {
            $this->stepSuccess();
        } else {
            $this->errors = $errors_step;
        }
    }

    protected function stepSuccess()
    {
        $this->setNextStep();
        if (!$this->complete) {
            $this->runStep();
        }
    }

    protected function setNextStep()
    {
        $indexCurrentStep = 0;

        if (!empty($this->currentStep)) {
            $indexCurrentStep = array_search($this->currentStep, $this->steps);

            if ($indexCurrentStep === false) {
                throw new \WappointmentException('Instalation step cannot be found(' . $this->currentStep . ')');
            }
            $indexCurrentStep++;
        }

        if (!isset($this->steps[$indexCurrentStep])) {
            throw new \WappointmentException('Instalation failure trying to run inexistant step');
            return;
        }

        $this->currentStep = $this->steps[$indexCurrentStep];

        // only when we reach the end of the instalation
        if ($this->currentStep === true) {
            //we then set a flag to say we've been through the instalation
            WPHelpers::deleteOption($this->key);
            return $this->completed();
        }

        //we save the new step whil the instalation process is on
        $this->saveCurrentStep($this->currentStep);
    }


    protected function saveCurrentStep($stepValue)
    {
        return WPHelpers::setOption($this->key, $stepValue);
    }

    protected function loadCurrentStep()
    {
        return WPHelpers::getOption($this->key);
    }
    protected function completed()
    {
        return true;
    }
}
