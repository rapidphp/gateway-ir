<?php

namespace Rapid\GatewayIR\Handlers;

use Laravel\SerializableClosure\SerializableClosure;
use Rapid\GatewayIR\Data\PaymentPrepare;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

class AnonymousPaymentHandler extends PaymentHandler
{

    public function __construct(
        public ?SerializableClosure $onPrepare = null,
        public ?SerializableClosure $onSuccess = null,
        public array $parameters = [],
    )
    {
    }

    public function prepare(PaymentPrepare $payment)
    {
        if (isset($this->onPrepare)) {
            ($this->onPrepare)($payment, ...$this->parameters);
        }
    }

    public function success(PaymentVerifyResult $data)
    {
        if (isset($this->onSuccess)) {
            ($this->onSuccess)($data, ...$this->parameters);
        }
    }

}