<?php

use Rapid\GatewayIR\Contracts\PaymentGateway;
use Rapid\GatewayIR\Payment;

if (!function_exists('payment')) {
    function payment(string $name = null): PaymentGateway
    {
        if (is_null($name)) {
            return Payment::primary();
        }

        return Payment::get($name);
    }
}
