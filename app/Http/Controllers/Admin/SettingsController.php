<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /** Plain text fields, grouped so new settings land in the right group. */
    protected array $fields = [
        'general' => [
            'site_name', 'site_tagline', 'site_email', 'site_address',
            'about_text', 'nav_cta_text', 'nav_cta_url', 'posts_per_page',
        ],
        'social' => [
            'facebook_url', 'twitter_url', 'linkedin_url',
            'vk_url', 'youtube_url', 'instagram_url',
        ],
        'seo' => [
            'meta_title', 'meta_description', 'google_analytics',
        ],
    ];

    public function save(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $request->validate([
            'site_name'        => ['required', 'string', 'max:120'],
            'site_email'       => ['nullable', 'email', 'max:120'],
            'posts_per_page'   => ['nullable', 'integer', 'min:3', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'site_logo'        => ['nullable', 'image', 'max:2048'],
            'site_favicon'     => ['nullable', 'image', 'max:512'],
        ]);

        foreach ($this->fields as $group => $keys) {
            foreach ($keys as $key) {
                Setting::put($key, (string) $request->input($key, ''));
            }
        }

        foreach (['site_logo', 'site_favicon'] as $key) {
            // "Remove" wins over a new upload, so an accidental double action
            // clears the image rather than silently keeping the old one.
            if ($request->boolean('remove_'.$key)) {
                $this->deleteStoredImage(Setting::get($key));
                Setting::put($key, '');
                continue;
            }

            if ($request->hasFile($key)) {
                $this->deleteStoredImage(Setting::get($key));
                Setting::put($key, $request->file($key)->store('branding', 'public'));
            }
        }

        Cache::forget('footer.recent');

        ActivityLog::record('settings_saved');

        return back()->with('settings_saved', true);
    }

    /** Only delete files we put on the storage disk, never a pasted URL. */
    protected function deleteStoredImage(?string $path): void
    {
        if (filled($path) && ! str_starts_with($path, 'http') && ! str_starts_with($path, '/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
