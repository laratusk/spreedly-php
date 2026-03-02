<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laratusk\Spreedly\Laravel\Facades\Spreedly;
use Laratusk\Spreedly\Laravel\Facades\SpreedlyCertificateManager;
use Laratusk\Spreedly\Services\CertificateManager;
use Laratusk\Spreedly\Support\MacAddress;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $token
 * @property string|null $pem
 * @property string|null $private_key
 * @property string|null $public_key
 * @property string|null $public_key_hash
 * @property string|null $mac_address
 * @property \Illuminate\Support\Carbon $uploaded_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $uploaded_to_spreedly
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder<static>|SpreedlyCertificate newModelQuery()
 * @method static Builder<static>|SpreedlyCertificate newQuery()
 * @method static Builder<static>|SpreedlyCertificate query()
 * @method static Builder<static>|SpreedlyCertificate expiring(int $days = 7)
 * @method static Builder<static>|SpreedlyCertificate mac()
 */
class SpreedlyCertificate extends Model
{
    const EXPIRING_DAYS = 7;

    protected $table = 'spreedly_certificates';

    protected $fillable = [
        'name',
        'token',
        'pem',
        'private_key',
        'public_key',
        'public_key_hash',
        'mac_address',
        'uploaded_at',
        'expires_at',
        'uploaded_to_spreedly',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'expires_at' => 'datetime',
        'uploaded_to_spreedly' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected $hidden = ['pem', 'public_key', 'public_key_hash', 'private_key'];

    protected static function booted(): void
    {
        static::creating(function (self $certificate): void {
            if (empty($certificate->expires_at)) {
                $certificate->expires_at = Carbon::now()->addDays(CertificateManager::DAYS_VALID);
            }

            if (! empty($certificate->private_key)) {
                $certificate->private_key = SpreedlyCertificateManager::encryptPrivateKey($certificate->private_key);
            }

            if (config('spreedly.mac_address_enabled')) {
                $certificate->mac_address = MacAddress::get();
            }
        });
    }

    /**
     * Retrieve the default certificate, honoring MAC-address-based selection when enabled.
     *
     * If mac_address_enabled is on, the certificate bound to the current machine is
     * returned. Falls back to the global default when no machine-specific record exists.
     */
    public static function current(): self
    {
        /** @var self $default */
        $default = self::query()->where('is_default', true)->first();

        if (! config('spreedly.mac_address_enabled')) {
            return $default;
        }

        $macAddress = MacAddress::get();

        if (! $macAddress) {
            return $default;
        }

        return self::query()->where('mac_address', $macAddress)->first() ?? $default;
    }

    public function uploadedToSpreedly(): bool
    {
        return $this->uploaded_to_spreedly;
    }

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function getPem(): string
    {
        return (string) $this->pem;
    }

    /**
     * Return the decrypted private key PEM.
     */
    public function getPrivateKey(): string
    {
        return SpreedlyCertificateManager::decryptPrivateKey((string) $this->private_key);
    }

    public function getPublicKey(): string
    {
        return (string) $this->public_key;
    }

    public function getPublicKeyHash(): string
    {
        return (string) $this->public_key_hash;
    }

    public function getToken(): string
    {
        return (string) $this->token;
    }

    public function getMacAddress(): ?string
    {
        return $this->mac_address;
    }

    public function isExpiring(): bool
    {
        return $this->expires_at < now()->adddays(self::EXPIRING_DAYS);
    }

    /**
     * Scope a query to only include expiring certificates.
     *
     * @param  Builder<SpreedlyCertificate>  $query
     * @return Builder<SpreedlyCertificate>
     */
    public function scopeExpiring(Builder $query, int $days = self::EXPIRING_DAYS): Builder
    {
        return $query->where('expires_at', '<', now()->addDays($days));
    }

    /**
     * Scope a query to only include this machine address certificates.
     *
     * @param  Builder<SpreedlyCertificate>  $query
     * @return Builder<SpreedlyCertificate>
     */
    public function scopeMac(Builder $query, int $days = 7): Builder
    {
        return $query->where('mac_address', '=', MacAddress::get());
    }

    public static function createNewCertificate(?string $name = null): self
    {
        $keyPair = SpreedlyCertificateManager::createCertificateKeyPair(
            $name ?? CertificateManager::COMMON_NAME,
            CertificateManager::DAYS_VALID,
            CertificateManager::KEY_BITS,
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
            'uploaded_at' => now(),
            'expires_at' => $spreedlyCert->expiresAt ?? now()->addYears(1),
            'uploaded_to_spreedly' => true,
        ]);
    }
}
