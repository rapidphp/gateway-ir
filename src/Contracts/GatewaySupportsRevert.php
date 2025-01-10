<?php

namespace Rapid\GatewayIR\Contracts;

use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

/**
 * Interface for payment gateways that support transaction reversal.
 */
interface GatewaySupportsRevert
{

    /**
     * Reverts a completed transaction.
     *
     * @param Model $transaction
     * @param PaymentVerifyResult $result
     * @return void
     */
    public function revert(Model $transaction, PaymentVerifyResult $result): void;

}