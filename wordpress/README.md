# MyRemo WordPress CMS

WordPressはCMSとして使い、フロントエンドはNetlify側からREST APIで求人データを取得します。

このREADMEをMyRemoのWordPress/求人管理の正本にします。運用ルールや入力項目を変えた場合は、先にこのREADMEを更新してから実装・本番反映します。

## 公開・確認URL

- テスト/本番フロントURL: https://my-remote-musubi.netlify.app
- 求人一覧: https://my-remote-musubi.netlify.app/jobs.html
- 求人詳細: `https://my-remote-musubi.netlify.app/recruit.html?job=<WordPressの求人スラッグ>`
- 応募フォーム: `https://my-remote-musubi.netlify.app/apply.html?job=<WordPressの求人スラッグ>`

## 管理方針

- スキーマ定義は `mu-plugins/myremote-cms.php` を正本にします。
- 求人などのコンテンツはWordPress標準エクスポートのXMLを `exports/` に保存します。
- WordPress本体、公式プラグイン、アップロード画像、DBダンプ、認証情報はGit管理しません。

## 管理画面・サーバー

### さくらレンタルサーバー

- コントロールパネルURL: https://secure.sakura.ad.jp/rs/cp/
- アカウントID: `enn-musubi.sakura.ne.jp`
- パスワード: Git管理しない。ローカル専用の `wordpress/access.local.md` を参照する。

### WordPress

- 管理画面URL: https://enn-musubi.sakura.ne.jp/my-remote/wp-login.php
- ユーザーID: `my-remote`
- パスワード: Git管理しない。ローカル専用の `wordpress/access.local.md` を参照する。

## 会員・応募API

会員はWordPress標準ユーザーDBに `applicant` ロールで保存し、応募は `application` カスタム投稿タイプに保存します。

- `POST /wp-json/myremote/v1/register`: 会員登録
- `POST /wp-json/myremote/v1/login`: ログイン
- `POST /wp-json/myremote/v1/password-reset`: パスワード再設定メール送信
- `GET /wp-json/myremote/v1/me`: ログイン中ユーザー取得
- `POST /wp-json/myremote/v1/applications`: 応募作成、履歴書アップロード、応募者/管理者への通知メール
- `POST /wp-json/myremote/v1/contact`: お問い合わせ送信

Netlify側は `assets/js/myremote-auth.js` からBearerトークンでこれらのAPIを呼び出します。

## 通知メール設定

WordPress管理画面の `設定 > MyRemo設定` で応募・お問い合わせ通知の送信先を設定できます。

未設定時はWordPressの管理者メールアドレスを使います。リリース時はここを info@ などの本番運用アドレスへ切り替えてください。

## 求人管理

求人はWordPress管理画面の `求人` カスタム投稿タイプで管理します。

入力は固定フォーム形式です。本文ブロックの自由入力に依存せず、以下の項目を埋めると、求人一覧と求人詳細ページに決まったレイアウトで反映されます。

### 基本項目

- タイトル: 求人名
- 抜粋: 求人一覧の短い説明。`求人概要` が未入力の場合の代替表示
- アイキャッチ画像: WordPress側の代表画像
- 職種: `職種` タクソノミー
- 業界: `業界` タクソノミー
- 働き方: `働き方` タクソノミー

### 求人情報フォーム

- 会社名
- 報酬
- 稼働時間
- 勤務地
- 契約形態
- 経験条件
- ラベル
- 応募URL
- 画像URL
- 求人概要
- 開始時期
- 契約期間
- 募集人数
- 面談回数
- 業務内容
- 必須スキル
- 歓迎スキル
- この案件の特徴
- 選考フロー
- 補足メモ

複数行入力の項目は、1行1項目として扱います。求人詳細ページでは、固定テンプレート内の箇条書きやカードに変換して表示します。

### フロント反映

- `jobs.html`: WordPress REST APIの `/wp-json/wp/v2/jobs` から求人一覧を取得
- `recruit.html`: URLの `job` パラメータに指定された求人スラッグから詳細を取得
- `apply.html`: URLの `job` パラメータを応募データに紐づけ

求人詳細ページは固定テンプレートです。WordPress側で自由なページ構成は作れませんが、入力フォームの項目を埋めれば同じ見た目で表示できます。

## コラム管理

`コラム` カスタム投稿タイプで記事を管理します。

- アイキャッチ画像: 一覧・詳細で使う代表画像
- 本文内メディア: ACFのギャラリーフィールドで本文用画像を管理
- コラムカテゴリ: コラム一覧の分類用

コラムも求人と同じく固定フォーム形式で管理します。本文ブロックの自由入力には依存せず、以下の項目を埋めると、コラム一覧と詳細ページに決まったレイアウトで反映されます。

### コラム情報フォーム

- リード文
- アイキャッチ画像URL
- 本文内メディア
- 本文内画像URL
- 本文内画像キャプション
- セクション1見出し
- セクション1本文
- セクション2見出し
- セクション2本文
- セクション3見出し
- セクション3本文
- チェックリスト
- CTAラベル
- CTA URL

### フロント反映

- `columns.html`: WordPress REST APIの `/wp-json/wp/v2/columns` からコラム一覧を取得
- `column-detail.html`: URLの `column` パラメータに指定されたコラムスラッグから詳細を取得

コラム詳細ページは固定テンプレートです。WordPress側で自由なページ構成は作れませんが、セクション見出し・本文・チェックリスト・画像・CTAを入力すれば同じ見た目で表示できます。

### テストデータ

表示確認用に、WordPress本番CMSへ以下の公開コラムを投入しています。

- `remote-work-first-week`: 在宅ワーク初週で整える、仕事が進む環境づくり
- `choose-remote-job`: 自分に合う在宅求人を見分ける3つのチェックポイント
- `application-profile-tips`: 応募前に整えたいプロフィールと職務メモの作り方

既存のサンプル求人は、求人一覧・求人詳細の表示確認用として公開状態で残しています。

## 本番への配置

```sh
scp wordpress/mu-plugins/myremote-cms.php \
  <ssh-user>@<host>:/home/<ssh-user>/www/my-remote/wp-content/mu-plugins/myremote-cms.php
```

配置後に必要ならパーマリンクを再生成します。

```sh
ssh <ssh-user>@<host>
cd ~/www/my-remote
wp rewrite flush
```

## コンテンツのエクスポート

```sh
ssh <ssh-user>@<host>
cd ~/www/my-remote
wp export --post_type=job --dir=$HOME --filename_format=myremote-jobs.xml
```

取得したXMLを `wordpress/exports/myremote-jobs.xml` に保存します。

## サンプル求人の再投入

注意: 既存の求人投稿を削除してからサンプル3件を作ります。

```sh
ssh <ssh-user>@<host>
sh ~/seed-myremote-jobs.sh
```
