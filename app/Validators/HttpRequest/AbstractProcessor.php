<?php

namespace Wappointment\Validators\HttpRequest;

use Wappointment\ClassConnect\Request;

abstract class AbstractProcessor implements InterfaceProcessor
{
    protected $validator = null;
    protected $request = null;
    protected $errors = [];
    protected $data = [];
    protected $autoResponse = false;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->validate();
        if ($this->autoResponse === true && $this->hasErrors()) {
            throw new \WappointmentValidationException('Review your fields', 1, null, $this->getErrors());
        }
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    public function getErrors()
    {
        if ($this->autoResponse === true) {
            return $this->errors;
        }
        return \Illuminate\Support\Arr::flatten($this->errors);
    }

    public function input($field)
    {
        return $this->request->input($field);
    }

    public function get($field)
    {
        return $this->data[$field] ?? false;
    }


    public function getData(): array
    {
        return $this->data;
    }

    public function prepareInputs($inputs)
    {
        return $inputs;
    }

    private function validate(): bool
    {
        $inputs = $this->request->all();

        $inputs = $this->prepareInputs($inputs);

        $this->validator = new \Rakit\Validation\Validator;
        $this->validator->setMessages($this->validationMessages());
        if (method_exists($this, 'addValidators')) {
            $this->addValidators();
        }

        $validation = $this->validator->make($inputs, $this->validationRules());
        $validation->validate();

        if ($validation->fails()) {
            $this->errors = $validation->errors()->toArray();
            return false;
        }

        $this->data = $validation->getValidData();

        return true;
    }
}
