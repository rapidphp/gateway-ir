<?php

namespace Rapid\GatewayIR\Http\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rapid\GatewayIR\Contracts\GatewaySupportsRevert;
use Rapid\GatewayIR\Contracts\PaymentGateway;
use Rapid\GatewayIR\Contracts\PaymentHandler;
use Rapid\GatewayIR\Data\PaymentPrepare;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Enums\TransactionStatuses;
use Rapid\GatewayIR\Exceptions\GatewayException;
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Exceptions\PaymentFailedException;
use Rapid\GatewayIR\Exceptions\PaymentVerifyRepeatedException;
use Rapid\GatewayIR\Jobs\TransactionDone;
use Rapid\GatewayIR\Payment;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GatewayService
 *
 * Provides services for handling payment transactions, including verification and retrying
 * of transactions. This class manages the transaction lifecycle, including handling success,
 * failure, and cancellation scenarios. It interacts with payment handlers and gateways to
 * facilitate payment processing.
 */
class GatewayService
{
    /**
     * Indicates whether to force commit the database transaction.
     *
     * @var bool
     */
    public bool $forceCommit;

    /**
     * Verifies a payment transaction based on the provided order ID and request.
     *
     * This method initiates a database transaction, retrieves the pending transaction,
     * prepares the verification, and processes the result. It handles various exceptions
     * that may occur during the verification process and updates the transaction status
     * accordingly.
     *
     * @param string $orderId The ID of the order to verify.
     * @param Request $request The HTTP request containing verification data.
     * @return mixed The response from the payment handler or null if cancelled.
     * @throws \Throwable If an error occurs during the verification process.
     */
    public function verify(string $orderId, Request $request)
    {
        $response = null;
        $this->forceCommit = false;

        try {
            DB::beginTransaction();

            $transaction = $this->getPendingTransaction($orderId);
            $handler = $this->exportHandler($transaction->handler);
            $gateway = $this->exportGateway($transaction->gateway);

            // prepare the verification
            if ($handler) {
                $prepare = new PaymentPrepare($gateway);
                $prepare->amount = $transaction->amount;

                if ($response = $handler->prepare($prepare)) {
                    return $response;
                }
            }

            try {

                $result = $gateway->verify($transaction, $request);

            } catch (PaymentFailedException|PaymentVerifyRepeatedException|GatewayException) {

                abort(Response::HTTP_FORBIDDEN);

            } catch (PaymentCancelledException $cancelled) {

                $transaction->update([
                    'status' => TransactionStatuses::Cancelled,
                ]);

                return null;

            }

            $status = TransactionStatuses::Success;
            try {
                $response = $handler?->success($result);
            } catch (\Throwable $exception) {
                report($exception);

                if ($gateway instanceof GatewaySupportsRevert) {
                    try {
                        $gateway->revert($transaction, $result);
                        $status = TransactionStatuses::Reverted;
                        goto skipHandling;
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                }

                dispatch(new TransactionDone($transaction, $result));
                $status = TransactionStatuses::PendInQueue;
            }
            skipHandling:

            $transaction->update([
                'status' => $status,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            if ($this->forceCommit) {
                DB::commit();
            } else {
                DB::rollBack();
            }
            throw $e;
        }

        return $response;
    }

    /**
     * Retries the success handling of a payment transaction.
     *
     * This method invokes the success method of the payment handler with the provided
     * transaction and verification result.
     *
     * @param Model $transaction The transaction model to retry.
     * @param PaymentVerifyResult $result The result of the payment verification.
     * @return void
     */
    public function retry(Model $transaction, PaymentVerifyResult $result)
    {
        $handler = $this->exportHandler($transaction->handler);

        $handler?->success($result);
    }

    /**
     * Get the transaction model
     *
     * @param string $orderId
     * @return Model
     */
    protected function getPendingTransaction(string $orderId): Model
    {
        $transaction = config('gateway-ir.table.model')::query()
            ->where('order_id', $orderId)
            ->lockForUpdate()
            ->first();

        if (!$transaction) {
            abort(Response::HTTP_FORBIDDEN);
        }

        // Check the status
        if ($transaction->status != TransactionStatuses::Pending) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $transaction;
    }

    /**
     * Export the handler from class name or serialized value
     *
     * @param mixed $handler
     * @return PaymentHandler|null
     */
    protected function exportHandler(?string $handler): ?PaymentHandler
    {
        if (is_null($handler)) {
            return null;
        }

        // Check and create the handler
        try {

            if (class_exists($handler) && is_a($handler, PaymentHandler::class, true)) {
                return new $handler();
            }

            $handler = @unserialize($handler);
            if (!($handler instanceof PaymentHandler)) {
                throw new \Exception();
            }

            return $handler;

        } catch (\Throwable) {
            abort(Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Export the gateway
     *
     * @param string $idName
     * @return PaymentGateway
     */
    protected function exportGateway(string $idName): PaymentGateway
    {
        return Payment::get($idName) ?? abort(Response::HTTP_UNAUTHORIZED);
    }

     /**
     * Handles the success response from the payment handler.
     *
     * This method invokes the success method of the provided payment handler with
     * the given verification result. It allows for custom handling of successful
     * payment verifications, delegating the response processing to the specific
     * payment handler implementation.
     *
     * @param PaymentHandler|null $handler The payment handler to use for processing the success response.
     * @param PaymentVerifyResult $result The result of the payment verification.
     * @return mixed The response from the payment handler's success method, or null if the handler is not provided.
     */
    protected function handleSuccess(?PaymentHandler $handler, PaymentVerifyResult $result)
    {
        return $handler?->success($result);
    }

}