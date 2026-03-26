<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Laratusk\Spreedly\Laravel\Actions\CreateSpreedlyCertificate;
use Laratusk\Spreedly\Laravel\Facades\SpreedlyCertificateManager;
use Laratusk\Spreedly\Laravel\Services\CertificateManager;
use Laratusk\Spreedly\Laravel\Support\MacAddress;
use RuntimeException;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $token
 * @property string|null $pem
 * @property string|null $private_key
 * @property string|null $public_key
 * @property string|null $public_key_hash
 * @property string|null $mac_address
 * @property Carbon $uploaded_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|SpreedlyCertificate newModelQuery()
 * @method static Builder<static>|SpreedlyCertificate newQuery()
 * @method static Builder<static>|SpreedlyCertificate query()
 * @method static Builder<static>|SpreedlyCertificate expiring(?int $days = null)
 * @method static Builder<static>|SpreedlyCertificate forCurrentMac()
 */
final class SpreedlyCertificate extends Model
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
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = ['pem', 'public_key', 'public_key_hash', 'private_key'];

    protected static function booted(): void
    {
        self::creating(function (self $certificate): void {
            if (empty($certificate->expires_at)) {
                $certificate->expires_at = now()->addDays(
                    (int) Config::get('spreedly.certificate_days_valid', CertificateManager::DAYS_VALID)
                );
            }

            if (! empty($certificate->private_key)) {
                $certificate->private_key = SpreedlyCertificateManager::encryptPrivateKey($certificate->private_key);
            }

            $certificate->mac_address = MacAddress::get();
        });
    }

    /**
     * Retrieve the default certificate, honoring MAC-address-based selection when enabled.
     *
     * The certificate bound to the current machine is
     * returned. Falls back to the global default when no machine-specific record exists.
     */
    public static function current(): self
    {
        $macAddress = MacAddress::get();

        if (! $macAddress) {
            throw new RuntimeException('MAC address not found');
        }

        return self::query()->where('mac_address', $macAddress)->first()
            ?? (new CreateSpreedlyCertificate)->execute();
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
        $days = (int) Config::get('spreedly.certificate_expiring_days', self::EXPIRING_DAYS);

        return $this->expires_at < now()->addDays($days);
    }

    /**
     * Scope a query to only include expiring certificates.
     *
     * @param  Builder<SpreedlyCertificate>  $query
     * @return Builder<SpreedlyCertificate>
     */
    public function scopeExpiring(Builder $query, ?int $days = null): Builder
    {
        $days ??= (int) Config::get('spreedly.certificate_expiring_days', self::EXPIRING_DAYS);

        return $query->where('expires_at', '<', now()->addDays($days));
    }

    /**
     * Scope a query to only include this machine address certificates.
     *
     * @param  Builder<SpreedlyCertificate>  $query
     * @return Builder<SpreedlyCertificate>
     */
    public function scopeForCurrentMac(Builder $query): Builder
    {
        return $query->where('mac_address', '=', MacAddress::get());
    }
}
