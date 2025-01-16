<?php

namespace Rapid\GatewayIR\Enums;

final class TransactionStatuses
{

    public const Pending = 'pending';

    public const Cancelled = 'cancelled';

    public const Success = 'success';

    public const InternalError = 'internal_error';

    public const PendInQueue = 'pend_in_queue';

    public const Reverted = 'reverted';

    public const Expired = 'expired';

}