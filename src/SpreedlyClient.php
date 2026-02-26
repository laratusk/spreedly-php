<?php

declare(strict_types=1);

namespace Laratusk\Spreedly;

use Laratusk\Spreedly\Contracts\SpreedlyClientInterface;
use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\Http\Transporter;
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

/**
 * Main entry point for the Spreedly PHP SDK.
 *
 * Usage:
 * ```php
 * $spreedly = new SpreedlyClient(
 *     environmentKey: 'your_environment_key',
 *     accessSecret: 'your_access_secret',
 * );
 *
 * $gateway = $spreedly->gateways->create(['gateway_type' => 'test']);
 * $transaction = $spreedly->transactions->purchase($gateway->token, [
 *     'payment_method_token' => 'pm_token',
 *     'amount' => 1000,  // $10.00 in cents
 *     'currency_code' => 'USD',
 * ]);
 * ```
 */
final readonly class SpreedlyClient implements SpreedlyClientInterface
{
    public GatewayResource $gateways;

    public PaymentMethodResource $paymentMethods;

    public TransactionResource $transactions;

    public ReceiverResource $receivers;

    public CertificateResource $certificates;

    public EnvironmentResource $environments;

    public EventResource $events;

    public MerchantProfileResource $merchantProfiles;

    public ComposerResource $composer;

    public ScaAuthenticationResource $scaAuthentication;

    public SubMerchantResource $subMerchants;

    public CardRefresherResource $cardRefresher;

    public ClaimResource $claim;

    public PaymentResource $payments;

    public ProtectionEventResource $protectionEvents;

    /**
     * @param  array<string, mixed>  $options  Configuration options:
     *                                         - base_url: string (default: 'https://core.spreedly.com/v1/')
     *                                         - timeout: int (default: 30)
     *                                         - connect_timeout: int (default: 10)
     *                                         - retries: int (default: 3)
     */
    public function __construct(
        string $environmentKey,
        string $accessSecret,
        ?TransporterInterface $transporter = null,
        array $options = [],
    ) {
        $transport = $transporter ?? new Transporter($environmentKey, $accessSecret, $options);

        $this->gateways = new GatewayResource($transport);
        $this->paymentMethods = new PaymentMethodResource($transport);
        $this->transactions = new TransactionResource($transport);
        $this->receivers = new ReceiverResource($transport);
        $this->certificates = new CertificateResource($transport);
        $this->environments = new EnvironmentResource($transport);
        $this->events = new EventResource($transport);
        $this->merchantProfiles = new MerchantProfileResource($transport);
        $this->composer = new ComposerResource($transport);
        $this->scaAuthentication = new ScaAuthenticationResource($transport);
        $this->subMerchants = new SubMerchantResource($transport);
        $this->cardRefresher = new CardRefresherResource($transport);
        $this->claim = new ClaimResource($transport);
        $this->payments = new PaymentResource($transport);
        $this->protectionEvents = new ProtectionEventResource($transport);
    }

    /**
     * Get the gateways resource.
     */
    public function gateways(): GatewayResource
    {
        return $this->gateways;
    }

    /**
     * Get the payment methods resource.
     */
    public function paymentMethods(): PaymentMethodResource
    {
        return $this->paymentMethods;
    }

    /**
     * Get the transactions resource.
     */
    public function transactions(): TransactionResource
    {
        return $this->transactions;
    }

    /**
     * Get the receivers resource.
     */
    public function receivers(): ReceiverResource
    {
        return $this->receivers;
    }

    /**
     * Get the certificates resource.
     */
    public function certificates(): CertificateResource
    {
        return $this->certificates;
    }

    /**
     * Get the environments resource.
     */
    public function environments(): EnvironmentResource
    {
        return $this->environments;
    }

    /**
     * Get the events resource.
     */
    public function events(): EventResource
    {
        return $this->events;
    }

    /**
     * Get the merchant profiles resource.
     */
    public function merchantProfiles(): MerchantProfileResource
    {
        return $this->merchantProfiles;
    }

    /**
     * Get the composer resource.
     */
    public function composer(): ComposerResource
    {
        return $this->composer;
    }

    /**
     * Get the SCA authentication resource.
     */
    public function scaAuthentication(): ScaAuthenticationResource
    {
        return $this->scaAuthentication;
    }

    /**
     * Get the sub merchants resource.
     */
    public function subMerchants(): SubMerchantResource
    {
        return $this->subMerchants;
    }

    /**
     * Get the card refresher resource.
     */
    public function cardRefresher(): CardRefresherResource
    {
        return $this->cardRefresher;
    }

    /**
     * Get the claim resource.
     */
    public function claim(): ClaimResource
    {
        return $this->claim;
    }

    /**
     * Get the payments resource.
     */
    public function payments(): PaymentResource
    {
        return $this->payments;
    }

    /**
     * Get the protection events resource.
     */
    public function protectionEvents(): ProtectionEventResource
    {
        return $this->protectionEvents;
    }
}
