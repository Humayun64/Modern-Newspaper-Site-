# Ticker: one headline no longer touches the next

One file:

```
public/css/widgets.css   (replaced)
```

Install batch 29 first.

---

## What changed

The items were already 34px apart — which sounds like plenty until you
notice that every item *begins* with a photograph. The eye reads
picture-and-text as one unit, so a sentence ending 34px before the next
picture looks stuck to it.

Widening the gap alone would have eaten the strip, so a hairline now does
the separating: 24px of padding each side of an item and a 1px rule between
them. Text to the next picture is 48px, with the rule in the middle. On a
phone the padding drops to 15px.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch30\amardesh24-batch30\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Ctrl+F5.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "Ticker separators" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```
