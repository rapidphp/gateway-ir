<?php

namespace Rapid\GatewayIR;

use Closure;
use Illuminate\Support\Facades\Facade;
use Rapid\GatewayIR\Contracts\PaymentGateway;

/**
 * @method static void define(string $name, PaymentGateway|Closure $gateway) Registers a payment gateway by name.
 * @method static null|PaymentGateway get(string $name) Retrieves a payment gateway by name.
 * @method static null|PaymentGateway primary() Retrieves the primary payment gateway.
 * @method static null|PaymentGateway secondary() Retrieves the secondary payment gateway.
 * @method static void setPrimary(string|Closure $name, null|PaymentGateway|Closure $gateway = null) Sets the primary payment gateway.
 * @method static void setSecondary(string|Closure $name, null|PaymentGateway|Closure $gateway = null) Sets the secondary payment gateway.
 * @method static string getModel() Get the transaction model class.
 * @method static void clearExpiredRecords() Clear the expired records.
 */
class Payment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PaymentFactory::class;
    }
}