<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\Branding\AppearanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function index()
    {
        $settings = [];
        try {
            $settings = SiteSetting::pluck('value', 'key')->toArray();
        } catch (\Throwable $e) {
        }
        return view('admin.settings.index', [
            'settings' => $settings,
            'appearance' => app(AppearanceService::class)->data(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'system_name' => 'nullable|string|max:180',
            'mission' => 'nullable|string|max:5000',
            'vision' => 'nullable|string|max:5000',
            'logo' => 'nullable|file|image|max:2048',
            'favicon' => 'nullable|file|mimes:ico,png,svg,webp|max:1024',
            'sms_username' => 'nullable|string|max:120',
            'sms_api_key' => 'nullable|string|max:255',
            'sms_sender' => 'nullable|string|max:50',
            'mail_host' => 'nullable|string|max:120',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string|max:120',
            'mail_password' => 'nullable|string|max:255',
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sidebar_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sidebar_default' => ['nullable', 'boolean'],
            'heading_font' => ['nullable', Rule::in(array_keys(AppearanceService::FONTS))],
            'body_font' => ['nullable', Rule::in(array_keys(AppearanceService::FONTS))],
            'theme_style' => ['nullable', Rule::in(array_keys(AppearanceService::STYLES))],
            'reset_appearance' => ['nullable', 'boolean'],
        ], [
            '*.regex' => 'Choose a colour from the colour picker.',
        ]);
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            SiteSetting::set('logo', $path);
        }
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('branding', 'public');
            SiteSetting::set('favicon', $path);
        }
        foreach (['system_name', 'mission', 'vision', 'sms_username', 'sms_api_key', 'sms_sender', 'mail_host', 'mail_port', 'mail_username', 'mail_password'] as $k) {
            if (array_key_exists($k, $data)) SiteSetting::set($k, $data[$k]);
        }

        if ($request->boolean('reset_appearance')) {
            foreach (AppearanceService::KEYS as $key) {
                SiteSetting::set($key, null);
            }
        } else {
            foreach (['primary_color', 'secondary_color', 'accent_color', 'background_color', 'heading_font', 'body_font', 'theme_style'] as $key) {
                if (! empty($data[$key])) SiteSetting::set($key, $data[$key]);
            }
            if ($request->has('sidebar_color') || $request->has('sidebar_default')) {
                SiteSetting::set('sidebar_color', $request->boolean('sidebar_default') ? null : ($data['sidebar_color'] ?? null));
            }
        }

        AppearanceService::forget();

        return back()->with('success', $request->boolean('reset_appearance') ? 'Settings saved. Appearance is back to the default look.' : 'Settings saved.');
    }

    public function backup()
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) mkdir($dir, 0755, true);
        $file = 'backup-' . date('Ymd-His') . '.zip';
        $full = $dir . DIRECTORY_SEPARATOR . $file;
        $zip = new \ZipArchive();
        $zip->open($full, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $db = database_path('database.sqlite');
        if (file_exists($db)) $zip->addFile($db, 'database.sqlite');
        foreach (['.env', 'storage/app/public'] as $extra) {
            $p = base_path($extra);
            if (is_file($p)) $zip->addFile($p, $extra);
            elseif (is_dir($p)) {
                $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($p, \RecursiveDirectoryIterator::SKIP_DOTS));
                foreach ($it as $f) $zip->addFile($f->getPathname(), 'public/' . $it->getSubPathName());
            }
        }
        $zip->close();
        return response()->download($full);
    }

    public function backupRemote()
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) mkdir($dir, 0755, true);
        $file = 'backup-' . date('Ymd-His') . '.zip';
        $full = $dir . DIRECTORY_SEPARATOR . $file;
        $zip = new \ZipArchive();
        $zip->open($full, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $db = database_path('database.sqlite');
        if (file_exists($db)) $zip->addFile($db, 'database.sqlite');
        $zip->close();
        try {
            $s3 = config('filesystems.disks.s3.key') ? 's3' : null;
            if ($s3) {
                Storage::disk('s3')->put('backups/' . $file, file_get_contents($full));
                return back()->with('success', 'Backup stored to S3: backups/' . $file);
            }
        } catch (\Throwable $e) {
            return back()->with('success', 'Remote backup failed, kept locally: ' . $file);
        }
        return back()->with('success', 'S3 not configured; backup kept locally: ' . $file);
    }
}
