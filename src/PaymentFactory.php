<?php

namespace Rapid\GatewayIR;

use Closure;
use Rapid\GatewayIR\Contracts\PaymentGateway;
use Rapid\GatewayIR\Enums\TransactionStatuses;

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
     * @param string $name
     * @param PaymentGateway|Closure $gateway
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
     * @param string $name
     * @return PaymentGateway|null
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
     * @return PaymentGateway|null
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
     * @param string|Closure $name
     * @param PaymentGateway|Closure|null $gateway
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
     * @return PaymentGateway|null
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
     * @param string|Closure $name
     * @param PaymentGateway|Closure|null $gateway
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

    /**
     * Get the transaction model class.
     *
     * @return string
     */
    public function getModel(): string
    {
        return config('gateway-ir.database.model');
    }

    /**
     * Expire and remove unused transactions.
     *
     * @return void
     */
    public function clearExpiredRecords(): void
    {
        $this->getModel()
            ::query()
            ->where('status', TransactionStatuses::Pending)
            ->where('created_at', '<=', now()->subSeconds(config('gateway-ir.expire.expire_after')))
            ->update(['status' => TransactionStatuses::Expired]);

        if ($doneKeep = config('gateway-ir.expire.dont_keep')) {
            $this->getModel()
                ::query()
                ->whereIn('status', $doneKeep)
                ->delete();
        }
    }
}
