<?php

namespace Rapid\GatewayIR\Handlers;

use Laravel\SerializableClosure\SerializableClosure;
use Rapid\GatewayIR\Data\PaymentPrepare;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

class AnonymousPaymentHandler extends PaymentHandler
{

    public array $parameters = [];

    public SerializableClosure $onPrepare;

    public SerializableClosure $onSuccess;

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