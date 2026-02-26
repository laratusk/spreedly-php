<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Enums;

enum TransactionState: string
{
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case GatewayProcessingFailed = 'gateway_processing_failed';
    case GatewayProcessingResultUnknown = 'gateway_processing_result_unknown';
    case Pending = 'pending';
    case Voided = 'voided';
    case Authorized = 'authorized';
    case Settling = 'settling';
    case Settled = 'settled';
    case SettlementDeclined = 'settlement_declined';
}
