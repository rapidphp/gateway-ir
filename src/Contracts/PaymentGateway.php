<?php

namespace Rapid\GatewayIR\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Exceptions\PaymentFailedException;
use Rapid\GatewayIR\Exceptions\PaymentVerifyRepeatedException;

interface PaymentGateway
{

    public function register(string $idName, bool $sandbox);

    public function idName(): string;

    public function endPoint(?string $path = null): string;

    public function verify(Model $transaction, Request $request): PaymentVerifyResult;

}