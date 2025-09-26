<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Models\Transaction;

class PaymentPrepare extends Data
{
    /**
     * The amount of the transaction.
     *
     * @var int
     */
    public int $amount;

    /**
     * The transaction record.
     *
     * @var Model|Transaction
     */
    public Model $record;
}