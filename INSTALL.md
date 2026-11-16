# Ticker: plain space, no rule

One file:

```
public/css/widgets.css   (replaced)
```

Replaces batches 30 and 31 — the dividing rule is gone entirely.

---

## What changed

No line. The space between items went from 34px to **52px**, so a headline
ends well clear of the next picture. On a phone it is 30px, where the strip
is narrow and 52px would waste it.

Done on the track's own `gap` rather than padding on each item — one number
to change, and nothing that can be overridden by another rule.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch32\amardesh24-batch32\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Ctrl+F5. If the spacing still looks unchanged, open the page with Ctrl+F5
held or check in a private window — that would mean the browser is serving
the old stylesheet, not that the file is wrong.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "Ticker spacing" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```
