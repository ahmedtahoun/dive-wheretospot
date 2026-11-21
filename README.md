# dive.wheretospot.com

Where To Spot · Dive: an independent guide to freediving in Dahab, Egypt. It lists every school, freelance instructor and coach, compared by price, depth, language and spot, and adds live sea conditions and interactive spot pages.

Plain PHP 8 + SQLite, no framework and no build step. Runs on the same Namecheap cPanel as shop.wheretospot.com.

## What's in it

| Page | URL | What it does |
|---|---|---|
| Home | `/en/` | Quick finder, today's conditions, Blue Hole teaser, featured, teaching this week, events, depth ladder |
| Find my match | `/en/match` | 4-step quiz (level, goal, needs like "nervous" or "female instructor", language/month/budget). Scores every provider as the share of the diver's asks they meet, explains each score (✓ / –), and sends one request to the top 3 (`/en/match/ask`) with the diver's needs attached |
| Directory | `/en/freediving` | Filters (level, type, agency, language, spot, this week, verified) kept in the URL, sorting, tick up to 4 to compare |
| Compare | `/en/compare?p=a,b,c` | Side-by-side table with the best value in each row highlighted |
| Provider profile | `/en/p/{slug}` | Courses and prices, achievements, photos and videos (tagged by spot), socials, events, inquiry form |
| Spots map | `/en/spots` | Leaflet map plus a list with mini cross-sections and live calm/chop/rough labels |
| Spot page | `/en/spots/{slug}` | **The dive**: scroll down and you descend. Depth gauge, water colour, cross-section with a diver marker, features and course depths, and who can take you to that depth |
| Today | `/en/today` | Wind, gusts, water, waves (Open-Meteo, cached 30 min), a 12-hour wind chart, spots sorted calmest first, teaching this week, events |
| Prices | `/en/prices` | The flagship SEO page: every course in one sortable table with typical prices per level |
| Share kit | `/en/p/{slug}/kit` | Instagram story card (canvas PNG), website badge (`/badge/{slug}.svg`), live widget (`/widget/today?p={slug}`) |
| Join | `/en/join` | 1-minute sign-up (or "Sign up on WhatsApp"), then the provider gets their private page straight away |
| Provider's private page | `/me/{token}` | No password: the link is the login. Edit profile, courses (one-tap presets) and prices, certificate number + card photo, social links and best posts, photos/videos with spot tags, events, "teaching this week" (auto-off after 7 days), views/clicks/inquiries for 30 days, replies to inquiries |
| Admin | `/admin/` | Approve, verify (with the certificate claimed + card photo), feature, private link (send on WhatsApp / replace), photo queue, weekly WhatsApp check, inquiries, events, remove sample data |

Providers also fill in "What you're best at": up to 6 specialties (`SPECIALTIES` in `app/bootstrap.php`), teaching style, private/small/group, extras (video, pickup, card…), months in Dahab, youngest age and coaching depth. It feeds the matcher, the "Best for" block on profiles, the "Good for…" filter and the compare table. Scoring lives in `app/match.php`.

Photos from verified providers publish straight away; others wait in Admin → Photos to check. If a verified provider changes their instructor number, the badge switches off until you check again.

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
