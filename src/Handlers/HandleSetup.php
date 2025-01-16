<?php

namespace Rapid\GatewayIR\Handlers;

use Closure;
use Rapid\GatewayIR\Data\PaymentCancelledResult;
use Rapid\GatewayIR\Data\PaymentFailed;
use Rapid\GatewayIR\Data\PaymentPrepare;
use Rapid\GatewayIR\Data\PaymentVerifyResult;

final class HandleSetup
{

    public function __construct()
    {
    }

    protected array $success = [];

    public function success(Closure $callback)
    {
        $this->success[] = $callback;
        return $this;
    }

    public function fireSuccess(PaymentVerifyResult $result)
    {
        foreach ($this->success as $callback) {
            if (null !== $response = $callback($result)) {
                return $response;
            }
        }

        return null;
    }

    protected array $fail = [];

    public function fail(Closure $callback)
    {
        $this->fail[] = $callback;
        return $this;
    }

    public function fireFail(PaymentFailed $result)
    {
        foreach ($this->fail as $callback) {
            if (null !== $response = $callback($result)) {
                return $response;
            }
        }

        return null;
    }

    protected array $cancel = [];

    public function cancel(Closure $callback)
    {
        $this->cancel[] = $callback;
        return $this;
    }

    public function fireCancel(PaymentCancelledResult $result)
    {
        foreach ($this->cancel as $callback) {
            if (null !== $response = $callback($result)) {
                return $response;
            }
        }

        return null;
    }

    protected array $validate = [];

    public function validate(Closure $callback)
    {
        $this->validate[] = $callback;
        return $this;
    }

    public function fireValidate(PaymentPrepare $result): bool
    {
        foreach ($this->validate as $callback) {
            if (false === $callback($result)) {
                return false;
            }
        }

        return true;
    }

}