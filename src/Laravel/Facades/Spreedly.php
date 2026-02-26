<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Laratusk\Spreedly\Resources\CardRefresherResource;
use Laratusk\Spreedly\Resources\CertificateResource;
use Laratusk\Spreedly\Resources\ClaimResource;
use Laratusk\Spreedly\Resources\ComposerResource;
use Laratusk\Spreedly\Resources\EnvironmentResource;
use Laratusk\Spreedly\Resources\EventResource;
use Laratusk\Spreedly\Resources\GatewayResource;
use Laratusk\Spreedly\Resources\MerchantProfileResource;
use Laratusk\Spreedly\Resources\PaymentMethodResource;
use Laratusk\Spreedly\Resources\PaymentResource;
use Laratusk\Spreedly\Resources\ProtectionEventResource;
use Laratusk\Spreedly\Resources\ReceiverResource;
use Laratusk\Spreedly\Resources\ScaAuthenticationResource;
use Laratusk\Spreedly\Resources\SubMerchantResource;
use Laratusk\Spreedly\Resources\TransactionResource;
use Laratusk\Spreedly\SpreedlyClient;

/**
 * Spreedly Laravel Facade.
 *
 * @method static GatewayResource gateways()
 * @method static PaymentMethodResource paymentMethods()
 * @method static TransactionResource transactions()
 * @method static ReceiverResource receivers()
 * @method static CertificateResource certificates()
 * @method static EnvironmentResource environments()
 * @method static EventResource events()
 * @method static MerchantProfileResource merchantProfiles()
 * @method static ComposerResource composer()
 * @method static ScaAuthenticationResource scaAuthentication()
 * @method static SubMerchantResource subMerchants()
 * @method static CardRefresherResource cardRefresher()
 * @method static ClaimResource claim()
 * @method static PaymentResource payments()
 * @method static ProtectionEventResource protectionEvents()
 *
 * @see SpreedlyClient
 */
final class Spreedly extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SpreedlyClient::class;
    }
}
