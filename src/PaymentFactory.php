<?php

namespace Rapid\GatewayIR;

use Closure;
use Rapid\GatewayIR\Contracts\PaymentGateway;

class PaymentFactory
{

    protected array $gateways = [];

    public function define(string $name, PaymentGateway|Closure $gateway): void
    {
        $gateway->register($name);

        if ($gateway instanceof PaymentGateway) {
            $this->gateways[$name] = $gateway;
        }
    }

    public function get(string $name): ?PaymentGateway
    {
        if (!isset($this->gateways[$name])) {
            return null;
        }

        $gateway = $this->gateways[$name];

        if (is_array($gateway)) {
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

    protected Closure|string $gatewayPrimary = 'primary';

    protected Closure|string $gatewaySecondary = 'secondary';

    public function primary(): ?PaymentGateway
    {
        if (is_string($this->gatewayPrimary)) {
            return $this->get($this->gatewayPrimary);
        }

        $gateway = ($this->gatewayPrimary)();

        if (is_string($gateway)) {
            return $this->get($gateway);
        }

        return $gateway;
    }

    public function setPrimary(string|Closure $name, null|PaymentGateway|Closure $gateway = null): void
    {
        $this->gatewayPrimary = $name;

        if (is_string($name) && isset($gateway)) {
            $this->define($name, $gateway);
        }
    }

    public function secondary(): ?PaymentGateway
    {
        if (is_string($this->gatewaySecondary)) {
            return $this->get($this->gatewaySecondary);
        }

        $gateway = ($this->gatewaySecondary)();

        if (is_string($gateway)) {
            return $this->get($gateway);
        }

        return $gateway;
    }

    public function setSecondary(string|Closure $name, null|PaymentGateway|Closure $gateway = null): void
    {
        $this->gatewayPrimary = $name;

        if (is_string($name) && isset($gateway)) {
            $this->define($name, $gateway);
        }
    }

}