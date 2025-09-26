<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Models\Transaction;

class PaymentFailed extends Data
{
    /**
     * The transaction record.
     *
     * @var Model|Transaction
     */
    public Model $record;
}