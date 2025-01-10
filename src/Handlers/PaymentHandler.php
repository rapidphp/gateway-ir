<?php

namespace Rapid\GatewayIR\Handlers;

use Illuminate\Queue\SerializesModels;
use Laravel\SerializableClosure\SerializableClosure;
use Rapid\GatewayIR\Data\PaymentPrepare;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

abstract class PaymentHandler
{
    use SerializesModels;

    abstract public function prepare(PaymentPrepare $payment);

    abstract public function success(PaymentVerifyResult $data);

}