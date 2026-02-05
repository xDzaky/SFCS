<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Log;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settingsCollection = Setting::all()->keyBy('key');
        
        // Convert to simple key-value array for easier access in views
        $settings = [];
        foreach ($settingsCollection as $key => $setting) {
            $settings[$key] = $setting->value;
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        
        // Save each setting
        foreach ($settings as $key => $value) {
            if ($value !== null) {
                Setting::setValue($key, $value);
            }
        }
        
        // Handle logo upload if present
        if ($request->hasFile('school_logo')) {
            $request->validate([
                'school_logo' => 'image|mimes:png,jpg,jpeg|max:2048',
            ]);
            
            $path = $request->file('school_logo')->store('settings', 'public');
            Setting::setValue('school_logo', $path, 'Path logo sekolah');
        }

        // Note: Log creation removed as it requires pengaduan_id which doesn't apply to settings

        return back()->with('success', 'Pengaturan berhasil disimpan');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg|max:1024',
        ]);

        $path = $request->file('logo')->store('settings', 'public');

        Setting::setValue('logo_path', $path, 'Path logo aplikasi');

        return back()->with('success', 'Logo berhasil diunggah');
    }
}
