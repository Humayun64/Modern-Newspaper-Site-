# Three small ones

```
public/css/widgets.css                        (replaced)
resources/views/home.blade.php                (replaced)
app/Console/Commands/ApplyCorrections.php     (replaced)
```

No migration. Install batch 28 first.

---

## 1. The white space at the top of each block — phones only

16px of padding above every heading became 4px, and the gap under the
heading 16px → 12px.

Not zero: at zero the heading's own line sits on the block's border and it
reads as broken rather than tight. Say the word and I will take the last
4px off.

Left as it was on desktop, where the white space is doing real work
separating the columns — the crosses were on phone screenshots. Tell me if
you want it there too.

## 2. ট্রেন্ডিং moves to the top of the sidebar

Above পরিচালকের পরিচিতি, and above the stock, weather and prayer widgets
as well. It is the only block in that column that changes with the news and
the one most likely to be clicked, so it belongs above the fold.

If you wanted it only above পরিচালকের পরিচিতি and still below the weather,
say so — it is one line.

## 3. The founder's line

"প্রতিষ্ঠাতা ও সম্পাদক, আমারদেশ২৪.নিউজ" → **"প্রতিষ্ঠাতা"**.

This one is stored in the database, so copying the file is not enough — the
command below writes it.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch29\amardesh24-batch29\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear && php artisan site:apply-corrections --force
```

Ctrl+F5 afterwards.

`--force` is needed: without it the command leaves a setting that is
already filled alone, and this one is. It also re-applies the other settled
corrections (section order, the header ad off, the banner colour) — all
unchanged, so nothing moves.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "Block padding on phones, trending to the top, founder line" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear && php artisan site:apply-corrections --force
```
