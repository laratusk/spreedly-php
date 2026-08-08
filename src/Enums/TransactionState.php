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
    // Accepted by the gateway, funds not received yet. Reached via callback on
    // slow rails such as PayPal ACH, which can sit here for days.
    case Processing = 'processing';
    // Setting the transaction up on the offsite gateway failed, so the
    // cardholder was never sent anywhere.
    case GatewaySetupFailed = 'gateway_setup_failed';
    case Voided = 'voided';
    case Authorized = 'authorized';
    case Settling = 'settling';
    case Settled = 'settled';
    case SettlementDeclined = 'settlement_declined';
}
