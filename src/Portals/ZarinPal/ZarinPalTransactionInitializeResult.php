<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Data\TransactionInitializeResult;

class ZarinPalTransactionInitializeResult extends TransactionInitializeResult
{
    /**
     * The authority code provided by ZarinPal for the transaction.
     *
     * @var string
     */
    public string $authority;

    /**
     * The type of fee associated with the transaction, if applicable.
     *
     * @var string|null
     */
    public ?string $feeType;

    /**
     * The amount of the fee associated with the transaction, if applicable.
     *
     * @var int|null
     */
    public ?int $fee;
}
