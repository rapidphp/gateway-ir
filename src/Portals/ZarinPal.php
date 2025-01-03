<?php

namespace Rapid\GatewayIR\Portals;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Rapid\GatewayIR\Abstract\PaymentGatewayAbstract;
use Rapid\GatewayIR\Contracts\PaymentHandler;
use Rapid\GatewayIR\Exceptions\GatewayException;
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Exceptions\PaymentFailedException;
use Rapid\GatewayIR\Exceptions\PaymentVerifyRepeatedException;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalGatewayException;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalPaymentVerifyResult;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalTransactionInitializeResult;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

class ZarinPal extends PaymentGatewayAbstract
{

    public function __construct(
        string $key,
        bool $sandbox = false,
    )
    {
        $this->key = $key;
        $this->setSandbox($sandbox);
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public static function sandbox(?string $key = null): static
    {
        return new static($key ?? Str::uuid(), true);
    }

    protected string $baseUrl = 'https://payment.zarinpal.com';
    protected string $sandboxBaseUrl = 'https://sandbox.zarinpal.com';

    public function request(
        int                   $amount,
        string                $description,
        string|PaymentHandler $handler,
        ?Model                $user = null,
        ?Model                $model = null,
        array                 $meta = [],
    ): ZarinPalTransactionInitializeResult
    {
        @[
            'currency' => $currency,
            'mobile' => $mobile,
            'email' => $email,
        ] = $meta;

        $transaction = $this->createNewRecord($amount, $description, $handler, $user, $model);

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->post($this->endPoint("pg/v4/payment/request.json"), array_filter([
                    'merchant_id' => $this->key,
                    'amount' => $amount,
                    'description' => $description,
                    'callback_url' => $this->getCallbackUrl($transaction),
                    'currency' => $currency,
                    'order_id' => $transaction->order_id,
                    'metadata' => array_filter([
                        'mobile' => $mobile,
                        'email' => $email,
                    ]),
                ]));

            if ($code = $response->json('errors.code')) {
                throw new ZarinPalGatewayException($code, $response->json('errors.message'));
            }

            $result = new ZarinPalTransactionInitializeResult($this);

            @[
                'message' => $message,
                'authority' => $result->authority,
                'fee_type' => $result->feeType,
                'fee' => $result->fee,
            ] = $response->json('data', []);

            $result->url = $this->endPoint('pg/StartPay/' . $result->authority);

            return $result;
        } catch (\Throwable $e) {
            $transaction->delete();
            throw $e;
        }
    }

    public function verify(Model $transaction, Request $request): ZarinPalPaymentVerifyResult
    {
        if ($request->get('Status') == 'NOK') {
            throw new PaymentCancelledException();
        } elseif ($request->get('Status') != 'OK') {
            abort(403);
        }

        $response = Http::asJson()
            ->acceptJson()
            ->post($this->endPoint("pg/v4/payment/verify.json"), array_filter([
                'merchant_id' => $this->key,
                'amount' => $transaction->amount,
                'authority' => $request->get('Authority'),
            ]));

        if ($code = $response->json('errors.code')) {
            $exception = new ZarinPalGatewayException($code, $response->json('errors.message'));

            match ($code) {
                -50, -51, -53, -55 => throw new PaymentFailedException($exception->getMessage(), $code, $exception),
                101                => throw new PaymentVerifyRepeatedException($exception->getMessage(), $code, $exception),
                default            => throw $exception,
            };
        }

        $result = new ZarinPalPaymentVerifyResult($this);
        $result->amount = $transaction->amount;
        $result->user = $transaction->user;
        $result->model = $transaction->model;

        @[
            'ref_id' => $result->refId,
            'card_pan' => $result->cardPan,
            'card_hash' => $result->cardHash,
            'fee_type' => $result->feeType,
            'fee' => $result->fee,
        ] = $response->json('data', []);

        return $result;
    }

}