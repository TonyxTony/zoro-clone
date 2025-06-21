<?php
session_start();
require('./_config.php');
require_once('./_php/anilist_api.php');

if(!isset($_GET['page'])){
    $page = 1;
}else{
    $page = $_GET['page']; 
}
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>Anime List on <?=$websiteTitle?></title>
    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="title" content="Anime List on <?=$websiteTitle?>">
    <meta name="description" content="Anime List in HD with No Ads. Watch anime online">
    <meta name="keywords" content="<?=$websiteTitle?>, watch anime online, free anime, anime stream, anime hd, english sub, kissanime, gogoanime, animeultima, 9anime, 123animes, <?=$websiteTitle?>, vidstreaming, gogo-stream, animekisa, zoro.to, gogoanime.run, animefrenzy, animekisa">
    <meta name="charset" content="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Language" content="en">
    <meta property="og:title" content="Anime List on <?=$websiteTitle?>">
    <meta property="og:description" content="Anime List on <?=$websiteTitle?> in HD with No Ads. Watch anime online">
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
        <link rel="stylesheet" type="text/css"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" />
        <link rel="stylesheet" type="text/css"
            href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css" />
    </noscript>
    <script></script>
</head>

<body data-page="page_anime">
    <div id="sidebar_menu_bg"></div>
    <div id="wrapper" data-page="page_home">
        <?php include('./_php/header.php')?>
        <div class="clearfix"></div>
        <div id="main-wrapper">
            <div class="container">
                <div id="main-content">
                    <section class="block_area block_area_category">
                        <div class="block_area-header">
                            <div class="float-left bah-heading mr-4">
                                <h2 class="cat-heading">Anime List</h2>
                            </div>
                            <div class="float-right bah-result">
                                <div class="cmb-item">
                                    <div class="nl-item">
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="tab-content">
                            <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">
                                <div class="film_list-wrap">

                                <?php 
                                // Use AniList API to get popular anime list
                                $result = get_popular_anime($page, 24);
                                
                                if (isset($result['data']) && isset($result['data']['Page']) && isset($result['data']['Page']['media'])) {
                                    $animeList = $result['data']['Page']['media'];
                                    $pageInfo = $result['data']['Page']['pageInfo'];
                                    $totalItems = count($animeList);

                                    foreach($animeList as $key => $anime) { 
                                        $animeTitle = !empty($anime['title']['english']) ? $anime['title']['english'] : $anime['title']['romaji'];
                                        $url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $animeTitle);
                                        $url_title = preg_replace('/-+/', '-', $url_title);
                                        $url_title = trim($url_title, '-');
                                        ?>
                                    <div class="flw-item">
                                        <div class="film-poster">
                                            <img class="film-poster-img lazyload" data-src="<?=$anime['coverImage']['large']?>"
                                                src="<?=$websiteUrl?>/files/images/no_poster.jpg" alt="<?=$animeTitle?>">
                                            <a class="film-poster-ahref" href="/anime/<?=$anime['id']?>/<?=$url_title?>" title="<?=$animeTitle?>"
                                                data-jname="<?=$anime['title']['romaji']?>"><i class="fas fa-play"></i></a>
                                        </div>
                                        <div class="film-detail">
                                            <h3 class="film-name">
                                                <a
                                                    href="/anime/<?=$anime['id']?>/<?=$url_title?>" title="<?=$animeTitle?>"
                                                    data-jname="<?=$anime['title']['romaji']?>"><?=$animeTitle?></a>
                                            </h3>
                                            <div class="fd-infor">
                                                <span class="fdi-item"># <?php echo (24 * ($page - 1)) + $key+1 ?></span>
                                                <span class="dot"></span>
                                                <span class="fdi-item"><?php echo $anime['format'] ?? 'Unknown'; ?></span>
                                                <?php if (!empty($anime['averageScore'])) { ?>
                                                <span class="dot"></span>
                                                <span class="fdi-item"><?=$anime['averageScore']?>%</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                <?php } 
                                } else {
                                    echo '<div class="alert alert-warning">No anime found or API error.</div>';
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
                                </style>
                                <div class="pagination">
                                    <nav>
                                        <ul class="ulclear az-list">
                                        <?php
                                        if (isset($pageInfo)) {
                                            // Generate pagination using the AniList pageInfo
                                            $baseUrl = "/anime";
                                            
                                            // Previous page
                                            if ($pageInfo['currentPage'] > 1) {
                                                echo '<li><a href="' . $baseUrl . '?page=1" title="First Page"><i class="fas fa-angle-double-left"></i></a></li>';
                                                echo '<li><a href="' . $baseUrl . '?page=' . ($pageInfo['currentPage'] - 1) . '" title="Previous Page"><i class="fas fa-angle-left"></i></a></li>';
                                            }
                                            
                                            // Calculate page range
                                            $startPage = max(1, $pageInfo['currentPage'] - 2);
                                            $endPage = min($pageInfo['lastPage'], $pageInfo['currentPage'] + 2);
                                            
                                            // Page numbers
                                            for ($i = $startPage; $i <= $endPage; $i++) {
                                                if ($i == $pageInfo['currentPage']) {
                                                    echo '<li class="active"><a href="' . $baseUrl . '?page=' . $i . '" title="Page ' . $i . '">' . $i . '</a></li>';
                                                } else {
                                                    echo '<li><a href="' . $baseUrl . '?page=' . $i . '" title="Page ' . $i . '">' . $i . '</a></li>';
                                                }
                                            }
                                            
                                            // Next page
                                            if ($pageInfo['hasNextPage']) {
                                                echo '<li><a href="' . $baseUrl . '?page=' . ($pageInfo['currentPage'] + 1) . '" title="Next Page"><i class="fas fa-angle-right"></i></a></li>';
                                                echo '<li><a href="' . $baseUrl . '?page=' . $pageInfo['lastPage'] . '" title="Last Page"><i class="fas fa-angle-double-right"></i></a></li>';
                                            }
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
                <?php include('./_php/sidenav.php'); ?>
                <div class="clearfix"></div>
            </div>
        </div>
        <?php include('./_php/footer.php'); ?>
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