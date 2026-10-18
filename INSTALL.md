# Batch 9 — PDF viewer, and the weather card

Two things: the Global coverage documents now open in a panel on the page,
and the weather card is redesigned — plus the reason it was missing.

---

## Install

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch9\amardesh24-batch9\*" "%USERPROFILE%\amardesh24\" /E /H /Y
```
```
php artisan optimize:clear
```

---

## Why the weather card was missing

Almost certainly the same thing that stopped the WordPress import: **PHP on
your machine has no CA bundle configured**, so it cannot make an HTTPS
request. The import got past it with `-d curl.cainfo=…` on the command line,
but `php artisan serve` never saw that setting — so the weather call fails
and the card hides itself rather than printing an error on the public site.

Fix it properly this time, in PowerShell (not cmd — the earlier `echo`
silently wrote nothing):

```
powershell -Command "Add-Content -Path 'D:\Xammp\php83\php.ini' -Value 'curl.cainfo = D:\Xammp\apache\bin\curl-ca-bundle.crt'"
```
```
powershell -Command "Add-Content -Path 'D:\Xammp\php83\php.ini' -Value 'openssl.cafile = D:\Xammp\apache\bin\curl-ca-bundle.crt'"
```

Check it took:

```
php -i | findstr /i "cainfo cafile"
```

Both should now show the path instead of "no value". Then **stop and restart
`php artisan serve`** — php.ini is only read at startup.

On the live server this does not arise; Linux has a system CA bundle. If the
card is missing there too, tell me and I will look at something else.

---

## The weather card

Laid out like the one you sent: place, the big reading with conditions,
feels-like and today's high/low and rain chance, then air quality under a
rule, then humidity and wind.

Air quality is the US AQI from Open-Meteo's air-quality service — free, no
key, same as the forecast. It is a second call, and a nice-to-have: if it is
slow or down the card still shows the weather.

The band is written in words as well as drawn on the bar. A colour on its own
tells a reader with colour-blindness nothing, and "অস্বাস্থ্যকর" tells
everyone the same thing.

---

## Global coverage — documents open on the page

**Images are accepted now, not only PDFs.** The file you uploaded,
`AP-News-Habib-Bhai.pdf.webp`, is a picture of a page, not a PDF — which is
why the row said "link" and nothing opened. Your current site's highlights
are all page scans like that, so refusing anything but PDF would have turned
away the very files this section is for.

Clicking a card now opens a panel over the page:

- a **PDF** goes in a frame, so the browser's own viewer gives page controls
- a **page scan** is shown full width and scrolls, the way you read a
  newspaper page

The bar carries the headline, "নতুন ট্যাবে", "মূল খবর" when an original link
was given, and a close button. Escape or a click outside closes it. On a
phone it fills the screen.

The corner badge now says what the file actually is — "PDF" or "ছবি". A
reader told "PDF" who gets a picture stops trusting the next badge.

**The "Low and order" entry has no file on it, only a link.** Remove it and
add it again with the `.webp` in the Document box, and it will open in the
panel.
