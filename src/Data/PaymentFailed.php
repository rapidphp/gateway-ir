<?php

namespace Rapid\GatewayIR\Data;

use Rapid\GatewayIR\Exceptions\GatewayException;

abstract class PaymentFailed extends Data
{

    public GatewayException $exception;

    public function translate($locate = null): string
    {
        return $this->exception->translate($locate);
    }

}