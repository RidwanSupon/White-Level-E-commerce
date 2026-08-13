<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index(SettingService $settingService)
    {
        $settings = $settingService->all();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request, SettingService $settingService)
    {
        $data = $request->except(['_token', '_method', 'site_logo_file']);

        // Handle Site Logo File Upload
        if ($request->hasFile('site_logo_file')) {
            $request->validate([
                'site_logo_file' => ['image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            ]);

            $file = $request->file('site_logo_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = app(\App\Services\StorageService::class)->upload($file, 'branding', $filename);
            
            if ($path) {
                $settingService->set('site_logo', $path);
            }
        }

        foreach ($data as $key => $value) {
            if ($value !== null) {
                $settingService->set($key, $value);
            }
        }

        Cache::forget('app_white_label_settings');

        return back()->with('success', 'White-Label branding, logo, and color settings updated successfully!');
    }
}
