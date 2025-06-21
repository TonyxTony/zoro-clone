<?php 
session_start();
require('../_config.php');
require('../_php/anilist_api.php'); // Include the AniList API functions
require_once('../_php/genre_functions.php'); // Include genre functions

$parts=parse_url($_SERVER['REQUEST_URI']); 
$page_url=explode('/', $parts['path']);
$id = $page_url[count($page_url)-1];
//$id = "action";
$genre = str_replace("+", "-", $id);
$id = str_replace("+", " ", $id);
$id = ucfirst($id);

// Determine if this is an official genre or tag
$isMainGenre = is_main_genre($id);
$displayType = $isMainGenre ? "Genre" : "Tag";

if(!isset($_GET['page'])){
    $page = 1;
}else{
    $page = $_GET['page']; 
}
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title><?=$id?> Anime on <?=$websiteTitle?></title>
    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="title" content="<?=$id?> Anime on <?=$websiteTitle?>">
    <meta name="description" content="Watch <?=$id?> anime online in HD with no ads. Best place for free anime streaming.">
    <meta name="keywords" content="<?=$id?>, <?=$websiteTitle?>, watch anime online, free anime, anime stream, anime hd, english sub, kissanime, gogoanime, animeultima, 9anime, 123animes, <?=$websiteTitle?>, vidstreaming, gogo-stream, animekisa, zoro.to, gogoanime.run, animefrenzy, animekisa">
    <meta name="charset" content="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Language" content="en">
    <meta property="og:title" content="<?=$id?> Anime on <?=$websiteTitle?>">
    <meta property="og:description" content="<?=$id?> Anime on <?=$websiteTitle?> in HD with No Ads. Watch anime online">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?=$websiteTitle?>">
    <meta itemprop="image" content="<?=$banner?>">
    <meta property="og:image" content="<?=$banner?>">
    <meta property="og:image:width" content="650">
    <meta property="og:image:height" content="350">
    <meta property="twitter:card" content="summary">
    <meta name="apple-mobile-web-app-status-bar" content="#202125">
    <meta name="theme-color" content="#202125">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" type="text/css">
    <link rel="apple-touch-icon" href="<?=$websiteUrl?>/favicon.png?v=<?=$version?>" />
    <link rel="shortcut icon" href="<?=$websiteUrl?>/favicon.png?v=<?=$version?>" type="image/x-icon"/>
    <link rel="apple-touch-icon" sizes="180x180" href="<?=$websiteUrl?>/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?=$websiteUrl?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?=$websiteUrl?>/favicon-16x16.png">
    <link rel="mask-icon" href="<?=$websiteUrl?>/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="icon" sizes="192x192" href="<?=$websiteUrl?>/files/images/touch-icon-192x192.png?v=<?=$version?>">
    <link rel="stylesheet" href="<?=$websiteUrl?>/files/css/style.css?v=<?=$version?>">
    <link rel="stylesheet" href="<?=$websiteUrl?>/files/css/min.css?v=<?=$version?>">
    <script type="text/javascript">
        setTimeout(function () {
            var wpse326013 = document.createElement('link');
            wpse326013.rel = 'stylesheet';
            wpse326013.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css';
            wpse326013.type = 'text/css';
            var godefer = document.getElementsByTagName('link')[0];
            godefer.parentNode.insertBefore(wpse326013, godefer);
            var wpse326013_2 = document.createElement('link');
            wpse326013_2.rel = 'stylesheet';
            wpse326013_2.href = 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css';
            wpse326013_2.type = 'text/css';
            var godefer2 = document.getElementsByTagName('link')[0];
            godefer2.parentNode.insertBefore(wpse326013_2, godefer2);
        }, 500);
    </script>
    <noscript>
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" />
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css" />
    </noscript>
    <script></script>
</head>

<body data-page="page_anime">
    <div id="sidebar_menu_bg"></div>
    <div id="wrapper" data-page="page_home">
        <?php include('../_php/header.php'); ?>
        <div class="clearfix"></div>
        <div id="main-wrapper">
            <div class="container">
                <div id="main-content">
                    <section class="block_area block_area_category">
                        <div class="block_area-header">
                            <div class="float-left bah-heading mr-4">
                                <h2 class="cat-heading"><?=$displayType?>: <?=$id?></h2>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="tab-content">
                            <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">
                                <div class="film_list-wrap">

                                <?php 
                                $hasResults = false;
                                
                                // Primary query based on whether it's a genre or tag
                                if ($isMainGenre) {
                                        // For main genres, use the standard genre query
                                        $query = '
                                        query ($page: Int, $perPage: Int, $genre: String) {
                                            Page(page: $page, perPage: $perPage) {
                                                pageInfo {
                                                    total
                                                    currentPage
                                                    lastPage
                                                    hasNextPage
                                                    perPage
                                                }
                                                media(genre: $genre, type: ANIME, sort: POPULARITY_DESC) {
                                                    id
                                                    title {
                                                        romaji
                                                        english
                                                        native
                                                    }
                                                    description
                                                    coverImage {
                                                        large
                                                        medium
                                                    }
                                                    status
                                                    format
                                                    episodes
                                                    duration
                                                    genres
                                                    seasonYear
                                                    averageScore
                                                }
                                            }
                                        }';
                                        
                                        $variables = [
                                        'genre' => $id,
                                        'page' => intval($page),
                                        'perPage' => 20
                                    ];
                                } else {
                                    // For tags, use tag query with the tag name
                                    $query = '
                                    query ($page: Int, $perPage: Int, $tag: String) {
                                        Page(page: $page, perPage: $perPage) {
                                            pageInfo {
                                                total
                                                currentPage
                                                lastPage
                                                hasNextPage
                                                perPage
                                            }
                                            media(tag: $tag, type: ANIME, sort: POPULARITY_DESC) {
                                                id
                                                title {
                                                    romaji
                                                    english
                                                    native
                                                }
                                                description
                                                coverImage {
                                                    large
                                                    medium
                                                }
                                                status
                                                format
                                                episodes
                                                duration
                                                genres
                                                tags {
                                                    name
                                                    rank
                                                }
                                                seasonYear
                                                averageScore
                                            }
                                        }
                                    }';
                                    
                                    $variables = [
                                        'tag' => $id,
                                            'page' => intval($page),
                                            'perPage' => 20
                                        ];
                                    } 
                                
                                $result = anilist_query($query, $variables);
                                
                                if (isset($result['data']) && isset($result['data']['Page']) && isset($result['data']['Page']['media']) && !empty($result['data']['Page']['media'])) {
                                    $animes = $result['data']['Page']['media'];
                                    $pageInfo = $result['data']['Page']['pageInfo'];
                                    $hasResults = true;
                                }
                                
                                // If no results from primary query, try the opposite approach
                                if (!$hasResults) {
                                    if ($isMainGenre) {
                                        // Try as a tag
                                        $query = '
                                        query ($page: Int, $perPage: Int, $tag: String) {
                                            Page(page: $page, perPage: $perPage) {
                                                pageInfo {
                                                    total
                                                    currentPage
                                                    lastPage
                                                    hasNextPage
                                                    perPage
                                                }
                                                media(tag: $tag, type: ANIME, sort: POPULARITY_DESC) {
                                                    id
                                                    title {
                                                        romaji
                                                        english
                                                        native
                                                    }
                                                    description
                                                    coverImage {
                                                        large
                                                        medium
                                                    }
                                                    status
                                                    format
                                                    episodes
                                                    duration
                                                    genres
                                                    tags {
                                                        name
                                                        rank
                                                    }
                                                    seasonYear
                                                    averageScore
                                                }
                                            }
                                        }';
                                        
                                        $variables = [
                                            'tag' => $id,
                                            'page' => intval($page),
                                            'perPage' => 20
                                        ];
                                    } else {
                                        // Try as a genre
                                        $query = '
                                        query ($page: Int, $perPage: Int, $genre: String) {
                                            Page(page: $page, perPage: $perPage) {
                                                pageInfo {
                                                    total
                                                    currentPage
                                                    lastPage
                                                    hasNextPage
                                                    perPage
                                                }
                                                media(genre: $genre, type: ANIME, sort: POPULARITY_DESC) {
                                                    id
                                                    title {
                                                        romaji
                                                        english
                                                        native
                                                    }
                                                    description
                                                    coverImage {
                                                        large
                                                        medium
                                                    }
                                                    status
                                                    format
                                                    episodes
                                                    duration
                                                    genres
                                                    seasonYear
                                                    averageScore
                                                }
                                            }
                                        }';
                                        
                                        $variables = [
                                            'genre' => $id,
                                            'page' => intval($page),
                                            'perPage' => 20
                                        ];
                                    }
                                    
                                    $result = anilist_query($query, $variables);
                                    
                                    if (isset($result['data']) && isset($result['data']['Page']) && isset($result['data']['Page']['media']) && !empty($result['data']['Page']['media'])) {
                                        $animes = $result['data']['Page']['media'];
                                        $pageInfo = $result['data']['Page']['pageInfo'];
                                        $hasResults = true;
                                        // Update display type
                                        $displayType = $isMainGenre ? "Tag" : "Genre";
                                    }
                                }
                                
                                // Last resort: Try text search
                                if (!$hasResults) {
                                        $query = '
                                        query ($page: Int, $perPage: Int, $search: String) {
                                            Page(page: $page, perPage: $perPage) {
                                                pageInfo {
                                                    total
                                                    currentPage
                                                    lastPage
                                                    hasNextPage
                                                    perPage
                                                }
                                                media(search: $search, type: ANIME, sort: POPULARITY_DESC) {
                                                    id
                                                    title {
                                                        romaji
                                                        english
                                                        native
                                                    }
                                                    description
                                                    coverImage {
                                                        large
                                                        medium
                                                    }
                                                    status
                                                    format
                                                    episodes
                                                    duration
                                                    genres
                                                    seasonYear
                                                    averageScore
                                                }
                                            }
                                        }';
                                        
                                        $variables = [
                                        'search' => $id,
                                            'page' => intval($page),
                                            'perPage' => 20
                                        ];
                                    
                                    $result = anilist_query($query, $variables);
                                    
                                    if (isset($result['data']) && isset($result['data']['Page']) && isset($result['data']['Page']['media']) && !empty($result['data']['Page']['media'])) {
                                        $animes = $result['data']['Page']['media'];
                                        $pageInfo = $result['data']['Page']['pageInfo'];
                                        $hasResults = true;
                                        // Update display type for search results
                                        $displayType = "Keyword";
                                    }
                                }
                                
                                if (!$hasResults) {
                                    echo '<div class="alert alert-warning">No anime found for ' . strtolower($displayType) . ' "' . $id . '". Try a different category.</div>';
                                } else {
                                    // Display anime results
                                    foreach($animes as $anime) { 
                                        if (isset($anime['title'])) {
                                            $title = !empty($anime['title']['english']) ? $anime['title']['english'] : $anime['title']['romaji'];
                                            $isDub = false; // AniList doesn't directly provide dub info
                                            $imgUrl = isset($anime['coverImage']['large']) ? $anime['coverImage']['large'] : $websiteUrl.'/files/images/no_poster.jpg';
                                            $animeId = $anime['id'];
                                            $releaseYear = isset($anime['seasonYear']) ? $anime['seasonYear'] : 'Unknown';
                                            $format = isset($anime['format']) ? $anime['format'] : '';
                                            
                                            // Create URL-friendly title
                                            $url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $title);
                                            $url_title = preg_replace('/-+/', '-', $url_title);
                                            $url_title = trim($url_title, '-');
                                            ?>
                                    <div class="flw-item">
                                        <div class="film-poster">
                                            <div class="tick ltr">
                                                <div class="tick-item-<?php echo $isDub ? "dub" : "sub"; ?> tick-eps amp-algn">
                                                    <?php echo $isDub ? "Dub" : "Sub"; ?>
                                                </div>
                                            </div>
                                            <div class="tick rtl">
                                                <?php if (!empty($format)): ?>
                                                <div class="tick-item tick-format"><?=$format?></div>
                                                <?php endif; ?>
                                            </div>
                                            <img class="film-poster-img lazyload"
                                                data-src="<?=$imgUrl?>"
                                                src="<?=$websiteUrl?>/files/images/no_poster.jpg"
                                                alt="<?=$title?>">
                                            <a class="film-poster-ahref"
                                                href="/anime/<?=$animeId?>/<?=$url_title?>"
                                                title="<?=$title?>"
                                                data-jname="<?=$title?>"><i class="fas fa-play"></i></a>
                                        </div>
                                        <div class="film-detail">
                                            <h3 class="film-name">
                                                <a
                                                    href="/anime/<?=$animeId?>/<?=$url_title?>"
                                                    title="<?=$title?>"
                                                    data-jname="<?=$title?>"><?=$title?></a>
                                            </h3>
                                            <div class="description">
                                                <?php if (isset($anime['genres']) && is_array($anime['genres']) && !empty($anime['genres'])): ?>
                                                <div class="fd-genres">
                                                    <span class="fdi-item"><?=implode(', ', array_slice($anime['genres'], 0, 3))?></span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="fd-infor">
                                                <span class="fdi-item"><?=$releaseYear?></span>
                                                <?php if (isset($anime['averageScore']) && $anime['averageScore'] > 0): ?>
                                                <span class="fdi-item fdi-score"><i class="fas fa-star mr-1"></i><?=number_format($anime['averageScore'] / 10, 1)?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                <?php 
                                        }
                                    }
                                }
                                ?>

                                </div>
                                <div class="clearfix"></div>
                                <style>
                                    .cus_pagi {
                                        margin-top: 7px;
                                    }

                                    div.cus_pagi input {
                                        background: #575757;
                                        color: #fff;
                                        border: 0;
                                        width: 56px;
                                        text-align: center;
                                        border-radius: 2px;
                                        height: 28px;
                                        outline: 0;
                                    }

                                    button.btn.btn-xs.btn-primary {
                                        padding: 7px 11px;
                                        height: 26px;
                                        margin-top: 12px;
                                        border-radius: 2px;
                                    }
                                    
                                    .tick-format {
                                        background-color: #4a4a4a;
                                        color: #fff;
                                        font-size: 10px;
                                        padding: 2px 4px;
                                        border-radius: 3px;
                                    }
                                    
                                    .fd-genres {
                                        font-size: 11px;
                                        color: #aaa;
                                        margin-bottom: 5px;
                                    }
                                    
                                    .fdi-score {
                                        color: #ffffff;
                                    }
                                    
                                    .fdi-score i {
                                        color: #ffc107;
                                        font-size: 12px;
                                    }
                                </style>
                                <div class="pagination">
                                    <nav>
                                        <ul class="ulclear az-list">
                                        <?php 
                                        // Generate pagination using AniList pageInfo
                                        if (isset($pageInfo)) {
                                            $currentUrl = $websiteUrl.'/genre/'.$id;
                                            echo format_pagination($pageInfo, $currentUrl);
                                        }
                                        ?>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <div class="clearfix"></div>
                </div>
                <?php include('../_php/sidenav.php'); ?>
                <div class="clearfix"></div>
            </div>
        </div>
        <?php include('../_php/footer.php'); ?>
        <div id="mask-overlay"></div>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        
        <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>
        <script type="text/javascript" src="<?=$websiteUrl?>/files/js/app.js"></script>
        <script type="text/javascript" src="<?=$websiteUrl?>/files/js/comman.js"></script>
        <script type="text/javascript" src="<?=$websiteUrl?>/files/js/movie.js"></script>
        <link rel="stylesheet" href="<?=$websiteUrl?>/files/css/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script type="text/javascript" src="<?=$websiteUrl?>/files/js/function.js"></script>
    </div>
</body>

</html>