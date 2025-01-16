<?php

namespace Rapid\GatewayIR\Portals\IDPay;

use Carbon\Carbon;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

class IDPayPaymentVerifyResult extends PaymentVerifyResult
{

    /**
     * The tracking ID of the transaction.
     *
     * @var string
     */
    public string $trackId;

    /**
     * The creation date and time of the transaction.
     *
     * @var Carbon
     */
    public Carbon $createdAt;

    /**
     * The verification date and time of the transaction.
     *
     * @var Carbon
     */
    public Carbon $verifiedAt;

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

}