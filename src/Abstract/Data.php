<?php

namespace Rapid\GatewayIR\Abstract;

use Rapid\GatewayIR\Contracts\PaymentGateway;

abstract class Data
{

    public function __construct(
        protected PaymentGateway $gateway,
    )
    {
    }

}