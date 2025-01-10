<?php

namespace Rapid\GatewayIR\Payment;

use Closure;
use Illuminate\Support\Traits\Conditionable;
use Laravel\SerializableClosure\SerializableClosure;
use Rapid\GatewayIR\Contracts\PaymentGateway;
use Rapid\GatewayIR\Handlers\AnonymousPaymentHandler;
use Rapid\GatewayIR\Handlers\PaymentHandler;

class PendingRequest
{
    use Conditionable;

    protected int $amount = 0;

    protected string $description = 'None';

    protected array $meta = [];

    protected string|PaymentHandler $handler;

    public function __construct(
        protected PaymentGateway $gateway,
    )
    {
    }

    public function amount(int $amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function description(string $description)
    {
        $this->description = $description;
        return $this;
    }

    public function meta(array $meta)
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    public function handler(string|PaymentHandler $handler)
    {
        $this->handler = $handler;
        return $this;
    }

    public function request()
    {
        return $this->gateway->request($this->amount, $this->description, $this->handler, $this->meta);
    }

    public function with(...$parameters)
    {
        $this->getAnonymousHandler()->parameters = $parameters;
        return $this;
    }

    public function onPrepare(Closure $callback)
    {
        $this->getAnonymousHandler()->onPrepare = new SerializableClosure($callback);
        return $this;
    }

    public function onSuccess(Closure $callback)
    {
        $this->getAnonymousHandler()->onSuccess = new SerializableClosure($callback);
        return $this;
    }

    private function getAnonymousHandler(): AnonymousPaymentHandler
    {
        if (!isset($this->handler)) {
            return $this->handler = new AnonymousPaymentHandler();
        }

        if ($this->handler instanceof AnonymousPaymentHandler) {
            return $this->handler;
        }

        throw new \TypeError(sprintf(
            "The handler should be anonymous, given [%s]",
            is_string($this->handler) ? $this->handler : get_class($this->handler)
        ));
    }

}