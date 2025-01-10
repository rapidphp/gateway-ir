<?php

namespace Rapid\GatewayIR\Portals\InternalSandbox;

use Rapid\GatewayIR\Data\TransactionInitializeResult;

class InternalSandboxTransactionInitializeResult extends TransactionInitializeResult
{
    /**
     * The URL to redirect to if the transaction is cancelled.
     *
     * @var string
     */
    public string $cancelUrl;

    /**
     * The URL to redirect to if the transaction is successful.
     *
     * @var string
     */
    public string $successUrl;
}
