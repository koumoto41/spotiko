# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Spotiko: a Laravel 8 (PHP ^8.0) app that aggregates a user's Spotify listening data (top artists/tracks, genres) via the Spotify Web API, exposes rankings through a CMS-style web UI (AdminLTE), and pushes/replies to users over a LINE bot webhook.

## Commands

Run via Docker (see `docker/docker-compose.yml`, services `spotiko_app` / `spotiko_web` / `spotiko_mysql`), or locally if PHP/Composer/Node are installed.

```bash
composer install
cp .env.example .env && php artisan key:generate

php artisan serve                 # dev server
php artisan migrate                # run migrations (database/migrations)

npm install
npm run dev                        # webpack build (dev)
npm run watch                      # rebuild on change
npm run prod                       # production build

vendor/bin/phpunit                          # run full test suite
vendor/bin/phpunit --testsuite=Unit         # Unit only
vendor/bin/phpunit --testsuite=Feature      # Feature only
vendor/bin/phpunit --filter=testMethodName  # single test

# Custom artisan commands (App\Console\Commands)
php artisan command:AggregateArtist   # aggregate top artists from Spotify
php artisan command:AggregateTrack    # aggregate top tracks from Spotify (via routes/console, no signature set explicitly beyond class)
php artisan command:PushMessage       # push a LINE message to hardcoded LINE_USER_ID list
```

There is no linter/formatter configured beyond `.editorconfig`.

## Architecture

Layering, in order of preference for new code — **Controller → UseCase/Service → Repository → Eloquent Model**:

- `app/Http/Controllers` — thin controllers. CMS-facing ones live under `Controllers/Cms`, JSON API ones under `Controllers/Api`.
- `app/UseCases` — single-action classes (`__invoke`) that orchestrate one flow, e.g. `App\UseCases\Aggregate\Artist`/`Track` (called from `AggregateController`), `GetSpotifyAccessToken`, `GetSpotifyAuthorizationCode`.
- `app/Services` — reusable business logic shared across use cases/controllers, e.g. `SpotifyApiService` (wraps `jwilsson/spotify-web-api-php`, handles token refresh), `AggregateService` (turns raw Spotify API payloads into arrays for persistence), `Cms\StockService`, `TableService`.
- `app/Repositories` — one per Eloquent model, DB access only (`ArtistRepository`, `AlbumRepository`, `TokenRepository`, `TopArtistRepository`, `TopTrackRepository`, `ArtistGenreRepository`, `ArtistGenreMapRepository`, `MyAlbumRepository`).
- `app/Models` — Eloquent models, kept thin under the new pattern.

**The codebase is mid-migration and both styles coexist** — check which layer you're touching before copying a pattern from a neighboring file:
- New/refactored code: constructor property promotion, typed params/returns, camelCase methods, DI via `App\Services\*Service` and `App\Repositories\*Repository`. See `TokenController`, `AggregateController`, `UseCases/Aggregate/*`, `Services/SpotifyApiService`, `Repositories/ArtistRepository`.
- Legacy (not-yet-migrated) code: `App\Library\SpotifyApi` / `App\Library\LineMessageApi`, snake_case methods, business logic embedded directly in Eloquent models (e.g. `App\Models\Artist::set_artist_array`, `insert_update_artist`), commands/controllers instantiating models directly (`new Models\Artist()`) instead of via DI. See `LineWebhookController`, `Console/Commands/aggregate/AggregateArtist`, `Console/Commands/line/PushMeaasge`.
- When extending existing legacy code, matching its existing style is fine; when adding genuinely new functionality, follow the new (Service/Repository) pattern instead of `App\Library`.
- Some in-progress files are inconsistent/broken (e.g. `App\Services\AggregateService::convertMyTopTrackToArrayBySpotifyApi` and `App\UseCases\Aggregate\Track` reference undefined variables/models — this is unfinished migration work, not a regression to silently "fix" by guessing intent without checking with the user first).

### Spotify integration

- `config/spotify.php` holds `CLIENT_ID`/`CLIENT_SECRET`/`REDIRECT_URI`/`scope`, sourced from `SPOTIFY_CLIENT_ID`/`SPOTIFY_CLIENT_SECRET` env vars.
- Auth flow: `GET /token` (`TokenController`, guarded by `auth.very_basic`) — no `code` param redirects to Spotify's authorize URL; with a `code`, exchanges it for tokens via `SpotifyApiService::getAccessToken`.
- Tokens are stored per user in the `token` table via `TokenRepository`/`Token` model, and refreshed on-demand (`SpotifyApiService::refreshAccessToken`, triggered by catching the "access token expired" exception from `jwilsson/spotify-web-api-php`).
- All current flows use a single hardcoded user, `USER_ID = 1` (see constants in `TokenController`, `AggregateController`, `LineWebhookController`, `Console/Commands/aggregate/*`) — this is not a multi-tenant app today.

### Aggregation flow

`GET /aggregate/artist` and `/aggregate/track` (both behind `auth.very_basic`) trigger `UseCases\Aggregate\Artist`/`Track`, which: fetch top items from Spotify (`SpotifyApiService::getMyTop`), transform them (`AggregateService`), upsert artists/genres/genre-mappings (`*Repository::upsert*`), and record ranked results into `top_artist`/`top_track` tables. The equivalent legacy path is the `command:AggregateArtist` console command.

### LINE bot

`POST /api/webhook` → `LineWebhookController::webhook`, using `linecorp/line-bot-sdk` via `App\Library\LineMessageApi`. Verifies the LINE signature, parses events, and replies based on keyword matching on the incoming text (`おすすめ：`, `新着`, `注目プレイリスト`, `help`, etc. — see the `*_SEARCH_WORD` constants). Recommendation replies pull from the `top_artist`/`top_track` tables populated by the aggregation flow above. `config/my-app.php` holds `LINE_ACCESS_TOKEN`/`LINE_CHANNEL_SECRET` (from env).

### CMS

`Controllers/Cms/TopController` renders the top artist/track rankings (`resources/views/top.blade.php`). `Controllers/Cms/StockController` + `Services/Cms/StockService` manage a personal album "stock" list, backed by `my_album`/`album` tables, with search against both the local DB and the Spotify API directly (`searchAlbumFromSpotify`). CMS UI is built on `jeroennoten/laravel-adminlte`.

### Auth

Everything under the main `web` route group (`/`, `/token`, `/aggregate/*`, `/cms*`) is protected by the `auth.very_basic` middleware (`olssonm/l5-very-basic-auth`), configured in `config/very_basic_auth.php`, active only in the `production` env. This is HTTP Basic Auth guarding the whole app, separate from Laravel's normal `auth`/session-based login scaffolding (which also exists — `Http/Controllers/Auth/*` — but isn't wired into any active routes in `routes/web.php`).

### Data model

Core tables (see `database/migrations`): `artist`, `album`, `track`, `artist_genre`, `artist_genre_map` (many-to-many), `top_artist`, `top_track` (dated rankings, one snapshot per day — see `AggregateService::decideTopArtist`, which deletes+replaces same-day rows), `token` (per-user Spotify tokens), `my_album` (CMS stock list), `feature_track`.
