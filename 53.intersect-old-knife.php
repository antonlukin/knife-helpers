<?php

if(php_sapi_name() !== 'cli') {
    exit;
}

$_SERVER = [
    "SERVER_PROTOCOL" => "HTTP/1.1",
    "HTTP_HOST"       => "knife.plus",
    "SERVER_NAME"     => "knife.plus",
    "REQUEST_URI"     => "/",
    "REQUEST_METHOD"  => "GET"
];

define('WP_CACHE', false);
define('WP_DEBUG', true);
define('WP_USE_THEMES', false);

require( __DIR__ . '/../wordpress/wp-load.php');

function load_media_posts() {
    $slugs = [];

    for ($i = 1; $i <= 13; $i++) {
        $xml = simplexml_load_file("https://knife.media/wp-sitemap-posts-post-{$i}.xml");

        if ($xml === false) {
            die("Ошибка загрузки XML\n");
        }

        $namespaces = $xml->getNamespaces(true);
        $namespace = $namespaces[''] ?? '';

        $urls = $xml->children($namespace)->url;

        foreach ($urls as $url) {
            $loc = (string) $url->loc;

            // Убираем домен, оставляем только путь
            $slug = parse_url($loc, PHP_URL_PATH);

            if ($slug) {
                $slugs[] = $slug;
            }
        }
    }

    file_put_contents(__DIR__ . '/53.output.json', json_encode($slugs));
}

function intersect_posts() {
    $output = json_decode(file_get_contents(__DIR__ . '/53.output.json'));

    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['post'],
        'orderby' => 'id',
        'order' => 'desc',
        'category_name' => 'longreads',
        'fields' => 'ids'
    ]);

    foreach ($posts as $post) {
        $slug = parse_url(get_permalink($post), PHP_URL_PATH);

        if (in_array($slug, $output)) {
            continue;
        }

        echo get_permalink($post) . "###" . html_entity_decode(strip_tags(get_the_title($post))) . "\n";
    }
}

intersect_posts();

