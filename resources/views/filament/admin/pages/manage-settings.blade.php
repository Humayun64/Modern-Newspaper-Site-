@php
    /** Resolve a stored image path to something the browser can load. */
    $imgSrc = function (?string $path) {
        if (blank($path)) return null;
        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
            ? $path
            : asset('storage/'.$path);
    };
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

        {{-- ---------------- header button ---------------- --}}
        <x-filament::section>
            <x-slot name="heading">Header button</x-slot>
            <x-slot name="description">The red button on the right of the navigation bar. Clear the label to hide it.</x-slot>

            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Button text</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" name="nav_cta_text" placeholder="Watch Videos"
                                           value="{{ old('nav_cta_text', $settings['nav_cta_text'] ?? '') }}" />
                    </x-filament::input.wrapper>
                </label>

                <label style="display:grid;gap:.35rem">
                    <span style="font-weight:600;font-size:.875rem">Button link</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" name="nav_cta_url" placeholder="https://…"
                                           value="{{ old('nav_cta_url', $settings['nav_cta_url'] ?? '') }}" />
                    </x-filament::input.wrapper>
                </label>
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
