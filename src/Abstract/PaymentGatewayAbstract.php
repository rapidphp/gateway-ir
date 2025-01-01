<?php

namespace Rapid\GatewayIR\Abstract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Rapid\GatewayIR\Contracts\PaymentGateway;
use Rapid\GatewayIR\Contracts\PaymentHandler;
use Rapid\GatewayIR\Enums\TransactionStatuses;

abstract class PaymentGatewayAbstract implements PaymentGateway
{

    public function register(string $idName)
    {
        $this->idName = $idName;
    }

    protected string $baseUrl;


    public function endPoint(?string $suffix = null): string
    {
        return $this->baseUrl . (isset($suffix) ? '/' . $suffix : null);
    }


    /**
     * Gateway api key
     *
     * @var string
     */
    protected string $key;

    public string $idName;

    public function idName(): string
    {
        return $this->idName;
    }


    protected function getTransactionModel(): string
    {
        return config('gateway-ir.table.model');
    }

    protected function createNewRecord(
        int                   $amount,
        ?string               $description,
        string|PaymentHandler $handler,
        ?Model                $user = null,
        ?Model                $model = null,
    ): Model
    {
        $transModel = $this->getTransactionModel();

        $tries = 10;
        do $orderId = $this->randomOrderId();
        while ($transModel::where('order_id', $orderId)->exists() && --$tries);

        if (!$tries) {
            throw new \Exception("Failed to generate random order id");
        }

        return $transModel::create([
            'order_id' => $orderId,
            'amount' => $amount,
            'description' => $description,
            'status' => TransactionStatuses::Pending,
            'handler' => is_string($handler) ? $handler : serialize($handler),
            'gateway' => static::class,
            'user_type' => $user?->getMorphClass(),
            'user_id' => $user?->getKey(),
            'model_type' => $model?->getMorphClass(),
            'model_id' => $model?->getKey(),
        ]);
    }

    protected function randomOrderId(): string
    {
        return Str::uuid();
    }

    protected function getCallbackUrl(Model $transaction): ?string
    {
        return config('gateway-ir.routes.enabled') ?
            route(config('gateway-ir.routes.name'), [
                'hash' => $transaction->random_hash,
                'id' => $transaction->order_id,
            ]) :
            null;
    }

}