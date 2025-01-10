<?php

namespace Rapid\GatewayIR\Contracts;

use Rapid\GatewayIR\Data\PaymentPrepare;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

interface PaymentHandler
{

    public function prepare(PaymentPrepare $payment);

    public function success(PaymentVerifyResult $data);

}