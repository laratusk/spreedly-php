<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Laratusk\Spreedly\DataTransferObjects\CertificateKeyPair;
use Laratusk\Spreedly\Services\CertificateManager;

/**
 * SpreedlyCertificateManager Laravel Facade.
 *
 * @method static CertificateKeyPair createCertificateKeyPair(string $commonName = CertificateManager::COMMON_NAME, int $daysValid = CertificateManager::DAYS_VALID, int $keyBits = CertificateManager::KEY_BITS, ?string $passphrase = null)
 * @method static string encryptPrivateKey(string $privateKey)
 * @method static string decryptPrivateKey(string $cipher)
 *
 * @see CertificateManager
 */
final class SpreedlyCertificateManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CertificateManager::class;
    }
}
