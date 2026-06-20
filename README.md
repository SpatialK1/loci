---

## API Routes

All routes go through `public/index.php`. Session auth is required for all routes except share tokens and login.

| Method | Route | Description |
|---|---|---|
| POST | `/login` | Create session |
| POST | `/logout` | Destroy session |
| GET | `/media` | List media — supports `?type=`, `?status=`, `?tag=`, `?recommender=`, `?sort=`, `?order=` |
| GET | `/media/{id}` | Get single media item |
| POST | `/media` | Create media item (checks for duplicates first) |
| PUT | `/media/{id}` | Update media item |
| DELETE | `/media/{id}` | Delete media item |
| GET | `/tags` | List all tags |
| POST | `/tags` | Create tag |
| PUT | `/tags/{id}` | Rename tag |
| DELETE | `/tags/{id}` | Delete tag |
| GET | `/recommenders` | List all recommenders |
| POST | `/recommenders` | Create recommender |
| PUT | `/recommenders/{id}` | Update recommender |
| DELETE | `/recommenders/{id}` | Delete recommender |
| GET | `/lists` | List all curated lists |
| GET | `/lists/{id}` | Get single list |
| POST | `/lists` | Create list |
| PUT | `/lists/{id}` | Update list |
| DELETE | `/lists/{id}` | Delete list |
| POST | `/lists/{id}/media` | Add media item to list |
| DELETE | `/lists/{id}/media/{mediaId}` | Remove media item from list |
| GET | `/settings` | Get all settings |
| PUT | `/settings` | Update settings |
| GET | `/share/{token}` | Public list access — no auth required |

---

## Database Schema

Six core tables plus a settings table:

| Table | Purpose |
|---|---|
| `media` | All media items — URLs, books, movies, podcasts |
| `tags` | Tag names |
| `media_tags` | Many-to-many join between media and tags |
| `recommenders` | People who recommend content |
| `lists` | Curated lists with share tokens |
| `media_lists` | Many-to-many join between media and lists |
| `settings` | Key-value store for site configuration |

---

## Installation

### Requirements

- PHP 8.2+
- MySQL 5.7+
- Apache with `mod_rewrite`
- Composer

### Steps

**1. Clone the repo**
```bash
git clone https://github.com/SpatialK1/loci.git
cd loci
```

**2. Install dependencies**
```bash
composer install
```

**3. Configure database credentials**
```bash
cp config.sample.php config.php
nano config.php
```

**4. Configure auth credentials**
```bash
cp api/auth.sample.php api/auth.php
nano api/auth.php
```

Generate a bcrypt hash for your password:
```bash
php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"
```

**5. Set up the database**

Create a MySQL database, then run:
```bash
mysql -h your_host -u your_user -p your_database < schema.sql
```

**6. Point your web server at `public/`**

Apache should serve from the `public/` directory. All other directories should be above the web root.

---

## Settings

Site settings are stored in the database and managed via the `/settings` API endpoint. Available settings:

| Key | Default | Description |
|---|---|---|
| `site_title` | `Loci` | Displayed in the browser tab and header |
| `site_public` | `false` | Allow unauthenticated GET requests |
| `theme` | `light` | `light` or `dark` |
| `font_size` | `1.0` | Base font size multiplier |
| `contact_url` | `` | Link displayed on public pages |
| `items_per_page` | `20` | Pagination size |
| `default_sort` | `created_at` | Default sort field |
| `default_sort_direction` | `DESC` | `ASC` or `DESC` |
| `default_status_filter` | `all` | `all`, `queue`, or `consumed` |
| `view_mode` | `list` | `list` or `card` |
| `language` | `auto` | `auto` detects from browser, or set a language code |

---

## Internationalization

UI strings live in `lang/en.php` (source of truth). Translation files for other languages mirror the same key structure. Non-English files are machine-translated and marked for community review.

To contribute a translation:
1. Find the appropriate file in `lang/` (e.g. `lang/fr.php`)
2. Review and correct the machine translations
3. For abbreviation/nickname lists used in duplicate detection, see `lang/abbreviations/` — these need native speaker input, not machine translation
4. Submit a pull request

---

## Open Architecture Question

We're currently seeking feedback on how to handle translations in JavaScript. The problem and options are documented here: [link to your write-up or a GitHub issue]

If you have an opinion, please open an issue or reach out directly.

---

## Status

| Feature | Status |
|---|---|
| Backend API | ✅ Complete |
| Session auth | ✅ Complete |
| Media CRUD | ✅ Complete |
| Tags, Recommenders, Lists | ✅ Complete |
| Settings | ✅ Complete |
| Duplicate detection | ✅ Complete |
| i18n infrastructure | ✅ Complete |
| Media view (`media.php`) | 🔄 In progress |
| Import (CSV, HTML, JSON) | 🔄 In progress |
| Lists view (`lists.php`) | ⏳ Not started |
| Settings view (`settings.php`) | ⏳ Not started |
| Light/dark themes | ⏳ Not started |
| RSS feed for public lists | ⏳ Not started |
| Bookmarklet | ⏳ Not started |

---

## License

MIT
