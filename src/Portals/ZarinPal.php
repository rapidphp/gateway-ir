<?php

namespace Rapid\GatewayIR\Portals;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Rapid\GatewayIR\Abstract\PaymentGatewayAbstract;
use Rapid\GatewayIR\Abstract\VerifyResult;
use Rapid\GatewayIR\Contracts\PaymentHandler;
use Rapid\GatewayIR\Exceptions\GatewayProblemException;
use Rapid\GatewayIR\Exceptions\PaymentCancelled;
use Rapid\GatewayIR\Exceptions\PaymentRepeated;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalCreationResult;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalVerifyResult;

class ZarinPal extends PaymentGatewayAbstract
{

    protected string $baseUrl = 'https://payment.zarinpal.com';
//    protected string $baseUrl = 'https://sandbox.zarinpal.com';

    public function request(
        int                   $amount,
        string                $description,
        string|PaymentHandler $handler,
        ?Model                $user = null,
        ?Model                $model = null,
        array                 $meta = [],
    )
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
                ->throw()
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

            $this->checkPublicResponse($response);

            $result = new ZarinPalCreationResult($this);

            @[
                'message' => $message,
                'authority' => $result->authority,
                'fee_type' => $result->feeType,
                'fee' => $result->fee,
            ] = $response->json('data', []);

            $result->url = $this->endPoint('pg/StartPay/' . $result->authority);

            return new ZarinPalCreationResult($this);
        }
        catch (\Throwable $e) {
            $transaction->delete();
            throw $e;
        }
    }

    public function verify(Model $transaction, Request $request): VerifyResult
    {
        if ($request->get('Status') == 'NOK') {
            throw new PaymentCancelled();
        } elseif ($request->get('Status') != 'OK') {
            abort(403);
        }

        $response = Http::asJson()
            ->acceptJson()
            ->throw()
            ->post($this->endPoint("pg/v4/payment/verify.json"), array_filter([
                'merchant_id' => $this->key,
                'amount' => $transaction->amount,
                'authority' => $request->get('Authority'),
            ]));

        $this->checkPublicResponse($response);

        $result = new ZarinPalVerifyResult($this);
        $result->amount = $transaction->amount;
        $result->user = $transaction->user;
        $result->model = $transaction->model;

        @[
            'code' => $code,
            'ref_id' => $refId,
            'card_pan' => $cardPan,
            'card_hash' => $cardHash,
            'fee_type' => $feeType,
            'fee' => $fee,
        ] = $response->json('data', []);

        switch ($code) {
            case -50:
            case -53:
            case -55:
                abort(\Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);

            case -51:
                throw new PaymentCancelled();

            case -52:
            case -54:
                throw new GatewayProblemException();

            case 101:
                throw new PaymentRepeated();

            case 100:
                break;

            default:
                throw new \Exception("");
        }

        return $result;
    }


    protected function getErrorMessage(int $code): string
    {

    }

}