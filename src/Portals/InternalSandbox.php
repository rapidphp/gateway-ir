<?php

namespace Rapid\GatewayIR\Portals;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Abstract\PaymentGatewayAbstract;
use Rapid\GatewayIR\Contracts\PaymentHandler;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Data\TransactionInitializeResult;
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Portals\InternalSandbox\InternalSandboxTransactionInitializeResult;

/**
 * Class InternalSandbox
 *
 * This class represents an internal sandbox payment gateway for testing purposes.
 * It extends the PaymentGatewayAbstract and provides methods to initialize transactions
 * and verify their results. The internal sandbox is designed to simulate payment processing
 * without affecting real transactions, making it suitable for development and testing environments.
 *
 * @property bool $isSandbox Indicates whether the gateway is in sandbox mode.
 */
class InternalSandbox extends PaymentGatewayAbstract
{
    public bool $isSandbox = true;

    /**
     * InternalSandbox constructor.
     *
     * Initializes the internal sandbox payment gateway. Throws a RuntimeException if
     * the application is in a production environment, as this gateway is not supported
     * in production.
     *
     * @throws \RuntimeException If the application is in production.
     */
    public function __construct()
    {
        if (app()->isProduction()) {
            throw new \RuntimeException('Internal sandbox payment gateway is not supported in production environment.');
        }
    }

    /**
     * Initializes a payment transaction request.
     *
     * This method creates a new transaction record and prepares the result for the
     * transaction initialization. It returns a TransactionInitializeResult object
     * containing the necessary information for the transaction.
     *
     * @param int $amount The amount to be processed.
     * @param string $description A description of the transaction.
     * @param PaymentHandler|string $handler The payment handler to be used.
     * @param array $meta Additional metadata for the transaction (optional).
     * @return TransactionInitializeResult The result of the transaction initialization.
     */
    public function request(int $amount, string $description, PaymentHandler|string $handler, array $meta = []): TransactionInitializeResult
    {
        $record = $this->createNewRecord($amount, $description, $handler);

        $result = new InternalSandboxTransactionInitializeResult($this);
        $result->url = $this->getCallbackUrl($record);
        $result->successUrl = $result->url . '?status=success';
        $result->cancelUrl = $result->url . '?status=cancel';

        return $result;
    }

    /**
     * Verifies the result of a payment transaction.
     *
     * This method checks the status of the transaction based on the request data.
     * It returns a PaymentVerifyResult object if the transaction is successful,
     * throws a PaymentCancelledException if the transaction is cancelled, or
     * provides a response with links to simulate success or cancellation.
     *
     * @param Model $transaction The transaction model to verify.
     * @param Request $request The HTTP request containing the transaction status.
     * @return PaymentVerifyResult The result of the payment verification.
     * @throws PaymentCancelledException If the transaction is cancelled.
     */
    public function verify(Model $transaction, Request $request): PaymentVerifyResult
    {
        $status = $request->get('status');
        
        if ($status === 'success') {
            $result = new PaymentVerifyResult($this);
            $result->amount = $transaction->amount;

            return $result;

        } elseif ($status === 'cancel') {
            throw new PaymentCancelledException();
        } else {
            response(<<<HTML
                Success: <a href="?status=success">Click to run success method</a>
                <br>
                Cancel: <a href="?status=cancel">Click to run cancel method</a>
                HTML)
                ->throwResponse();
        }
    }
}
