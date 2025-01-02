<?php

namespace Rapid\GatewayIR\Contracts;

use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

interface GatewaySupportsRevert
{

    public function revert(Model $transaction, PaymentVerifyResult $result): void;

}