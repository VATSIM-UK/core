# Handoff: Public Events pages — make the rendered pages match the approved design

A previous agent built and reviewed the public Events pages (list + detail). **Functionality and tests are done and green, but the rendered pages do not match the approved design.** Your job is to make them match. You have full context below.

## Where everything is

All files are in the repo working tree. **No commits have been made** (deliberate — do not commit/push). Branch: `events-frontend`.

- **Design source of truth (open in a browser):** `.superpowers/events-design/design-preview.html` — a self-contained standalone HTML mockup of the approved design. `vatsimuk-logo.png` sits next to it for the nav logo.
- **Current rendered state (screenshots):** `.superpowers/events-design/current-list.png` and `.superpowers/events-design/current-detail.png`.
- **Original implementation plan (context):** `docs/superpowers/plans/2026-09-25-public-events-pages.md`.
- Progress/handoff scratch: `.superpowers/sdd/events-pages/` (ledger + per-task briefs/reports + review diffs).

## Running things (devcontainer)

Everything runs inside the devcontainer. **On Windows hosts, route commands through the helper** (do NOT use host read/write/edit on repo files; the host checkout is stale):

```
bash .opencode/skills/devcontainer/scripts/dc.sh '<cmd>'                       # run a command
bash .opencode/skills/devcontainer/scripts/dc.sh 'cat -- /workspace/<path>'    # read a file
bash .opencode/skills/devcontainer/scripts/dc.sh 'cat > /workspace/<path>'     # write via stdin
```

- Dev server / site: the container Apache serves `http://core.test` (also reachable at `http://localhost`). The user runs `make frontend-dev` (Vite dev server on `http://core.test:5173`; HMR host is `core.test`). Editing `vite.config.js` restarts the Vite dev server.
- Tests: `... dc.sh 'cd /workspace && php artisan test tests/Feature/Events tests/Unit/Events tests/Unit/Support/MemberDisplayNameTest.php'`
- Build (needed for `@vite` in tests): `... dc.sh 'cd /workspace && npm run build'`
- Lint: `... dc.sh 'cd /workspace && composer lint -- --test --parallel'`

There is currently **one published test event**: `#64` "London Luton Live" (not rostered, no positions/managers). List at `/events`, detail at `/events/64`.

## The approved design (what it must look like)

Palette/tokens (from the app's own `tailwind.config.js`): navy `#17375e` (`uknavy`) header bars; brand `#25ADE3` (`brand`) accents; white panels `rounded-xl shadow-sm ring-1 ring-gray-200/80`; amber "Rostered" pill (`bg-amber-50 text-amber-700 border-amber-200`), green "Bookable" pill; banner fallback gradient `from-[#3a74b8] to-uknavy`.

**List (`GET /events`)**
- Page heading "Events" + one-line subtitle; a compact segmented control ("Upcoming" active / "Past") on the right.
- Navy "Upcoming events" panel containing a responsive card grid (1 / 2 / 3 columns).
- Card: banner area (event `image_url` cover, else navy gradient), a date chip top-left (weekday/day/month), a `Rostered`/`Bookable` pill bottom-right, then name (semibold ~15px), tagline (2-line clamp, gray), optional position chips (mono, sky), and a footer row: `HH:MM – HH:MMz` + "View event →".
- Navy "Past events" panel below with the same cards dimmed + a "Past" pill, paginated (`$past->links()`).

**Detail (`GET /events/{event}`)**
- Cover strip (image or gradient). **No text overlaid on the cover.**
- Navy title bar *below* the cover: "← All events", the event name, and the `Rostered`/`Bookable` pill.
- Light sub-bar: tagline + date + time range + duration.
- Two-column body: left (2/3) "About this event" (rich HTML) and "Positions covered"; right (1/3) "Event details" sidebar with Date/Time(Zulu)/Duration/Positions/Booking/Organisers, then actions: **Add to calendar** (Google / Outlook Web / `.ics`), **Copy share link**, and (only when NOT rostered) **Book a position →** linking to `route('site.bookings.calendar', ['year' => ..., 'month' => ...])`.

**Formatting rules (must match admin panel)**
- Date `d. m. Y` (e.g. `18. 10. 2026`) via `toPanelDate()`; time `H:i` + `z` via `toPanelTime()`.
- Organisers: preferred first name + last initial + CID, e.g. `Alex S. (1234567)`.
- `rostered` events: static badge, no booking link. Non-rostered: "Bookable" + booking link.
- Only published events (`published_at !== null`) appear; drafts 404 on detail.

## Data + services (already built, tested)

- `App\Models\Events\Event` — `published()`/`upcoming()` scopes, `positions` (belongsToMany `Position.callsign`), `managers`, `organiserLabels(): array<int,string>`.
- `App\Repositories\Events\EventRepository` — `getUpcoming(): Collection`, `getPast(int $perPage = 12): LengthAwarePaginator`.
- `App\Services\Events\EventCalendarService` — `google()`, `webOutlook()`, `ics()`, `icsFilename()` (uses `spatie/calendar-links`).
- `App\Support\MemberDisplayName`.

## Components / routes / views

- `routes/web-livewire.php`: `site.events.index` (`GET /events`) and `site.events.show` (`GET /events/{event}`) — **public** (no auth middleware).
- `App\Livewire\Events\Index` (+ `getPast()` pagination via `WithPagination`) → `resources/views/livewire/events/index.blade.php`.
- `App\Livewire\Events\Show` → `resources/views/livewire/events/show.blade.php`.
- `resources/views/components/events/card.blade.php` (the list card).
- `resources/views/livewire/events/layout.blade.php` — extends the legacy `layout` and loads the page CSS entry.
- `resources/assets/css/events.css` — page-specific Tailwind entry (`@import "tailwindcss"` + `@config` + `@source` for the events views), registered in `vite.config.js` (`input`).

## THE root cause of the visual mismatch (most important section)

The legacy `resources/views/layout.blade.php` loads **Bootstrap 3 + normalize** (compiled from `resources/assets/sass/app.scss`) as **unlayered CSS** via `@vite`. Tailwind v4 emits all utilities inside **`@layer`** (`theme`, `base`, `components`, `utilities`). Per the CSS cascade, **unlayered CSS always wins over layered CSS**, regardless of specificity or source order.

Verified live in the browser (Playwright, on `/events`):
- normalize's HTML5 rule `article,aside,footer,header,main,nav,section { display: block }` overrides Tailwind's `.flex` / `.grid`. Test: `.flex` gives `display:flex` on a `<div>`, but `display:block` on `<header>`, `<section>`, `<nav>`, `<aside>`, `<article>`, `<main>`, `<footer>`.
- Bootstrap's `h1–h6 { font-size; font-weight }` beats `text-*`/`font-*`; a native `<h3 class="text-base font-semibold">` computed `24px / 500 / margin 20px 0 10px`.
- Bootstrap's `p { margin: 0 0 10px }`, `ul { padding-left: 40px }`, `a:hover { text-decoration: underline }` similarly override utilities.

**Why the "new dashboard" looks right despite the same layout:** `mship/management/dashboard-beta.blade.php` also `@extends('layout')` (so Bootstrap IS loaded), but it uses **zero native `<h1–h6>`** and `<div>` wrappers everywhere, plus its own `mship-dashboard.css` Tailwind entry loaded via `@section('styles')`. Its card component (`resources/views/components/mship/dashboard/card.blade.php`) uses `<p role="heading" aria-level="2" class="... text-lg font-semibold ... m-0">`. That is the established pattern for Tailwind on a member page.

**What the previous agent already changed to follow that pattern (keep it):**
- Replaced all `<h1–h6>` with `<p role="heading" aria-level="N" class="... m-0">`.
- Replaced `<header>/<section>/<nav>/<aside>` wrappers with `<div>` in `index.blade.php` and `show.blade.php` (`.flex`/`.grid` now apply; grid is 3-up).
- Added `m-0`/`mb-0` to text elements; `no-underline hover:no-underline` on links.
- Added `resources/assets/css/events.css` and load it in the events layout via `@section('styles')` (mirrors the dashboard/bookings).
- Detail cover always paints the gradient behind the image (so a broken/missing `image_url` still looks right).

With those changes the layout utilities now work (header flexes, grid is 3 columns, cards render). **However, the previous agent was told this still does not match the design closely enough.** Likely remaining fidelity issues to investigate:
1. **Site chrome:** the legacy layout renders a large banner image + a breadcrumb bar above the content (from `_bannerUrl`). The design mockup had neither. Decide whether the design should be adapted to this chrome or whether events should use a chrome-free/different layout.
2. **Content width:** events use `max-w-6xl` (~1008px at the app's 14px root) centered inside `.page_content { width: 90% }`, leaving wide gray margins. The design mockup filled its canvas. Compare with the bookings calendar which uses a full-width panel.
3. **Fonts:** the app body font is `Calibri` (set in `app.scss`); the mockup used `system-ui`. Any text that still inherits Bootstrap/normalize may differ.
4. **Card banner:** the app recreated only a flat vertical gradient; the mockup had a radial highlight + diagonal stripe pattern (the mockup's CSS is the reference).
5. Any remaining Bootstrap element rules bleeding through (e.g. `ul`/`dl` spacing in the rich description and the facts list).
6. `p` margin: Bootstrap `p { margin: 0 0 10px }` may still win on some paragraphs; verify.

### Alternative architecture if the pattern can't get close enough
Render the events pages through a **Bootstrap-free** layout: `resources/views/components/layouts/app.blade.php` is what the **roster** pages use (Filament styles + Tailwind only, no `app.scss`). To keep the site nav you would build a small bare layout that includes `components.nav` and loads the Tailwind/events CSS (the nav is Tailwind-based; FontAwesome icons come from `resources/assets/js/app.js`, which also uses jQuery/Bootstrap JS — so a bare layout must load FontAwesome another way or include app.js plus jQuery/Bootstrap JS). This is a bigger change but removes the conflict entirely.

## Status

- Events PHPUnit tests: **70 passed**; `composer lint` clean (Pint/blade-formatter).
- Do **not** commit or push. Leave everything in the working tree.
- The dev DB was reset at some point; `App\Models\Mship\Account::count()` is 0. Only event `#64` exists.

## Suggested first steps

1. Open `.superpowers/events-design/design-preview.html` and compare to `current-list.png` / `current-detail.png` (and render `/events` + `/events/64` yourself).
2. Enumerate concrete, pixel-level differences (spacing, width, colors, fonts, chrome).
3. Decide between: (a) keep the legacy layout and continue the `<div>`/`<p>`/page-CSS pattern, tightening fidelity; or (b) move events to a Bootstrap-free layout.
4. If you change CSS/entries, run `npm run build` and re-run the events tests.
