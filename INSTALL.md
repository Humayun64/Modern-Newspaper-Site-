# Demo videos

One new command. Nothing else changes — no migration, no CSS, no views.

It puts three sample articles in a **ভিডিও** category so "সর্বশেষ ভিডিও"
is not an empty page when the client opens it. Each one demonstrates a
different way of giving a video:

1. an ordinary YouTube link
2. a YouTube Shorts link
3. a video file played by the site itself

---

## Install on the PC

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch19\amardesh24-batch19\*" "%USERPROFILE%\amardesh24\" /E /H /Y
```
```
php artisan site:demo-videos --dry-run
```
```
php artisan site:demo-videos
```

Then open `/video` and check all three play.

## On the live server

```
git pull
```
```
php artisan optimize:clear
```
```
php artisan site:demo-videos
```

No `migrate` this time.

---

## Removing them

When the client has real videos:

```
php artisan site:demo-videos --remove
```

It finds them by slug (`demo-video-1`, `-2`, `-3`), which nothing else on
the site uses — so it cannot reach a real article, even if the titles were
edited. The ভিডিও category stays.

---

## If one of them plays nothing

The first two point at a video on someone else's YouTube channel, and that
can be taken down at any time — I could not reach YouTube from here to
check them. Two ways to fix it:

- open the article in the admin and paste a different link, or
- `php artisan site:demo-videos --remove` then
  `php artisan site:demo-videos --youtube="https://www.youtube.com/watch?v=…"`

`--shorts=` and `--mp4=` work the same way.

---

## What the client needs to know

On the Post screen, right-hand side, **Featured image and video**:

- **Video link** — YouTube, Shorts, youtu.be, Facebook, fb.watch, Vimeo.
  The site turns it into a player inside the article.
- **Or upload a video** — MP4/WebM, up to 50 MB.

Give one or the other. If both are given, the uploaded file wins.

The link is almost always the better choice: YouTube and Facebook carry the
bandwidth and adapt the quality to the reader's connection. An uploaded file
is sent in full by this host to every reader, which is slow on a phone and
fills the disk.

A story reaches **সর্বশেষ ভিডিও** if any one of these is true: its Type is
Video, it has a video link or file, or it is in the ভিডিও or পডকাস্ট
category.

**Worth checking:** the 50 MB limit is the form's. The server's own
`upload_max_filesize` / `post_max_size` may be lower, and then a large
upload fails with no message. hPanel → PHP Configuration.
