<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\BillingSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = BillingSetting::all()->groupBy('group');
        
        return view('billing::admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token']);

        foreach ($data as $key => $value) {
            $setting = BillingSetting::where('key', $key)->first();
            
            if ($setting) {
                // Special handling for boolean checkboxes which might not be sent if unchecked
                // But here we are iterating over sent data. 
                // For checkboxes, we usually send a hidden input or handle it in frontend.
                // Let's assume the frontend sends the value correctly.
                
                // If it's a password field and empty, skip updating (don't overwrite with empty)
                if ($setting->is_encrypted && empty($value)) {
                    continue;
                }

                $setting->update(['value' => $value]);
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function testVennConnection()
    {
        try {
            $key = BillingSetting::where('key', 'venn_key')->first()->value;
            $url = config('services.venn.url', 'https://api.venn.com/v1');
            
            if (empty($key)) {
                return response()->json(['success' => false, 'message' => 'Venn API Key is not configured.']);
            }

            $response = Http::withToken($key)->get("{$url}/ping");

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Connection successful!']);
            }

            return response()->json(['success' => false, 'message' => 'Connection failed: ' . $response->status()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        }
    }
}
