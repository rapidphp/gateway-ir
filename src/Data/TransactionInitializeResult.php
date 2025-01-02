<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

class TransactionInitializeResult extends Data
{

    public ?Model $user;

    public ?Model $model;

    public string $url;

    public function redirect(): RedirectResponse
    {
        return response()->redirectTo($this->url);
    }

}