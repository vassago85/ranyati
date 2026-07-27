<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Runtime resolver for the private Cloudflare R2 bucket that stores
 * custody photos and firearm licences.
 *
 * The disk isn't declared in config/filesystems.php on purpose: the R2
 * credentials live in the `settings` table (encrypted secret_key) so that
 * ops staff can rotate them from the admin panel without an app deploy.
 * When settings are empty this class falls back to R2_* env vars so a
 * fresh install can be primed via .env.
 *
 * All uploads go through here. Callers must use ->disk() rather than
 * Storage::disk('custody'), because there is no static "custody" disk
 * unless the settings/env are populated.
 */
class CustodyDisk
{
    /**
     * Setting keys used by the storage module. Kept as constants so the
     * settings view, the controller, and this resolver stay in sync.
     */
    public const KEY_ACCOUNT_ID = 'r2.account_id';
    public const KEY_ACCESS_KEY = 'r2.access_key';
    public const KEY_SECRET_KEY = 'r2.secret_key';
    public const KEY_BUCKET     = 'r2.bucket';
    public const KEY_ENDPOINT   = 'r2.endpoint';

    /**
     * Build a Flysystem-backed disk pointing at the configured R2 bucket.
     * Throws when credentials are missing rather than silently returning
     * a broken filesystem — every caller (uploads, presigning) needs to
     * surface a helpful "R2 not configured" error to the operator.
     */
    public static function disk(): Filesystem
    {
        $config = self::config();

        foreach (['key', 'secret', 'bucket', 'endpoint'] as $required) {
            if (empty($config[$required])) {
                throw new RuntimeException(
                    'Custody storage (Cloudflare R2) is not configured. Set the missing "'.$required.'" value on the Storage settings page.'
                );
            }
        }

        return Storage::build([
            'driver'                  => 's3',
            'key'                     => $config['key'],
            'secret'                  => $config['secret'],
            'region'                  => 'auto',
            'bucket'                  => $config['bucket'],
            'endpoint'                => $config['endpoint'],
            'use_path_style_endpoint' => true,
            'throw'                   => true,
            'visibility'              => 'private',
        ]);
    }

    /**
     * Read-only view of the current R2 configuration. Returns raw values
     * with the secret decrypted; used by disk() and the test-connection
     * endpoint. Callers responsible for not logging the secret.
     *
     * @return array{account_id: ?string, key: ?string, secret: ?string, bucket: ?string, endpoint: ?string}
     */
    public static function config(): array
    {
        $accountId = self::setting(self::KEY_ACCOUNT_ID, env('R2_ACCOUNT_ID'));
        $key       = self::setting(self::KEY_ACCESS_KEY, env('R2_ACCESS_KEY'));
        $secretRaw = self::setting(self::KEY_SECRET_KEY, null);
        $bucket    = self::setting(self::KEY_BUCKET,     env('R2_BUCKET'));
        $endpoint  = self::setting(self::KEY_ENDPOINT,   env('R2_ENDPOINT'));

        // Compose endpoint from account ID if the operator only supplied
        // the ID (Cloudflare R2's standard endpoint pattern).
        if (! $endpoint && $accountId) {
            $endpoint = 'https://'.$accountId.'.r2.cloudflarestorage.com';
        }

        $secret = null;
        if ($secretRaw) {
            try {
                $secret = Crypt::decryptString($secretRaw);
            } catch (\Throwable) {
                $secret = null;
            }
        }
        if (! $secret) {
            $secret = env('R2_SECRET_KEY') ?: null;
        }

        return [
            'account_id' => $accountId ?: null,
            'key'        => $key ?: null,
            'secret'     => $secret ?: null,
            'bucket'     => $bucket ?: null,
            'endpoint'   => $endpoint ?: null,
        ];
    }

    /**
     * Whether the disk has enough config to attempt an upload/read.
     */
    public static function isConfigured(): bool
    {
        $c = self::config();

        return $c['key'] && $c['secret'] && $c['bucket'] && $c['endpoint'];
    }

    /**
     * Encrypt-then-persist the R2 secret. Blank input is a no-op so the
     * settings form can be re-saved without wiping the stored secret.
     */
    public static function persistSecret(?string $plaintext): void
    {
        if ($plaintext === null || $plaintext === '') {
            return;
        }

        Setting::set(self::KEY_SECRET_KEY, Crypt::encryptString($plaintext), 'storage');
    }

    /**
     * Persist the non-secret R2 fields, treating "" as "leave unchanged"
     * only for the secret. All other keys accept a blank to clear.
     */
    public static function persistNonSecret(array $input): void
    {
        $map = [
            self::KEY_ACCOUNT_ID => $input['account_id'] ?? null,
            self::KEY_ACCESS_KEY => $input['access_key'] ?? null,
            self::KEY_BUCKET     => $input['bucket'] ?? null,
            self::KEY_ENDPOINT   => $input['endpoint'] ?? null,
        ];

        foreach ($map as $key => $value) {
            Setting::set($key, $value !== null ? trim((string) $value) : '', 'storage');
        }
    }

    /**
     * Round-trip a tiny put/delete to confirm credentials + bucket work.
     * Returns human-readable success/failure for the settings page.
     *
     * @return array{ok: bool, message: string}
     */
    public static function testConnection(): array
    {
        try {
            $disk = self::disk();
            $probe = 'connection-test/'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.txt';
            $disk->put($probe, 'Ranyati Storage connection test — '.now()->toIso8601String());

            if (! $disk->exists($probe)) {
                return ['ok' => false, 'message' => 'Wrote a test object but could not read it back.'];
            }

            $disk->delete($probe);

            return ['ok' => true, 'message' => 'Success — R2 credentials and bucket are working.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Standard object path for a custody file. Keeps everything nicely
     * grouped in the bucket for operators poking around outside the app.
     */
    public static function pathFor(string $bookCode, int $itemId, string $kind, string $extension): string
    {
        return sprintf(
            'custody/%s/%d/%s-%s.%s',
            trim($bookCode) ?: 'unknown',
            $itemId,
            $kind,
            (string) \Illuminate\Support\Str::uuid(),
            ltrim($extension, '.'),
        );
    }

    /**
     * Read a setting without letting a totally missing settings table
     * (fresh clone, migrations pending) crash the whole request.
     */
    private static function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}
