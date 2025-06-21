<?php 
session_start();
require('../_config.php'); 
require('../_php/anilist_api.php'); // Include the AniList API functions

$parts=parse_url($_SERVER['REQUEST_URI']); 
$page_url=explode('/', $parts['path']);
$url = $page_url[count($page_url)-1];
$name = str_replace("-", " ", $url);
$name = ucfirst($name);
if(!isset($_GET['page'])){
    $page = 1;
}else{
    $page = $_GET['page']; 
}
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title><?=$name?> on <?=$websiteTitle?></title>
    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="title" content="<?=$name?> on <?=$websiteTitle?>">
    <meta name="description" content="Tv Series in HD with No Ads. Watch anime online">
    <meta name="keywords" content="<?=$websiteTitle?>, watch anime online, free anime, anime stream, anime hd, english sub, kissanime, gogoanime, animeultima, 9anime, 123animes, <?=$websiteTitle?>, vidstreaming, gogo-stream, animekisa, zoro.to, gogoanime.run, animefrenzy, animekisa">
    <meta name="charset" content="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Language" content="en">
    <meta property="og:title" content="<?=$name?> on <?=$websiteTitle?>">
    <meta property="og:description" content="<?=$name?> on <?=$websiteTitle?> in HD with No Ads. Watch anime online">
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
                                <h2 class="cat-heading"><?=$name?></h2>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="tab-content">
                            <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">
                                <div class="film_list-wrap">

                                <?php 
                                // Convert URL to season name for AniList query
                                $season = null;
                                $year = null;
                                
                                // Parse seasonal URLs like "fall-2023-anime"
                                if (strpos($url, '-anime') !== false) {
                                    $parts = explode('-', $url);
                                    if (count($parts) >= 3) {
                                        $season = strtoupper($parts[0]);
                                        $year = intval($parts[1]);
                                    }
                                } else {
                                $season = strtoupper($url);
                                }
                                
                                // Debug info
                                // echo "<div class='alert alert-info'>Debug: URL=$url, Season=$season, Year=$year</div>";
                                
                                // Determine the type of query based on URL
                                $isSeasonQuery = in_array($season, ['WINTER', 'SPRING', 'SUMMER', 'FALL']);
                                $isFormatQuery = in_array(strtolower($url), ['ova', 'ona', 'special']);
                                
                                // Set up query variables
                                $perPage = 20;
                                $response = null;
                                
                                if ($isSeasonQuery && $year) {
                                    // Season query with year
                                    $query = '
                                    query ($page: Int, $perPage: Int, $season: MediaSeason, $seasonYear: Int) {
                                        Page(page: $page, perPage: $perPage) {
                                            pageInfo {
                                                total
                                                currentPage
                                                lastPage
                                                hasNextPage
                                                perPage
                                            }
                                            media(type: ANIME, season: $season, seasonYear: $seasonYear, sort: POPULARITY_DESC) {
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
                                                bannerImage
                                                episodes
                                                status
                                                season
                                                seasonYear
                                                format
                                                genres
                                                averageScore
                                            }
                                        }
                                    }';
                                    
                                    $variables = [
                                        'page' => intval($page),
                                        'perPage' => $perPage,
                                        'season' => $season,
                                        'seasonYear' => $year
                                    ];
                                    
                                    $response = anilist_query($query, $variables);
                                } elseif ($isSeasonQuery) {
                                    // Fallback for season without a year - use current year
                                    $year = date('Y');
                                    
                                    $query = '
                                    query ($page: Int, $perPage: Int, $season: MediaSeason, $seasonYear: Int) {
                                        Page(page: $page, perPage: $perPage) {
                                            pageInfo {
                                                total
                                                currentPage
                                                lastPage
                                                hasNextPage
                                                perPage
                                            }
                                            media(type: ANIME, season: $season, seasonYear: $seasonYear, sort: POPULARITY_DESC) {
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
                                                bannerImage
                                                episodes
                                                status
                                                season
                                                seasonYear
                                                format
                                                genres
                                                averageScore
                                            }
                                        }
                                    }';
                                    
                                    $variables = [
                                        'page' => intval($page),
                                        'perPage' => $perPage,
                                        'season' => $season,
                                        'seasonYear' => intval($year)
                                    ];
                                    
                                    $response = anilist_query($query, $variables);
                                } elseif ($isFormatQuery) {
                                    // Format query for OVA, ONA, or SPECIAL
                                    $formats = [strtoupper($url)]; // Convert url to uppercase for API
                                    $response = get_anime_by_formats($formats, intval($page), $perPage);
                                } else {
                                    // Genre query
                                    $genre = $name;
                                    
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
                                            media(type: ANIME, genre: $genre, sort: POPULARITY_DESC) {
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
                                                bannerImage
                                                episodes
                                                status
                                                season
                                                seasonYear
                                                format
                                                genres
                                                averageScore
                                            }
                                        }
                                    }';
                                    
                                    $variables = [
                                        'page' => intval($page),
                                        'perPage' => $perPage,
                                        'genre' => $genre
                                    ];
                                    
                                    $response = anilist_query($query, $variables);
                                }
                                
                                // Check if response exists and has the expected structure
                                if (isset($response['data']) && isset($response['data']['Page']) && isset($response['data']['Page']['media'])) {
                                    $animes = $response['data']['Page']['media'];
                                    $pageInfo = $response['data']['Page']['pageInfo'];
                                    
                                    if (empty($animes)) {
                                        echo '<div class="alert alert-warning">No anime found in this category.</div>';
                                    } else {
                                        foreach($animes as $anime) { 
                                            // Make sure we have a title to display
                                            if (isset($anime['title'])) {
                                                $title = !empty($anime['title']['english']) ? $anime['title']['english'] : $anime['title']['romaji'];
                                                $isDub = false; // AniList doesn't directly provide dub info
                                                $imgUrl = isset($anime['coverImage']['large']) ? $anime['coverImage']['large'] : $websiteUrl.'/files/images/no_poster.jpg';
                                                $animeId = $anime['id'];
                                                $status = isset($anime['status']) ? $anime['status'] : 'UNKNOWN';
                                                $format = isset($anime['format']) ? $anime['format'] : 'UNKNOWN';
                                                
                                                // Create URL-friendly title
                                                $url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $title);
                                                $url_title = preg_replace('/-+/', '-', $url_title);
                                                $url_title = trim($url_title, '-');
                                                
                                                // Get genres if available
                                                $genres = [];
                                                if (isset($anime['genres']) && is_array($anime['genres'])) {
                                                    $genres = $anime['genres'];
                                                }
                                            ?>
                                                <div class="flw-item">
                                                    <div class="film-poster">
                                                    <div class="tick ltr">
                                                            <div class="tick-item-<?php echo $isDub ? "dub" : "sub"; ?> tick-eps amp-algn">
                                                                <?php echo $isDub ? "Dub" : "Sub"; ?>
                                                            </div>
                                                        </div>
                                                        <div class="tick rtl">
                                                            <?php if (!empty($format) && $format !== 'UNKNOWN'): ?>
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
                                                            <?php if (!empty($genres)): ?>
                                                            <div class="fd-genres">
                                                                <span class="fdi-item"><?=implode(', ', array_slice($genres, 0, 3))?></span>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="fd-infor">
                                                            <span class="fdi-item"><?=$format?></span>
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
                                    
                                    // Pagination info
                                    if (isset($pageInfo)) {
                                        $currentUrl = $websiteUrl.'/sub-category/'.$url;
                                        $pagination = format_pagination($pageInfo, $currentUrl);
                                    }
                                } else {
                                    // Display error message if API response is not as expected
                                    echo '<div class="alert alert-danger">Error: Could not fetch anime data. Please try again later.</div>';
                                    // For debugging purposes - you can comment this out in production
                                    // echo '<div style="display:none;">Debug info: ' . json_encode($response) . '</div>';
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
                                        <?php if (isset($pagination)) echo $pagination; ?>
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