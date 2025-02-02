<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Exceptions\GatewayException;

class ZarinPalGatewayException extends GatewayException
{

    public function __construct(int $code, ?string $message = null, ?\Throwable $previous = null)
    {
        $this->code = $code;
        parent::__construct($message ?? $this->translate('en'), $code, $previous);
    }

    /**
     * Translates the error code into a user-friendly message.
     *
     * @param string|null $locale
     * @return string
     */
    public function translate(?string $locale = null): string
    {
        $key = "gateway-ir::zarinpal.codes.{$this->code}";
        $message = trans($key, locale: $locale);

        if ($key != $message) {
            return $message;
        }

        if ($this->code > 0) {
            return trans('gateway-ir::zarinpal.codes.unknown_code', locale: $locale);
        }

        return trans('gateway-ir::zarinpal.codes.unknown', locale: $locale);
    }

}
