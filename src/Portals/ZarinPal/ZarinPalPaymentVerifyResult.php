<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Data\PaymentVerifyResult;

class ZarinPalPaymentVerifyResult extends PaymentVerifyResult
{

    public int $refId;

    public string $cardPan;

    public string $cardHash;

    public ?string $feeType;

    public ?int $fee;

}