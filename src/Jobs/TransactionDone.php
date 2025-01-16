<?php

namespace Rapid\GatewayIR\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Services\GatewayService;

class TransactionDone implements ShouldQueue
{

    public function __construct(
        public Model $transaction,
        public PaymentVerifyResult $result,
    )
    {
    }

    public function handle()
    {
        app(GatewayService::class)->retry($this->transaction, $this->result);
    }

}