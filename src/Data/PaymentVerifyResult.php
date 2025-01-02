<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Database\Eloquent\Model;

class PaymentVerifyResult extends Data
{

    public int $amount;

    public ?Model $user;

    public ?Model $model;

}