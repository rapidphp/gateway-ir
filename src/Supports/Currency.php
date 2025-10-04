<?php

namespace Rapid\GatewayIR\Supports;

class Currency
{
    public static function convert(int|float $amount, string $from, string $to): int|float
    {
        $amount = match (strtolower($from)) {
            'rial', 'irr'  => $amount,
            'toman', 'irt' => $amount * 10,
        };

        return match (strtolower($to)) {
            'rial', 'irr'  => $amount,
            'toman', 'irt' => $amount / 10,
        };
    }
}