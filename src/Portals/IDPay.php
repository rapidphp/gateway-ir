<?php

namespace Rapid\GatewayIR\Portals;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Rapid\GatewayIR\Concerns\GatewayDefaults;
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Exceptions\PaymentFailedException;
use Rapid\GatewayIR\Exceptions\PaymentVerifyRepeatedException;
use Rapid\GatewayIR\Handlers\PaymentHandler;
use Rapid\GatewayIR\Payment\PaymentGatewayAbstract;
use Rapid\GatewayIR\Portals\IDPay\IDPayGatewayException;
use Rapid\GatewayIR\Portals\IDPay\IDPayPaymentVerifyResult;
use Rapid\GatewayIR\Portals\IDPay\IDPayTransactionInitializeResult;
use Rapid\GatewayIR\Supports\Currency;

class IDPay extends PaymentGatewayAbstract
{
    use GatewayDefaults;

    protected const BASE_URL = 'https://api.idpay.ir';

    /**
     * @inheritDoc
     */
    public function request(
        int                   $amount,
        string                $description,
        string|PaymentHandler $handler,
        array                 $meta = [],
    ): IDPayTransactionInitializeResult
    {
        @[
            'mobile' => $mobile,
            'phone' => $phone,
            'email' => $email,
            'mail' => $mail,
            'name' => $name,
            'currency' => $currency,
        ] = $meta;

        $amount = Currency::convert($amount, $currency ?? 'IRR', 'IRR');

        $transaction = $this->createNewRecord($amount, $description, $handler);

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->withHeader('X-API-KEY', $this->key)
                ->when($this->isSandbox)
                ->withHeader('X-SANDBOX', '1')
                ->post($this->endPoint("v1.1/payment"), array_filter([
                    'order_id' => $transaction->order_id,
                    'amount' => $amount,
                    'description' => $description,
                    'callback_url' => $this->getCallbackUrl($transaction),
                    'name' => $name,
                    'phone' => $mobile ?? $phone,
                    'mail' => $email ?? $mail,
                ]));

            if ($response->clientError()) {
                throw new IDPayGatewayException($response->json('error_code', 0), $response->json('error_message'));
            }

            $authority = $response->json('id');
            $this->initializeRecord($transaction, $authority);

            $result = new IDPayTransactionInitializeResult($this);
            $result->url = $response->json('link');

            return $result;
        } catch (\Throwable $e) {
            $transaction->delete();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function verify(Model $transaction, Request $request): IDPayPaymentVerifyResult
    {
        if ($request->get('status') == 7) {
            throw new PaymentCancelledException();
        } elseif ($request->get('status') != 100) {
            abort(403);
        }

        abort_if(Validator::make($request->all(), [
            'id' => 'required|string',
        ])->fails(), 403);

        abort_if($request->input('id') != $transaction->authority, 403);

        $response = Http::asJson()
            ->acceptJson()
            ->withHeader('X-API-KEY', $this->key)
            ->when($this->isSandbox)
            ->withHeader('X-SANDBOX', '1')
            ->post($this->endPoint("v1.1/payment/verify"), array_filter([
                'id' => $request->input('id'),
                'order_id' => $transaction->order_id,
            ]));

        if ($response->clientError()) {
            throw new IDPayGatewayException($response->json('error_code', 0), $response->json('error_message'));
        }

        if (100 != $status = $response->json('status')) {
            match ($status) {
                7        => throw new PaymentCancelledException(),
                101, 200 => throw new PaymentVerifyRepeatedException(),
                default  => throw new PaymentFailedException(),
            };
        }

        $result = new IDPayPaymentVerifyResult($this);

        $result->createdAt = Carbon::make($response->json('payment.date'));
        $result->verifiedAt = Carbon::make($response->json('verify.date'));
        $result->amount = $transaction->amount;

        @[
            'track_id' => $result->trackId,
        ] = $response->json();

        @[
            'card_no' => $result->cardPan,
            'hashed_card_no' => $result->cardHash,
        ] = $response->json('payment', []);

        return $result;
    }

}