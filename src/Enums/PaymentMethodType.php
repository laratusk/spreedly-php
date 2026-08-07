<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Enums;

enum PaymentMethodType: string
{
    case CreditCard = 'credit_card';
    case BankAccount = 'bank_account';
    case ApplePay = 'apple_pay';
    case GooglePay = 'google_pay';
    case ThirdPartyToken = 'third_party_token';
    case NetworkToken = 'network_token';
    case AndroidPay = 'android_pay';
    case SpAccount = 'spAccount';
    // The redirect-backed payment method an offsite or 3DS2 transaction is created with.
    case Sprel = 'sprel';
}
