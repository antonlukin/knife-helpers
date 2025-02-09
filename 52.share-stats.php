<?php

if(php_sapi_name() !== 'cli') {
    exit;
}

$_SERVER = [
    "SERVER_PROTOCOL" => "HTTP/1.1",
    "HTTP_HOST"       => "knife.media",
    "SERVER_NAME"     => "knife.media",
    "REQUEST_URI"     => "/",
    "REQUEST_METHOD"  => "GET"
];

define('WP_CACHE', false);
define('WP_DEBUG', true);
define('WP_USE_THEMES', false);

require( __DIR__ . '/../wordpress/wp-load.php');

function find_posts() {
    global $wpdb;

    $conf = wp_parse_args(
        KNIFE_ANALYTICS,
        array(
            'host'     => DB_HOST,
            'name'     => DB_NAME,
            'user'     => DB_USER,
            'password' => DB_PASSWORD,
        )
    );

    // Create custom db connection
    $db = new wpdb( $conf['user'], $conf['password'], $conf['name'], $conf['host'] );

    $posts = $db->get_results(
        "SELECT post_id, slug, pageviews, publish, fb, vk FROM posts
        LEFT JOIN shares USING (post_id)
        LEFT JOIN views USING (post_id)",
        ARRAY_A
    );

    usort($posts, function($a, $b) {
        return $b['pageviews'] <=> $a['pageviews'];
    });

    $count = 0;

    foreach ($posts as $post) {
        if ($post['post_id'] < 129379 || empty(get_permalink($post['post_id']))) {
            continue;
        }

        if ($count++ > 50) {
            break;
        }

        printf(
            "<p>%s<br>%s<br>Views: %s</p>",
            get_permalink($post['post_id']),
            get_the_title($post['post_id']),
            $post['pageviews']
        );
    }

    usort($posts, function($a, $b) {
        return $b['fb'] <=> $a['fb'];
    });

    $count = 0;

    foreach ($posts as $post) {
        if ($post['post_id'] < 129379 || empty(get_permalink($post['post_id']))) {
            continue;
        }

        if ($count++ > 50) {
            break;
        }

        printf(
            "<p>%s<br>%s<br>Facebook: %s</p>",
            get_permalink($post['post_id']),
            get_the_title($post['post_id']),
            $post['fb']
        );
    }

    usort($posts, function($a, $b) {
        return $b['vk'] <=> $a['vk'];
    });

    $count = 0;

    foreach ($posts as $post) {
        if ($post['post_id'] < 129379 || empty(get_permalink($post['post_id']))) {
            continue;
        }

        if ($count++ > 50) {
            break;
        }

        printf(
            "<p>%s<br>%s<br>VK: %s</p>",
            get_permalink($post['post_id']),
            get_the_title($post['post_id']),
            $post['vk']
        );
    }
}


find_posts();
