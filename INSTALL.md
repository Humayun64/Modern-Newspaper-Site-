# Client corrections — batch 5

Fixes the breaking ticker going empty, and moves the stock and weather
readings into the sidebar as proper cards.

---

## 1. Breaking news was disappearing — my mistake

Batch 4 measured the day from **publish time**. Every article on this site was
imported and carries its original date, so everything was already older than a
day: marking something breaking did nothing, and the strip went blank.

The rule should have been a day from the moment it is **marked** breaking.
That needs a timestamp of its own, which this batch adds.

- new `breaking_since` column, stamped when the flag goes on and cleared when it comes off
- the ticker and `site:expire-breaking` both measure from it
- anything already flagged gets its day starting from the moment you migrate,
  so nothing vanishes as you run this

An editor can now mark a week-old story breaking and get a full day out of it.

## 2. Stock and weather, in the sidebar

They were a thin strip in the top bar. They are now two cards in the homepage
sidebar, which is where the client's reference has them.

**Weather** needs nothing: Dhaka, from Open-Meteo, refreshed every half hour.
It shows the temperature, conditions, feels-like, today's high and low,
humidity and wind. Another city is three fields in Site settings.

**Stock** has two ways to fill it, under Site settings → Top bar widgets:

- **Stock widget code** — paste an embed from a market-data provider
  (TradingView and similar give one away free). That is what sites with a
  live DSE chart are actually running; the exchange publishes no free feed of
  its own, so there is nothing to build against directly. This is the one
  that looks like the client's reference.
- **DSEX index / change / as of** — typed in. Plainer, always correct, and no
  third-party script on the page. Used when the embed is empty.

---

## Install — one command at a time

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch5\amardesh24-batch5\*" "%USERPROFILE%\amardesh24\" /E /H /Y
```
```
php artisan migrate
```
```
php artisan optimize:clear
```
```
php artisan site:refresh-home --force
```

That last one re-marks the breaking articles with the new timestamp, so the
strip comes back immediately instead of waiting for an editor.

---

## Files

```
database/migrations/2026_10_18_000002_add_breaking_since_to_posts_table.php  new
app/Models/Post.php                                                          replaces
app/Console/Commands/ExpireBreaking.php                                      replaces
app/Console/Commands/RefreshHomepage.php                                     replaces
app/Http/Controllers/Admin/SettingsController.php                            replaces
resources/views/partials/ticker.blade.php                                    replaces
resources/views/partials/widget-weather.blade.php                            new
resources/views/partials/widget-stock.blade.php                              new
resources/views/home.blade.php                                               replaces
resources/views/filament/admin/pages/manage-settings.blade.php               replaces
public/css/widgets.css                                                       replaces
```

---

## Notes

The top-bar strip is still there and still works; the sidebar cards are
separate. If the client wants the strip gone now that the cards exist, clear
the DSEX field and untick "Show Dhaka weather" — or say so and I will take
the strip out entirely.

The cards are on the homepage only. Say the word if they should follow the
sidebar onto category and article pages too.

**Pasted widget code is printed to the page as-is.** Only an admin can set
it, which is the same trust any "paste this code" box carries — but it is why
that field is not something to hand to an editor.
