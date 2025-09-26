<?php

namespace Rapid\GatewayIR\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Rapid\GatewayIR\Handlers\PaymentHandler;
use Rapid\GatewayIR\Services\GatewayService;

/**
 * @property int $id
 * @property string $order_id
 * @property string $authority
 * @property int $amount
 * @property string $description
 * @property string $status
 * @property null|string $handler
 * @property string $gateway
 * @property string $tracking_code
 * @property null|Carbon $paid_at
 */
class Transaction extends Model
{
    public function getTable()
    {
        return config('gateway-ir.database.table');
    }

    protected $fillable = [
        'order_id',
        'authority',
        'amount',
        'description',
        'status',
        'handler',
        'gateway',
        'tracking_code',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function paymentHandler(): Attribute
    {
        return Attribute::get(function (): ?PaymentHandler {
            return app(GatewayService::class)->getTransactionHandler($this);
        });
    }
}