<?php

namespace Rapid\GatewayIR\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Rapid\GatewayIR\Abstract\VerifyResult;

interface PaymentGateway
{

    public function register(string $idName);

    public function idName(): string;

    public function endPoint(?string $suffix = null): string;

    public function verify(Model $transaction, Request $request): VerifyResult;

}