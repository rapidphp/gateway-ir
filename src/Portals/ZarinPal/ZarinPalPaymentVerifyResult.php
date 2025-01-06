<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Data\PaymentVerifyResult;

/**
 * Class ZarinPalPaymentVerifyResult
 *
 * Represents the result of a payment verification from the ZarinPal payment gateway.
 * This class extends the PaymentVerifyResult class and includes additional properties
 * specific to the ZarinPal payment verification response.
 */
class ZarinPalPaymentVerifyResult extends PaymentVerifyResult
{
    /**
     * @var int The reference ID of the transaction.
     */
    public int $refId;

    /**
     * @var string The card number (PAN) used for the transaction, masked for security.
     */
    public string $cardPan;

    /**
     * @var string A hash of the card information for verification purposes.
     */
    public string $cardHash;

    /**
     * @var string|null The type of fee applied to the transaction, if any.
     */
    public ?string $feeType;

    /**
     * @var int|null The amount of the fee applied to the transaction, if any.
     */
    public ?int $fee;
}
