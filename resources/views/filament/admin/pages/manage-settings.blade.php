@php
    /** Resolve a stored image path to something the browser can load. */
    $imgSrc = function (?string $path) {
        if (blank($path)) return null;
        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
            ? $path
            : asset('storage/'.$path);
    };

    /** Colour pickers cannot hold an empty value, so an unset one shows the theme default. */
    $colours = [
        'color_header_bg' => ['Masthead and footer', '#1c1d20', 'The dark band behind the logo, the date bar and the footer.'],
        'color_nav_bg'    => ['Menu bar',            '#26272b', 'The bar holding the main navigation.'],
        'color_accent'    => ['Accent',              '#d32b2b', 'Breaking label, category badges, the Latest Video button.'],
        'color_accent_dk' => ['Accent (hover)',      '#a81f1f', 'The darker shade used when the accent is hovered.'],
    ];
@endphp

<x-filament-panels::page>

    @if (session('settings_saved'))
        <div style="padding:12px 16px;border-radius:8px;background:#dcfce7;color:#166534;font-weight:600">
            Settings saved.
        </div>
    @endif

    @if ($errors->any())
        <div style="padding:12px 16px;border-radius:8px;background:#fee2e2;color:#991b1b">
            <strong>Check these fields:</strong>
            <ul style="margin:6px 0 0;padding-left:18px">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.save') }}" enctype="multipart/form-data"
          style="display:grid;gap:1.5rem">
        @csrf

        {{-- ---------------- branding ---------------- --}}
        <x-filament::section>
            <x-slot name="heading">Branding</x-slot>
            <x-slot name="description">Logo, favicon and the name shown in the masthead.</x-slot>

            <div style="display:grid;gap:1rem">
                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Site name</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" name="site_name" required
                                           value="{{ old('site_name', $settings['site_name'] ?? '') }}" />
                    </x-filament::input.wrapper>
                </label>

                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Tagline</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" name="site_tagline"
                                           value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}" />
                    </x-filament::input.wrapper>
                </label>

                @foreach (['site_logo' => 'Logo', 'site_favicon' => 'Favicon'] as $key => $label)
                    <div style="display:grid;gap:.5rem">
                        <span style="font-weight:600;font-size:.875rem">{{ $label }}</span>

                        @if ($src = $imgSrc($settings[$key] ?? null))
                            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                                <img src="{{ $src }}" alt="{{ $label }}"
                                     style="max-height:{{ $key === 'site_favicon' ? '32' : '56' }}px;background:#1c1d20;padding:6px;border-radius:6px">
                                <label style="display:inline-flex;align-items:center;gap:6px;font-size:.85rem">
                                    <input type="checkbox" name="remove_{{ $key }}" value="1"> Remove
                                </label>
                            </div>
                        @endif

                        <input type="file" name="{{ $key }}" accept="image/*" style="font-size:.85rem">
                        <span style="font-size:.78rem;opacity:.65">
                            {{ $key === 'site_logo'
                                ? 'PNG or SVG with a transparent background works best. Leave empty to show the site name as text.'
                                : 'A square image, 32×32 or larger.' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- ---------------- appearance ---------------- --}}
        <x-filament::section>
            <x-slot name="heading">Appearance</x-slot>
            <x-slot name="description">
                Bar colours for the public site. Tick "Use default" to go back to the theme colour.
            </x-slot>

            <div style="display:grid;gap:1.25rem;grid-template-columns:repeat(auto-fit,minmax(260px,1fr))">
                @foreach ($colours as $key => [$label, $default, $help])
                    @php $current = old($key, $settings[$key] ?? ''); @endphp
                    <div style="display:grid;gap:.45rem">
                        <span style="font-weight:600;font-size:.875rem">{{ $label }}</span>

                        <div style="display:flex;align-items:center;gap:10px">
                            <input type="color" name="{{ $key }}"
                                   value="{{ $current ?: $default }}"
                                   style="width:46px;height:34px;padding:0;border:1px solid rgba(128,128,128,.35);border-radius:.4rem;background:transparent;cursor:pointer">

                            <code style="font-size:.8rem;opacity:.75">{{ $current ?: $default.' (default)' }}</code>
                        </div>

                        <label style="display:inline-flex;align-items:center;gap:6px;font-size:.8rem">
                            <input type="checkbox" name="reset_{{ $key }}" value="1"> Use default
                        </label>

                        <span style="font-size:.78rem;opacity:.65">{{ $help }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- ---------------- editorial ---------------- --}}
        <x-filament::section>
            <x-slot name="heading">Editorial</x-slot>
            <x-slot name="description">
                The "সম্পাদকীয়" card in the sidebar. Leave the name empty to fall back to the admin account.
            </x-slot>

            <div style="display:grid;gap:1rem">
                <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">Name</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="editor_name"
                                               value="{{ old('editor_name', $settings['editor_name'] ?? '') }}" />
                        </x-filament::input.wrapper>
                    </label>

                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">Role</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="editor_role" placeholder="সম্পাদক ও প্রকাশক"
                                               value="{{ old('editor_role', $settings['editor_role'] ?? '') }}" />
                        </x-filament::input.wrapper>
                    </label>
                </div>

                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Short note</span>
                    <textarea name="editor_bio" rows="3" maxlength="400"
                              style="width:100%;padding:.55rem .75rem;border:1px solid rgba(128,128,128,.35);border-radius:.5rem;background:transparent;color:inherit;font:inherit">{{ old('editor_bio', $settings['editor_bio'] ?? '') }}</textarea>
                </label>

                <div style="display:grid;gap:.5rem">
                    <span style="font-weight:600;font-size:.875rem">Photo</span>

                    @if ($src = $imgSrc($settings['editor_photo'] ?? null))
                        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                            <img src="{{ $src }}" alt="Editor"
                                 style="height:56px;width:56px;object-fit:cover;border-radius:50%">
                            <label style="display:inline-flex;align-items:center;gap:6px;font-size:.85rem">
                                <input type="checkbox" name="remove_editor_photo" value="1"> Remove
                            </label>
                        </div>
                    @endif

                    <input type="file" name="editor_photo" accept="image/*" style="font-size:.85rem">
                    <span style="font-size:.78rem;opacity:.65">A square photo works best. Without one, initials are drawn instead.</span>
                </div>
            </div>
        </x-filament::section>

        {{-- ---------------- top bar widgets ---------------- --}}
        <x-filament::section>
            <x-slot name="heading">Top bar widgets</x-slot>
            <x-slot name="description">
                Weather and the stock index, shown at the top right of every page.
            </x-slot>

            <div style="display:grid;gap:1rem">
                <label style="display:inline-flex;align-items:center;gap:8px;font-size:.875rem;font-weight:600">
                    {{-- An unticked checkbox posts nothing at all, so this
                         carries the "off" value and the checkbox overrides it. --}}
                    <input type="hidden" name="weather_enabled" value="0">
                    <input type="checkbox" name="weather_enabled" value="1"
                           @checked(old('weather_enabled', $settings['weather_enabled'] ?? '1') === '1')>
                    Show Dhaka weather
                </label>
                <span style="font-size:.78rem;opacity:.65;margin-top:-.5rem">
                    Fetched automatically every 30 minutes from Open-Meteo. No account, no key.
                </span>

                <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">City shown</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="weather_city" placeholder="ঢাকা"
                                               value="{{ old('weather_city', $settings['weather_city'] ?? '') }}" />
                        </x-filament::input.wrapper>
                    </label>

                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">Latitude</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="weather_lat" placeholder="23.8103"
                                               value="{{ old('weather_lat', $settings['weather_lat'] ?? '') }}" />
                        </x-filament::input.wrapper>
                    </label>

                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">Longitude</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="weather_lon" placeholder="90.4125"
                                               value="{{ old('weather_lon', $settings['weather_lon'] ?? '') }}" />
                        </x-filament::input.wrapper>
                        <span style="font-size:.78rem;opacity:.65">Leave all three empty for Dhaka.</span>
                    </label>
                </div>

                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Stock widget code</span>
                    <span style="font-size:.78rem;opacity:.65">
                        Paste an embed from a market-data provider (TradingView and similar give one away free)
                        to get a live chart. Leave empty to show the typed figures below instead.
                    </span>
                    <textarea name="stock_embed" rows="4"
                              style="width:100%;padding:.55rem .75rem;border:1px solid rgba(128,128,128,.35);border-radius:.5rem;background:transparent;color:inherit;font:inherit;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.75rem">{{ old('stock_embed', $settings['stock_embed'] ?? '') }}</textarea>
                </label>

                <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">DSEX index</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="dse_index" placeholder="5,432.10"
                                               value="{{ old('dse_index', $settings['dse_index'] ?? '') }}" />
                        </x-filament::input.wrapper>
                        <span style="font-size:.78rem;opacity:.65">Leave empty to hide the stock reading.</span>
                    </label>

                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">Change (%)</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="dse_change" placeholder="-0.82"
                                               value="{{ old('dse_change', $settings['dse_change'] ?? '') }}" />
                        </x-filament::input.wrapper>
                        <span style="font-size:.78rem;opacity:.65">A minus sign shows a red down arrow; anything else, green up.</span>
                    </label>

                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">As of</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="dse_as_of" maxlength="60" placeholder="১৮ অক্টোবর"
                                               value="{{ old('dse_as_of', $settings['dse_as_of'] ?? '') }}" />
                        </x-filament::input.wrapper>
                    </label>
                </div>

                <p style="font-size:.8rem;opacity:.65;margin:0">
                    The exchange publishes no free live feed of its own, which is why this is either
                    a provider's widget or a typed figure. Both appear in the homepage sidebar.
                </p>
            </div>
        </x-filament::section>

        {{-- ---------------- header controls ---------------- --}}
        <x-filament::section>
            <x-slot name="heading">Header controls</x-slot>
            <x-slot name="description">
                The two links on the right of the navigation bar, in the order they appear.
                Clear a label to hide that control.
            </x-slot>

            <div style="display:grid;gap:1.25rem">
                <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">1 · Latest link text</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="nav_latest_text" placeholder="সাম্প্রতিক"
                                               value="{{ old('nav_latest_text', $settings['nav_latest_text'] ?? '') }}" />
                        </x-filament::input.wrapper>
                    </label>

                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">1 · Latest link URL</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="nav_latest_url" placeholder="{{ url('/latest') }}"
                                               value="{{ old('nav_latest_url', $settings['nav_latest_url'] ?? '') }}" />
                        </x-filament::input.wrapper>
                        <span style="font-size:.78rem;opacity:.65">Leave empty to use the built-in সাম্প্রতিক page.</span>
                    </label>
                </div>

                <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">2 · Button text</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="nav_cta_text" placeholder="Latest Video"
                                               value="{{ old('nav_cta_text', $settings['nav_cta_text'] ?? '') }}" />
                        </x-filament::input.wrapper>
                        <span style="font-size:.78rem;opacity:.65">This label is excluded from the translator and stays as written.</span>
                    </label>

                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">2 · Button link</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="nav_cta_url" placeholder="{{ url('/video') }}"
                                               value="{{ old('nav_cta_url', $settings['nav_cta_url'] ?? '') }}" />
                        </x-filament::input.wrapper>
                        <span style="font-size:.78rem;opacity:.65">Leave empty to use the built-in video listing.</span>
                    </label>
                </div>

                <p style="font-size:.8rem;opacity:.65;margin:0">
                    Search and the language switcher follow these two automatically.
                </p>
            </div>
        </x-filament::section>

        {{-- ---------------- contact ---------------- --}}
        <x-filament::section>
            <x-slot name="heading">Contact and about</x-slot>

            <div style="display:grid;gap:1rem">
                <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">Email</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="email" name="site_email"
                                               value="{{ old('site_email', $settings['site_email'] ?? '') }}" />
                        </x-filament::input.wrapper>
                    </label>

                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">Articles per page</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="number" name="posts_per_page" min="3" max="60"
                                               value="{{ old('posts_per_page', $settings['posts_per_page'] ?? '12') }}" />
                        </x-filament::input.wrapper>
                    </label>
                </div>

                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Address</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" name="site_address"
                                           value="{{ old('site_address', $settings['site_address'] ?? '') }}" />
                    </x-filament::input.wrapper>
                </label>

                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">About text</span>
                    <span style="font-size:.78rem;opacity:.65">Shown in the footer and the sidebar editorial card.</span>
                    <textarea name="about_text" rows="4"
                              style="width:100%;padding:.55rem .75rem;border:1px solid rgba(128,128,128,.35);border-radius:.5rem;background:transparent;color:inherit;font:inherit">{{ old('about_text', $settings['about_text'] ?? '') }}</textarea>
                </label>
            </div>
        </x-filament::section>

        {{-- ---------------- social ---------------- --}}
        <x-filament::section>
            <x-slot name="heading">Social links</x-slot>
            <x-slot name="description">Empty links are hidden on the site rather than pointing nowhere.</x-slot>

            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
                @foreach ([
                    'facebook_url'  => 'Facebook',
                    'twitter_url'   => 'X (Twitter)',
                    'linkedin_url'  => 'LinkedIn',
                    'vk_url'        => 'VK',
                    'youtube_url'   => 'YouTube',
                    'instagram_url' => 'Instagram',
                ] as $key => $label)
                    <label style="display:grid;gap:.35rem">
                        <span style="font-weight:600;font-size:.875rem">{{ $label }}</span>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" name="{{ $key }}" placeholder="https://…"
                                               value="{{ old($key, $settings[$key] ?? '') }}" />
                        </x-filament::input.wrapper>
                    </label>
                @endforeach
            </div>
        </x-filament::section>

        {{-- ---------------- seo ---------------- --}}
        <x-filament::section>
            <x-slot name="heading">SEO</x-slot>

            <div style="display:grid;gap:1rem">
                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Default meta title</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" name="meta_title"
                                           value="{{ old('meta_title', $settings['meta_title'] ?? '') }}" />
                    </x-filament::input.wrapper>
                </label>

                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Default meta description</span>
                    <textarea name="meta_description" rows="3" maxlength="300"
                              style="width:100%;padding:.55rem .75rem;border:1px solid rgba(128,128,128,.35);border-radius:.5rem;background:transparent;color:inherit;font:inherit">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                </label>

                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Google Analytics ID</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" name="google_analytics" placeholder="G-XXXXXXXXXX"
                                           value="{{ old('google_analytics', $settings['google_analytics'] ?? '') }}" />
                    </x-filament::input.wrapper>
                </label>
            </div>
        </x-filament::section>

        <div>
            <x-filament::button type="submit">Save settings</x-filament::button>
        </div>
    </form>

</x-filament-panels::page>
