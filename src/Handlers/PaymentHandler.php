<?php

namespace Rapid\GatewayIR\Handlers;

use Illuminate\Queue\SerializesModels;

abstract class PaymentHandler
{
    use SerializesModels;

    public function getSetup(): HandleSetup
    {
        $setup = new HandleSetup();
        $this->setup($setup);

        return $setup;
    }

    public abstract function setup(HandleSetup $setup): void;

}