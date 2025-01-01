<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Abstract\VerifyResult;

class ZarinPalVerifyResult extends VerifyResult
{

    public int $refId;

    public string $cardPan;

    public string $cardHash;

    public ?string $feeType;

    public ?int $fee;

}