<?php

namespace Rapid\GatewayIR\Portals;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Rapid\GatewayIR\Contracts\GatewaySupportsRevert;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Data\TransactionInitializeResult;
use Rapid\GatewayIR\Handlers\PaymentHandler;
use Rapid\GatewayIR\Payment\PaymentGatewayAbstract;
use Rapid\GatewayIR\Portals\NextPay\NextPayGatewayException;
use Rapid\GatewayIR\Portals\NextPay\NextPayPaymentVerifyResult;
use Rapid\GatewayIR\Portals\NextPay\NextPayTransactionInitializeResult;

class NextPay extends PaymentGatewayAbstract implements GatewaySupportsRevert
{

    protected const BASE_URL = 'https://nextpay.org';

    /**
     * @inheritDoc
     */
    public function request(
        int                   $amount,
        string                $description,
        string|PaymentHandler $handler,
        array                 $meta = [],
    ): NextPayTransactionInitializeResult
    {
        @[
            'name' => $name,
            'currency' => $currency,
            'mobile' => $mobile,
            'custom' => $custom,
            'allowedCard' => $allowedCard,
        ] = $meta;

        $transaction = $this->createNewRecord($amount, $description, $handler);

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->post($this->endPoint("nx/gateway/token"), array_filter([
                    'api_key' => $this->key,
                    'amount' => $amount,
                    'payer_desc' => $description,
                    'callback_uri' => $this->getCallbackUrl($transaction),
                    'currency' => $currency,
                    'order_id' => $transaction->order_id,
                    'customer_phone' => $mobile,
                    'custom_json_fields' => isset($custom) ? json_encode($custom) : null,
                    'payer_name' => $name,
                    'allowed_card' => $allowedCard,
                ]));

            if (-1 !== $code = $response->json('code')) {
                throw new NextPayGatewayException($code);
            }

            $authority = $response->json('trans_id');
            $this->initializeRecord($transaction, $authority);

            $result = new NextPayTransactionInitializeResult($this);

            $result->authority = $authority;
            $result->url = $this->endPoint('nx/gateway/payment/' . $authority);

            return $result;
        } catch (\Throwable $e) {
            $transaction->delete();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function verify(Model $transaction, Request $request): NextPayPaymentVerifyResult
    {
        abort_if(
            Validator::make($request->all(), [
                'trans_id' => 'required|string|max:255',
                'order_id' => 'required|string|max:255',
                'amount' => 'required|integer|min:0',
            ])->fails(),
            403,
        );

        [
            'trans_id' => $transId,
            'orderId' => $orderId,
            'amount' => $amount,
        ] = $request->all();

        abort_if($orderId != $transaction->order_id || $amount != $transaction->amount || $transId != $transId->authority, 403);

        $response = Http::asJson()
            ->acceptJson()
            ->post($this->endPoint("nx/gateway/verify"), array_filter([
                'api_key' => $this->key,
                'trans_id' => $transId,
                'amount' => $amount,
            ]));

        if (0 !== $code = $response->json('code')) {
            $exception = new NextPayGatewayException($code);

            match ($code) {
//                -50, -51, -53, -55 => throw new PaymentFailedException($exception->getMessage(), $code, $exception),
//                101                => throw new PaymentVerifyRepeatedException($exception->getMessage(), $code, $exception),
                default => throw $exception,
            };
        }

        $result = new NextPayPaymentVerifyResult($this);
        $result->amount = $transaction->amount;

        @[
            'Shaparak_Ref_Id' => $result->refId,
            'card_holder' => $result->cardPan,
            'customer_phone' => $result->mobile,
        ] = $response->json();

        $result->custom = $response->json('custom');

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function revert(Model $transaction, PaymentVerifyResult $result): void
    {
        $response = Http::asJson()
            ->acceptJson()
            ->post($this->endPoint("nx/gateway/verify"), array_filter([
                'api_key' => $this->key,
                'trans_id' => $transaction->authority,
                'amount' => $transaction->amount,
                'refund_request' => 'yes_money_back',
            ]));

        if (-90 !== $code = $response->json('code')) {
            throw new NextPayGatewayException($code);
        }
    }

}