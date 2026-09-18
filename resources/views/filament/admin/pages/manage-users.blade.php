<x-filament-panels::page>

<style>
.us { --line: rgba(128,128,128,.28); --soft: rgba(128,128,128,.08); display: grid; gap: 1.25rem; }
.us-note { padding: .7rem .9rem; border-radius: .5rem; font-weight: 600; font-size: .875rem; }
.us-ok  { background: #dcfce7; color: #166534; }
.us-bad { background: #fee2e2; color: #991b1b; }

.us-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
.us-table th {
    text-align: left; font-size: .75rem; font-weight: 600; opacity: .6;
    padding: .5rem .6rem; border-bottom: 1px solid var(--line);
}
.us-table td { padding: .7rem .6rem; border-bottom: 1px solid var(--line); vertical-align: middle; }
.us-who { display: flex; align-items: center; gap: .7rem; }
.us-avatar { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.us-name { font-weight: 600; }
.us-mail { font-size: .75rem; opacity: .6; }

.us-pill { display: inline-block; padding: .18rem .55rem; border-radius: 999px; font-size: .7rem; font-weight: 700; }
.role-admin  { background: #d03b3b; color: #fff; }
.role-editor { background: #2a78d6; color: #fff; }
.role-author { background: rgba(128,128,128,.25); }
.state-off   { background: rgba(128,128,128,.2); opacity: .8; }
.state-on    { background: #0ca30c; color: #fff; }

.us-actions { display: flex; gap: .4rem; justify-content: flex-end; }
.us-link { font-size: .78rem; font-weight: 600; text-decoration: none; color: inherit;
           border: 1px solid var(--line); border-radius: .4rem; padding: .3rem .6rem; background: none; cursor: pointer; }
.us-link:hover { background: #d32b2b; border-color: #d32b2b; color: #fff; }

.us-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); }
.us-field { display: grid; gap: .35rem; }
.us-label { font-weight: 600; font-size: .8125rem; }
.us-hint { font-size: .75rem; opacity: .6; }
.us-input, .us-select, .us-area {
    width: 100%; padding: .52rem .7rem; border: 1px solid var(--line);
    border-radius: .5rem; background: transparent; color: inherit; font: inherit; font-size: .875rem;
}
.us-area { min-height: 84px; }
.us-btn { border: 0; border-radius: .5rem; padding: .55rem 1.1rem; cursor: pointer;
          font-size: .875rem; font-weight: 600; background: #d32b2b; color: #fff; }
.us-roles { display: grid; gap: .5rem; font-size: .8125rem; }
.us-roles div { display: grid; grid-template-columns: 70px 1fr; gap: .6rem; }
.us-roles strong { font-weight: 700; }
</style>

@php
    $avatar = fn ($u) => $u->photo
        ? asset('storage/'.$u->photo)
        : 'https://ui-avatars.com/api/?name='.urlencode($u->name).'&size=68&background=d32b2b&color=fff';
@endphp

<div class="us">

    @if (session('user_saved'))
        <div class="us-note us-ok">User saved.</div>
    @endif

    @if ($errors->any())
        <div class="us-note us-bad">
            <ul style="margin:0;padding-left:18px">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ---------------- list ---------------- --}}
    <x-filament::section>
        <x-slot name="heading">Accounts</x-slot>
        <x-slot name="description">{{ $users->count() }} {{ \Illuminate\Support\Str::plural('account', $users->count()) }}</x-slot>

        <div style="overflow-x:auto">
            <table class="us-table">
                <thead>
                    <tr>
                        <th>Person</th><th>Role</th><th>Status</th><th>Articles</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr>
                            <td>
                                <span class="us-who">
                                    <img class="us-avatar" src="{{ $avatar($u) }}" alt="">
                                    <span>
                                        <span class="us-name">{{ $u->name }}</span>
                                        @if ($u->designation)<span class="us-mail"> — {{ $u->designation }}</span>@endif
                                        <br><span class="us-mail">{{ $u->email }}</span>
                                    </span>
                                </span>
                            </td>
                            <td><span class="us-pill role-{{ $u->role }}">{{ $u->role }}</span></td>
                            <td>
                                <span class="us-pill {{ $u->is_active ? 'state-on' : 'state-off' }}">
                                    {{ $u->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td>{{ number_format($u->posts_count) }}</td>
                            <td>
                                <div class="us-actions">
                                    <a class="us-link" href="{{ \App\Filament\Admin\Pages\ManageUsers::getUrl() }}?edit={{ $u->id }}">Edit</a>
                                    @if ($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle', $u) }}">
                                            @csrf
                                            <button class="us-link" type="submit">{{ $u->is_active ? 'Disable' : 'Enable' }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- ---------------- add / edit ---------------- --}}
    <x-filament::section>
        <x-slot name="heading">{{ $editing ? 'Edit '.$editing->name : 'Add a user' }}</x-slot>
        <x-slot name="description">
            {{ $editing ? 'Leave the password empty to keep the current one.' : 'They sign in at /admin with this email and password.' }}
        </x-slot>

        <form method="POST" action="{{ route('admin.users.save') }}" enctype="multipart/form-data" style="display:grid;gap:1rem">
            @csrf
            @if ($editing)<input type="hidden" name="id" value="{{ $editing->id }}">@endif

            <div class="us-grid">
                <label class="us-field">
                    <span class="us-label">Name</span>
                    <input class="us-input" name="name" required value="{{ old('name', $editing->name ?? '') }}">
                </label>

                <label class="us-field">
                    <span class="us-label">Name in Bangla</span>
                    <input class="us-input" name="name_bn" value="{{ old('name_bn', $editing->name_bn ?? '') }}">
                    <span class="us-hint">Shown as the byline when set.</span>
                </label>

                <label class="us-field">
                    <span class="us-label">Email</span>
                    <input class="us-input" type="email" name="email" required value="{{ old('email', $editing->email ?? '') }}">
                </label>

                <label class="us-field">
                    <span class="us-label">Password</span>
                    <input class="us-input" type="password" name="password" autocomplete="new-password"
                           @if(! $editing) required @endif placeholder="{{ $editing ? 'Unchanged' : 'At least 8 characters' }}">
                </label>

                <label class="us-field">
                    <span class="us-label">Role</span>
                    <select class="us-select" name="role" required>
                        @foreach (['author' => 'Author', 'editor' => 'Editor', 'admin' => 'Admin'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $editing->role ?? 'author') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="us-field">
                    <span class="us-label">Designation</span>
                    <input class="us-input" name="designation" placeholder="স্টাফ রিপোর্টার"
                           value="{{ old('designation', $editing->designation ?? '') }}">
                </label>

                <label class="us-field">
                    <span class="us-label">Facebook</span>
                    <input class="us-input" name="facebook" value="{{ old('facebook', $editing->facebook ?? '') }}">
                </label>

                <label class="us-field">
                    <span class="us-label">X (Twitter)</span>
                    <input class="us-input" name="twitter" value="{{ old('twitter', $editing->twitter ?? '') }}">
                </label>
            </div>

            <label class="us-field">
                <span class="us-label">Photo</span>
                @if ($editing && $editing->photo)
                    <img class="us-avatar" style="width:56px;height:56px" src="{{ asset('storage/'.$editing->photo) }}" alt="">
                @endif
                <input type="file" name="photo" accept="image/*" style="font-size:.85rem">
                <span class="us-hint">Appears on the byline and in the author box under each article.</span>
            </label>

            <label class="us-field">
                <span class="us-label">Bio</span>
                <textarea class="us-area" name="bio">{{ old('bio', $editing->bio ?? '') }}</textarea>
            </label>

            <label class="us-field" style="display:flex;gap:.5rem;align-items:center">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing->is_active ?? true))>
                <span class="us-label" style="margin:0">Active — can sign in</span>
            </label>

            <div style="display:flex;gap:.6rem;align-items:center">
                <button class="us-btn" type="submit">{{ $editing ? 'Save changes' : 'Add user' }}</button>
                @if ($editing)
                    <a class="us-link" href="{{ \App\Filament\Admin\Pages\ManageUsers::getUrl() }}">Cancel</a>
                @endif
            </div>
        </form>
    </x-filament::section>

    {{-- ---------------- what the roles mean ---------------- --}}
    <x-filament::section>
        <x-slot name="heading">What each role can do</x-slot>

        <div class="us-roles">
            <div>
                <strong>Author</strong>
                <span>Writes and edits their own articles. Cannot touch anyone else's, cannot delete,
                      and cannot reach ads, users, settings or the activity log.</span>
            </div>
            <div>
                <strong>Editor</strong>
                <span>Everything an author can do, plus editing and deleting any article, managing
                      sections, tags and pages. No access to ads, users or settings.</span>
            </div>
            <div>
                <strong>Admin</strong>
                <span>Everything, including ads, user accounts, site settings, menus and the activity log.</span>
            </div>
        </div>
    </x-filament::section>

</div>

</x-filament-panels::page>
