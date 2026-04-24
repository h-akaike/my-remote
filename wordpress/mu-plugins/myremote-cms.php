<?php
/**
 * Plugin Name: MyRemo CMS
 * Description: Registers MyRemo job content types, taxonomies, fields, members, applications, and REST metadata.
 * Version: 0.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

const MYREMOTE_TOKEN_HASH_META = '_myremote_auth_token_hash';
const MYREMOTE_TOKEN_EXPIRES_META = '_myremote_auth_token_expires';

add_action('init', function () {
    add_role('applicant', '応募者', [
        'read' => true,
    ]);

    register_post_type('job', [
        'labels' => [
            'name' => '求人',
            'singular_name' => '求人',
            'add_new_item' => '求人を追加',
            'edit_item' => '求人を編集',
            'new_item' => '新規求人',
            'view_item' => '求人を表示',
            'search_items' => '求人を検索',
            'not_found' => '求人が見つかりません',
            'menu_name' => '求人',
        ],
        'public' => true,
        'show_in_rest' => true,
        'rest_base' => 'jobs',
        'menu_icon' => 'dashicons-businessperson',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'jobs'],
    ]);

    $taxonomies = [
        'job_type' => ['name' => '職種', 'slug' => 'job-types'],
        'job_industry' => ['name' => '業界', 'slug' => 'job-industries'],
        'work_style' => ['name' => '働き方', 'slug' => 'work-styles'],
    ];

    foreach ($taxonomies as $taxonomy => $config) {
        register_taxonomy($taxonomy, ['job'], [
            'labels' => [
                'name' => $config['name'],
                'singular_name' => $config['name'],
                'search_items' => $config['name'] . 'を検索',
                'all_items' => 'すべての' . $config['name'],
                'edit_item' => $config['name'] . 'を編集',
                'update_item' => $config['name'] . 'を更新',
                'add_new_item' => $config['name'] . 'を追加',
                'menu_name' => $config['name'],
            ],
            'hierarchical' => true,
            'public' => true,
            'show_in_rest' => true,
            'rest_base' => $config['slug'],
            'rewrite' => ['slug' => $config['slug']],
        ]);
    }

    $meta_fields = [
        'company_name',
        'hourly_rate',
        'work_hours',
        'location',
        'employment_type',
        'experience_level',
        'featured_label',
        'application_url',
        'image_url',
    ];

    foreach ($meta_fields as $field) {
        register_post_meta('job', $field, [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => '__return_true',
        ]);
    }

    register_post_type('application', [
        'labels' => [
            'name' => '応募',
            'singular_name' => '応募',
            'add_new_item' => '応募を追加',
            'edit_item' => '応募を編集',
            'new_item' => '新規応募',
            'view_item' => '応募を表示',
            'search_items' => '応募を検索',
            'not_found' => '応募が見つかりません',
            'menu_name' => '応募',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => false,
        'menu_icon' => 'dashicons-clipboard',
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);

    $application_meta_fields = [
        'user_id',
        'job_slug',
        'job_title',
        'resume_attachment_id',
        'resume_url',
        'status',
        'last_name',
        'first_name',
        'email',
        'phone',
        'work_status',
        'start_date',
        'experience',
        'message',
    ];

    foreach ($application_meta_fields as $field) {
        register_post_meta('application', $field, [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => false,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }
});

add_action('rest_api_init', function () {
    register_rest_route('myremote/v1', '/register', [
        'methods' => 'POST',
        'callback' => 'myremote_rest_register',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('myremote/v1', '/login', [
        'methods' => 'POST',
        'callback' => 'myremote_rest_login',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('myremote/v1', '/password-reset', [
        'methods' => 'POST',
        'callback' => 'myremote_rest_password_reset',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('myremote/v1', '/contact', [
        'methods' => 'POST',
        'callback' => 'myremote_rest_contact',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('myremote/v1', '/logout', [
        'methods' => 'POST',
        'callback' => 'myremote_rest_logout',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('myremote/v1', '/me', [
        [
            'methods' => 'GET',
            'callback' => 'myremote_rest_me',
            'permission_callback' => '__return_true',
        ],
        [
            'methods' => ['POST', 'PATCH'],
            'callback' => 'myremote_rest_update_me',
            'permission_callback' => '__return_true',
        ],
    ]);

    register_rest_route('myremote/v1', '/applications', [
        [
            'methods' => 'GET',
            'callback' => 'myremote_rest_applications',
            'permission_callback' => '__return_true',
        ],
        [
            'methods' => 'POST',
            'callback' => 'myremote_rest_create_application',
            'permission_callback' => '__return_true',
        ],
    ]);
});

add_filter('manage_application_posts_columns', function (array $columns): array {
    $columns['applicant'] = '応募者';
    $columns['job_title'] = '求人';
    $columns['status'] = 'ステータス';
    $columns['resume'] = '履歴書';

    return $columns;
});

add_action('manage_application_posts_custom_column', function (string $column, int $post_id): void {
    if ($column === 'applicant') {
        echo esc_html(trim(
            (string) get_post_meta($post_id, 'last_name', true) . ' ' .
            (string) get_post_meta($post_id, 'first_name', true)
        ));
        echo '<br><small>' . esc_html((string) get_post_meta($post_id, 'email', true)) . '</small>';
    }

    if ($column === 'job_title') {
        echo esc_html((string) get_post_meta($post_id, 'job_title', true));
    }

    if ($column === 'status') {
        echo esc_html((string) get_post_meta($post_id, 'status', true) ?: 'new');
    }

    if ($column === 'resume') {
        $resume_url = (string) get_post_meta($post_id, 'resume_url', true);
        if ($resume_url) {
            echo '<a href="' . esc_url($resume_url) . '" target="_blank" rel="noopener">開く</a>';
        }
    }
}, 10, 2);

function myremote_request_data(WP_REST_Request $request): array
{
    $json = $request->get_json_params();
    if (is_array($json) && $json) {
        return $json;
    }

    return $request->get_params();
}

function myremote_param(array $data, string $key): string
{
    return isset($data[$key]) ? sanitize_text_field(wp_unslash($data[$key])) : '';
}

function myremote_email_param(array $data): string
{
    return isset($data['email']) ? sanitize_email(wp_unslash($data['email'])) : '';
}

function myremote_token_hash(string $plain): string
{
    return hash_hmac('sha256', $plain, wp_salt('auth'));
}

function myremote_create_auth_token(int $user_id, bool $remember = false): array
{
    $plain = bin2hex(random_bytes(32));
    $expires = time() + ($remember ? MONTH_IN_SECONDS : DAY_IN_SECONDS);

    update_user_meta($user_id, MYREMOTE_TOKEN_HASH_META, myremote_token_hash($plain));
    update_user_meta($user_id, MYREMOTE_TOKEN_EXPIRES_META, $expires);

    return [
        'token' => $user_id . '.' . $plain,
        'expires_at' => gmdate('c', $expires),
    ];
}

function myremote_current_user_from_request(WP_REST_Request $request)
{
    $header = $request->get_header('authorization');
    if (!$header || !preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
        return new WP_Error('myremote_missing_token', 'ログインが必要です。', ['status' => 401]);
    }

    $parts = explode('.', trim($matches[1]), 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0]) || $parts[1] === '') {
        return new WP_Error('myremote_invalid_token', 'ログイン情報が無効です。', ['status' => 401]);
    }

    $user_id = (int) $parts[0];
    $user = get_user_by('id', $user_id);
    $stored_hash = (string) get_user_meta($user_id, MYREMOTE_TOKEN_HASH_META, true);
    $expires = (int) get_user_meta($user_id, MYREMOTE_TOKEN_EXPIRES_META, true);

    if (!$user || !$stored_hash || $expires < time()) {
        return new WP_Error('myremote_expired_token', 'ログインの有効期限が切れました。', ['status' => 401]);
    }

    if (!hash_equals($stored_hash, myremote_token_hash($parts[1]))) {
        return new WP_Error('myremote_invalid_token', 'ログイン情報が無効です。', ['status' => 401]);
    }

    return $user;
}

function myremote_user_response(WP_User $user): array
{
    return [
        'id' => $user->ID,
        'email' => $user->user_email,
        'display_name' => $user->display_name,
        'last_name' => (string) get_user_meta($user->ID, 'last_name', true),
        'first_name' => (string) get_user_meta($user->ID, 'first_name', true),
        'phone' => (string) get_user_meta($user->ID, 'phone', true),
        'birthdate' => (string) get_user_meta($user->ID, 'birthdate', true),
    ];
}

function myremote_auth_response(WP_User $user, bool $remember = false): WP_REST_Response
{
    $token = myremote_create_auth_token($user->ID, $remember);

    return new WP_REST_Response([
        'token' => $token['token'],
        'expires_at' => $token['expires_at'],
        'user' => myremote_user_response($user),
    ], 200);
}

function myremote_rest_register(WP_REST_Request $request)
{
    $data = myremote_request_data($request);
    $email = myremote_email_param($data);
    $password = isset($data['password']) ? (string) wp_unslash($data['password']) : '';
    $last_name = myremote_param($data, 'last_name');
    $first_name = myremote_param($data, 'first_name');

    if (!is_email($email)) {
        return new WP_Error('myremote_invalid_email', 'メールアドレスを確認してください。', ['status' => 400]);
    }

    if (email_exists($email)) {
        return new WP_Error('myremote_email_exists', 'このメールアドレスはすでに登録されています。', ['status' => 409]);
    }

    if (strlen($password) < 8) {
        return new WP_Error('myremote_short_password', 'パスワードは8文字以上で入力してください。', ['status' => 400]);
    }

    $user_id = wp_create_user($email, $password, $email);
    if (is_wp_error($user_id)) {
        return $user_id;
    }

    wp_update_user([
        'ID' => $user_id,
        'display_name' => trim($last_name . ' ' . $first_name) ?: $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'role' => 'applicant',
    ]);

    update_user_meta($user_id, 'phone', myremote_param($data, 'phone'));
    update_user_meta($user_id, 'birthdate', myremote_param($data, 'birthdate'));

    $user = get_user_by('id', $user_id);

    return myremote_auth_response($user, true);
}

function myremote_rest_login(WP_REST_Request $request)
{
    $data = myremote_request_data($request);
    $email = myremote_email_param($data);
    $password = isset($data['password']) ? (string) wp_unslash($data['password']) : '';
    $remember = !empty($data['remember']);

    $user = wp_authenticate($email, $password);
    if (is_wp_error($user)) {
        return new WP_Error('myremote_invalid_login', 'メールアドレスまたはパスワードが違います。', ['status' => 401]);
    }

    return myremote_auth_response($user, $remember);
}

function myremote_rest_password_reset(WP_REST_Request $request)
{
    $data = myremote_request_data($request);
    $email = myremote_email_param($data);

    if (!is_email($email)) {
        return new WP_Error('myremote_invalid_email', 'メールアドレスを確認してください。', ['status' => 400]);
    }

    $user = get_user_by('email', $email);
    if ($user) {
        $key = get_password_reset_key($user);
        if (is_wp_error($key)) {
            return new WP_Error('myremote_reset_failed', 'パスワード再設定メールを送信できませんでした。', ['status' => 500]);
        }

        $reset_url = network_site_url('wp-login.php?action=rp&key=' . rawurlencode($key) . '&login=' . rawurlencode($user->user_login), 'login');
        $sent = wp_mail(
            $user->user_email,
            '【MyRemo】パスワード再設定',
            "MyRemoのパスワード再設定を受け付けました。\n\n以下のURLから新しいパスワードを設定してください。\n{$reset_url}\n\nこのメールに心当たりがない場合は破棄してください。",
            ['Content-Type: text/plain; charset=UTF-8']
        );

        if (!$sent) {
            return new WP_Error('myremote_reset_failed', 'パスワード再設定メールを送信できませんでした。', ['status' => 500]);
        }
    }

    return new WP_REST_Response([
        'ok' => true,
        'message' => '登録済みの場合、パスワード再設定メールを送信しました。',
    ], 200);
}

function myremote_rest_contact(WP_REST_Request $request)
{
    $data = myremote_request_data($request);
    $name = myremote_param($data, 'name');
    $email = myremote_email_param($data);
    $message = isset($data['message']) ? sanitize_textarea_field(wp_unslash($data['message'])) : '';

    if ($name === '' || !is_email($email) || $message === '') {
        return new WP_Error('myremote_invalid_contact', 'お名前、メールアドレス、お問い合わせ内容を入力してください。', ['status' => 400]);
    }

    $subject = '【MyRemo】お問い合わせ';
    $body = "お問い合わせが届きました。\n\nお名前: {$name}\nメール: {$email}\n\n内容:\n{$message}\n";
    $headers = ['Reply-To: ' . $name . ' <' . $email . '>'];
    $sent = wp_mail(get_option('admin_email'), $subject, $body, $headers);

    if (!$sent) {
        return new WP_Error('myremote_contact_failed', 'お問い合わせを送信できませんでした。', ['status' => 500]);
    }

    return new WP_REST_Response(['ok' => true], 200);
}

function myremote_rest_logout(WP_REST_Request $request)
{
    $user = myremote_current_user_from_request($request);
    if (is_wp_error($user)) {
        return $user;
    }

    delete_user_meta($user->ID, MYREMOTE_TOKEN_HASH_META);
    delete_user_meta($user->ID, MYREMOTE_TOKEN_EXPIRES_META);

    return new WP_REST_Response(['ok' => true], 200);
}

function myremote_rest_me(WP_REST_Request $request)
{
    $user = myremote_current_user_from_request($request);
    if (is_wp_error($user)) {
        return $user;
    }

    return new WP_REST_Response(['user' => myremote_user_response($user)], 200);
}

function myremote_rest_update_me(WP_REST_Request $request)
{
    $user = myremote_current_user_from_request($request);
    if (is_wp_error($user)) {
        return $user;
    }

    $data = myremote_request_data($request);
    $profile_fields = ['last_name', 'first_name', 'phone', 'birthdate'];

    foreach ($profile_fields as $field) {
        if (array_key_exists($field, $data)) {
            update_user_meta($user->ID, $field, myremote_param($data, $field));
        }
    }

    wp_update_user([
        'ID' => $user->ID,
        'display_name' => trim(
            (string) get_user_meta($user->ID, 'last_name', true) . ' ' .
            (string) get_user_meta($user->ID, 'first_name', true)
        ) ?: $user->user_email,
    ]);

    return myremote_rest_me($request);
}

function myremote_application_response(WP_Post $post): array
{
    return [
        'id' => $post->ID,
        'title' => get_the_title($post),
        'created_at' => get_post_time('c', true, $post),
        'job_slug' => (string) get_post_meta($post->ID, 'job_slug', true),
        'job_title' => (string) get_post_meta($post->ID, 'job_title', true),
        'status' => (string) get_post_meta($post->ID, 'status', true),
        'resume_url' => (string) get_post_meta($post->ID, 'resume_url', true),
    ];
}

function myremote_handle_resume_upload(WP_REST_Request $request, int $post_id)
{
    $files = $request->get_file_params();
    if (empty($files['resume']) || !empty($files['resume']['error'])) {
        return null;
    }

    $file = $files['resume'];
    $allowed_mimes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    $check = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $allowed_mimes);
    if (empty($check['ext']) || empty($check['type'])) {
        return new WP_Error('myremote_invalid_resume', '履歴書はPDFまたはWord形式でアップロードしてください。', ['status' => 400]);
    }

    if ((int) $file['size'] > 10 * MB_IN_BYTES) {
        return new WP_Error('myremote_resume_too_large', '履歴書ファイルは10MB以内にしてください。', ['status' => 400]);
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = media_handle_upload('resume', $post_id, [], [
        'test_form' => false,
        'mimes' => $allowed_mimes,
    ]);

    if (is_wp_error($attachment_id)) {
        return $attachment_id;
    }

    return [
        'id' => $attachment_id,
        'url' => wp_get_attachment_url($attachment_id),
    ];
}

function myremote_send_application_emails(int $post_id): void
{
    $email = (string) get_post_meta($post_id, 'email', true);
    $name = trim(
        (string) get_post_meta($post_id, 'last_name', true) . ' ' .
        (string) get_post_meta($post_id, 'first_name', true)
    );
    $job_title = (string) get_post_meta($post_id, 'job_title', true);
    $admin_email = get_option('admin_email');

    if (is_email($email)) {
        wp_mail(
            $email,
            '【MyRemo】ご応募を受け付けました',
            "{$name} 様\n\nMyRemoへのご応募ありがとうございます。\n以下の求人への応募を受け付けました。\n\n求人: {$job_title}\n\n担当コンサルタントより1〜2営業日以内にご連絡いたします。\n\nMyRemo",
            ['Content-Type: text/plain; charset=UTF-8']
        );
    }

    if (is_email($admin_email)) {
        $resume_url = (string) get_post_meta($post_id, 'resume_url', true);
        $body = "新しい応募が届きました。\n\n応募ID: {$post_id}\n求人: {$job_title}\n応募者: {$name}\nメール: {$email}\n電話: " . (string) get_post_meta($post_id, 'phone', true) . "\n履歴書: " . ($resume_url ?: 'なし') . "\n\nWordPress管理画面で詳細を確認してください。";
        wp_mail($admin_email, '【MyRemo】新しい応募が届きました', $body, ['Content-Type: text/plain; charset=UTF-8']);
    }
}

function myremote_rest_applications(WP_REST_Request $request)
{
    $user = myremote_current_user_from_request($request);
    if (is_wp_error($user)) {
        return $user;
    }

    $query = new WP_Query([
        'post_type' => 'application',
        'post_status' => ['publish', 'private'],
        'posts_per_page' => 50,
        'meta_key' => 'user_id',
        'meta_value' => (string) $user->ID,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    return new WP_REST_Response([
        'applications' => array_map('myremote_application_response', $query->posts),
    ], 200);
}

function myremote_rest_create_application(WP_REST_Request $request)
{
    $user = myremote_current_user_from_request($request);
    if (is_wp_error($user)) {
        return $user;
    }

    $data = myremote_request_data($request);
    $job_title = myremote_param($data, 'job_title') ?: 'MyRemo掲載求人';
    $email = myremote_email_param($data) ?: $user->user_email;
    $last_name = myremote_param($data, 'last_name') ?: (string) get_user_meta($user->ID, 'last_name', true);
    $first_name = myremote_param($data, 'first_name') ?: (string) get_user_meta($user->ID, 'first_name', true);
    $message = isset($data['message']) ? sanitize_textarea_field(wp_unslash($data['message'])) : '';
    $experience = isset($data['experience']) ? sanitize_textarea_field(wp_unslash($data['experience'])) : '';

    $post_id = wp_insert_post([
        'post_type' => 'application',
        'post_status' => 'private',
        'post_author' => $user->ID,
        'post_title' => sprintf('応募: %s / %s', $job_title, $email),
        'post_content' => trim($experience . "\n\n" . $message),
    ], true);

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    $meta = [
        'user_id' => (string) $user->ID,
        'job_slug' => myremote_param($data, 'job_slug'),
        'job_title' => $job_title,
        'status' => 'new',
        'last_name' => $last_name,
        'first_name' => $first_name,
        'email' => $email,
        'phone' => myremote_param($data, 'phone') ?: (string) get_user_meta($user->ID, 'phone', true),
        'work_status' => myremote_param($data, 'work_status'),
        'start_date' => myremote_param($data, 'start_date'),
        'experience' => $experience,
        'message' => $message,
    ];

    foreach ($meta as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }

    $resume = myremote_handle_resume_upload($request, $post_id);
    if (is_wp_error($resume)) {
        wp_delete_post($post_id, true);
        return $resume;
    }

    if (is_array($resume)) {
        update_post_meta($post_id, 'resume_attachment_id', (string) $resume['id']);
        update_post_meta($post_id, 'resume_url', (string) $resume['url']);
    }

    if (empty($data['_skip_email'])) {
        myremote_send_application_emails($post_id);
    }

    return new WP_REST_Response([
        'application' => myremote_application_response(get_post($post_id)),
    ], 201);
}

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_myremote_job_fields',
        'title' => '求人情報',
        'fields' => [
            [
                'key' => 'field_myremote_company_name',
                'label' => '会社名',
                'name' => 'company_name',
                'type' => 'text',
            ],
            [
                'key' => 'field_myremote_hourly_rate',
                'label' => '報酬',
                'name' => 'hourly_rate',
                'type' => 'text',
                'placeholder' => '例: ¥1,800 〜 ¥2,200',
            ],
            [
                'key' => 'field_myremote_work_hours',
                'label' => '稼働時間',
                'name' => 'work_hours',
                'type' => 'text',
                'placeholder' => '例: 週3日 / 1日4h〜',
            ],
            [
                'key' => 'field_myremote_location',
                'label' => '勤務地',
                'name' => 'location',
                'type' => 'text',
                'default_value' => '完全在宅',
            ],
            [
                'key' => 'field_myremote_employment_type',
                'label' => '契約形態',
                'name' => 'employment_type',
                'type' => 'text',
                'placeholder' => '例: 業務委託',
            ],
            [
                'key' => 'field_myremote_experience_level',
                'label' => '経験条件',
                'name' => 'experience_level',
                'type' => 'text',
                'placeholder' => '例: 未経験可',
            ],
            [
                'key' => 'field_myremote_featured_label',
                'label' => 'ラベル',
                'name' => 'featured_label',
                'type' => 'text',
                'placeholder' => '例: 新着',
            ],
            [
                'key' => 'field_myremote_application_url',
                'label' => '応募URL',
                'name' => 'application_url',
                'type' => 'url',
                'default_value' => '/apply.html',
            ],
            [
                'key' => 'field_myremote_image_url',
                'label' => '画像URL',
                'name' => 'image_url',
                'type' => 'url',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'job',
                ],
            ],
        ],
        'position' => 'acf_after_title',
        'style' => 'default',
        'active' => true,
        'show_in_rest' => 1,
    ]);
});
