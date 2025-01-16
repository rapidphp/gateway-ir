<?php

namespace Rapid\GatewayIR\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rapid\GatewayIR\Contracts\GatewaySupportsRevert;
use Rapid\GatewayIR\Contracts\PaymentGateway;
use Rapid\GatewayIR\Data\PaymentCancelledResult;
use Rapid\GatewayIR\Data\PaymentFailed;
use Rapid\GatewayIR\Data\PaymentPrepare;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Enums\TransactionStatuses;
use Rapid\GatewayIR\Exceptions\GatewayException;
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Exceptions\PaymentFailedException;
use Rapid\GatewayIR\Exceptions\PaymentVerifyRepeatedException;
use Rapid\GatewayIR\Handlers\PaymentHandler;
use Rapid\GatewayIR\Jobs\TransactionDone;
use Rapid\GatewayIR\Payment;
use Symfony\Component\HttpFoundation\Response;

class GatewayService
{

    /**
     * Verifies a payment transaction based on the provided order ID and request.
     *
     * @param string $orderId
     * @param Request $request
     * @return mixed
     */
    public function verify(string $orderId, Request $request)
    {
        Payment::clearExpiredRecords();

        $errorCode = null;

        $response = DB::transaction(function () use ($orderId, $request, &$errorCode) {

            $transaction = $this->getPendingTransaction($orderId);
            $handler = $this->exportHandler($transaction->handler);
            $gateway = $this->exportGateway($transaction->gateway);
            $handlerSetup = $handler?->getSetup();

            // prepare the verification
            if ($handler) {
                $prepare = new PaymentPrepare($gateway);
                $prepare->amount = $transaction->amount;

                if (!$handlerSetup->fireValidate($prepare)) {
                    abort(403);
                }
            }

            try {

                $result = $gateway->verify($transaction, $request);

            } catch (PaymentFailedException $failed) {

                $data = new PaymentFailed($gateway);

                return $handlerSetup->fireFail($data) ?? $errorCode = 403;

            } catch (PaymentVerifyRepeatedException|GatewayException) {

                abort(Response::HTTP_FORBIDDEN);

            } catch (PaymentCancelledException $cancelled) {

                $transaction->update([
                    'status' => TransactionStatuses::Cancelled,
                ]);

                $data = new PaymentCancelledResult($gateway);
                return $handlerSetup->fireCancel($data);

            }

            try {
                $transaction->update([
                    'status' => TransactionStatuses::Success,
                ]);

                return $handlerSetup?->fireSuccess($result);
            } catch (\Throwable $exception) {
                report($exception);

                if ($gateway instanceof GatewaySupportsRevert) {
                    try {
                        $gateway->revert($transaction, $result);
                        $transaction->update([
                            'status' => TransactionStatuses::Reverted,
                        ]);

                        return null;
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                }

                dispatch(new TransactionDone($transaction, $result));
                $transaction->update([
                    'status' => TransactionStatuses::PendInQueue,
                ]);
            }

            return null;
        });

        if (isset($errorCode)) {
            abort($errorCode);
        }

        return $response;
    }

    /**
     * Retries the success handling of a payment transaction.
     *
     * @param Model $transaction
     * @param PaymentVerifyResult $result
     * @return void
     */
    public function retry(Model $transaction, PaymentVerifyResult $result)
    {
        $handler = $this->exportHandler($transaction->handler);

        $handler?->getSetup()?->fireSuccess($result);
    }

    /**
     * Get the transaction model
     *
     * @param string $orderId
     * @return Model
     */
    protected function getPendingTransaction(string $orderId): Model
    {
        $transaction = Payment::getModel()
            ::query()
            ->where('order_id', $orderId)
            ->lockForUpdate()
            ->first();

        if (!$transaction) {
            abort(Response::HTTP_FORBIDDEN);
        }

        // Check the status
        if ($transaction->status == TransactionStatuses::Expired) {
            abort(419);
        }

        if ($transaction->status != TransactionStatuses::Pending) {
            abort(403);
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
        return Payment::get($idName) ?? abort(419);
    }

    /**
     * Handles the success response from the payment handler.
     *
     * @param PaymentHandler|null $handler
     * @param PaymentVerifyResult $result
     * @return mixed
     */
    protected function handleSuccess(?PaymentHandler $handler, PaymentVerifyResult $result)
    {
        return $handler?->success($result);
    }

}