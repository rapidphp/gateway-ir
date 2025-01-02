<?php

namespace Rapid\GatewayIR;

use Closure;
use Illuminate\Support\Facades\Facade;
use Rapid\GatewayIR\Contracts\PaymentGateway;

/**
 * @method static void define(string $name, PaymentGateway|Closure $gateway)
 * @method static null|PaymentGateway get(string $name)
 * @method static null|PaymentGateway primary()
 * @method static null|PaymentGateway secondary()
 * @method static void setPrimary(string|Closure $name, null|PaymentGateway|Closure $gateway = null)
 * @method static void setSecondary(string|Closure $name, null|PaymentGateway|Closure $gateway = null)
 */
class Payment extends Facade
{

    protected static function getFacadeAccessor()
    {
        return PaymentFactory::class;
    }

}