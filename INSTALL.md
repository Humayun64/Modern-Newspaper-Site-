# Correction 13 — video on the article

```
xcopy "%USERPROFILE%\Downloads\amardesh24-batch17\amardesh24-batch17\*" "%USERPROFILE%\amardesh24\" /E /H /Y
```
```
php artisan migrate
```
```
php artisan optimize:clear
```

Batches 15 and 16 go in first; this builds on them.

---

## What the writer sees

The "Featured image" panel is now **"Featured image and video"**, and the two
video fields live there — because for a writer it is one decision: what
picture or clip leads this story.

- **Video link** — YouTube or Facebook. Always visible now; it used to be
  hidden unless Type was set to Video, which is why nobody found it.
- **Or upload a video** — MP4 or WebM, up to 50 MB.

Type → Video is still what puts the story in **সর্বশেষ ভিডিও**; the helper
text under the dropdown now says so.

## What the reader sees

The video plays **on the page**, under the headline:

- an uploaded file in the browser's own player, with the featured image as
  the poster frame and `preload="none"` — a 50 MB file should not download
  itself for everyone who came for the headline
- a YouTube, Shorts, Vimeo or Facebook link as a proper embed
- anything else stays a link, which is honest: better a link that works than
  an iframe showing an error

It appears whenever the article has a video, not only when Type says Video.
A reporter who pastes a link should not also have to remember a dropdown.

## One piece of advice

**Prefer the link over the upload.** YouTube and Facebook carry the
bandwidth, adapt the quality to the reader's connection, and cost nothing.
An uploaded file is served by your Hostinger plan at full size to every
reader — slow on a phone, and it eats the disk you are also storing 1,327
article images on.

The upload is there because the client asked for it, and it works. It is
just the more expensive of the two.

If an upload fails silently, it is the server's own limit, not the 50 MB
here — `upload_max_filesize` and `post_max_size` in PHP. Tell me and I will
check it.
