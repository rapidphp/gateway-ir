<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Database\Eloquent\Model;

class PaymentPrepare extends Data
{

    public int $amount;

    public ?Model $user;

    public ?Model $model;

}