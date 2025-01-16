<?php

namespace Rapid\GatewayIR\Portals\IDPay;

use Rapid\GatewayIR\Exceptions\GatewayException;

class IDPayGatewayException extends GatewayException
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
        $key = "gateway-ir::idpay.codes.{$this->code}";
        $message = trans($key, locale: $locale);

        if ($key != $message) {
            return $message;
        }

        return trans('gateway-ir::idpay.codes.unknown', locale: $locale);
    }

}