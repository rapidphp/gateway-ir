<?php

namespace Rapid\GatewayIR\Data;

use Rapid\GatewayIR\Contracts\PaymentGateway;

abstract class Data
{

    public function __construct(
        protected PaymentGateway $gateway,
    )
    {
    }

}