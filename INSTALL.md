# One picture width down the main column

One file:

```
public/css/widgets.css   (replaced)
```

No migration. Install batch 38 first.

---

## What changed

A block in the main column mixes two card shapes — the lead card at the top
and the smaller rows under it. Their pictures were 170px and 104px, so the
right-hand edge stepped in and out down the page and the block read as two
lists stacked rather than one.

The rows now take the lead card's width, and its 4:3 shape with it. The
shape matters: square 170px pictures would have made every row as tall as
its photograph, with a short headline floating in all that space. At 4:3
the row stays close to the height of its own text.

Picture widths now, in the main column: **170px**, dropping to **116px**
below 880px — the same numbers the lead card already used, so they move
together from here on.

**The sidebar is deliberately left alone** at 104px. That column is 330px
wide; a 170px picture there would leave the headline nowhere to go.

Checked at 1300, 880 and 390px — same width and the same right edge on all
three.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch39\amardesh24-batch39\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan optimize:clear
```

Ctrl+F5.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "One picture width in the main column" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan optimize:clear
```
