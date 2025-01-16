<?php

namespace Rapid\GatewayIR\Concerns;

use Illuminate\Support\Str;

trait GatewayDefaults
{

    /**
     * Creates a new instance of the gateway.
     *
     * @param string $key
     * @return static
     */
    public static function make(string $key): static
    {
        return new static($key);
    }

    /**
     * Creates a new instance of the gateway in sandbox mode.
     *
     * @param string|null $key
     * @return static
     */
    public static function sandbox(?string $key = null): static
    {
        return new static($key ?? Str::uuid(), true);
    }

}