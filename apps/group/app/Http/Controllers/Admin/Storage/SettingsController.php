<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\CustodyDisk;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $config = CustodyDisk::config();

        return view('admin.storage.settings', [
            'accountId'   => $config['account_id'] ?? '',
            'accessKey'   => $config['key'] ?? '',
            'bucket'      => $config['bucket'] ?? '',
            'endpoint'    => $config['endpoint'] ?? '',
            'secretMask'  => $this->maskSecret($config['secret'] ?? ''),
            'defaultRate' => (float) Setting::get('storage.default_rate', 100),
            'configured'  => CustodyDisk::isConfigured(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'r2_account_id' => ['nullable', 'string', 'max:100'],
            'r2_access_key' => ['nullable', 'string', 'max:255'],
            'r2_secret_key' => ['nullable', 'string', 'max:1024'],
            'r2_bucket'     => ['nullable', 'string', 'max:255'],
            'r2_endpoint'   => ['nullable', 'string', 'max:255', 'url'],
            'default_rate'  => ['required', 'numeric', 'min:0', 'max:99999.99'],
        ]);

        CustodyDisk::persistNonSecret([
            'account_id' => $validated['r2_account_id'] ?? '',
            'access_key' => $validated['r2_access_key'] ?? '',
            'bucket'     => $validated['r2_bucket'] ?? '',
            'endpoint'   => $validated['r2_endpoint'] ?? '',
        ]);

        CustodyDisk::persistSecret($validated['r2_secret_key'] ?? null);

        Setting::set('storage.default_rate', number_format((float) $validated['default_rate'], 2, '.', ''), 'storage');

        return redirect()->route('admin.storage.settings')->with('success', 'Storage settings saved.');
    }

    public function testConnection()
    {
        $result = CustodyDisk::testConnection();

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Show only the last 4 chars of a stored secret so operators can see
     * "yes, a key is saved" without ever rendering the plaintext key.
     */
    private function maskSecret(?string $secret): string
    {
        if (! $secret) {
            return '';
        }
        $length = strlen($secret);
        if ($length <= 4) {
            return str_repeat('•', $length);
        }

        return str_repeat('•', max(4, $length - 4)).substr($secret, -4);
    }
}
