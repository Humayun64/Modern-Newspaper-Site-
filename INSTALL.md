# Batch 11 — font, social links, banners, prayer times

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch11\amardesh24-batch11\*" "%USERPROFILE%\amardesh24\" /E /H /Y
```
```
php artisan optimize:clear
```

Then hard-refresh the browser (Ctrl+F5) — the font changed, and the old one
will be sitting in the cache.

---

## The font

Hind Siliguri → **Noto Sans Bengali**. The reason is the numerals: ১ and ২
were being misread, which matters on a site where every date, count and
temperature is in Bangla digits. Noto Sans Bengali draws them with clearly
different shapes and holds together at 12–13px, which is where most of this
page lives.

Headings keep Noto Serif Bengali.

Nothing else had to change — the font is set in one variable, and this
overrides it from `widgets.css`, which loads after the main stylesheet.

## Social links — only the ones you fill in

Every icon used to render whatever the settings said, falling back to `#`.
So clearing a link in the admin did nothing and the row still advertised
accounts that do not exist. That was a bug, and the settings page even
claimed the opposite.

Now a network appears **only when it has a URL**. So:

**Site settings → Social links** → clear every box except **Facebook**
(and YouTube if the client wants it — your marks on the picture ticked both,
your note said Facebook only, so I have left that to you).

The "আমাদের সাথে থাকুন" card disappears entirely if none are filled, and
narrows its grid to one or two columns when only one or two are.

## The banners

Both are gone from the site — masthead and sidebar. The Ads module and the
header slot are untouched, so when the client does sell a banner it is
**Site settings → Advertising → "Show the masthead banner"** and uploading
the image under Ads. Nothing has to be rebuilt.

## Prayer times

New card in the sidebar, directly under the weather: ফজর, সূর্যোদয়, যোহর,
আসর, মাগরিব, এশা, with the next prayer named at the top and the time
remaining counting down.

Times come from AlAdhan — free, no key, same arrangement as the weather.
Cached for the day, so it is one request each morning, not one per reader.

Defaults are Dhaka and the Karachi calculation (method 1), which is the one
normally used in Bangladesh. Both are in **Site settings → Top bar widgets**
if the client wants something else — method 3 is Muslim World League.

The next prayer is marked by a label as well as a tint, so it does not rely
on anyone seeing the colour.
