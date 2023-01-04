<?php

namespace Yep\Pay\External\Exception;

class ValidationException extends YepPayException
{
    public $errors;
    public function __construct($message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }
}
