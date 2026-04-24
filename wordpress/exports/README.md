# WordPress Exports

WordPress管理画面の「ツール > エクスポート」またはWP-CLIで出力したWXR/XMLを保存します。

基本方針:

- `myremote-jobs.xml`: 求人投稿タイプのエクスポート
- 投稿タイプやフィールド定義は `../mu-plugins/myremote-cms.php` で管理
- XMLはコンテンツ復元用で、WordPress本体やプラグインの代替ではありません
