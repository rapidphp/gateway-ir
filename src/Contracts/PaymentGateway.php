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

    public function register(string $idName);

    public function idName(): string;

    public function endPoint(?string $path = null): string;

    public function request(
        int                   $amount,
        string                $description,
        string|PaymentHandler $handler,
        ?Model                $user = null,
        ?Model                $model = null,
        array                 $meta = [],
    );

    public function verify(Model $transaction, Request $request): PaymentVerifyResult;

}