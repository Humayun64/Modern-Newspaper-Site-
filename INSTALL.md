# Slider arrows on the picture, and the wide cards turned round

```
public/css/widgets.css                          (replaced)
resources/views/sections/posts_slider.blade.php (replaced)
```

No migration. Install batch 36 first.

---

## 1. সংবাদ স্লাইডার — arrows on the photograph

They were beside the heading; they now sit on the picture, left and right,
the same as প্রধান সংবাদ. The heading row is left with the title alone.

The picture in this slider is pinned to 16:9 and the arrow overlay is given
the same ratio, so the buttons land dead centre on the photograph at any
width rather than drifting onto the headline below it. Checked at 1300px
and 390px — exact on both.

## 2. Phones: the last lists turn round

The wide cards (জাতীয়, খেলাধুলা and the rest of the main column) now put
the picture on the right and the headline at the left edge, matching
সর্বশেষ and ট্রেন্ডিং. The whole page reads one way now.

## 3. A bug found on the way — worth knowing about

The wide card is a grid of four loose children: picture, headline, summary,
byline. Only the two columns were declared, so they were placed in reading
order — picture, headline, and then **the summary wrapped back under the
picture, into a 116px column**. That is the thin ribbon of squeezed text in
the খেলাধুলা block in your screenshot.

The picture now holds its own column for the full height and everything
else goes in the text column. Fixed on desktop as well, where it was doing
the same thing with more room to hide it.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch37\amardesh24-batch37\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Ctrl+F5.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "Slider arrows on the picture, wide cards flipped on phones" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```
