<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingManagementController extends Controller
{
    public function index()
    {
        $dbSettings = Setting::pluck('value', 'key')->toArray();

        $defaults = [
            'business_name' => 'North Fork Auto',
            'phone' => '(907) 733-3030',
            'after_hours_phone' => '+1 (907) 232-3859',
            'email' => 'titussr84@yahoo.com',
            'domain' => 'www.northforkauto.com',
            'address' => 'Talkeetna Spur Road, Talkeetna, AK 99676',
            'hours' => 'Mon – Fri: 8:00 AM – 5:00 PM',
            'footer_credit' => 'Designed by Azora Solution · www.azorasolution.com',
            'meta_description' => 'Professional, honest automotive care for drivers across Talkeetna and the Mat-Su Valley.',
        ];

        $settings = array_merge($defaults, $dbSettings);

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $allowed = [
            'business_name',
            'phone',
            'after_hours_phone',
            'email',
            'domain',
            'address',
            'hours',
            'footer_credit',
            'meta_description',
        ];

        foreach ($request->only($allowed) as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'general']
            );
        }

        return back()->with('success', 'Shop settings updated successfully.');
    }
}
