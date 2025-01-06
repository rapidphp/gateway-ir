<?php

namespace Rapid\GatewayIR\Portals\ZarinPal;

use Rapid\GatewayIR\Exceptions\GatewayException;

/**
 * Class ZarinPalGatewayException
 *
 * Represents an exception specific to the ZarinPal payment gateway.
 * This class extends the base GatewayException and provides additional
 * functionality for translating error codes into user-friendly messages.
 */
class ZarinPalGatewayException extends GatewayException
{
    /**
     * ZarinPalGatewayException constructor.
     *
     * @param int $code The error code associated with the exception.
     * @param string|null $message An optional message describing the exception.
     * @param \Throwable|null $previous An optional previous throwable used for exception chaining.
     */
    public function __construct(int $code, ?string $message = null, ?\Throwable $previous = null)
    {
        $this->code = $code;
        parent::__construct($message ?? $this->translate('en'), $code, $previous);
    }

    /**
     * Translates the error code into a user-friendly message.
     *
     * This method looks up the error code in the translation files and returns
     * the corresponding message. If the code is not found, it returns a default
     * message indicating an unknown error.
     *
     * @param string|null $locale The locale to use for translation. Defaults to null.
     * @return string The translated error message.
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
