<?php

namespace Rapid\GatewayIR\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;

class TransactionDone implements ShouldQueue
{

    public function __construct(
        public Model $transaction,
    )
    {
    }

    public function handle()
    {
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

    }

}