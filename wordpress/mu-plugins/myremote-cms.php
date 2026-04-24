<?php
/**
 * Plugin Name: MyRemo CMS
 * Description: Registers MyRemo job content types, taxonomies, fields, and REST metadata.
 * Version: 0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
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
});

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
