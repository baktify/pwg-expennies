<?php

namespace App\Exceptions;

class InvalidLinkException extends \RuntimeException
{
    public function __construct(
        string      $message = "Invalid Link",
        int         $code = 400,
        ?\Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}