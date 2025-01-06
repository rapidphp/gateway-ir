<?php

namespace Rapid\GatewayIR\Data;

/**
 * Class PaymentVerifyResult
 *
 * This class represents the result of a payment verification process.
 * It contains information about the verified payment, including the
 * amount that was processed. This class is typically used to encapsulate
 * the results returned after verifying a payment transaction.
 */
class PaymentVerifyResult extends Data
{
    /**
     * The amount that was processed in the payment transaction.
     *
     * @var int
     */
    public int $amount;
}
