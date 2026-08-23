# フロントエンドのテスト方針(React / front/)

テストランナーは **Jest**(`ts-jest` でTypeScript/JSXをトランスパイル)+ **React Testing Library**。設定は `front/jest.config.cjs`(トランスパイル用の型設定は `front/tsconfig.jest.json` に分離。メインの `tsconfig.json` はVite向けのESM設定のため)。

## 対象範囲

- `src/auth/AuthContext.tsx` — ログイン/ログアウトの状態遷移、`useAuth()` がProvider外で呼ばれた際に例外を投げること
- `src/api/client.ts` — CSRF/axios設定(`withCredentials`, `withXSRFToken` など)、`ensureCsrfCookie()` の呼び出し
- `src/auth/RequireAuth.tsx` — ローディング中/未ログイン時のリダイレクト/ログイン時の描画の3分岐

個別ページ(`src/pages/*`)の見た目レベルのテストはROIが低いため対象外とする。フォーム送信など重要な操作フローが増えたら都度判断する。

## モック方針

- `src/api/*.ts` の関数は `jest.mock('../api/auth')` のようにモジュール単位でモックする(`AuthContext.test.tsx` を参照)
- Contextを直接差し替えられない場合(`AuthContext` 本体を export していない等)は、消費側のフック(`useAuth`)を `jest.mock('./AuthContext')` でモックする(`RequireAuth.test.tsx` を参照)
- CSS import は `jest.config.cjs` の `moduleNameMapper` で `identity-obj-proxy` に差し替え済み

## ファイル配置

- ソースと同階層に `*.test.tsx` / `*.test.ts` を置く(colocation)
- グローバルセットアップ(`@testing-library/jest-dom` の読み込み)は `src/setupTests.ts`
- `it()`の説明文字列は日本語で書く。`describe()`は対象のモジュール/コンポーネント名(英語識別子)のまま可

## 実行コマンド

```bash
docker compose exec front npm run test
docker compose exec front npm run lint
docker compose exec front npm run build  # 型チェック含む
```
