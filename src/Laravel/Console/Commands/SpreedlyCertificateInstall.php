<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laratusk\Spreedly\Laravel\Actions\CreateSpreedlyCertificate;
use Laratusk\Spreedly\Laravel\Models\SpreedlyCertificate;

final class SpreedlyCertificateInstall extends Command
{
    protected $signature = 'spreedly:certificate-install
                            {--force : Replace the certificate immediately without checking expiry}';

    protected $description = 'Renew the Spreedly certificate for this machine when expiring, or force-replace it';

    public function handle(): int
    {
        $this->info('Starting Spreedly certificate renewal process...');

        $certificate = SpreedlyCertificate::query()->forCurrentMac()->latest()->first();

        if ($certificate === null) {
            $this->warn('No certificate found. Creating a new one...');

            return $this->createCertificate();
        }

        if (! $this->option('force') && ! $certificate->isExpiring()) {
            $this->info(
                'Certificate is valid, no renewal needed. Expires: '.
                ($certificate->expires_at?->toIso8601String() ?? 'N/A')
            );

            return self::SUCCESS;
        }

        $this->info("Renewing certificate: {$certificate->name} ({$certificate->token})");

        return $this->renewCertificate($certificate);
    }

    private function createCertificate(): int
    {
        try {
            (new CreateSpreedlyCertificate)->execute();

            $this->info('New Spreedly certificate successfully created.');

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to create certificate: '.$e->getMessage());

            Log::error('Spreedly certificate creation failed', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }

    private function renewCertificate(SpreedlyCertificate $old): int
    {
        try {
            $new = (new CreateSpreedlyCertificate)->execute($old->name);
        } catch (Exception $e) {
            $this->error("Failed to renew certificate {$old->token}: {$e->getMessage()}");

            Log::error('Spreedly certificate renewal failed', [
                'token' => $old->token,
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }

        // New certificate is live — remove the old record.
        $old->delete();

        $this->info(
            "Certificate renewed successfully. Old: {$old->token}, New: {$new->token}, ".
            'Expires: '.($new->expires_at?->toIso8601String() ?? 'N/A')
        );

        Log::info('Spreedly certificate successfully renewed', [
            'old_token' => $old->token,
            'new_token' => $new->token,
            'new_expires_at' => $new->expires_at?->toIso8601String(),
        ]);

        return self::SUCCESS;
    }
}
