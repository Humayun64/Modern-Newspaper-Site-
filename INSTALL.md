# Breaking strip — always the four newest articles

One file: `resources/views/partials/ticker.blade.php`.
No migration, no CSS, no settings row.

**This replaces batch 20. If you have not installed batch 20, skip it.**

---

## The rule now

The strip shows the **four newest published articles**. Always.

- Nothing to tick.
- Nothing expires.
- Publish a new article and it goes to the front; the oldest of the four
  drops out by itself.

The 24-hour window is gone, and so is the "Breaking news" tick deciding
what shows here. That tick still exists on the post screen but no longer
affects the strip.

To show a different number, set `breaking_count` in settings.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch21\amardesh24-batch21\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Open the homepage — the strip should be there with the four newest
headlines.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "Breaking strip: four newest articles" && git push
```

On the server:

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```

---

## Tidying up

The four posts flagged breaking on 3 November still carry the flag in the
admin list. They do nothing now, but if you want them cleared:

```
php artisan site:expire-breaking
```

---

## Still open

The live site serves a `robots.txt` that blocks search engines, so Google
is not indexing it. Check with:

```
grep -E 'APP_ENV|APP_DEBUG|APP_URL|APP_INDEXABLE' ~/apps/amardesh24/.env
```

Send me the output and I will give you the exact line to change.
