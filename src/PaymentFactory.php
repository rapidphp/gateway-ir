<?php

namespace Rapid\GatewayIR;

use Closure;
use Rapid\GatewayIR\Contracts\PaymentGateway;

/**
 * Factory class for managing payment gateways.
 *
 * This class is responsible for defining, retrieving, and managing
 * payment gateway instances. It allows for the registration of
 * gateways by name and provides methods to access primary and
 * secondary gateways. The factory can handle both concrete
 * implementations of the PaymentGateway interface and closures
 * that return gateway instances.
 */
class PaymentFactory
{
    /**
     * An array of defined payment gateways.
     *
     * @var array
     */
    protected array $gateways = [];

    /**
     * The name of the primary payment gateway.
     *
     * @var string
     */
    protected string $gatewayPrimary = 'primary';

    /**
     * The name of the secondary payment gateway.
     *
     * @var string
     */
    protected string $gatewaySecondary = 'secondary';

    /**
     * Defines a payment gateway by name.
     *
     * This method registers a payment gateway instance or a closure
     * that returns a payment gateway. If the provided gateway is an
     * instance of PaymentGateway, it will also be registered with
     * the given name.
     *
     * @param string $name The name of the payment gateway.
     * @param PaymentGateway|Closure $gateway The payment gateway instance or a closure that returns it.
     * @return void
     */
    public function define(string $name, PaymentGateway|Closure $gateway): void
    {
        $this->gateways[$name] = $gateway;

        if ($gateway instanceof PaymentGateway) {
            $gateway->register($name);
        }
    }

    /**
     * Retrieves a payment gateway by name.
     *
     * This method returns the payment gateway associated with the
     * given name. If the gateway is defined as a closure, it will
     * be resolved and registered before being returned.
     *
     * @param string $name The name of the payment gateway to retrieve.
     * @return PaymentGateway|null The payment gateway instance or null if not found.
     * @throws \TypeError If the resolved gateway is not an instance of PaymentGateway.
     */
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
                    'Gateway resolver of [%s] returned [%s], expected [%s]',
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

    /**
     * Retrieves the primary payment gateway.
     *
     * @return PaymentGateway|null The primary payment gateway instance or null if not defined.
     */
    public function primary(): ?PaymentGateway
    {
        return $this->get($this->gatewayPrimary);
    }

    /**
     * Sets the primary payment gateway.
     *
     * This method allows for defining a new primary payment gateway
     * by name or closure. If a gateway is provided, it will be
     * registered with the specified name.
     *
     * @param string|Closure $name The name of the primary payment gateway or a closure.
     * @param PaymentGateway|Closure|null $gateway The payment gateway instance or closure to set as primary.
     * @return void
     */
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

    /**
     * Retrieves the secondary payment gateway.
     *
     * @return PaymentGateway|null The secondary payment gateway instance or null if not defined.
     */
    public function secondary(): ?PaymentGateway
    {
        return $this->get($this->gatewaySecondary);
    }

    /**
     * Sets the secondary payment gateway.
     *
     * This method allows for defining a new secondary payment gateway
     * by name or closure. If a gateway is provided, it will be registered
     * with the specified name. If a closure is passed as the name, it
     * will be treated as the gateway, and the name will default to 'secondary'.
     *
     * @param string|Closure $name The name of the secondary payment gateway or a closure.
     * @param PaymentGateway|Closure|null $gateway The payment gateway instance or closure to set as secondary.
     * @return void
     */
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
