Resource Usage Tracker

■ ローカルへの開発環境の構築

必要環境
- PHP 8.3.7
- Composer 2.8.5
- CodeIgniter 4 フレームワーク

構築手順
1. PHPのインストール
   - 公式サイト: https://www.php.net/downloads から PHP 8.3.7 をダウンロードしてインストール。
   - インストール後、環境変数に `C:\php\php8.3.7` を追加。
   - インストール確認:
   ```
   php -v
   ```
   予期される出力:
   ```
   PHP 8.3.7 (cli) (built: May  8 2024 08:56:56) (ZTS Visual C++ 2019 x64)
   ```

2. Composerのインストール
   - 公式サイト: https://getcomposer.org/download/ から Composer をダウンロードし、インストール。
   - インストール後、環境変数に `C:\ProgramData\ComposerSetup\bin` を追加。
   - インストール確認:
   ```
   composer -V
   ```
   予期される出力:
   ```
   Composer version 2.8.5 2025-01-21 15:23:40
   ```

3. プロジェクトのインストールとアプリ構築
   ```
   cd C:\Users\yoshifumi_nishi\work\DeviceUsageTracker
   composer install
   ```
   - これにより `composer.json` に基づき、必要なライブラリがインストールされます。

4. データベースのマイグレーションを実行する前に、テスト用データベースを作成する必要があります。
   - MySQL または SQLite などのデータベースを設定し、以下のSQLを実行してテスト用データベースを作成。
   ```sql
   CREATE DATABASE resource_usage_tracker_test;
   ```
   - `.env` ファイルが存在しない場合は、`env` ファイルをコピーして作成。
   ```
   copy env .env
   ```
   - `.env` ファイルを編集し、データベース接続情報を設定。

5. データベースのマイグレーション実行
   ```
   php spark migrate
   ```
   - これにより、データベーススキーマが最新の状態に更新されます。

6. サーバー起動 (ローカル開発用)
   ```
   php spark serve
   ```
   - デフォルトでは `http://localhost:8080` でアクセス可能。


■ 改修PGを本番環境（保守VM1）へ適用する方法

1. 管理者権限で PowerShell を起動
2. 以下のコマンドを実行
   ```
   cd ..\..\work\ResourceUsageTracker\
   git pull origin main
   copy env .env
   php spark migrate
   httpd -k restart
   ```
   これにより最新のコードが適用され、`.env` ファイルが作成され、データベースのマイグレーションが実行され、Apache が再起動されます。

