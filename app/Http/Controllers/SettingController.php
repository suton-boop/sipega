<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Update a system setting via POST (Fast Update)
     */
    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable|string'
        ]);

        Setting::set($request->key, $request->value);

        return back()->with('success', "Pengaturan '{$request->key}' berhasil diperbarui.");
    }
}
