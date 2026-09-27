<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Show the application-wide alert threshold settings.
     */
    public function edit(): Response
    {
        Gate::authorize('view', AppSetting::class);

        return Inertia::render('Settings/Edit', [
            'settings' => AppSetting::current(),
        ]);
    }

    /**
     * Update the alert threshold settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $setting = AppSetting::current();

        Gate::authorize('update', $setting);

        $data = $request->validate([
            'warning_days' => ['nullable', 'integer', 'min:0'],
            'attention_days' => ['nullable', 'integer', 'min:0'],
            'first_contact_days' => ['nullable', 'integer', 'min:0'],
            'visit_days' => ['nullable', 'integer', 'min:0'],
        ]);

        $setting->update($data);
        AppSetting::forgetCached();

        return Redirect::route('settings.edit')->with('status', 'Налаштування оновлено.');
    }
}
