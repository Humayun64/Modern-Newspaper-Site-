# Keep video stories out of the news listings

Four files. No migration, no CSS.

```
app/Support/VideoContent.php          (new)
app/Models/HomeSection.php            (replaced)
app/Http/Controllers/HomeController.php (replaced)
resources/views/partials/ticker.blade.php (replaced)
```

Install batch 21 first if you have not — this builds on its ticker.

---

## What it does

Video stories no longer appear in:

- the **ব্রেকিং** strip
- the **সর্বশেষ** sidebar list
- the **জনপ্রিয়** tab
- any other homepage section

They still appear, as before, on **সর্বশেষ ভিডিও** (`/video`), on the
ভিডিও category page, and anywhere a section is pointed at a video
category — filtering those would leave an empty block.

## What counts as a video story

Either of these:

- the post's **Type** is Video, or
- it is in a video category (primary or additional) — matched by the slug
  `video`/`videos` or by any category name containing ভিডিও

**Having a video link is deliberately not enough.** An ordinary report can
carry a clip of the scene without becoming a video story. If links counted,
the news lists would quietly empty themselves as that habit spread.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch22\amardesh24-batch22\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Open the homepage. The three demo videos should be gone from the strip and
from সর্বশেষ, and still be there at `/video`.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "Keep video stories out of the news listings" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```

---

## Two things to know

**The category list is cached for an hour.** Make a new video category and
it starts being excluded within the hour — or immediately after
`php artisan optimize:clear`.

**Two files were replaced, not edited:** `HomeSection.php` and
`HomeController.php`. Neither has changed since 18 October, so nothing
should be lost — but if you edited either by hand since then, tell me and
I will merge instead.
