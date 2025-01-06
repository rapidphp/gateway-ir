<?php

namespace Rapid\GatewayIR\Contracts;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Data\TransactionInitializeResult;

/**
 * Interface for payment gateway implementations.
 *
 * This interface defines the contract that all payment gateway classes must
 * adhere to. It includes methods for registering the gateway, handling
 * payment requests, and verifying transactions. Implementing classes should
 * provide the specific logic for interacting with different payment providers.
 */
interface PaymentGateway
{
    /**
     * Registers the payment gateway with a given identifier name.
     *
     * @param string $idName The identifier name for the payment gateway.
     * @return void
     */
    public function register(string $idName): void;

    /**
     * Retrieves the identifier name for the payment gateway.
     *
     * @return string The identifier name of the payment gateway.
     */
    public function getIDName(): string;

    /**
     * Initiates a payment request.
     *
     * This method is responsible for creating a payment request to the
     * payment gateway. It should return a result object containing the
     * necessary information for the transaction initialization.
     *
     * @param int $amount The amount to be charged.
     * @param string $description A description of the transaction.
     * @param string|PaymentHandler $handler The payment handler to be used.
     * @param array $meta Optional metadata for the transaction.
     * @return TransactionInitializeResult The result of the transaction initialization.
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
     * This method checks the validity of a transaction based on the provided
     * request data and the associated transaction model. It should return
     * a result object indicating the outcome of the verification.
     *
     * @param Model $transaction The transaction model instance to verify.
     * @param Request $request The HTTP request containing verification data.
     * @return PaymentVerifyResult The result of the payment verification.
     */
    public function verify(Model $transaction, Request $request): PaymentVerifyResult;
}