<?php

namespace Rapid\GatewayIR\Portals\InternalSandbox;

use Rapid\GatewayIR\Data\TransactionInitializeResult;

class InternalSandboxTransactionInitializeResult extends TransactionInitializeResult
{

    public string $successUrl;

    public string $cancelUrl;

}