# Top bar on a switch

```
resources/views/partials/header.blade.php                    (replaced)
resources/views/filament/admin/pages/manage-settings.blade.php (replaced)
app/Http/Controllers/Admin/SettingsController.php            (replaced)
```

No migration — settings live in the existing `settings` table.
All three were built from the copies you sent, so nothing earlier is lost.

---

## What you get

**Site settings → Top bar**, a new section with three switches:

- **Show the top bar** — the master switch
- **Date and clock**
- **Social icons**

Turning both halves off hides the bar too. An empty strip above the logo is
worse than no strip.

With the clock off, the icons stay on the right rather than sliding across
to where the date used to be.

**Default is shown.** A new setting arrives empty in the database, and a bar
that vanished by itself after an update would be read as a fault.

## Two small things done on the way

**A hidden `#live-clock` is left behind when the bar is off.** The layout's
script looks for that element every second; an error thrown there would take
the rest of that script block with it. An empty hidden span costs nothing
and removes the risk.

**"Top bar widgets" is now "Sidebar widgets".** Those are the weather,
prayer times and stock index, and they render in the homepage sidebar, not
the top bar — with a real "Top bar" section beside it the old name would
have sent you to the wrong place. Nothing about them changed.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch40\amardesh24-batch40\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Open **Site settings**, untick **Show the top bar**, save, and check the
site. Then tick it back on.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "Top bar switches in site settings" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```
