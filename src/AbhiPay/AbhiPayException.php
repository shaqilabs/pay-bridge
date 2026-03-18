<?php

namespace ShaqiLabs\AbhiPay;

class AbhiPayException extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
