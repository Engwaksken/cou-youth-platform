<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
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
        return view('admin.settings.index', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'system_name' => 'nullable|string|max:180',
            'logo' => 'nullable|file|image|max:2048',
            'favicon' => 'nullable|file|max:1024',
            'sms_username' => 'nullable|string|max:120',
            'sms_api_key' => 'nullable|string|max:255',
            'sms_sender' => 'nullable|string|max:50',
            'mail_host' => 'nullable|string|max:120',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string|max:120',
            'mail_password' => 'nullable|string|max:255',
        ]);
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            SiteSetting::set('logo', $path);
        }
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('branding', 'public');
            SiteSetting::set('favicon', $path);
        }
        foreach (['system_name', 'sms_username', 'sms_api_key', 'sms_sender', 'mail_host', 'mail_port', 'mail_username', 'mail_password'] as $k) {
            if (array_key_exists($k, $data)) SiteSetting::set($k, $data[$k]);
        }
        return back()->with('success', 'Settings saved.');
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
