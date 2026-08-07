<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Enums;

enum TransactionType: string
{
    case Purchase = 'Purchase';
    case Authorize = 'Authorize';
    case Authorization = 'Authorization';
    case Verification = 'Verification';
    case OffsitePurchase = 'OffsitePurchase';
    case OffsiteAuthorization = 'OffsiteAuthorization';
    case ExportPaymentMethods = 'ExportPaymentMethods';
    case ReplacePaymentMethod = 'ReplacePaymentMethod';
    case ContactCardHolder = 'ContactCardHolder';
    case NoUpdate = 'NoUpdate';
    case Inquiry = 'Inquiry';
    case ScaAuthentication = 'Sca::Authentication';
    case Capture = 'Capture';
    case Void = 'Void';
    case Credit = 'Credit';
    case GeneralCredit = 'GeneralCredit';
    case Verify = 'Verify';
    case Store = 'Store';
    case Redact = 'Redact';
    case Retain = 'Retain';
    case Complete = 'Complete';
    case Confirm = 'Confirm';
    case Recache = 'Recache';
    case AddPaymentMethod = 'AddPaymentMethod';
    case UpdatePaymentMethod = 'UpdatePaymentMethod';
    case DeliverPaymentMethod = 'DeliverPaymentMethod';
}
