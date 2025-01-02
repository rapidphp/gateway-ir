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
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Exceptions\PaymentFailedException;
use Rapid\GatewayIR\Exceptions\PaymentVerifyRepeatedException;
use Rapid\GatewayIR\Gateway;
use Rapid\GatewayIR\Jobs\TransactionDone;
use Symfony\Component\HttpFoundation\Response;

class GatewayService
{

    public bool $forceCommit;

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
                $prepare->model = $transaction->model;
                $prepare->user = $transaction->user;

                if ($response = $handler->prepare($prepare)) {
                    return $response;
                }
            }

            try {

                $result = $gateway->verify($transaction, $request);

            } catch (PaymentFailedException|PaymentVerifyRepeatedException) {

                abort(Response::HTTP_FORBIDDEN);

            } catch (PaymentCancelledException $cancelled) {

                $transaction->update([
                    'status' => TransactionStatuses::Cancelled,
                ]);

                return null;

            } catch (\Throwable $exception) {

                abort(Response::HTTP_FORBIDDEN);

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
            abort(Response::HTTP_GONE);
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
            abort(Response::HTTP_GONE);
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
        return Gateway::get($idName) ?? abort(Response::HTTP_GONE);
    }

    protected function handleSuccess(?PaymentHandler $handler, PaymentVerifyResult $result)
    {
        return $handler?->success($result);
    }

}