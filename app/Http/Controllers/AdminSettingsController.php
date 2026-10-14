<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminSettingsRequest;
use App\Models\SiteSetting;
use App\Support\SchoolSettingFields;
use Illuminate\Database\Schema\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        if (! Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
            });
        }

        $settings = SiteSetting::pluck('value', 'key')->all();

        return view('admin.settings.index', [
            'fields' => SchoolSettingFields::ALL,
            'settings' => $settings,
        ]);
    }

    public function update(AdminSettingsRequest $request): RedirectResponse
    {
        if (! Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
            });
        }

        $data = $request->validated();

        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                SiteSetting::where('key', $key)->delete();
                continue;
            }

            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value]
            );
        }

        return redirect()->route('admin.settings.index')->with('status', 'Pengaturan sekolah berhasil disimpan.');
    }
}
