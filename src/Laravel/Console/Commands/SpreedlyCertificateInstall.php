<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laratusk\Spreedly\Laravel\Models\SpreedlyCertificate;

class SpreedlyCertificateInstall extends Command
{
    protected $signature = 'spreedly:certificate-install
                            {--force : Renew certificates even if they are not expiring}';

    protected $description = 'Renew Spreedly certificates approaching expiration or force renew all';

    public function handle(): int
    {
        $this->info('Starting Spreedly certificates renewal process...');

        $allCertificates = SpreedlyCertificate::query()->mac()->get();

        $query = SpreedlyCertificate::query()->mac();

        if (! $this->option('force')) {
            $query->expiring();
        }

        $certificates = $query->get();

        /**
         * If no certificates exist at all → create a fresh one
         */
        if ($allCertificates->isEmpty()) {
            $this->warn('No certificates found. Creating a new one...');

            try {
                SpreedlyCertificate::createNewCertificate();

                $this->info('New Spreedly certificate successfully created.');

                return self::SUCCESS;
            } catch (Exception $e) {
                $this->error('Failed to create initial certificate: '.$e->getMessage());
                Log::error('Initial Spreedly certificate creation failed', [
                    'error' => $e->getMessage(),
                ]);

                return self::FAILURE;
            }
        }

        if (! $this->option('force')) {
            $certificates = $allCertificates->filter(fn (SpreedlyCertificate $c) => $c->isExpiring());
            if ($certificates->isEmpty()) {
                $this->info('No expiring certificates found.');
                return self::SUCCESS;
            }
        }

        $renewedCount = 0;
        $failedCount = 0;

        foreach ($certificates as $certificate) {
            $this->info("Attempting to renew certificate: $certificate->name ($certificate->token)");

            try {
                $newCertificate = SpreedlyCertificate::createNewCertificate($certificate->name);

                $certificate->delete();

                $this->info(
                    "Renewed successfully. Name: $certificate->name, Old: $certificate->token, New: $newCertificate->token, ".
                    'Expires: '.($newCertificate->expires_at?->toIso8601String() ?? 'N/A')
                );

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
