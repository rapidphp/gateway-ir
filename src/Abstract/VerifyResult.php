<?php

namespace Rapid\GatewayIR\Abstract;

use Illuminate\Database\Eloquent\Model;

abstract class VerifyResult extends Data
{

    public int $amount;

    public ?Model $user;

    public ?Model $model;

}