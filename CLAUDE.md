# CLAUDE.md

このファイルは、このリポジトリで作業する Claude Code (claude.ai/code) 向けのガイダンスを提供する。

## 概要

Spotiko: Spotifyの視聴データ(トップアーティスト/トラック、ジャンル)を集計するアプリ。クリーンアーキテクチャで構築した Laravel 13(PHP ^8.3)のJSON API と、React(Vite + TypeScript)のSPAに分かれている。LINEボットのwebhookからのメッセージにも返信する。

## コマンド

Docker経由で実行する(`docker/docker-compose.yml`、サービスは `spotiko_api`(php-fpm)/ `spotiko_front`(Vite dev server)/ `spotiko_apache`(webサーバー、ポート1111)/ `spotiko_db`(mysql)):

```bash
cd docker && docker compose up -d

# api
docker compose exec api composer install
docker compose exec api php artisan migrate --seed   # 管理者ユーザーを1件シード(AdminUserSeeder参照)
docker compose exec api vendor/bin/phpunit
docker compose exec api vendor/bin/phpstan analyse    # larastan, level 5, phpstan.neon参照
docker compose exec api vendor/bin/pint               # PHPコードフォーマット(pint.json: laravelプリセット)

# front — :5173でホットリロード、/api と /sanctum を apache へプロキシ
docker compose exec front npm run dev
docker compose exec front npm run build                # 型チェック後、../public/ へビルド
docker compose exec front npm run lint                 # ESLint(front/eslint.config.js)
```

カスタムartisanコマンド(`app/Console/Commands`): `command:AggregateArtist`, `command:AggregateTrack`(下記APIからも実行可能)、`command:PushMessage`(LINEへハードコードされたユーザーリストにプッシュ送信)。

コミットメッセージには Conventional Commits の prefix(`feat:`, `fix:`, `chore:`, `refactor:` など)を付けること。

## アーキテクチャ

### バックエンド(`app/`)— クリーンアーキテクチャ、層ごとにディレクトリを分離

```
Domain/<Feature>/Repositories/*Interface.php   # 契約(interface)のみ。Eloquent/Laravelへの依存なし
Application/Services/*, Application/UseCases/<Feature>/*Aggregation*Case.php   # オーケストレーション。Domainのinterfaceにのみ依存
Infrastructure/Persistence/Eloquent/Models/*   # Eloquentモデル — スキーマ定義のみ、ビジネスロジックなし
Infrastructure/Persistence/Eloquent/Repositories/*   # Domain interfaceの実装
Infrastructure/ExternalServices/Spotify/SpotifyApiClient.php   # Domain\Spotify\SpotifyClientInterface を実装、jwilsson/spotify-web-api-php をラップ
Infrastructure/ExternalServices/Line/LineMessagingClient.php   # Domain\Line\LineMessagingClientInterface を実装、linecorp/line-bot-sdk をラップ
Presentation/Http/Controllers/Api/*   # 薄いController: バリデーション → UseCase/Serviceを呼ぶ → JSONを返す
Presentation/Http/Requests/*, Presentation/Http/Resources/*
Providers/RepositoryServiceProvider.php   # 全てのDomain interfaceをInfrastructure実装にバインド(config/app.phpに登録)
```

`<Feature>` = Artist, Track, Album, MyAlbum, ArtistGenre, ArtistGenreMap, TopArtist, TopTrack, Token。どのRepositoryも同じパターン(`Domain`にinterface1つ、`Infrastructure`にEloquentベースの実装1つ)に従っているので、新規追加時は `ArtistRepositoryInterface`/`Infrastructure/.../ArtistRepository` をテンプレートにすればよい。

**フレームワークの慣習的なパスはそのまま** — `app/Console/Commands`, `app/Http/Kernel.php`, `app/Http/Middleware`, `app/Providers`, `app/Exceptions` はLaravelの標準的な配置のままで、Domain/Application/Infrastructure/Presentation の分割対象外(Consoleコマンドはコンテナから `Application\UseCases\*` を解決して呼び出すだけの薄いラッパー)。

Spotify ID(`spotify_id` のカラム/パラメータ)はbase62の**文字列**(`varchar(25)`)であり、`int` にキャストしてはいけない — これはクリーンアーキテクチャへの書き換え中に見つけて直した実バグ(`int`のままだと `SpotifyWebAPI` の呼び出しが数値でないIDで必ず落ちる)。

### フロントエンド(`front/`)— Vite + React + TypeScript の SPA

- `src/api/` — バックエンドの機能ごとに1モジュール(`auth.ts`, `rankings.ts`, `stock.ts`, `spotify.ts`, `aggregate.ts`)。Sanctumのcookieベース認証用に設定した共有の `axios` インスタンス(`client.ts`)をラップしている(`withXSRFToken`、ログイン前に `ensureCsrfCookie()` が `/sanctum/csrf-cookie` を叩く)。
- `src/auth/AuthContext.tsx` + `RequireAuth.tsx` — セッション状態とルートガード。未ログイン時は `/login` へリダイレクト。
- `src/pages/` — 機能領域ごとに1ページ(`RankingsPage`, `StockPage`, `SpotifyConnectPage`/`SpotifyCallbackPage`, `AggregatePage`, `LoginPage`)。
- Dev serverは `/api` と `/sanctum` を `apache` コンテナへプロキシする(`vite.config.ts` 参照)ので、ブラウザは常に単一オリジンとしか通信しない — dev・本番ビルドどちらもCORS設定は不要。
- **ビルド出力は `../public/`(Laravelの`public/`)に直接出力**し、`emptyOutDir: false` にしている。これによりapacheが単一の `DocumentRoot` でSPAとAPIの両方を配信できる。`emptyOutDir: true` には絶対にしないこと — Laravelの `index.php`/`.htaccess` が消えてしまう。

### 認証

Laravel Sanctum、SPA/cookieモード(Basic認証ではない — `olssonm/l5-very-basic-auth` と共に撤去済み)。`routes/api.php`: `POST /api/login` は公開エンドポイント。`/api/webhook`(LINE、署名検証で保護)と `/api/spotify/callback`(OAuthリダイレクト、SPAセッション外でブラウザから直接叩かれる)を除く全てが `auth:sanctum` の配下。管理者ユーザーは `database/seeders/AdminUserSeeder.php` で1件シードされる(認証情報は `ADMIN_EMAIL`/`ADMIN_PASSWORD` 環境変数)。`SANCTUM_STATEFUL_DOMAINS` にはブラウザが実際に使うhost:portを含める必要がある(apache経由のビルドなら `localhost:1111`、Vite dev serverなら `localhost:5173`)— Sanctumのデフォルトのドメインリストにはどちらも**含まれていない**ので、`.env` で明示的に設定すること。

### Spotify連携

- `config/spotify.php`: `CLIENT_ID`/`CLIENT_SECRET` は環境変数から。`REDIRECT_URI` はデフォルトで `{APP_URL}/api/spotify/callback`(`SPOTIFY_REDIRECT_URI` で上書き可能。Spotify Developer Dashboard側の登録値と一致させること)。
- フロー: Reactの `SpotifyConnectPage` が `GET /api/spotify/authorize-url` を呼ぶ(認可URLをJSONで返す)→ `window.location.href = url` で遷移。Spotifyがブラウザを `GET /api/spotify/callback`(`SpotifyAuthController`。XHRではなくブラウザのトップレベル遷移なので `auth:sanctum` の対象外)へリダイレクトし、コードを交換した後 `{FRONTEND_URL}/spotify/callback?status=success|error` へリダイレクトする。
- トークンはユーザーごとに `token` テーブル(`TokenRepositoryInterface`)に保存され、必要に応じて更新される(`SpotifyApiClient::refreshAccessToken`。「access token expired」例外をキャッチして発火)。
- 現状は全てシングルユーザー: 関連するController/Consoleコマンドに `USER_ID = 1` がハードコードされている。

### 集計フロー

`POST /api/aggregate/artist` / `/api/aggregate/track`(CLIからは `command:AggregateArtist`/`command:AggregateTrack` でも実行可能)が `Application\UseCases\Aggregation\AggregateArtistUseCase`/`AggregateTrackUseCase` を実行する: Spotifyからトップアイテムを取得 → `AggregateService` で変換 → アーティスト/トラック/ジャンル/ジャンルマッピングをupsert → 同日分を置き換える形で `top_artist`/`top_track` にスナップショットを記録。ArtistとTrack両方のフローが完全に実装済みで同じ形をしている(Track側はかつて未完成のスタブだったが、クリーンアーキテクチャへの書き換え時に新しい `TrackRepositoryInterface` を含めて完成させた)。

### LINEボット

`POST /api/webhook` → `LineWebhookController`(`auth:sanctum` の対象外 — `LineMessagingClientInterface` 経由で自前でLINEの署名検証を行う)。受信テキストのキーワードマッチで返信内容を決める(`おすすめ：`, `新着`, `注目プレイリスト`, `help` — `*_SEARCH_WORD` の定数を参照)。おすすめ返信は `top_artist`/`top_track` テーブルから取得する。`config/my-app.php` に `LINE_ACCESS_TOKEN`/`LINE_CHANNEL_SECRET` を保持。

### データモデル

主要テーブル(`database/migrations` 参照): `artist`, `album`, `track`, `artist_genre`, `artist_genre_map`(多対多)、`top_artist`, `top_track`(日付付きランキング。1日1スナップショットで、同日分は削除して置き換える)、`token`(ユーザーごとのSpotifyトークン)、`my_album`(ストックリスト)、`users`/`personal_access_tokens`(Sanctum)。`feature_track` はマイグレーションには存在するが現在未使用(対応するConsoleコマンドがレガシー・壊れていたため、直すのではなく削除した — 現状これを書き込む/読み込むコードは無い)。

### テスト

`tests/Feature/AuthTest.php` がSanctumのログイン/ログアウト/未認証時のフローをカバーしている — 新しいFeatureテストを書く際のテンプレートとして使うこと(`$this->withHeader('Origin', ...)` が必要。これは `tests/TestCase.php` でグローバルに設定済み。Sanctumはフロントエンドからのリクエストと認識したものにしかセッションミドルウェアを付与しないため)。**phpunitは分離されたインメモリSQLite DBに対して実行される**(`phpunit.xml` の `DB_CONNECTION`/`DB_DATABASE` の上書き設定)— これは意図的な設定で、以前は `RefreshDatabase` が共有の開発用MySQL DBに向いていて、実行するたびにデータを消していた。
