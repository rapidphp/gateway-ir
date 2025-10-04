<?php

namespace Rapid\GatewayIR\Portals;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Rapid\GatewayIR\Concerns\GatewayDefaults;
use Rapid\GatewayIR\Payment\PaymentGatewayAbstract;
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Exceptions\PaymentFailedException;
use Rapid\GatewayIR\Exceptions\PaymentVerifyRepeatedException;
use Rapid\GatewayIR\Handlers\PaymentHandler;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalGatewayException;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalPaymentVerifyResult;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalTransactionInitializeResult;
use Rapid\GatewayIR\Supports\Currency;

class ZarinPal extends PaymentGatewayAbstract
{
    use GatewayDefaults;

    protected const BASE_URL = 'https://payment.zarinpal.com';
    protected const SANDBOX_BASE_URL = 'https://sandbox.zarinpal.com';

    /**
     * @inheritDoc
     */
    public function request(
        int                   $amount,
        string                $description,
        string|PaymentHandler $handler,
        array                 $meta = [],
    ): ZarinPalTransactionInitializeResult
    {
        @[
            'currency' => $currency,
            'mobile' => $mobile,
            'email' => $email,
        ] = $meta;

        $amount = Currency::convert($amount, $currency ?? 'IRR', 'IRR');

        $transaction = $this->createNewRecord($amount, $description, $handler);

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->post($this->endPoint("pg/v4/payment/request.json"), array_filter([
                    'merchant_id' => $this->key,
                    'currency' => 'IRR',
                    'amount' => $amount,
                    'description' => $description,
                    'callback_url' => $this->getCallbackUrl($transaction),
                    'order_id' => $transaction->order_id,
                    'metadata' => array_filter([
                        'mobile' => $mobile,
                        'email' => $email,
                    ]),
                ]));

            if ($code = $response->json('errors.code')) {
                throw new ZarinPalGatewayException($code, $response->json('errors.message'));
            }

            $authority = $response->json('data.authority');
            $this->initializeRecord($transaction, $authority);

            $result = new ZarinPalTransactionInitializeResult($this);

            @[
                'message' => $message,
                'fee_type' => $result->feeType,
                'fee' => $result->fee,
            ] = $response->json('data', []);

            $result->url = $this->endPoint('pg/StartPay/' . $authority);

            return $result;
        } catch (\Throwable $e) {
            $transaction->delete();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function verify(Model $transaction, Request $request): ZarinPalPaymentVerifyResult
    {
        if ($request->input('Status') == 'NOK') {
            throw new PaymentCancelledException();
        } elseif ($request->input('Status') != 'OK') {
            abort(403);
        }

        abort_if(Validator::make($request->all(), [
            'Authority' => 'required|string|max:255',
        ])->fails(), 403);

        abort_if($request->input('Authority') != $transaction->authority, 403);

        $response = Http::asJson()
            ->acceptJson()
            ->post($this->endPoint("pg/v4/payment/verify.json"), array_filter([
                'merchant_id' => $this->key,
                'amount' => $transaction->amount,
                'authority' => $request->input('Authority'),
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
