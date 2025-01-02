<?php

namespace Rapid\GatewayIR\Contracts;

use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Data\PaymentFailed;
use Rapid\GatewayIR\Data\PaymentPrepare;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

interface PaymentHandler
{

    public function prepare(PaymentPrepare $payment);

    public function success(PaymentVerifyResult $data);

}