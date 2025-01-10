<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Database\Eloquent\Model;

class PaymentPrepare extends Data
{
    /**
     * The amount of the transaction.
     *
     * @var int
     */
    public int $amount;
}