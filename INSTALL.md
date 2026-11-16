# SEO panel in English

```
app/Support/SeoAnalyzer.php                       (replaced)
resources/views/filament/seo-analysis.blade.php   (replaced)
```

No migration — batch 34 already added the column. Install batch 34 first.

---

## What changed

Every line in the panel is English now, and the numbers are plain digits
rather than Bangla ones. My mistake — you told me the admin stays in
English and I wrote the panel in Bangla anyway.

Nothing else moved: same checks, same weights, same score.

Band wording: **Good** (80+), **Needs work** (50–79), **Poor** (under 50).
Groups: **Problems**, **Improvements**, **Good**.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch35\amardesh24-batch35\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "SEO panel in English" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```
