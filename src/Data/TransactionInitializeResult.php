<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Http\RedirectResponse;

/**
 * Class TransactionInitializeResult
 *
 * This class represents the result of a transaction initialization process.
 * It contains the URLs for redirecting the user after a transaction is initialized.
 */
class TransactionInitializeResult extends Data
{
    /**
     * The URL to redirect to after transaction initialization.
     *
     * @var string
     */
    public string $url;

    /**
     * Redirects the user to the specified main URL.
     *
     * This method generates a RedirectResponse that will redirect the user
     * to the URL stored in the `url` property.
     *
     * @return RedirectResponse The response that performs the redirection.
     */
    public function redirect(): RedirectResponse
    {
        return response()->redirectTo($this->url);
    }
}
