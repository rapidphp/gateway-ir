<?php

namespace Rapid\GatewayIR;

use Closure;
use Rapid\GatewayIR\Contracts\PaymentGateway;

class PaymentFactory
{

    protected array $gateways = [];

    public function define(string $name, PaymentGateway|Closure $gateway): void
    {
        $this->gateways[$name] = $gateway;
    }

    public function get(string $name): ?PaymentGateway
    {
        if (!isset($this->gateways[$name])) {
            return null;
        }

        $gateway = $this->gateways[$name];

        if ($gateway instanceof Closure) {
            $this->gateways[$name] = $gateway = $gateway();
        }

        return $gateway;
    }

    protected string $gatewayPrimary = 'primary';

    protected string $gatewaySecondary = 'secondary';

    public function primary(): ?PaymentGateway
    {
        return $this->get($this->gatewayPrimary);
    }

    public function setPrimary(string $name, null|PaymentGateway|Closure $gateway = null): void
    {
        $this->gatewayPrimary = $name;

        if (isset($gateway)) {
            $this->define($name, $gateway);
        }
    }

    public function secondary(): ?PaymentGateway
    {
        return $this->get($this->gatewaySecondary);
    }

    public function setSecondary(string $name, null|PaymentGateway|Closure $gateway = null): void
    {
        $this->gatewayPrimary = $name;

        if (isset($gateway)) {
            $this->define($name, $gateway);
        }
    }

}