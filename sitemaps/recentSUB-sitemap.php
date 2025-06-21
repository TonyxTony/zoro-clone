<?php
include('../_config.php');
include('../_php/anilist_api.php');

header('Content-type: application/xml');

echo "<?xml version='1.0' encoding='UTF-8'?>"."\n";
echo "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>"."\n";

$data = get_latest_subbed(1, 50);
if (isset($data['data']) && isset($data['data']['Page']) && isset($data['data']['Page']['media'])) {
    foreach($data['data']['Page']['media'] as $anime) {
        $title = isset($anime['title']['english']) ? $anime['title']['english'] : $anime['title']['romaji'];
        $url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $title);
        $url_title = preg_replace('/-+/', '-', $url_title);
        $url_title = trim($url_title, '-');
        
        echo "<url>";
        echo "<loc>https://{$_SERVER['SERVER_NAME']}/anime/{$anime['id']}/{$url_title}</loc>";
        echo "<changefreq>daily</changefreq>";
        echo "<priority>0.8000</priority>";
        echo "</url>";
    }
}

echo "</urlset>";
?>