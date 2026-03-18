<?php

namespace ShaqiLabs\AlfalahAPG;
use Exception;

class AlfalahAPGException extends Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
