<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Contracts;

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

interface SpreedlyClientInterface
{
    /**
     * Get the gateways resource.
     */
    public function gateways(): GatewayResource;

    /**
     * Get the payment methods resource.
     */
    public function paymentMethods(): PaymentMethodResource;

    /**
     * Get the transactions resource.
     */
    public function transactions(): TransactionResource;

    /**
     * Get the receivers resource.
     */
    public function receivers(): ReceiverResource;

    /**
     * Get the certificates resource.
     */
    public function certificates(): CertificateResource;

    /**
     * Get the environments resource.
     */
    public function environments(): EnvironmentResource;

    /**
     * Get the events resource.
     */
    public function events(): EventResource;

    /**
     * Get the merchant profiles resource.
     */
    public function merchantProfiles(): MerchantProfileResource;

    /**
     * Get the composer resource.
     */
    public function composer(): ComposerResource;

    /**
     * Get the SCA authentication resource.
     */
    public function scaAuthentication(): ScaAuthenticationResource;

    /**
     * Get the sub merchants resource.
     */
    public function subMerchants(): SubMerchantResource;

    /**
     * Get the card refresher resource.
     */
    public function cardRefresher(): CardRefresherResource;

    /**
     * Get the claim resource.
     */
    public function claim(): ClaimResource;

    /**
     * Get the payments resource.
     */
    public function payments(): PaymentResource;

    /**
     * Get the protection events resource.
     */
    public function protectionEvents(): ProtectionEventResource;
}
