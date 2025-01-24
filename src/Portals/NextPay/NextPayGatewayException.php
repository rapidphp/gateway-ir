<?php

namespace Rapid\GatewayIR\Portals\NextPay;

use Rapid\GatewayIR\Exceptions\GatewayException;

class NextPayGatewayException extends GatewayException
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
        $key = "gateway-ir::nextpay.codes.{$this->code}";
        $message = trans($key, locale: $locale);

        if ($key != $message) {
            return $message;
        }

        return trans('gateway-ir::nextpay.codes.unknown', locale: $locale);
    }

}
