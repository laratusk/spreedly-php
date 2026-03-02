<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

final readonly class CertificateKeyPair
{
    public function __construct(
        public string $pem,
        public string $privateKey,
    ) {}
}
