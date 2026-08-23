# バックエンドのテスト方針(Laravel / Pest)

テストランナーは **Pest**(`pestphp/pest` + `pestphp/pest-plugin-laravel`)。`tests/Pest.php` で `Feature` 配下に `Tests\TestCase` + `RefreshDatabase` をバインドしている。`Unit` 配下は素の状態(フレームワーク非起動、DBアクセスなし)。

## レイヤーとテスト種別の対応

| レイヤー | テスト種別 | 手法 |
|---|---|---|
| `app/Domain/` | なし | interfaceのみでロジックが無いため対象外 |
| `app/Application/`(UseCase・Service) | **Unit** | Domain interfaceをMockeryでモック、DB不要。`tests/Unit/Application/...` に `app/Application/...` と同じ構成でミラー配置 |
| `app/Infrastructure/Persistence/Eloquent/Repositories/` | **Unit(DB統合)** | インメモリSQLiteに対して実行し、join/upsertが正しいSQLを生成するか検証 |
| `app/Presentation/Http/Controllers/Api/` | **Feature** | HTTPリクエスト〜レスポンスの一気通貫。`RefreshDatabase` + インメモリSQLite。`tests/Feature/AuthTest.php` を雛形とする |

## 優先順位

1. クリーンアーキテクチャへの書き換え時にバグを埋めた/新規に完成させた箇所を最優先で厚めにする — `AggregateService`(Artist/Track変換ロジック)、`StockService::registerMyAlbum`、`TrackRepository`、Spotify ID の string型まわり
2. 認証(Sanctum)は `AuthTest` が骨格。バリデーションエラー時のレスポンス形など不足分を追加
3. 各Controllerの正常系 + 主要な異常系(未認証401、バリデーション422)
4. 些末なgetter/setterやDTOはテストしない

## Mockeryの使い方(重要な制約)

`Application/Services/AggregateService` は `final class` なので **Mockeryで直接モックできない**(finalクラスはproxyサブクラスを作れない)。この種のクラスをUseCaseテストで使う場合は、モックするのではなく、**Domain interfaceだけをモックし、実体のServiceインスタンスを組み立てて注入する**(真の境界はDomain interfaceに置く)。`tests/Unit/Application/UseCases/Aggregation/AggregateArtistUseCaseTest.php` を参照。

## 命名・スタイル

- Pestの関数スタイル(`test('...', function () { ... })` / `it('...', function () { ... })`)を使う。PHPUnitクラス形式は使わない
- テスト名(`test()`/`it()`の説明文字列)は日本語で書く。何を検証しているかが一目でわかることを優先する
- 1テスト = 1つのふるまいの検証。`expect()->toBe()` などPestのアサーション、または `$this->getJson()->assertOk()` などLaravelのHTTPテストヘルパーを使う
- UnitテストでのDomain interfaceモックはコンストラクタ注入で `Mockery::mock(XxxRepositoryInterface::class)`
- Featureテストで外部API(Spotify)を呼ぶ経路に当たる場合は、`$this->app->instance(SpotifyClientInterface::class, $fake)` でコンテナごと差し替え、実際のネットワーク呼び出しを発生させない
- `phpunit.xml` はインメモリSQLite(`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)を使う設定になっている。共有の開発用MySQLに向けないこと(過去に `RefreshDatabase` がdev DBのシードデータを消した事故がある)

## 実行コマンド

```bash
docker compose exec api vendor/bin/pest
docker compose exec api vendor/bin/pest --filter="テスト名の一部"
docker compose exec api vendor/bin/phpstan analyse   # テストと合わせて型/未定義変数もチェック(tests/は対象外)
docker compose exec api vendor/bin/pint              # フォーマット
composer test                                        # config:clear してから pest を実行
```
