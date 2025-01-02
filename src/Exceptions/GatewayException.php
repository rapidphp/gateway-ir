<?php

namespace Rapid\GatewayIR\Exceptions;

abstract class GatewayException extends \Exception
{

    abstract public function translate(?string $locale = null): string;

}