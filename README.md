# BlogForge.live

A complete Medium-inspired blogging platform starter built with **PHP 8.1+, MongoDB, HTML, CSS and vanilla JavaScript**. The UI uses the supplied BlogForge logo and is responsive across desktop and mobile.

## Included

- Minimal cover homepage: logo, headline and call-to-action buttons only (no posts)
- Explore/search/filter page
- Focused article reading page with likes, bookmarks UI, share and comments
- Email-only registration/sign-in. Sign-up asks for first name, last name, email, password, confirm password and Terms & Privacy agreement
- Author profile with an **Edit profile** page (name, username, bio, photo URL, change password) and a distraction-free story editor
- Draft/publish workflow, categories and tags
- Author/admin roles
- Admin overview, posts, comments and category management
- MongoDB repositories, indexes, seed script and admin-account script
- CSRF protection, password hashing and output escaping

## 1. Requirements

- PHP 8.1 or newer
- Composer
- MongoDB server or MongoDB Atlas
- PHP MongoDB extension (`ext-mongodb`)

## 2. Install

From the project directory:

```bash
composer install
cp .env.example .env
```

Edit `.env`:

```env
APP_URL=http://localhost:8000
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DATABASE=blogforge
```

For MongoDB Atlas, put the Atlas connection string in `MONGODB_URI`.

## 3. Prepare MongoDB

Create indexes:

```bash
php scripts/create_indexes.php
```

Optional: seed the default categories (add `--demo` to also insert sample stories):

```bash
php scripts/seed.php
```

If demo stories were seeded earlier and you want them gone:

```bash
php scripts/remove_demo_posts.php
```

Create the first admin:

```bash
php scripts/create_admin.php admin@example.com 'StrongPassword123!' 'BlogForge Admin'
```

Every email signup receives the `author` role. The CLI command above creates or upgrades an account to `admin`.

## 4. Run locally

On Windows, extract the entire ZIP and double-click `START_BLOGFORGE.bat` in the outer folder.

For a manual local start, run from `blogforge-site`:

```bash
php -S localhost:8000 -t . router.php
```

Open `http://localhost:8000`. The router blocks direct access to private configuration files. Do not start the built-in server without the router while `.env` is in the document root.

To troubleshoot database connectivity, run `CHECK_CONNECTION.bat` in the outer folder. It checks `.env`, the MongoDB PHP extension, Composer dependencies, and the Atlas ping without printing your password.

## 5. Production deployment for BlogForge.live

Point the web root at this project folder, enable HTTPS, run `composer install --no-dev --optimize-autoloader`, upload `.env` outside version control, and set:

```env
APP_ENV=production
APP_URL=https://BlogForge.live
```

Recommended server settings:

- PHP 8.5 or newer
- HTTPS only
- `display_errors=Off`
- secure session cookies
- MongoDB Atlas IP/network rules restricted to your server where practical
- regular MongoDB backups

## 6. MongoDB collections

- `users`: first_name, last_name, name, email, handle, password_hash, bio, avatar, role, terms_accepted_at
- `posts`: title, slug, excerpt, body, category, tags, author, status, metrics, timestamps
- `comments`: post_id, body, author, moderation status, timestamp
- `reactions`: post_id, user_id, type
- `categories`: name, slug

## 7. Important project files

- `index.php` — cover homepage
- `explore.php` — discovery/search
- `article.php` — reading page
- `auth.php` — email sign-in / registration
- `write.php` — author editor
- `profile.php` — author profile
- `profile-edit.php` — edit profile & change password (API: `api/profile.php`)
- `terms.php` — Terms of Service & Privacy Policy
- `admin/` — admin UI
- `api/` — JSON/auth endpoints
- `src/` — database/auth/repositories/helpers
- `assets/css/style.css` — complete responsive design system
- `assets/js/app.js` — client interactions
- `scripts/` — DB indexes, seed, admin setup

## 8. Before a public launch

This package is a strong full-stack starter, but for a high-traffic production service you should add email verification, password reset/email delivery, image upload/object storage, rate limiting, richer moderation tools, pagination/cursor feeds, notifications, bookmark persistence, content reporting, audit logs, observability, automated tests and a production-grade HTML/Markdown sanitizer if rich HTML is introduced.

## Logo files

- `assets/img/blogforge-logo.png` — full transparent logo
- `assets/img/blogforge-icon.png` — square icon for header/app usage
- `assets/img/favicon.png` — favicon
