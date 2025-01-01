<?php

namespace Rapid\GatewayIR\Http\Controllers;

use Illuminate\Http\Request;
use Rapid\GatewayIR\Contracts\PaymentGateway;
use Rapid\GatewayIR\Contracts\PaymentHandler;
use Rapid\GatewayIR\Enums\TransactionStatuses;
use Rapid\GatewayIR\Jobs\TransactionDone;
use Symfony\Component\HttpFoundation\Response;

class GatewayController
{

    public function accept(string $orderId, Request $request)
    {
        $transaction = config('gateway-ir.table.model')::query()
            ->where('order_id', $orderId)
            ->first();

        if (!$transaction) {
            abort(Response::HTTP_FORBIDDEN);
        }

        // Check the status
        if ($transaction->status != TransactionStatuses::Pending) {
            abort(Response::HTTP_GONE);
        }

        // Check and create the handler
        try {
            $handler = null;

            if ($transaction->handler) {
                if (class_exists($transaction->handler) &&
                    is_a($transaction->handler, PaymentHandler::class, true)) {

                    $handler = new $handler();

                } else {

                    $handler = unserialize($handler);
                    throw_unless($handler instanceof PaymentHandler);

                }
            }

            /** @var ?PaymentHandler $handler */
        } catch (\Throwable) {
            abort(Response::HTTP_GONE);
        }

        // Get the gateway
        $gateway = $transaction->gateway;

        if (!$gateway || !class_exists($gateway) || !is_a($gateway, PaymentGateway::class, true)) {
            abort(Response::HTTP_GONE);
        }

        $gateway = new $gateway();

        $handler?->before($transaction);

        try {

            $result = $gateway->verify($transaction, $request);

        } catch (\Throwable $exception) {

            abort(Response::HTTP_FORBIDDEN);

        }

        // Forcing to run the handler event
        if ($handler) {
            try {
                $handler->success($result);
                $transaction->update(['status' => TransactionStatuses::Paid]);
            } catch (\Throwable $exception) {
                dispatch(new TransactionDone($transaction));
                $transaction->update(['status' => TransactionStatuses::PaidFailed]);
            }
        } else {
            $transaction->update(['status' => TransactionStatuses::Paid]);
        }
    }

}