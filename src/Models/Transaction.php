<?php

namespace Rapid\GatewayIR\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    public function getTable()
    {
        return config('gateway-ir.table.table');
    }

    protected $fillable = [
        'order_id',
        'authority',
        'amount',
        'description',
        'status',
        'handler',
        'gateway',
        'user_type',
        'user_id',
        'model_type',
        'model_id',
    ];

    public function user()
    {
        return $this->morphTo();
    }

    public function model()
    {
        return $this->morphTo();
    }

    public static function forUser(Builder $query, Model $user)
    {
        $query->whereMorphedTo('user', $user);
    }

    public static function forModel(Builder $query, Model $model)
    {
        $query->whereMorphedTo('model', $model);
    }

}