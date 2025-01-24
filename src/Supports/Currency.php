<?php

namespace Rapid\GatewayIR\Supports;

class Currency
{

    public static function to(int $amountIRT, string $currency): int|float
    {
        return match (strtolower($currency)) {
            'rial', 'irr'  => $amountIRT * 10,
            'toman', 'irt' => $amountIRT,
        };
    }

    public static function from(int|float $amount, string $currency): int
    {
        return match (strtolower($currency)) {
            'rial', 'irr'  => $amount / 10,
            'toman', 'irt' => $amount,
        };
    }

}