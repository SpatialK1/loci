# Loci

A self-hosted personal media archive for collecting and curating URLs, books, movies, and podcasts. Built with vanilla PHP, MySQL, and vanilla JavaScript — no framework, no npm, no build step.

> ⚠️ **Work in progress.** This project is under active development and is not yet feature-complete. Feedback is welcome — see the [Open Architecture Question](#open-architecture-question) below.

---

## What It Does

- Save URLs, books, movies, and podcasts to a personal archive
- Tag, filter, and sort your collection
- Track read/watched/listened status with timestamps
- Curate items into shareable lists with unique share tokens
- Flag dead links and paywalled content
- Record who recommended something to you
- Fuzzy duplicate detection when adding items (catches abbreviations, typos, nickname variations)
- Import from CSV, Netscape HTML bookmarks, and Firefox JSON (in progress)
- Multilingual UI — English, French, German, Portuguese, Italian, Japanese, Mandarin, Farsi, Russian
- Public/private toggle — share your whole archive or keep it private
- Light/dark theme support (in progress)

---

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.5, raw PDO via MeekroDB |
| Database | MySQL |
| Frontend | Vanilla JavaScript, PHP-rendered HTML |
| Hosting | DreamHost shared hosting (Apache + .htaccess routing) |
| Dependencies | Composer (MeekroDB only) |

---

## Project Structure

```
loci/
├── api/
│   ├── auth.php                      # Session auth — gitignored, copy from auth.sample.php
│   ├── auth.sample.php               # Auth template for new installs
│   ├── db.php                        # MeekroDB database connection initializer
│   ├── helpers/
│   │   └── i18n.php                  # Language detection, t() helper, I18n class
│   └── repositories/
│       ├── BaseRepository.php        # Shared type casting methods
│       ├── MediaRepository.php       # CRUD for media items
│       ├── TagRepository.php         # CRUD for tags
│       ├── RecommenderRepository.php # CRUD for recommenders
│       ├── ListRepository.php        # CRUD for curated lists
│       ├── SettingsRepository.php    # Read/write site settings
│       ├── DuplicateDetector.php     # Fuzzy duplicate matching
│       └── ImportRepository.php      # CSV/HTML/JSON import (in progress)
├── lang/
│   ├── en.php                        # English UI strings (source of truth)
│   ├── fr.php                        # French
│   ├── de.php                        # German
│   ├── pt.php                        # Portuguese
│   ├── it.php                        # Italian
│   ├── ja.php                        # Japanese
│   ├── zh.php                        # Mandarin (Simplified)
│   ├── fa.php                        # Farsi (RTL)
│   ├── ru.php                        # Russian
│   └── abbreviations/
│       ├── en.php                    # English abbreviations and nicknames for duplicate detection
│       └── [other langs]             # Stubs ready for community contribution
├── public/                           # Web root — Apache serves from here
│   ├── .htaccess                     # HTTPS redirect, routing to index.php
│   ├── index.php                     # Front controller — all API requests route through here
│   ├── media.php                     # Main media archive view
│   ├── login.php                     # Login page
│   ├── import.php                    # Import UI (in progress)
│   ├── css/
│   │   ├── style.css                 # Base layout and sizing (theme-agnostic)
│   │   ├── theme-light.css           # Light color theme (in progress)
│   │   └── theme-dark.css            # Dark color theme (in progress)
│   └── js/
│       ├── api.js                    # JavaScript API client — all fetch calls
│       ├── media.js                  # Media view controller
│       ├── login.js                  # Login form handler
│       └── import.js                 # Import UI controller (in progress)
├── config.php                        # DB credentials — gitignored, copy from config.sample.php
├── config.sample.php                 # Config template for new installs
├── composer.json                     # Composer dependencies (MeekroDB)
├── composer.lock                     # Locked dependency versions
└── schema.sql                        # MySQL schema — run this to set up the database
```

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

Site settings are stored in the database and managed via the `/settings` API endpoint.

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

We are currently seeking feedback on how to handle translations in JavaScript. The app uses PHP's `t()` helper for server-rendered strings, but dynamically rendered JavaScript content (modals, list items, buttons) requires a separate approach.

**The options under consideration:**

1. **Server-rendered HTML fragments** — move all HTML generation into PHP, JavaScript handles interactions only. Cleanest long-term, requires refactoring current JS rendering code.
2. **Data attributes on HTML elements** — PHP writes translated strings into hidden DOM elements, JavaScript reads them. Simple, no dependencies, minor HTML bloat.
3. **`GET /lang` API endpoint** — JavaScript fetches all translations on page load. Clean separation, adds one HTTP request.
4. **i18n JavaScript library** — industry standard for JS-heavy apps, adds a dependency and parallel translation files.
5. **Injected `Lang` object** (current approach) — PHP encodes translations into a JavaScript object on page load. Works but requires manually maintaining which strings are included.

If you have an opinion, please open a GitHub issue.

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