<?php

namespace Rapid\GatewayIR\Portals\NextPay;

use Rapid\GatewayIR\Data\PaymentVerifyResult;

class NextPayPaymentVerifyResult extends PaymentVerifyResult
{

    public string $cardPan;

    public string $refId;

    public ?array $custom;

    public string $mobile;

}
