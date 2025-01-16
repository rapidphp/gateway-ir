<?php

namespace Rapid\GatewayIR\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Data\TransactionInitializeResult;
use Rapid\GatewayIR\Handlers\PaymentHandler;
use Rapid\GatewayIR\Payment\PendingRequest;

/**
 * Interface for payment gateway implementations.
 */
interface PaymentGateway
{

    /**
     * Registers the payment gateway with a given identifier name.
     *
     * @param string $idName
     * @return void
     */
    public function register(string $idName): void;

    /**
     * Retrieves the identifier name for the payment gateway.
     *
     * @return string
     */
    public function getIDName(): string;

    /**
     * Initiates a payment request.
     *
     * @param int $amount
     * @param string $description
     * @param string|PaymentHandler $handler
     * @param array $meta
     * @return TransactionInitializeResult
     */
    public function request(
        int $amount,
        string $description,
        string|PaymentHandler $handler,
        array $meta = []
    ): TransactionInitializeResult;

    /**
     * Verifies a completed transaction.
     *
     * @param Model $transaction
     * @param Request $request
     * @return PaymentVerifyResult
     */
    public function verify(Model $transaction, Request $request): PaymentVerifyResult;

    /**
     * Prepares a new pending payment request.
     *
     * @return PendingRequest
     */
    public function prepare(): PendingRequest;

}