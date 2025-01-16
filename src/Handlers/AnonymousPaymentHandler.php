<?php

namespace Rapid\GatewayIR\Handlers;

use Laravel\SerializableClosure\SerializableClosure;

class AnonymousPaymentHandler extends PaymentHandler
{

    public function __construct(
        public ?SerializableClosure $success = null,
        public ?SerializableClosure $validate = null,
        public array $parameters = [],
    )
    {
    }

    public function setup(HandleSetup $setup): void
    {
        $setup
            ->when($this->success)
            ->success($this->resolveCallback($this->success))
            ->when($this->validate)
            ->validate($this->resolveCallback($this->validate));
    }

    protected function resolveCallback($callback)
    {
        if ($callback === null) {
            return null;
        }

        return function ($data) use ($callback) {
            return $callback($data, ...$this->parameters);
        };
    }

}