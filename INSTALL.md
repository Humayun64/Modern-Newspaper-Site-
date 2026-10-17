# Client corrections — batch 1 (header, menu, colours)

Covers items **1, 2, 6, 8, 10, 11, 14, 19** from the two photographed pages.

| # | Correction | What changed |
|---|---|---|
| 1 | Change the banner colour | New **Appearance** section in Site settings — masthead, menu bar and accent colour are now admin-controlled |
| 2 | Logo instead of the "Amar Desh" text | Already supported; upload it under Site settings → Branding → Logo (see note below) |
| 6, 8 | Add সাম্প্রতিক to the header | New **সাম্প্রতিক** link + a `/latest` page listing everything, newest first |
| 8 | Latest video / podcast | New `/video` and `/podcast` listings |
| 10 | "Watch Videos" → "Latest Video", not translated | Renamed, and marked `translate="no"` so Google leaves it alone |
| 11 | Order: Latest video → search → language | Header controls are now সাম্প্রতিক → Latest Video → search → language |
| 14 | Menu order with "আরও" | `php artisan site:header-menu` builds the agreed order in one go |
| 19 | Better-looking language switcher | Replaced the plain dropdown with a proper panel (globe, current language, tick on the active one) |

---

## Files in this bundle

```
app/Console/Commands/BuildHeaderMenu.php              new
app/Http/Controllers/LatestController.php             new
app/Http/Controllers/Admin/SettingsController.php     replaces
resources/views/partials/header.blade.php             replaces
resources/views/filament/admin/pages/manage-settings.blade.php   replaces
public/css/site.css                                   replaces
routes/web.php                                        replaces
```

Every file is complete — nothing here is a fragment to paste into an existing file.

---

## Install — one command at a time

**Step 1.** Copy the files in. Change the second path if your project is somewhere else.

```
xcopy "%USERPROFILE%\Downloads\amardesh24-client-fixes-1\*" "D:\Xammp\htdocs\amardesh24\" /E /H /Y
```

**Step 2.** Clear the compiled views and config.

```
php artisan optimize:clear
```

**Step 3.** Build the header menu in the agreed order.

```
php artisan site:header-menu --dry-run
```

That one changes nothing — it just prints the menu it would build and tells you
which categories are missing. Read the list, then run it for real:

```
php artisan site:header-menu
```

**Step 4.** Open the site and check the header.

---

## What you need to do in the admin afterwards

1. **Upload the logo** — Site settings → Branding → Logo. Until a file is there,
   the masthead shows the site name as text. That is why the live site still
   shows "amarDesh24.news" in letters: the setting is empty in the live
   database, which git does not touch.

2. **Pick the banner colour** — Site settings → Appearance. Four pickers:
   masthead/footer, menu bar, accent, accent hover. Tick "Use default" on any
   of them to go back to the theme colour. The client did not say which colour
   they want in the note, so nothing has been changed by default — the site
   looks exactly as it does now until someone picks one.

3. **Create any missing categories** — step 3 will name them. From the client's
   list these are likely to need creating: **স্বাস্থ্য** (correction 21),
   possibly **সাক্ষাৎকার** and **বিজ্ঞাপন**. Create them under Categories,
   then run `php artisan site:header-menu` again.

---

## Notes and judgement calls

**Correction 14 and "আরও".** The dropdown heading is a `#` link — it opens the
submenu rather than going anywhere, which is how the same thing works on the
current WordPress site. On a phone the submenu is always expanded, so nothing
is unreachable.

**Correction 19.** The note says "like the current website". I could not see
the switcher on amardesh24.news from here — it is injected by JavaScript and
does not appear in the page source I can read — so I built a clean panel
rather than guess at a copy. If the client specifically wants flags per
language, that is a small change on top of this.

**Correction 3 / 5 (remove ads) are not in this batch.** Together they read as
"no advertising anywhere", which would mean the whole ads module sits unused.
Before I strip it out, ask the client whether advertising is gone for good or
just not wanted on the homepage for launch — removing it is easy, putting it
back is not.

**Correction 13 (breaking news expiring after a day)** is the only item on the
list that needs a database column and a scheduled job. It is in batch 3.

**New URLs.** `/latest`, `/video` and `/podcast` are now reserved. If an
article already has one of those three slugs it will stop being reachable at
the site root — unlikely, but worth a look if a story disappears.

---

## If something looks wrong

Run `php artisan optimize:clear` first — a cached view or route is the usual
cause after a file swap. Then hard-refresh the browser (Ctrl+F5); the
stylesheet is cache-busted by file date, but the browser can still hold the
old HTML.
