# プロジェクト

このプロジェクトは [Symfony Docker](https://github.com/dunglas/symfony-docker) で生成された、[FrankenPHP](https://frankenphp.dev) 上で動作する Symfony アプリケーションです。

スタックには以下が含まれます。

- Caddy（FrankenPHP 経由）
- リアルタイム通信用の [Mercure](https://mercure.rocks)
- プリロード用の [Vulcain](https://vulcain.rocks)

Dockerfile は開発用（dev）と本番用（prod）を分けたマルチステージビルド構成です。

## Dev Container 環境

このプロジェクトは Dev Container 内で動作し、外向き通信は明示的に許可されたドメイン以外を遮断するファイアウォール設定になっています。

## ドメインの許可リスト追加

外向き通信が失敗する場合（例: `curl`、`composer require`、新しいレジストリへの `npm install`）は、そのドメインをファイアウォールの許可リストに追加する必要がある可能性があります。

`.devcontainer/init-firewall.sh` を編集し、dnsmasq 設定ブロック内の `ipset=` 行に対象ドメインを追加してください。

```bash
ipset=/github.com/anthropic.com/.../NEW_DOMAIN.COM/allowed-domains
```

変更を反映するには Dev Container を再ビルドしてください。
