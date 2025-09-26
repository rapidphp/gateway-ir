<?php

namespace Rapid\GatewayIR\Data;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

class TransactionInitializeResult extends Data
{
    /**
     * The URL to redirect to after transaction initialization.
     *
     * @var string
     */
    public string $url;

    /**
     * The method to redirect after transaction initialization.
     *
     * @var string
     */
    public string $method = 'GET';

    /**
     * The data to send with redirect after transaction initialization.
     *
     * @var array
     */
    public array $data = [];

    /**
     * Redirects the user to the specified gateway URL.
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        return response()->redirectTo($this->urlToRedirect());
    }

    /**
     * @return string
     */
    public function urlToRedirect(): string
    {
        if ($this->method == 'GET' && !$this->data) {
            return $this->url;
        }

        return URL::signedRoute(
            config('gateway-ir.routes.redirect_name'),
            array_merge(
                [
                    'to' => $this->url,
                    'method' => $this->method,
                ],
                $this->data ? ['data' => json_encode($this->data)] : [],
            ),
            now()->addMinutes(30),
        );
    }
}
