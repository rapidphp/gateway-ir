<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Abstract\CreationResult;

class ZarinPalCreationResult extends CreationResult
{

    public string $authority;

    public ?string $feeType;

    public ?int $fee;

}