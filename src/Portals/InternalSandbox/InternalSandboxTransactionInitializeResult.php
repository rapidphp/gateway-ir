<?php

namespace Rapid\GatewayIR\Portals\InternalSandbox;

use Rapid\GatewayIR\Data\TransactionInitializeResult;

/**
 * Class InternalSandboxTransactionInitializeResult
 *
 * Represents the result of a transaction initialization in the Internal Sandbox environment.
 * This class extends the TransactionInitializeResult and includes additional properties
 * specific to the sandbox environment, such as URLs for redirecting after transaction
 * cancellation or success.
 */
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
