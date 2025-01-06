<?php

namespace Rapid\GatewayIR\Http\Controllers;

use Illuminate\Http\Request;
use Rapid\GatewayIR\Http\Services\GatewayService;

/**
 * Class GatewayController
 *
 * Handles incoming HTTP requests related to payment gateway operations.
 * This controller provides endpoints for processing payment transactions,
 * including accepting and verifying payment requests.
 */
class GatewayController
{
    /**
     * Accepts a payment transaction and verifies it based on the provided order ID.
     *
     * This method retrieves the order ID from the request and delegates the
     * verification process to the GatewayService. It returns the response
     * from the verification process, which may include success or error
     * information based on the transaction status.
     *
     * @param string $orderId The ID of the order to verify.
     * @param Request $request The HTTP request containing verification data.
     * @return mixed The response from the GatewayService verification method.
     */
    public function accept(string $orderId, Request $request)
    {
        return app(GatewayService::class)->verify($orderId, $request);
    }
}
