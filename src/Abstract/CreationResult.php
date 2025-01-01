<?php

namespace Rapid\GatewayIR\Abstract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

abstract class CreationResult extends Data
{

    public string $url;

    public function redirect(): RedirectResponse
    {
        return response()->redirectTo($this->url);
    }

    public ?Model $user;

    public ?Model $model;

}