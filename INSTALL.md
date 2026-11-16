# White phone background, pictures on the right, and a Breaking news screen

```
public/css/widgets.css                                   (replaced)
resources/views/partials/ticker.blade.php                (replaced)
app/Filament/Admin/Pages/BreakingNewsPage.php            (new)
resources/views/filament/admin/pages/breaking-news.blade.php (new)
```

No migration — `is_breaking` already exists on the posts table.
Install batch 32 first.

---

## 1. White background on phones

The grey wash behind the blocks is gone below 620px. On a wide screen that
grey separates the columns; on a phone there are no columns, so it was only
a grey frame around everything.

The blocks keep their hairline border — that is now the only thing marking
where one section ends and the next begins, so it has to stay.

Desktop is untouched.

## 2. Picture right, headline left — phones

In the horizontal lists (সর্বশেষ, ট্রেন্ডিং, the list under প্রধান সংবাদ)
the picture moves to the right and the headline starts at the left edge.
The headline is what a reader scans, so it begins where the eye returns.

The trending number moves across with the picture it belongs to, or it
would sit on top of the text.

## 3. Breaking news — a screen of its own

**Admin → Breaking news.** Two lists: what is in the strip now, and
everything else with an **Add** button. Search for older headlines. **Clear
all** empties it.

The tick on the article screen does the same thing — **Homepage placement →
Breaking news** — so a reporter can mark a story while writing it.

**How the strip decides what to show:**

1. if anything is ticked, those stories — newest first, up to `breaking_count` (4)
2. if nothing is ticked, the newest published articles

The fallback is deliberate: an editor who marks nothing still gets a strip,
and an empty strip at the top of a news homepage reads as a broken site.
The two never mix — one tick and the ticks take over completely, because a
reader cannot tell which list a headline came from and an editor would not
expect their choice to share the row with whatever happened to be newest.

The screen warns you if more stories are ticked than the strip can show,
and marks any ticked story that is not published — readers never see those.

The strip is cached for a minute, but the admin screen clears that cache
the moment you tick something, so the change shows at once.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch33\amardesh24-batch33\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Then open the admin and check **Breaking news** is in the menu. Ctrl+F5 on
the site.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "White phone background, pictures right, breaking news screen" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```
