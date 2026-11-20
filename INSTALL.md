# Picture right, headline left — on the desktop too

One file:

```
public/css/widgets.css   (replaced)
```

No migration. Install batch 37 first.

---

## What changed

The desktop now reads the same way as the phone: the headline starts at the
left edge and the picture sits on the right, in সর্বশেষ, ট্রেন্ডিং, জাতীয়,
খেলাধুলা and every other horizontal list. The ranking number in ট্রেন্ডিং
travels across with the picture it belongs to.

Checked at 390, 620, 880, 1040, 1300 and 1440px — picture on the right at
every one, and no sideways scroll anywhere.

Untouched: the lead card and the carousels, where the headline already sits
under the photograph, and the upright cards on category and search pages.

## How it is written

All the rules that decide which side the picture is on now sit in **one
block at the end of the file**, including restatements of the phone rules
from the earlier rounds. Before this there were three places that could
each flip a list, and they had drifted apart. One place to change it means
one place to look when it is wrong.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch38\amardesh24-batch38\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Ctrl+F5 — the stylesheet changed.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "Picture right on every width" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```
