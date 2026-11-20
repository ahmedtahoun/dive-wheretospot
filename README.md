# dive.wheretospot.com

Where To Spot · Dive: an independent guide to freediving in Dahab, Egypt. It lists every school, freelance instructor and coach, compared by price, depth, language and spot, and adds live sea conditions and interactive spot pages.

Plain PHP 8 + SQLite, no framework and no build step. Runs on the same Namecheap cPanel as shop.wheretospot.com.

## What's in it

| Page | URL | What it does |
|---|---|---|
| Home | `/en/` | Quick finder, today's conditions, Blue Hole teaser, featured, teaching this week, events, depth ladder |
| Directory | `/en/freediving` | Filters (level, type, agency, language, spot, this week, verified) kept in the URL, sorting, tick up to 4 to compare |
| Compare | `/en/compare?p=a,b,c` | Side-by-side table with the best value in each row highlighted |
| Provider profile | `/en/p/{slug}` | Courses and prices, achievements, photos and videos (tagged by spot), socials, events, inquiry form |
| Spots map | `/en/spots` | Leaflet map plus a list with mini cross-sections and live calm/chop/rough labels |
| Spot page | `/en/spots/{slug}` | **The dive**: scroll down and you descend. Depth gauge, water colour, cross-section with a diver marker, features and course depths, and who can take you to that depth |
| Today | `/en/today` | Wind, gusts, water, waves (Open-Meteo, cached 30 min), a 12-hour wind chart, spots sorted calmest first, teaching this week, events |
| Prices | `/en/prices` | The flagship SEO page: every course in one sortable table with typical prices per level |
| Share kit | `/en/p/{slug}/kit` | Instagram story card (canvas PNG), website badge (`/badge/{slug}.svg`), live widget (`/widget/today?p={slug}`) |
| Join | `/en/join` | Free listing request, goes to admin approval |
| Admin | `/admin/` | Approve, verify, feature, founding member, teaching this week, edit profile and courses, upload photos and videos with a spot tag, inquiries, events, remove sample data |

Contact links go through `/go/{whatsapp|instagram|…}/{slug}`, which counts each click. That count is the proof of value when you sell featured spots.

## Files

- `index.php`: the router. `app/pages.php`: public pages. `app/admin.php`: admin.
- `app/spots.php`: the spot data (text, depth, cross-section shape, features). Pins and wind exposure are first estimates; check them with a local instructor.
- `app/seed.php`: **fictional sample providers**, marked "Sample profile" on the site. Remove them in Admin → "Remove sample data" before launch.
- `app/conditions.php`: Open-Meteo weather and marine data plus the calm/chop/rough estimate.
- `assets/`: `site.css`, `app.js` (filters, compare, price table), `dive.js` (scroll dive), `map.js`, `kit.js`.

## Run locally

The browser-pane server is `dive-local` in `~/Documents/GitHub/.claude/launch.json`. It runs `php -S` with `dev/router.php`, and `DIVE_DATA_DIR` points at a scratch folder, so the test database never lands in the project. Admin password locally: `dive-local`. That password works only on localhost; the live site needs a hash in `config.local.php`.

## Deploy

See [DEPLOY.md](DEPLOY.md).
