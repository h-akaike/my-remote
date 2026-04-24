# MyRemo WordPress CMS

WordPressはCMSとして使い、フロントエンドはNetlify側からREST APIで求人データを取得します。

## 管理方針

- スキーマ定義は `mu-plugins/myremote-cms.php` を正本にします。
- 求人などのコンテンツはWordPress標準エクスポートのXMLを `exports/` に保存します。
- WordPress本体、公式プラグイン、アップロード画像、DBダンプ、認証情報はGit管理しません。

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
