<?php

namespace Rapid\GatewayIR\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Services\GatewayService;

class TransactionRetry implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Model $transaction,
        public PaymentVerifyResult $result,
    )
    {
    }

    public int $tries = 10;

    public function handle()
    {
        try {
            app(GatewayService::class)->retry($this->transaction, $this->result);
        } catch (\Throwable $e) {
            if ($this->attempts() < $this->tries) {
                $this->release(now()->addMinutes(($this->attempts() + 2) ** 2));
            }

            $this->fail($e);
        }
    }

}