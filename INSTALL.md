# SEO panel on the article screen

```
app/Support/SeoAnalyzer.php                                (new)
resources/views/filament/seo-analysis.blade.php            (new)
database/migrations/..._add_focus_keyphrase_to_posts_table.php (new)
app/Filament/Admin/Resources/Posts/Schemas/PostForm.php    (replaced)
```

Install batch 33 first. **This one needs `migrate`.**

---

## What it does

The **SEO** section on the article screen now shows a score out of 100 and
a list of what is wrong, what could be better, and what is already fine —
grouped, in Bangla, like the panel you sent.

It recalculates when a field loses focus, not on every keystroke. Every
keystroke would mean a request per letter typed, which on a slow connection
makes the whole editor feel stuck.

**New field: Focus keyphrase** — the words a reader would type into Google
to find the story. Not shown to readers, never goes into the page. Without
it the panel can only measure lengths; with it, it can check the headline,
the opening paragraph, the meta description and the web address.

## What it checks

| | Weight |
|---|---|
| Focus keyphrase set | 3 |
| Keyphrase in the headline | 3 |
| Keyphrase in the first paragraph | 2 |
| Keyphrase in the meta description | 2 |
| Keyphrase in the web address | 1 |
| Text length | 3 |
| Meta description length (120–156) | 3 |
| Title length (30–60) | 2 |
| Links to your own stories | 2 |
| Links to outside sources | 1 |
| alt text on images in the body | 1 |

Each one scores full marks when good, half when it is an "improvement",
nothing when it is a problem.

## One deliberate difference from Yoast

The 300-word minimum is an English figure. A complete Bangla news report is
often 250 words, so anything over 150 counts as an *improvement* here
rather than a *problem*. A panel that shows red on every ordinary news
brief teaches a newsroom to ignore the panel.

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch34\amardesh24-batch34\*" "%USERPROFILE%\amardesh24\" /E /H /Y && php artisan migrate && php artisan optimize:clear
```

Open any article in the admin, scroll to **SEO**, type a focus keyphrase
and click outside the box — the score should move.

## Then live

```
cd %USERPROFILE%\amardesh24 && git add -A && git commit -m "SEO analysis panel" && git push
```

```
cd ~/apps/amardesh24 && git pull && php artisan migrate --force && php artisan optimize:clear
```
