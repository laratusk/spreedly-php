<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Contracts;

use Laratusk\Spreedly\DataTransferObjects\CertificateKeyPair;

interface CertificateManagerInterface
{
    public function createCertificateKeyPair(
        string $commonName,
        int $daysValid,
        int $keyBits,
        ?string $passphrase = null,
    ): CertificateKeyPair;

    public function encryptPrivateKey(string $privateKey): string;

    public function decryptPrivateKey(string $cipher): string;
}
