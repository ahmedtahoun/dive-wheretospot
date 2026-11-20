# Deploying dive.wheretospot.com

Do this only once you're happy with the prototype locally.

1. **GitHub**: create a private repo (e.g. `ahmedtahoun/dive-wheretospot`), then commit and push this folder.
2. **Subdomain in cPanel**: Domains → Create a new domain → `dive.wheretospot.com`, with document root `/home/viorowzb/dive.wheretospot.com`.
3. **DNS in Wix**: add the same kind of record you added for `uae.wheretospot.com` (an A record `dive` → the cPanel server IP). Wait for it to resolve, then run AutoSSL in cPanel.
4. **cPanel Git Version Control**: clone the GitHub repo, then "Deploy HEAD Commit". `.cpanel.yml` copies the site into place and creates `/home/viorowzb/dive-data` (the database lives there, outside the website).
5. **Settings (server only)**: in File Manager, copy `config.local.example.php` to `/home/viorowzb/dive.wheretospot.com/config.local.php` and fill in:
   - `admin_password_hash`: make it with `php -r "echo password_hash('YOUR-PASSWORD', PASSWORD_DEFAULT);"`
   - `admin_email`: gets every inquiry and listing request
   - `whatsapp_admin`: your WhatsApp number, for the "Sign up on WhatsApp" button
   (`config.local.example.php` is not deployed; copy its contents from GitHub.)
6. Open `https://dive.wheretospot.com/admin/`, log in, and **remove the sample data** before telling anyone about the site.
7. Submit `https://dive.wheretospot.com/sitemap.xml` in Google Search Console.

Later deploys: push to GitHub, then Pull + Deploy in cPanel. The database (`dive-data`) and `uploads/` are never overwritten.

## Before launch checklist

- [ ] Remove sample providers and events (Admin). Their sample photos (from Unsplash) go with them.
- [ ] Spot photos are free Unsplash placeholders, credited on each page (`photo` in `app/spots.php`). Swap in your own photos of each spot when you have them.
- [ ] Check spot pins, depths and wind exposure in `app/spots.php` with a local instructor
- [ ] Have the privacy & terms page (`/en/privacy`) checked
- [ ] Map tiles come from tile.openstreetmap.org (free, light use only). If traffic grows, switch to a tile provider with a key.
- [ ] Add real providers only with their OK; tick "Verified" only after checking their instructor number on the agency's site
