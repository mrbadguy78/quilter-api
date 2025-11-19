<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InsufficientFundsException extends HttpException
{
    public function __construct(string $message = 'Insufficient funds for withdrawal.')
    {
        parent::__construct(422, $message);
    }
}
