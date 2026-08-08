# PDF Secure (mod_pdfsecure)

A Moodle activity module that serves course PDFs with a **per-user watermark burned
into the file**, and never serves the unwatermarked original.

![Moodle](https://img.shields.io/badge/Moodle-4.5%20%E2%80%93%205.2-orange.svg)
![License](https://img.shields.io/badge/License-GPLv3-blue.svg)

**Author:** Daividdi

---

## What this does, and what it does not

Read this part before deciding whether the plugin is right for you.

A document a browser can display is a document the reader can copy. There is no
configuration, no plugin and no commercial DRM that changes this — a screenshot, a
screen recording or a phone camera defeats all of them. Anything claiming otherwise
is selling reassurance, not protection.

So this module does not try to prevent copying. It makes copies **attributable**:

**It does:**

- Stamp every page with the name, email, user id, activity id and access time of
  the account that opened it — written into the PDF's page content, not overlaid by
  the browser, so it survives download, re-upload and forwarding.
- Repeat the same identity in the PDF metadata (`Author`, `Subject`), so a leaked
  file can be traced by tooling without reading a single page.
- Refuse to serve the original upload. The raw file is never reachable over HTTP,
  by any URL, for any user.
- Bind each generated copy to one account. Editing the user id in the URL returns
  404, so nobody can fetch a colleague's stamped copy and frame them with it.
- Fail closed. If a PDF cannot be stamped, the reader gets an error — never the
  unstamped original.

**It does not:**

- Prevent screenshots, screen recordings or photographs of the screen.
- Prevent a determined user from saving what the viewer renders.
- Protect the file once it has legitimately left your site by another route — for
  example a course backup. If your teachers can download `.mbz` archives, they can
  extract every original PDF, and no viewer-side control changes that. Restrict
  `moodle/backup:downloadfile` if that matters to you.

The honest summary: **it will not stop a leak, it will tell you whose account it
came from.** In practice that is the control that changes behaviour.

---

## How it works

1. A teacher uploads a PDF to the activity as usual. The original is stored
   untouched and is never served.
2. On a user's **first view**, the plugin renders a personalised copy with
   [FPDI](https://www.setasign.com/products/fpdi/about/) — a tiled diagonal mark
   plus a legible footer stamp — and caches it.
3. Later views serve the cached copy. It regenerates automatically when the source
   file changes, or when the watermark settings change.
4. The document is displayed in a bundled [PDF.js](https://mozilla.github.io/pdf.js/)
   viewer with its download and print controls removed.

The viewer-side controls are a speed bump for the casual user, nothing more. The
watermark is the part that actually holds.

### Caching

Generated copies live in the `watermarked` file area, keyed by
`v<RENDER_VERSION>-<settings hash>-<source contenthash>`. All three parts matter:
the content hash invalidates when the file is replaced, the render version when the
code changes, and the settings hash when an administrator retunes the appearance —
without that last one, changing the tone in the UI would silently do nothing for
anyone who already had a cached copy.

---

## Requirements

- Moodle 4.5 or later (tested on 4.5 and 5.2, including the 5.x split webroot)
- PHP 8.1+ with `iconv` supporting `//TRANSLIT`

FPDI and FPDF are bundled in `vendor/` — no Composer step is needed on the server.

**Known limitation of the PDF engine:** FPDI cannot read encrypted PDFs, and
importing drops annotations, links, bookmarks and form fields. For reading material
this is usually fine; if your PDFs are interactive forms, test before rolling out.

---

## Installation

```bash
cd /path/to/moodle/mod          # Moodle 5.x: /path/to/moodle/public/mod
git clone https://github.com/Daividdi/moodle-mod_pdfsecure.git pdfsecure
php admin/cli/upgrade.php --non-interactive
```

The directory **must** be named `pdfsecure`.

## Settings

*Site administration → Plugins → Activity modules → PDF Secure*

| Setting | Default | Notes |
| --- | --- | --- |
| Watermark tone | Light | Darker survives a low-quality photo better; lighter reads through more comfortably |
| Watermark text size | 11 pt | Larger is more legible in a photo of the screen, and covers more of the page |
| Include date in the diagonal watermark | On | The footer stamp always carries the date regardless |
| Show footer stamp | On | The legible line meant to be read by eye when tracing a leak |
| Keep watermarked copies for | 30 days | See below |

Changing any of the appearance settings regenerates every cached copy on next access.

### Retention

One copy is stored **per user, per document**, so this file area grows with
users x documents x time and has no natural ceiling. A few hundred learners and a
few dozen handbooks reach tens of gigabytes, and a full disk takes the whole site
down, not just this plugin.

A nightly task deletes copies older than the retention window. They regenerate
transparently on the next view at a cost of hundredths of a second, so a short
retention is close to free — pruning a copy that is still in use is cheaper than
the bookkeeping needed to avoid it. Set it to *Keep forever* only if disk space is
genuinely not a concern.

---

## Privacy

The plugin writes the viewer's name, email and user id into the documents it
generates, and stores those documents in the Moodle file API. Deployments subject to
GDPR or similar should say so in their privacy policy: this is personal data being
embedded in a file that may leave the platform — which is, after all, the point.

---

## Licence

GPL v3 or later, matching Moodle.

Bundled third-party components keep their own licences: PDF.js (Apache 2.0),
FPDF and FPDI (see `vendor/setasign/`).
