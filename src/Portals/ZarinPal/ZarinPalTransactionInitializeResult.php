<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Data\TransactionInitializeResult;

class ZarinPalTransactionInitializeResult extends TransactionInitializeResult
{

    public string $authority;

    public ?string $feeType;

    public ?int $fee;

}