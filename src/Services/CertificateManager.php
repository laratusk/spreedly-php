<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Services;

use Illuminate\Support\Facades\Crypt;
use Laratusk\Spreedly\Contracts\CertificateManagerInterface;
use Laratusk\Spreedly\DataTransferObjects\CertificateKeyPair;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use OpenSSLCertificateSigningRequest;
use RuntimeException;

final class CertificateManager implements CertificateManagerInterface
{
    public const COMMON_NAME = 'spreedly.certificate';

    public const DAYS_VALID = 365;

    public const KEY_BITS = 2048;

    public function createCertificateKeyPair(
        string $commonName = self::COMMON_NAME,
        int $daysValid = self::DAYS_VALID,
        int $keyBits = self::KEY_BITS,
        ?string $passphrase = null,
    ): CertificateKeyPair {
        $dn = ['commonName' => $commonName];

        $config = [
            'private_key_bits' => $keyBits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $privateKeyResource = openssl_pkey_new($config);

        if (! ($privateKeyResource instanceof OpenSSLAsymmetricKey)) {
            throw new RuntimeException('Private key generation failed: '.openssl_error_string());
        }

        $csr = openssl_csr_new($dn, $privateKeyResource);

        if (! ($csr instanceof OpenSSLCertificateSigningRequest)) {
            throw new RuntimeException('CSR generation failed: '.openssl_error_string());
        }

        $cert = openssl_csr_sign($csr, null, $privateKeyResource, $daysValid);

        if (! ($cert instanceof OpenSSLCertificate)) {
            throw new RuntimeException('Certificate signing failed: '.openssl_error_string());
        }

        openssl_pkey_export($privateKeyResource, $privateKeyPem, $passphrase);
        openssl_x509_export($cert, $certPem);

        return new CertificateKeyPair($certPem, $privateKeyPem);
    }

    public function encryptPrivateKey(string $privateKey): string
    {
        return Crypt::encrypt($privateKey);
    }

    public function decryptPrivateKey(string $cipher): string
    {
        return Crypt::decrypt($cipher);
    }
}
