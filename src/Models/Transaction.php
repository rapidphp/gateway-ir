<?php

namespace Rapid\GatewayIR\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    public function getTable()
    {
        return config('gateway-ir.table.table');
    }

    protected $fillable = [
        'order_id',
        'authority',
        'amount',
        'description',
        'status',
        'handler',
        'gateway'
    ];

}