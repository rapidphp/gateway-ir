<?php

namespace Rapid\GatewayIR;

use Closure;
use Illuminate\Support\Facades\Facade;
use Rapid\GatewayIR\Contracts\PaymentGateway;

/**
 * @method void define(string $name, PaymentGateway|Closure $gateway)
 * @method null|PaymentGateway get(string $name)
 * @method null|PaymentGateway primary()
 * @method null|PaymentGateway secondary()
 * @method void setPrimary(string $name, null|PaymentGateway|Closure $gateway = null)
 * @method void setSecondary(string $name, null|PaymentGateway|Closure $gateway = null)
 */
class Payment extends Facade
{

    protected static function getFacadeAccessor()
    {
        return PaymentFactory::class;
    }

}