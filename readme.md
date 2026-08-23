# Spotiko

Spotifyの視聴データ（トップアーティスト/トラック、ジャンル）を集計するアプリ。クリーンアーキテクチャで構築したLaravel 13のJSON APIと、React（Vite + TypeScript）のSPAで構成し、LINEボットのwebhookにも返信する。

## 主な機能

- **トップランキング表示**: 集計済みのアーティスト/トラックランキングを閲覧
- **ストック管理**: Spotify検索からアルバムを登録・一覧表示
- **Spotify連携**: OAuthで自分のSpotifyアカウントと連携し、視聴データを取得
- **集計トリガー**: アーティスト/トラックの集計をSPAから手動実行（Artisanコマンドからも実行可）
- **LINEボット**: LINEからのメッセージにキーワードマッチでおすすめを返信

## 技術スタック

| レイヤー | 技術 |
|---|---|
| API | Laravel 13 (PHP ^8.3) / クリーンアーキテクチャ (Domain / Application / Infrastructure / Presentation) |
| 認証 | Laravel Sanctum（SPA・cookieベース） |
| フロントエンド | React 18 + Vite + TypeScript |
| DB | MySQL |
| 外部連携 | Spotify Web API (`jwilsson/spotify-web-api-php`), LINE Messaging API (`linecorp/line-bot-sdk`) |
| テスト | Pest（バックエンド）/ Jest + React Testing Library（フロントエンド） |
| 静的解析・整形 | Larastan (PHPStan), Laravel Pint, ESLint |
| 実行環境 | Docker Compose（`api` / `front` / `apache` / `db` の4コンテナ） |

## セットアップ

```bash
cd docker
docker compose up -d

# API
docker compose exec api composer install
docker compose exec api php artisan migrate --seed   # 管理者ユーザーを1件シード

# フロントエンド
docker compose exec front npm install
docker compose exec front npm run dev                # http://localhost:5173 でホットリロード
```

apache経由（`http://localhost:1111`）で本番ビルド相当のSPA + APIが単一オリジンで確認できる。`.env`には `SANCTUM_STATEFUL_DOMAINS` に `localhost:1111` / `localhost:5173` を含めておくこと。

Spotify連携を使う場合は `config/spotify.php` に対応する `SPOTIFY_CLIENT_ID` / `SPOTIFY_CLIENT_SECRET` を、LINEボットを使う場合は `LINE_ACCESS_TOKEN` / `LINE_CHANNEL_SECRET` を `.env` に設定する。

## よく使うコマンド

```bash
# API
docker compose exec api vendor/bin/pest              # テスト
docker compose exec api vendor/bin/phpstan analyse    # 静的解析
docker compose exec api vendor/bin/pint               # フォーマット

# フロントエンド
docker compose exec front npm run test                # テスト
docker compose exec front npm run lint                # Lint
docker compose exec front npm run build                # 型チェック + ビルド(../public/へ出力)
```

カスタムArtisanコマンド: `command:AggregateArtist` / `command:AggregateTrack`（集計処理。APIエンドポイントからも実行可）、`command:PushMessage`（LINEへのプッシュ送信）。

## ディレクトリ構成

```
app/
  Domain/           # Repositoryのinterface。フレームワーク非依存
  Application/       # UseCase・Service。Domainのinterfaceにのみ依存
  Infrastructure/     # Eloquentモデル・Repository実装、外部APIクライアント
  Presentation/       # Controller・Request・Resource
front/
  src/api/           # バックエンドAPIラッパー
  src/auth/           # 認証状態・ルートガード
  src/pages/          # 画面ごとのコンポーネント
docker/               # docker-compose.yml、各サービスのDockerfile
```

詳しいアーキテクチャや開発方針は [CLAUDE.md](./CLAUDE.md) を参照。

## テスト方針

バックエンドはPest、フロントエンドはJest + React Testing Libraryを使用。方針の詳細は [`.claude/rules/backend-testing.md`](./.claude/rules/backend-testing.md) / [`.claude/rules/frontend-testing.md`](./.claude/rules/frontend-testing.md) を参照。
