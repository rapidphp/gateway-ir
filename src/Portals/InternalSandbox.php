<?php

namespace Rapid\GatewayIR\Portals;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Abstract\PaymentGatewayAbstract;
use Rapid\GatewayIR\Contracts\PaymentHandler;
use Rapid\GatewayIR\Data\PaymentVerifyResult;
use Rapid\GatewayIR\Data\TransactionInitializeResult;
use Rapid\GatewayIR\Exceptions\PaymentCancelledException;
use Rapid\GatewayIR\Portals\InternalSandbox\InternalSandboxTransactionInitializeResult;

class InternalSandbox extends PaymentGatewayAbstract
{

    /**
     * Indicates whether the gateway is in sandbox mode.
     *
     * @var bool
     */
    public bool $isSandbox = true;

    public function __construct()
    {
        if (app()->isProduction()) {
            throw new \RuntimeException('Internal sandbox payment gateway is not supported in production environment.');
        }
    }

    /**
     * @inheritDoc
     */
    public function request(int $amount, string $description, PaymentHandler|string $handler, array $meta = []): TransactionInitializeResult
    {
        $record = $this->createNewRecord($amount, $description, $handler);

        $result = new InternalSandboxTransactionInitializeResult($this);
        $result->url = $this->getCallbackUrl($record);
        $result->successUrl = $result->url . '?status=success';
        $result->cancelUrl = $result->url . '?status=cancel';

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function verify(Model $transaction, Request $request): PaymentVerifyResult
    {
        $status = $request->get('status');
        
        if ($status === 'success') {
            $result = new PaymentVerifyResult($this);
            $result->amount = $transaction->amount;

            return $result;

        } elseif ($status === 'cancel') {
            throw new PaymentCancelledException();
        } else {
            response(<<<HTML
                Success: <a href="?status=success">Click to run success method</a>
                <br>
                Cancel: <a href="?status=cancel">Click to run cancel method</a>
                HTML)
                ->throwResponse();
        }
    }
}
