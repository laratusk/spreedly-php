<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly payment method.
 * Amounts in Spreedly are always integers (cents).
 */
final readonly class PaymentMethod
{
    /**
     * @param  array<string, mixed>  $errors
     */
    public function __construct(
        public string $token,
        public string $storageState,
        public bool $test,
        public ?string $lastFourDigits,
        public ?string $firstSixDigits,
        public ?string $cardType,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $month,
        public ?string $year,
        public ?string $email,
        public ?string $address1,
        public ?string $address2,
        public ?string $city,
        public ?string $state,
        public ?string $zip,
        public ?string $country,
        public ?string $phoneNumber,
        public string $paymentMethodType,
        public ?string $bank,
        public ?string $bankName,
        public ?string $bankType,
        public ?string $bankRoutingNumber,
        public ?string $bankAccountNumber,
        public ?string $bankAccountHolderType,
        public ?string $bankAccountType,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
        public bool $eligible_for_card_updater,
        public array $errors,
        public ?string $fingerprint,
        public ?string $callbackUrl,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $pm = $data['payment_method'] ?? $data;

        return new self(
            token: (string) ($pm['token'] ?? ''),
            storageState: (string) ($pm['storage_state'] ?? ''),
            test: (bool) ($pm['test'] ?? false),
            lastFourDigits: isset($pm['last_four_digits']) ? (string) $pm['last_four_digits'] : null,
            firstSixDigits: isset($pm['first_six_digits']) ? (string) $pm['first_six_digits'] : null,
            cardType: isset($pm['card_type']) ? (string) $pm['card_type'] : null,
            firstName: isset($pm['first_name']) ? (string) $pm['first_name'] : null,
            lastName: isset($pm['last_name']) ? (string) $pm['last_name'] : null,
            month: isset($pm['month']) ? (string) $pm['month'] : null,
            year: isset($pm['year']) ? (string) $pm['year'] : null,
            email: isset($pm['email']) ? (string) $pm['email'] : null,
            address1: isset($pm['address1']) ? (string) $pm['address1'] : null,
            address2: isset($pm['address2']) ? (string) $pm['address2'] : null,
            city: isset($pm['city']) ? (string) $pm['city'] : null,
            state: isset($pm['state']) ? (string) $pm['state'] : null,
            zip: isset($pm['zip']) ? (string) $pm['zip'] : null,
            country: isset($pm['country']) ? (string) $pm['country'] : null,
            phoneNumber: isset($pm['phone_number']) ? (string) $pm['phone_number'] : null,
            paymentMethodType: (string) ($pm['payment_method_type'] ?? ''),
            bank: isset($pm['bank']) ? (string) $pm['bank'] : null,
            bankName: isset($pm['bank_name']) ? (string) $pm['bank_name'] : null,
            bankType: isset($pm['bank_type']) ? (string) $pm['bank_type'] : null,
            bankRoutingNumber: isset($pm['bank_routing_number']) ? (string) $pm['bank_routing_number'] : null,
            bankAccountNumber: isset($pm['bank_account_number']) ? (string) $pm['bank_account_number'] : null,
            bankAccountHolderType: isset($pm['bank_account_holder_type']) ? (string) $pm['bank_account_holder_type'] : null,
            bankAccountType: isset($pm['bank_account_type']) ? (string) $pm['bank_account_type'] : null,
            createdAt: CarbonImmutable::parse($pm['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($pm['updated_at'] ?? 'now'),
            eligible_for_card_updater: (bool) ($pm['eligible_for_card_updater'] ?? false),
            errors: (array) ($pm['errors'] ?? []),
            fingerprint: isset($pm['fingerprint']) ? (string) $pm['fingerprint'] : null,
            callbackUrl: isset($pm['callback_url']) ? (string) $pm['callback_url'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'storage_state' => $this->storageState,
            'test' => $this->test,
            'last_four_digits' => $this->lastFourDigits,
            'first_six_digits' => $this->firstSixDigits,
            'card_type' => $this->cardType,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'month' => $this->month,
            'year' => $this->year,
            'email' => $this->email,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'phone_number' => $this->phoneNumber,
            'payment_method_type' => $this->paymentMethodType,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
            'eligible_for_card_updater' => $this->eligible_for_card_updater,
            'errors' => $this->errors,
            'fingerprint' => $this->fingerprint,
            'callback_url' => $this->callbackUrl,
        ];
    }
}
