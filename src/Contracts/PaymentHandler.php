<?php

namespace Rapid\GatewayIR\Contracts;

use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Abstract\VerifyResult;

interface PaymentHandler
{

    public function before(Model $transaction);

    public function success(VerifyResult $data);

    public function cancel(VerifyResult $data);

}