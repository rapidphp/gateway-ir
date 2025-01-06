<?php

namespace Rapid\GatewayIR\Contracts;

use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

/**
 * Interface for payment gateways that support transaction reversal.
 *
 * This interface defines the contract for payment gateway implementations
 * that allow for the reversal of transactions. Implementing classes must
 * provide the logic for reverting a transaction based on the provided
 * transaction model and verification result.
 */
interface GatewaySupportsRevert
{
    /**
     * Reverts a completed transaction.
     *
     * This method is responsible for handling the reversal of a transaction
     * that has already been processed. It takes the transaction model and
     * the result of the payment verification as parameters to perform the
     * necessary actions to revert the transaction.
     *
     * @param Model $transaction The transaction model instance to be reverted.
     * @param PaymentVerifyResult $result The result of the payment verification
     *                                     associated with the transaction.
     * @return void
     */
    public function revert(Model $transaction, PaymentVerifyResult $result): void;
}