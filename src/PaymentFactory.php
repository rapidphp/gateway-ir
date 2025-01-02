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

        if ($gateway instanceof PaymentGateway) {
            $gateway->register($name);
        }
    }

    public function get(string $name): ?PaymentGateway
    {
        if (!isset($this->gateways[$name])) {
            return null;
        }

        $gateway = $this->gateways[$name];

        if ($gateway instanceof Closure) {
            $gateway = $gateway();

            if (isset($gateway) && !($gateway instanceof PaymentGateway)) {
                throw new \TypeError(sprintf(
                    "Gateway resolver of [%s] returned [%s], expected [%s]",
                    $name,
                    is_object($gateway) ? get_class($gateway) : gettype($gateway),
                    PaymentGateway::class,
                ));
            }

            if (isset($gateway)) {
                $gateway->register($name);
            }

            $this->gateways[$name] = $gateway;
        }

        return $gateway;
    }

    protected string $gatewayPrimary = 'primary';

    protected string $gatewaySecondary = 'secondary';

    public function primary(): ?PaymentGateway
    {
        return $this->get($this->gatewayPrimary);
    }

    public function setPrimary(string|Closure $name, null|PaymentGateway|Closure $gateway = null): void
    {
        if ($name instanceof Closure) {
            $gateway = $name;
            $name = 'primary';
        }

        $this->gatewayPrimary = $name;

        if (isset($gateway)) {
            $this->define($name, $gateway);
        }
    }

    public function secondary(): ?PaymentGateway
    {
        return $this->get($this->gatewaySecondary);
    }

    public function setSecondary(string|Closure $name, null|PaymentGateway|Closure $gateway = null): void
    {
        if ($name instanceof Closure) {
            $gateway = $name;
            $name = 'secondary';
        }

        $this->gatewayPrimary = $name;

        if (isset($gateway)) {
            $this->define($name, $gateway);
        }
    }

}