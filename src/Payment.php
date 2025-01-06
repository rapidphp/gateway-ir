<?php

namespace Rapid\GatewayIR;

use Closure;
use Illuminate\Support\Facades\Facade;
use Rapid\GatewayIR\Contracts\PaymentGateway;

/**
 * Class Payment
 *
 * This class serves as a facade for the PaymentFactory, providing a static interface
 * to manage payment gateways. It allows for defining, retrieving, and setting both
 * primary and secondary payment gateways through static method calls.
 *
 * @method static void define(string $name, PaymentGateway|Closure $gateway Registers a payment gateway by name.
 * @method static null|PaymentGateway get(string $name) Retrieves a payment gateway by name.
 * @method static null|PaymentGateway primary() Retrieves the primary payment gateway.
 * @method static null|PaymentGateway secondary() Retrieves the secondary payment gateway.
 * @method static void setPrimary(string|Closure $name, null|PaymentGateway|Closure $gateway = null) Sets the primary payment gateway.
 * @method static void setSecondary(string|Closure $name, null|PaymentGateway|Closure $gateway = null) Sets the secondary payment gateway.
 */
class Payment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PaymentFactory::class;
    }
}