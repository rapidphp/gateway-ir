<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Data\TransactionInitializeResult;

/**
 * Class ZarinPalTransactionInitializeResult
 *
 * This class extends the TransactionInitializeResult to represent the result
 * of a transaction initialization specifically for the ZarinPal payment gateway.
 * It includes additional properties related to the ZarinPal transaction, such as
 * the authority code and any applicable fees.
 */
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
