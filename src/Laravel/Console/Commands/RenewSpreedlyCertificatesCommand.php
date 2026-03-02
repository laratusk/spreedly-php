<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laratusk\Spreedly\Laravel\Facades\Spreedly;
use Laratusk\Spreedly\Laravel\Facades\SpreedlyCertificateManager;
use Laratusk\Spreedly\Laravel\Models\SpreedlyCertificate;
use Laratusk\Spreedly\Services\CertificateManager;

class RenewSpreedlyCertificatesCommand extends Command
{
    protected $signature = 'spreedly:renew-certificates';

    protected $description = 'Renew Spreedly certificates approaching expiration';

    public function handle(): int
    {
        $this->info('Starting Spreedly certificates renewal process...');

        $certificates = SpreedlyCertificate::expiring()->get();

        if ($certificates->isEmpty()) {
            $this->info('No expiring certificates found.');

            return self::SUCCESS;
        }

        $renewedCount = 0;
        $failedCount = 0;

        foreach ($certificates as $certificate) {
            $this->info("Attempting to renew certificate: $certificate->name ($certificate->token)");

            try {
                $keyPair = SpreedlyCertificateManager::createCertificateKeyPair(
                    $certificate->name ?? CertificateManager::COMMON_NAME,
                    CertificateManager::DAYS_VALID,
                    CertificateManager::KEY_BITS,
                );

                $spreedlyCert = Spreedly::certificates()->create([
                    'pem' => $keyPair->pem,
                    'private_key' => $keyPair->privateKey,
                ]);

                $newCertificate = SpreedlyCertificate::create([
                    'name' => $certificate->name,
                    'gateway' => $certificate->gateway,
                    'token' => $spreedlyCert->token,
                    'environment' => $certificate->environment,
                    'pem' => $keyPair->pem,
                    'private_key' => $keyPair->privateKey,
                    'uploaded_at' => now(),
                    'expires_at' => $spreedlyCert->expiresAt ?? now()->addYears(1),
                    'uploaded_to_spreedly' => true,
                    'is_default' => $certificate->is_default,
                ]);

                $certificate->delete();

                $this->info("Successfully renewed certificate. Name: $newCertificate->name, Gateway: $newCertificate->gateway, Old token: $certificate->token, New token: $newCertificate->token, Expires at: ".($newCertificate->expires_at?->toIso8601String() ?? 'N/A'));
                Log::info('Spreedly certificate successfully renewed', [
                    'old_token' => $certificate->token,
                    'new_token' => $newCertificate->token,
                    'new_expires_at' => $newCertificate->expires_at?->toIso8601String(),
                ]);
                $renewedCount++;

            } catch (Exception $e) {
                $this->error("Failed to renew certificate $certificate->token: {$e->getMessage()}");
                Log::error('Spreedly certificate renewal failed', [
                    'token' => $certificate->token,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $this->info("Renewal complete. Renewed: $renewedCount. Failed: $failedCount.");

        return $failedCount === 0 ? self::SUCCESS : self::FAILURE;
    }
}
