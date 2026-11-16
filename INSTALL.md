# Admin sidebar — menu items moved left

```
app/Providers/Filament/AdminPanelProvider.php   (replaced)
resources/views/filament/admin-tweaks.blade.php (new)
```

No migration. Install batch 35 first.

---

## What changed

The sidebar's left gutter goes from Filament's 1.5rem to **0.55rem**, and
each item's own padding and the icon-to-label gap come in with it. The
brand line and any group headings move to the same left edge so the column
still reads as one.

Done with a render hook that drops a `<style>` block at the end of `<head>`
— not a compiled Filament theme. A theme would mean a Vite build step, and
every future deploy would then need `npm run build` before the admin looked
right. This is a handful of cosmetic overrides; it does not deserve a build.

**To change the amount**, edit one line in `admin-tweaks.blade.php`:

```css
--admin-sidebar-gutter: .55rem;
```

Smaller moves it further left; `1.5rem` puts it back to where it was.

## If it does not move

Filament renames its internal classes between major versions, and these
rules target those names. If nothing shifts, send me a screenshot with the
browser's inspector open on a menu item (right-click → Inspect) and I will
match the real class names. Nothing can break either way — a rule that
matches nothing is ignored.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch36\amardesh24-batch36\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Then Ctrl+F5 in the admin.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "Admin sidebar spacing" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```
