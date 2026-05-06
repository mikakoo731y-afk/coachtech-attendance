**勤怠管理システム (coachtech-attendance)**

**1. 環境構築の手順**
**Dockerビルド**

git clone https://github.com/mikakoo731y-afk/coachtech-attendance
cd coachtech-attendance
docker-compose up -d --build

コードは注意してご使用ください。

**Laravel環境構築**

docker-compose exec php bash
composer install
cp .env.example .env
chmod -R 777 storage bootstrap/cache

コードは注意してご使用ください。

**データベース構築**

php artisan key:generate
php artisan storage:link
php artisan migrate:fresh --seed

コードは注意してご使用ください。

**2. 開発環境URL**

管理者画面:　http://localhost/admin/login
ログイン画面: http://localhost/login
MailHog: http://localhost:8025

**3. 使用技術（実行環境）**
**言語・フレームワーク**
PHP: 8.2
Laravel: 10.x
Laravel Fortify: 認証機能（ログイン・登録）の実装に使用

**データベース・ミドルウェア**
MySQL: 8.0
Nginx: 最新版

**開発インフラ**
Docker / Docker Compose: 開発環境のコンテナ化

**4. 主要な実装機能**
タイムゾーン設定: アプリケーション・OSレベルで日本時間(JST)に対応
打刻機能: 出勤・退勤・休憩開始・休憩終了の記録
勤怠管理: 日別・利用者別の勤怠情報の表示
バリデーション: FormRequestによる入力チェック
テスト: PHPUnitによる機能テストの実装
データの自動生成: Factory / Seederによるテスト用ユーザーおよび勤怠データの生成
管理者	maill: admin@example.com	pass: password123	
一般　maill: user@example.com	pass: password123

**5. テストの実行**

docker-compose exec php php artisan test

**6.ER図**
```mermaid
erDiagram
    users ||--o{ attendances : "1対多"
    users ||--o{ attendance_correct_requests : "1対多"
    attendances ||--o{ rests : "1対多"
    attendances ||--o{ attendance_correct_requests : "1対多"
    attendance_correct_requests ||--o{ rest_correct_requests : "1対多"

    users {
        bigint id PK
        string name "氏名"
        string email "メールアドレス"
        string password "パスワード"
        tinyInteger role "0:一般, 1:管理者"
    }

    attendances {
        bigint id PK
        bigint user_id FK "ユーザーID"
        date date "勤務日"
        time clock_in "出勤時間"
        time clock_out "退勤時間"
    }

    rests {
        bigint id PK
        bigint attendance_id FK "勤怠ID"
        time start_time "休憩開始"
        time end_time "休憩終了"
    }

    attendance_correct_requests {
        bigint id PK
        bigint attendance_id FK "勤怠ID"
        bigint user_id FK "ユーザーID"
        time proposed_clock_in "修正後出勤"
        time proposed_clock_out "修正後退勤"
        text reason "修正理由"
        tinyInteger status "1:承認待ち, 2:承認済み"
    }

    rest_correct_requests {
        bigint id PK
        bigint attendance_correct_request_id FK "勤怠修正申請ID"
        time proposed_start_time "修正後休憩開始"
        time proposed_end_time "修正後休憩終了"
    }
```



