<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Http\RedirectResponse;

class TransactionInitializeResult extends Data
{

    /**
     * The URL to redirect to after transaction initialization.
     *
     * @var string
     */
    public string $url;

    /**
     * Redirects the user to the specified gateway URL.
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        return response()->redirectTo($this->url);
    }

}
