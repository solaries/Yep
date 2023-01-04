<?php

namespace Yep\Pay\External\Exception;

class YepPayException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
