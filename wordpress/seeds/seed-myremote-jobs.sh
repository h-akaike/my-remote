#!/bin/sh
set -eu

cd "$HOME/www/my-remote"

wp post list --post_type=job --format=ids | xargs -r wp post delete --force

JOB1=$(wp post create \
  --post_type=job \
  --post_status=publish \
  --post_title="大手SaaS企業のカスタマーサポート（完全在宅）" \
  --post_name="saas-customer-support-remote" \
  --post_excerpt="ユーザーからのお問い合わせ対応や導入支援をお任せします。マニュアル完備で未経験の方でも安心です。" \
  --post_content="<p>ユーザーからのお問い合わせ対応、初期設定サポート、FAQ整備を担当する完全在宅のカスタマーサポート案件です。</p><h2>仕事内容</h2><ul><li>メール・チャットでのお問い合わせ対応</li><li>導入時のオンラインサポート</li><li>ナレッジベースの更新</li></ul><h2>歓迎条件</h2><p>カスタマーサポート経験、SaaS利用経験、丁寧な文章コミュニケーションができる方。</p>" \
  --porcelain)
wp post meta update "$JOB1" company_name "株式会社サンプルSaaS"
wp post meta update "$JOB1" hourly_rate "¥1,800 〜 ¥2,200"
wp post meta update "$JOB1" work_hours "週3日 / 1日4h〜"
wp post meta update "$JOB1" location "完全在宅"
wp post meta update "$JOB1" employment_type "業務委託"
wp post meta update "$JOB1" experience_level "未経験可"
wp post meta update "$JOB1" featured_label "新着"
wp post meta update "$JOB1" application_url "/apply.html"
wp post meta update "$JOB1" image_url "https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=80"
wp post term set "$JOB1" job_type customer-support
wp post term set "$JOB1" job_industry it-saas
wp post term set "$JOB1" work_style fully-remote contract

JOB2=$(wp post create \
  --post_type=job \
  --post_status=publish \
  --post_title="オンラインスクール運営アシスタント" \
  --post_name="online-school-assistant" \
  --post_excerpt="受講生対応、日程調整、資料チェックなどを担当する在宅アシスタント案件です。" \
  --post_content="<p>オンラインスクールの運営チームで、受講生サポートや講師との日程調整を担当します。</p><h2>仕事内容</h2><ul><li>受講生からの問い合わせ一次対応</li><li>講座スケジュール調整</li><li>教材・資料のチェック</li></ul>" \
  --porcelain)
wp post meta update "$JOB2" company_name "MyRemo Education Partner"
wp post meta update "$JOB2" hourly_rate "¥1,500 〜 ¥1,900"
wp post meta update "$JOB2" work_hours "週2日〜 / 平日日中中心"
wp post meta update "$JOB2" location "完全在宅"
wp post meta update "$JOB2" employment_type "業務委託"
wp post meta update "$JOB2" experience_level "事務経験歓迎"
wp post meta update "$JOB2" featured_label "急募"
wp post meta update "$JOB2" application_url "/apply.html"
wp post meta update "$JOB2" image_url "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80"
wp post term set "$JOB2" job_type assistant
wp post term set "$JOB2" job_industry education
wp post term set "$JOB2" work_style fully-remote contract

JOB3=$(wp post create \
  --post_type=job \
  --post_status=publish \
  --post_title="BtoBマーケティング記事の編集サポート" \
  --post_name="btob-marketing-editor" \
  --post_excerpt="記事構成の確認、校正、CMS入稿補助を行うリモート編集サポート案件です。" \
  --post_content="<p>BtoBメディアの記事制作チームで、構成確認や校正、CMS入稿補助を担当します。</p><h2>仕事内容</h2><ul><li>記事構成案のチェック</li><li>誤字脱字・表記ゆれの確認</li><li>WordPress入稿補助</li></ul>" \
  --porcelain)
wp post meta update "$JOB3" company_name "株式会社コンテンツワークス"
wp post meta update "$JOB3" hourly_rate "¥2,000 〜 ¥2,800"
wp post meta update "$JOB3" work_hours "週10時間〜"
wp post meta update "$JOB3" location "完全在宅"
wp post meta update "$JOB3" employment_type "業務委託"
wp post meta update "$JOB3" experience_level "編集経験者向け"
wp post meta update "$JOB3" featured_label "高単価"
wp post meta update "$JOB3" application_url "/apply.html"
wp post meta update "$JOB3" image_url "https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=900&q=80"
wp post term set "$JOB3" job_type marketing
wp post term set "$JOB3" job_industry it-saas
wp post term set "$JOB3" work_style fully-remote contract

printf "Created jobs: %s %s %s\n" "$JOB1" "$JOB2" "$JOB3"
