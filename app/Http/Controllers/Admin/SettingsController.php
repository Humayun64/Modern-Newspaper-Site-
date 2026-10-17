<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'about_text', 'nav_cta_text', 'nav_cta_url',
            'nav_latest_text', 'nav_latest_url', 'posts_per_page',
        ],
        'editorial' => [
            'editor_name', 'editor_role', 'editor_bio',
        ],
        'widgets' => [
            'dse_index', 'dse_change', 'dse_as_of', 'stock_embed',
            'weather_enabled', 'weather_city', 'weather_lat', 'weather_lon',
        ],
        'appearance' => [
            'color_accent', 'color_accent_dk', 'color_header_bg', 'color_nav_bg',
        ],
        'social' => [
            'facebook_url', 'twitter_url', 'linkedin_url',
            'vk_url', 'youtube_url', 'instagram_url',
        ],
        'seo' => [
            'meta_title', 'meta_description', 'google_analytics',
        ],
    ];

    /** Appearance values go straight into a CSS variable, so they are checked. */
    protected array $colourKeys = [
        'color_accent', 'color_accent_dk', 'color_header_bg', 'color_nav_bg',
    ];

    public function save(Request $request)
    {
        // #rgb, #rrggbb or #rrggbbaa. Anything else would be written into a
        // style attribute unescaped, so it is rejected rather than cleaned.
        $hex = ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'];

        $request->validate([
            'site_name'        => ['required', 'string', 'max:120'],
            'site_email'       => ['nullable', 'email', 'max:120'],
            'posts_per_page'   => ['nullable', 'integer', 'min:3', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'site_logo'        => ['nullable', 'image', 'max:2048'],
            'site_favicon'     => ['nullable', 'image', 'max:512'],
            'editor_photo'     => ['nullable', 'image', 'max:2048'],
            'editor_name'      => ['nullable', 'string', 'max:120'],
            'editor_role'      => ['nullable', 'string', 'max:120'],
            'editor_bio'       => ['nullable', 'string', 'max:400'],
            // Typed in by hand, so it is checked: "5,432.10" and "-0.82".
            'dse_index'        => ['nullable', 'regex:/^[0-9][0-9,]*(\\.[0-9]{1,2})?$/'],
            'dse_change'       => ['nullable', 'regex:/^[+-]?[0-9]+(\\.[0-9]{1,2})?$/'],
            'dse_as_of'        => ['nullable', 'string', 'max:60'],
            // Pasted provider markup. Admin-only, and printed unescaped, so
            // the length is capped and a stray <script src> is all it can be.
            'stock_embed'      => ['nullable', 'string', 'max:4000'],
            'weather_city'     => ['nullable', 'string', 'max:60'],
            'weather_lat'      => ['nullable', 'numeric', 'between:-90,90'],
            'weather_lon'      => ['nullable', 'numeric', 'between:-180,180'],
            'color_accent'     => $hex,
            'color_accent_dk'  => $hex,
            'color_header_bg'  => $hex,
            'color_nav_bg'     => $hex,
        ], [
            'color_accent.regex'    => 'The accent colour must be a hex value such as #d32b2b.',
            'color_accent_dk.regex' => 'The accent hover colour must be a hex value such as #a81f1f.',
            'color_header_bg.regex' => 'The masthead colour must be a hex value such as #1c1d20.',
            'color_nav_bg.regex'    => 'The menu bar colour must be a hex value such as #26272b.',
            'dse_index.regex'       => 'The stock index must be a number, for example 5,432.10.',
            'dse_change.regex'      => 'The change must be a number with a sign, for example -0.82 or 1.24.',
        ]);

        foreach ($this->fields as $group => $keys) {
            foreach ($keys as $key) {
                $value = (string) $request->input($key, '');

                // A colour picker always posts something, so an unused slot
                // would silently lock to #000000. Blank means "use the theme
                // default", and the checkbox beside each picker sends that.
                if (in_array($key, $this->colourKeys, true) && $request->boolean('reset_'.$key)) {
                    $value = '';
                }

                Setting::put($key, $value);
            }
        }

        foreach (['site_logo', 'site_favicon', 'editor_photo'] as $key) {
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

        // The public site caches its section tree and ticker; branding changes
        // should show immediately rather than after the cache expires.
        Cache::forget('home.sections');
        Cache::forget('footer.recent');
        Cache::forget('topbar.weather');

        // The weather cache key carries the coordinates, so moving the pin
        // has to clear whatever was cached for the old ones.
        foreach (Cache::get('widget.weather.keys', []) as $key) {
            Cache::forget($key);
        }

        Cache::forget('widget.weather.'
            .(float) $request->input('weather_lat', 23.8103).','
            .(float) $request->input('weather_lon', 90.4125));

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
