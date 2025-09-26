<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Models\Transaction;

class PaymentCancelledResult extends Data
{
    /**
     * The transaction record.
     *
     * @var Model|Transaction
     */
    public Model $record;
}