<?php

namespace Rapid\GatewayIR\Abstract;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Contracts\PaymentGateway;
use Rapid\GatewayIR\Contracts\PaymentHandler;
use Rapid\GatewayIR\Enums\TransactionStatuses;

/**
 * Abstract class representing a payment gateway.
 *
 * This class serves as a base implementation for specific payment gateway
 * integrations. It defines common properties and methods that all payment
 * gateways should implement, including handling sandbox mode, generating
 * transaction records, and constructing endpoint URLs.
 *
 * The class implements the PaymentGateway contract, ensuring that all
 * derived classes adhere to the required interface for payment processing.
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

    /**
     * Registers the payment gateway with a given ID name.
     *
     * @param string $idName The identifier name for the payment gateway.
     * @return void
     */
    public function register(string $idName): void
    {
        $this->idName = $idName;
    }

    /**
     * Sets the sandbox mode for the payment gateway.
     *
     * @param bool $sandbox Indicates whether to enable sandbox mode.
     * @return void
     * @throws \RuntimeException If attempting to enable sandbox mode in a production environment.
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
     * @param string|null $path Optional path to append to the base URL.
     * @return string The complete endpoint URL.
     */
    protected function endPoint(?string $path = null): string
    {
        $baseUrl = $this->isSandbox ? static::SANDBOX_BASE_URL : static::BASE_URL;
        return $baseUrl . ($path ? '/' . $path : '');
    }

    /**
     * Retrieves the identifier name for the payment gateway.
     *
     * @return string The identifier name.
     */
    public function getIDName(): string
    {
        return $this->idName;
    }

    /**
     * Retrieves the transaction model class name from the configuration.
     *
     * @return string The fully qualified class name of the transaction model.
     */
    protected function getTransactionModel(): string
    {
        return config('gateway-ir.database.model');
    }

    /**
     * Creates a new transaction record in the database.
     *
     * @param int $amount The amount for the transaction.
     * @param string|null $description A description of the transaction.
     * @param string|PaymentHandler $handler The payment handler to be used.
     * @return Model The created transaction record.
     * @throws \RuntimeException If unable to generate a unique order ID.
     */
    protected function createNewRecord(
        int $amount,
        ?string $description,
        string|PaymentHandler $handler
    ): Model {
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
     * @return string The generated order ID.
     */
    protected function randomOrderID(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Retrieves the callback URL for a given transaction.
     *
     * This method constructs the callback URL using the transaction's order ID.
     * It generates the URL based on the route name defined in the configuration.
     *
     * @param Model $transaction The transaction model instance for which to generate the callback URL.
     * @return string The generated callback URL.
     */
    protected function getCallbackUrl(Model $transaction): ?string
    {
        return route(config('gateway-ir.routes.name'), [
            'order_id' => $transaction->order_id,
        ]);
    }
}