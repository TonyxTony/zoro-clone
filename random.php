<?php 
include './_config.php'; 
require_once __DIR__ . '/_php/anilist_api.php';

// Step 1: Get total anime count (using a small query)
$pageInfo = get_popular_anime(1, 1); // Only need pageInfo
$total = $pageInfo['data']['Page']['pageInfo']['total'] ?? 10000; // fallback if API fails

// Step 2: Pick a random offset
$perPage = 50;
$randomIndex = rand(0, $total - 1);
$page = intval($randomIndex / $perPage) + 1;
$indexOnPage = $randomIndex % $perPage;

// Step 3: Fetch that page
$animePage = get_popular_anime($page, $perPage);
$animeList = $animePage['data']['Page']['media'] ?? [];

// Step 4: Pick the anime and redirect
if (isset($animeList[$indexOnPage])) {
    $anime = $animeList[$indexOnPage];
    $animeId = $anime['id'];
    $title = !empty($anime['title']['english']) ? $anime['title']['english'] : $anime['title']['romaji'];
    $url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $title);
    $url_title = preg_replace('/-+/', '-', $url_title);
    $url_title = trim($url_title, '-');
    $websiteUrl = $websiteUrl ?? '/anime'; // fallback if not set
    header('Location: ' . $websiteUrl . '/anime/' . $animeId . '/' . $url_title);
    exit;
} else {
    echo 'Could not find a random anime. Please try again.';
}
?>