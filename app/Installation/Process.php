<?php

namespace Wappointment\Installation;

use Wappointment\WP\Helpers as WPHelpers;

class Process
{
    private $errors = [];
    private $steps = [
        'Wappointment\Installation\Steps\CreateMigrationTable',
        'Wappointment\Installation\Steps\CreateTables',
        //'Wappointment\Installation\Steps\SeedData',
        true
    ];
    private $complete = false;

    private $currentStep = '';

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

    private function testSystem()
    {
        return (new Check())->getErrors();
    }

    private function isUpToDate()
    {
        if (empty(WPHelpers::getOption('installation_completed'))) return false;
        return true;
    }

    private function saveCurrentStep($stepValue)
    {
        return WPHelpers::setOption('installation_step', $stepValue);
    }

    private function loadCurrentStep()
    {
        return WPHelpers::getOption('installation_step');
    }

    private function runStep()
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

    private function stepSuccess()
    {
        $this->setNextStep();
        if (!$this->complete) {
            $this->runStep();
        }
    }

    private function setNextStep()
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
            $this->runWPspecials();
            return $this->completed();
        }

        //we save the new step whil the instalation process is on
        $this->saveCurrentStep($this->currentStep);
    }

    private function runWPspecials()
    {
        (new \Wappointment\WP\CustomPage())->install();
    }

    private function completed()
    {
        $this->complete = true;
        WPHelpers::deleteOption('installation_step');
        \Wappointment\Services\Settings::save('activeStaffId', WPHelpers::userId());
        \Wappointment\Services\Settings::save('email_notifications', WPHelpers::currentUserEmail());

        return WPHelpers::setOption('installation_completed', (int) current_time('timestamp'), true);
    }
}
