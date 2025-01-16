<?php

namespace Rapid\GatewayIR\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Rapid\GatewayIR\Contracts\PaymentGateway;
use Rapid\GatewayIR\Enums\TransactionStatuses;
use Rapid\GatewayIR\Handlers\PaymentHandler;
use Rapid\GatewayIR\Payment;

/**
 * Abstract class representing a payment gateway.
 *
 * This class serves as a base implementation for specific payment gateway
 * integrations.
 */
abstract class PaymentGatewayAbstract implements PaymentGateway
{

    protected const BASE_URL = '';
    protected const SANDBOX_BASE_URL = '';

    /**
     * Gateway API key.
     *
     * @var string
     */
    protected string $key;

    /**
     * Indicates whether the gateway is in sandbox mode.
     *
     * @var bool
     */
    public bool $isSandbox = false;

    /**
     * The identifier name for the payment gateway.
     *
     * @var string
     */
    public string $idName;

    public function __construct(string $key, bool $sandbox = false)
    {
        $this->key = $key;
        $this->setSandbox($sandbox);
    }

    /**
     * Registers the payment gateway with a given ID name.
     *
     * @param string $idName
     * @return void
     */
    public function register(string $idName): void
    {
        $this->idName = $idName;
    }

    /**
     * Sets the sandbox mode for the payment gateway.
     *
     * @param bool $sandbox
     * @return void
     */
    protected function setSandbox(bool $sandbox): void
    {
        if ($sandbox && app()->isProduction()) {
            throw new \RuntimeException('Sandbox payment gateway is not supported in production environment.');
        }

        $this->isSandbox = $sandbox;
    }

    /**
     * Constructs the endpoint URL for the payment gateway.
     *
     * @param string|null $path
     * @return string
     */
    protected function endPoint(?string $path = null): string
    {
        $baseUrl = $this->isSandbox ? static::SANDBOX_BASE_URL ?: static::BASE_URL : static::BASE_URL;
        return $baseUrl . ($path ? '/' . $path : '');
    }

    /**
     * Retrieves the identifier name for the payment gateway.
     *
     * @return string
     */
    public function getIDName(): string
    {
        return $this->idName;
    }

    /**
     * Retrieves the transaction model class name from the configuration.
     *
     * @return string
     */
    protected function getTransactionModel(): string
    {
        return Payment::getModel();
    }

    /**
     * Creates a new transaction record in the database.
     *
     * @param int $amount
     * @param string|null $description
     * @param string|PaymentHandler $handler
     * @return Model
     */
    protected function createNewRecord(
        int $amount,
        ?string $description,
        string|PaymentHandler $handler
    ): Model {
        Payment::clearExpiredRecords();

        $transModel = $this->getTransactionModel();
        $tries = 10;

        // Generate a unique order ID
        do {
            $orderId = $this->randomOrderID();
        } while ($transModel::where('order_id', $orderId)->exists() && --$tries);

        if (!$tries) {
            throw new \RuntimeException('Failed to generate random order id');
        }

        return $transModel::create([
            'order_id' => $orderId,
            'amount' => $amount,
            'description' => $description,
            'status' => TransactionStatuses::Pending,
            'handler' => is_string($handler) ? $handler : serialize($handler),
            'gateway' => $this->getIDName()
        ]);
    }

    /**
     * Generates a random order ID using UUID.
     *
     * @return string
     */
    protected function randomOrderID(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Retrieves the callback URL for a given transaction.
     *
     * @param Model $transaction
     * @return string|null
     */
    protected function getCallbackUrl(Model $transaction): ?string
    {
        return route(config('gateway-ir.routes.name'), [
            'order_id' => $transaction->order_id,
        ]);
    }

    /**
     * Prepares a new pending payment request.
     *
     * @return PendingRequest
     */
    public function prepare(): PendingRequest
    {
        return new PendingRequest($this);
    }

}