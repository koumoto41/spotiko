# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Spotiko: a Spotify listening-data aggregator (top artists/tracks, genres) split into a Laravel 13 (PHP ^8.3) JSON API built with clean architecture, and a React (Vite + TypeScript) SPA. It also replies to users over a LINE bot webhook.

## Commands

Run via Docker (`docker/docker-compose.yml`, services `spotiko_api` (php-fpm) / `spotiko_front` (Vite dev server) / `spotiko_apache` (web server, port 1111) / `spotiko_db` (mysql)):

```bash
cd docker && docker compose up -d

# api
docker compose exec api composer install
docker compose exec api php artisan migrate --seed   # seeds one admin user, see AdminUserSeeder
docker compose exec api vendor/bin/phpunit
docker compose exec api vendor/bin/phpstan analyse    # larastan, level 5, see phpstan.neon

# front — hot-reloads on :5173, proxies /api and /sanctum to apache
docker compose exec front npm run dev
docker compose exec front npm run build                # type-checks, then builds into ../public/
```

Custom artisan commands (`app/Console/Commands`): `command:AggregateArtist`, `command:AggregateTrack` (also triggerable via the API, see below), `command:PushMessage` (LINE push to a hardcoded user list).

There is no linter/formatter configured beyond `.editorconfig` (PHP) and TypeScript's own type-checking (front).

## Architecture

### Backend (`app/`) — clean architecture, one directory per layer

```
Domain/<Feature>/Repositories/*Interface.php   # contracts only, zero Eloquent/Laravel deps
Application/Services/*, Application/UseCases/<Feature>/*Aggregation*Case.php   # orchestration, depends only on Domain interfaces
Infrastructure/Persistence/Eloquent/Models/*   # Eloquent models — schema config only, no business logic
Infrastructure/Persistence/Eloquent/Repositories/*   # Domain interface implementations
Infrastructure/ExternalServices/Spotify/SpotifyApiClient.php   # implements Domain\Spotify\SpotifyClientInterface, wraps jwilsson/spotify-web-api-php
Infrastructure/ExternalServices/Line/LineMessagingClient.php   # implements Domain\Line\LineMessagingClientInterface, wraps linecorp/line-bot-sdk
Presentation/Http/Controllers/Api/*   # thin controllers: validate → call UseCase/Service → return JSON
Presentation/Http/Requests/*, Presentation/Http/Resources/*
Providers/RepositoryServiceProvider.php   # binds every Domain interface to its Infrastructure implementation (registered in config/app.php)
```

`<Feature>` = Artist, Track, Album, MyAlbum, ArtistGenre, ArtistGenreMap, TopArtist, TopTrack, Token. Every repository follows the same pattern — one interface in `Domain`, one Eloquent-backed implementation in `Infrastructure` — so use `ArtistRepositoryInterface`/`Infrastructure/.../ArtistRepository` as the template for any new one.

**Framework-conventional paths stay put** — `app/Console/Commands`, `app/Http/Kernel.php`, `app/Http/Middleware`, `app/Providers`, `app/Exceptions` are unchanged Laravel locations, not part of the Domain/Application/Infrastructure/Presentation split (Console commands are thin wrappers that resolve an `Application\UseCases\*` from the container and invoke it).

Spotify IDs (`spotify_id` columns/params) are base62 **strings** (`varchar(25)`), never cast to `int` — this was a real bug fixed during the clean-architecture rewrite (`SpotifyWebAPI` calls would fatal on non-numeric IDs otherwise).

### Frontend (`front/`) — Vite + React + TypeScript SPA

- `src/api/` — one module per backend feature (`auth.ts`, `rankings.ts`, `stock.ts`, `spotify.ts`, `aggregate.ts`) wrapping a shared `axios` instance (`client.ts`) configured for Sanctum's cookie-based SPA auth (`withXSRFToken`, `ensureCsrfCookie()` hits `/sanctum/csrf-cookie` before login).
- `src/auth/AuthContext.tsx` + `RequireAuth.tsx` — session state and route guarding; unauthenticated users are redirected to `/login`.
- `src/pages/` — one page per feature area (`RankingsPage`, `StockPage`, `SpotifyConnectPage`/`SpotifyCallbackPage`, `AggregatePage`, `LoginPage`).
- Dev server proxies `/api` and `/sanctum` to the `apache` container (see `vite.config.ts`) so the browser only ever talks to one origin — no CORS config needed in either dev or the production build.
- **Build output goes straight into `../public/`** (Laravel's `public/`) with `emptyOutDir: false`, so apache can serve the SPA and the API from the same `DocumentRoot`. Never set `emptyOutDir: true` — it would delete Laravel's `index.php`/`.htaccess`.

### Auth

Laravel Sanctum, SPA/cookie mode (not Basic Auth — that was removed along with `olssonm/l5-very-basic-auth`). `routes/api.php`: `POST /api/login` is public; everything else except `/api/webhook` (LINE, signature-verified) and `/api/spotify/callback` (OAuth redirect, hit directly by the browser outside any SPA session) sits behind `auth:sanctum`. One admin user is seeded by `database/seeders/AdminUserSeeder.php` (credentials via `ADMIN_EMAIL`/`ADMIN_PASSWORD` env vars). `SANCTUM_STATEFUL_DOMAINS` must include whatever host:port the browser actually uses (`localhost:1111` for the apache-served build, `localhost:5173` for the Vite dev server) — Sanctum's default domain list does **not** include either, so this must be set explicitly in `.env`.

### Spotify integration

- `config/spotify.php`: `CLIENT_ID`/`CLIENT_SECRET` from env, `REDIRECT_URI` defaults to `{APP_URL}/api/spotify/callback` (override via `SPOTIFY_REDIRECT_URI`; must match what's registered in the Spotify Developer Dashboard).
- Flow: React's `SpotifyConnectPage` calls `GET /api/spotify/authorize-url` (returns the Spotify authorize URL as JSON) and does `window.location.href = url`; Spotify redirects the browser to `GET /api/spotify/callback` (`SpotifyAuthController`, outside `auth:sanctum` since it's a top-level browser navigation, not an XHR), which exchanges the code and redirects to `{FRONTEND_URL}/spotify/callback?status=success|error`.
- Tokens are stored per user in the `token` table (`TokenRepositoryInterface`) and refreshed on-demand (`SpotifyApiClient::refreshAccessToken`, triggered by catching the "access token expired" exception).
- Everything is still single-user: `USER_ID = 1` is hardcoded in the relevant controllers/console commands.

### Aggregation flow

`POST /api/aggregate/artist` / `/api/aggregate/track` (also `command:AggregateArtist`/`command:AggregateTrack` from the CLI) run `Application\UseCases\Aggregation\AggregateArtistUseCase`/`AggregateTrackUseCase`: fetch top items from Spotify, transform via `AggregateService`, upsert artists/tracks/genres/genre-mappings, and record same-day-replaced snapshots into `top_artist`/`top_track`. Both Artist and Track flows are fully implemented and share the same shape (Track used to be an unfinished stub — it was completed, including a new `TrackRepositoryInterface`, during the clean-architecture rewrite).

### LINE bot

`POST /api/webhook` → `LineWebhookController` (outside `auth:sanctum` — verifies the LINE signature itself via `LineMessagingClientInterface`). Replies based on keyword matching on the incoming text (`おすすめ：`, `新着`, `注目プレイリスト`, `help` — see the `*_SEARCH_WORD` constants); recommendation replies pull from the `top_artist`/`top_track` tables. `config/my-app.php` holds `LINE_ACCESS_TOKEN`/`LINE_CHANNEL_SECRET`.

### Data model

Core tables (see `database/migrations`): `artist`, `album`, `track`, `artist_genre`, `artist_genre_map` (many-to-many), `top_artist`, `top_track` (dated rankings, one snapshot per day — deletes+replaces same-day rows), `token` (per-user Spotify tokens), `my_album` (stock list), `users`/`personal_access_tokens` (Sanctum). `feature_track` exists in migrations but is currently unused (its console command was legacy/broken and was removed rather than fixed — no code populates or reads it today).

### Testing

`tests/Feature/AuthTest.php` covers the Sanctum login/logout/unauthenticated flow — use it as the template for new feature tests (needs `$this->withHeader('Origin', ...)`, set globally in `tests/TestCase.php`, since Sanctum only attaches session middleware to requests it recognizes as coming from the frontend). **phpunit runs against an isolated in-memory SQLite DB** (`phpunit.xml`'s `DB_CONNECTION`/`DB_DATABASE` overrides) — this is deliberate, `RefreshDatabase` previously wiped the shared dev MySQL database when pointed at it.
