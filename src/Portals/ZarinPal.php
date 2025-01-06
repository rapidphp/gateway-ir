<?php

namespace Rapid\GatewayIR\Portals;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Rapid\GatewayIR\Abstract\PaymentGatewayAbstract;
use Rapid\GatewayIR\Contracts\PaymentHandler;
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Exceptions\PaymentFailedException;
use Rapid\GatewayIR\Exceptions\PaymentVerifyRepeatedException;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalGatewayException;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalPaymentVerifyResult;
use Rapid\GatewayIR\Portals\ZarinPal\ZarinPalTransactionInitializeResult;

class ZarinPal extends PaymentGatewayAbstract
{

    protected const BASE_URL = 'https://payment.zarinpal.com';
    protected const SANDBOX_BASE_URL = 'https://sandbox.zarinpal.com';

     /**
     * ZarinPal constructor.
     *
     * Initializes the ZarinPal payment gateway with the provided merchant key
     * and sets the environment mode (sandbox or production).
     *
     * @param string $key The merchant key for ZarinPal.
     * @param bool $sandbox Indicates whether to use sandbox mode.
     */
    public function __construct(string $key, bool $sandbox = false)
    {
        $this->key = $key;
        $this->setSandbox($sandbox);
    }

    /**
     * Creates a new instance of the ZarinPal gateway.
     *
     * @param string $key The merchant key for ZarinPal.
     * @return static A new instance of the ZarinPal gateway.
     */
    public static function make(string $key): static
    {
        return new static($key);
    }

    /**
     * Creates a new instance of the ZarinPal gateway in sandbox mode.
     *
     * @param string|null $key The merchant key for ZarinPal (optional).
     * @return static A new instance of the ZarinPal gateway in sandbox mode.
     */
    public static function sandbox(?string $key = null): static
    {
        return new static($key ?? Str::uuid(), true);
    }

    /**
     * Initiates a payment request to the ZarinPal gateway.
     *
     * This method creates a new transaction record and sends a request to
     * the ZarinPal API to initiate a payment. It returns a result object
     * containing the necessary information for the transaction, including
     * the payment URL and any applicable fees.
     *
     * @param int $amount The amount to be processed.
     * @param string $description A description of the transaction.
     * @param string|PaymentHandler $handler The payment handler to be used.
     * @param array $meta Additional metadata for the transaction (optional).
     * @return ZarinPalTransactionInitializeResult The result of the transaction initialization.
     * @throws ZarinPalGatewayException If the ZarinPal API returns an error.
     * @throws \Throwable If an unexpected error occurs during the request.
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

        $transaction = $this->createNewRecord($amount, $description, $handler);

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

    /**
     * Verifies the result of a payment transaction with the ZarinPal gateway.
     *
     * This method checks the status of the payment transaction based on the request data.
     * If the transaction status is 'NOK', a PaymentCancelledException is thrown.
     * If the status is not 'OK', a 403 Forbidden response is returned.
     * If the status is 'OK', a request is sent to the ZarinPal API to verify the payment.
     * The method returns a ZarinPalPaymentVerifyResult object containing the details of the verified payment.
     *
     * @param Model $transaction The transaction model instance to verify.
     * @param Request $request The HTTP request containing the transaction status and authority.
     * @return ZarinPalPaymentVerifyResult The result of the payment verification.
     * @throws PaymentCancelledException If the transaction status is 'NOK'.
     * @throws \Illuminate\Http\Exceptions\HttpResponseException If the transaction status is not 'OK'.
     * @throws PaymentFailedException If the ZarinPal API returns an error indicating the payment has failed.
     * @throws PaymentVerifyRepeatedException If the payment verification is repeated.
     * @throws ZarinPalGatewayException If an unexpected error occurs during the verification process.
     */
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
