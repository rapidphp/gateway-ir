<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Data\PaymentVerifyResult;

class ZarinPalPaymentVerifyResult extends PaymentVerifyResult
{
    /**
     * The reference ID of the transaction.
     *
     * @var int
     */
    public int $refId;

    /**
     * The card number (PAN) used for the transaction, masked for security.
     *
     * @var string
     */
    public string $cardPan;

    /**
     * A hash of the card information for verification purposes.
     *
     * @var string
     */
    public string $cardHash;

    /**
     * The type of fee applied to the transaction, if any.
     *
     * @var string|null
     */
    public ?string $feeType;

    /**
     * The amount of the fee applied to the transaction, if any.
     *
     * @var int|null
     */
    public ?int $fee;
}
