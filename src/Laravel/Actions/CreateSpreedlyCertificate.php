<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Actions;

use Illuminate\Support\Facades\Config;
use Laratusk\Spreedly\Laravel\Facades\Spreedly;
use Laratusk\Spreedly\Laravel\Facades\SpreedlyCertificateManager;
use Laratusk\Spreedly\Laravel\Models\SpreedlyCertificate;
use Laratusk\Spreedly\Laravel\Services\CertificateManager;

final class CreateSpreedlyCertificate
{
    public function execute(?string $name = null): SpreedlyCertificate
    {
        $keyPair = SpreedlyCertificateManager::createCertificateKeyPair(
            $name ?? CertificateManager::COMMON_NAME,
            (int) Config::get('spreedly.certificate_days_valid', CertificateManager::DAYS_VALID),
            (int) Config::get('spreedly.certificate_key_bits', CertificateManager::KEY_BITS),
        );

        $spreedlyCert = Spreedly::certificates()->create([
            'pem' => $keyPair->pem,
            'private_key' => $keyPair->privateKey,
        ]);

        return SpreedlyCertificate::query()->create([
            'name' => $name ?? CertificateManager::COMMON_NAME,
            'token' => $spreedlyCert->token,
            'pem' => $keyPair->pem,
            'private_key' => $keyPair->privateKey,
            'public_key' => $keyPair->publicKey,
            'public_key_hash' => $keyPair->publicKeyHash,
            'uploaded_at' => now(),
            'expires_at' => $spreedlyCert->expiresAt ?? now()->addYears(1),
        ]);
    }
}
