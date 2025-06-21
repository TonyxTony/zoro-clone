<?php 
require('./_config.php'); 
require('./_php/anilist_api.php');
session_start();

// Get the keyword and page parameters
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search_type = 'anime'; // Default to anime

// Create a clean copy of the keyword for display and API use
$keyword2 = $keyword;
$keyword = str_replace(' ', '%20', $keyword);
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>Search results for "<?=$keyword2?>" on <?=$websiteTitle?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="title" content="Search results for "<?=$keyword2?>" on <?=$websiteTitle?>">
    <meta name="description" content="Search results for "<?=$keyword2?>" on <?=$websiteTitle?> - Watch anime and read manga online">
    <meta name="keywords"
        content="<?=$websiteTitle?>, watch anime online, free anime, anime stream, anime hd, english sub, kissanime, gogoanime, animeultima, 9anime, 123animes, <?=$websiteTitle?>, vidstreaming, gogo-stream, animekisa, zoro.to, gogoanime.run, animefrenzy, animekisa">
    <meta name="charset" content="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="robots" content="noindex, follow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Language" content="en">
    <meta property="og:title" content="Search results for "<?=$keyword2?>" on <?=$websiteTitle?>">
    <meta property="og:description"
        content="Search results for "<?=$keyword2?>" on <?=$websiteTitle?> - Watch anime and read manga online">
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
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css?v=<?=$version?>"
        type="text/css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css?v=<?=$version?>"
        type="text/css">
    <link rel="apple-touch-icon" href="<?=$websiteUrl?>/favicon.png?v=<?=$version?>" />
    <link rel="shortcut icon" href="<?=$websiteUrl?>/favicon.png?v=<?=$version?>" type="image/x-icon"/>
    <link rel="apple-touch-icon" sizes="180x180" href="<?=$websiteUrl?>/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?=$websiteUrl?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?=$websiteUrl?>/favicon-16x16.png">
    <link rel="mask-icon" href="<?=$websiteUrl?>/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="icon" sizes="192x192" href="<?=$websiteUrl?>/files/images/touch-icon-192x192.png?v=<?=$version?>">
    <link rel="stylesheet" href="<?=$websiteUrl?>/files/css/style.css?v=<?=$version?>">

    <link rel="stylesheet" href="<?=$websiteUrl?>/files/css/min.css?v=<?=$version?>?v=<?=$version?>">
    <script type="text/javascript">
    setTimeout(function() {
        var wpse326013 = document.createElement('link');
        wpse326013.rel = 'stylesheet';
        wpse326013.href =
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css?v=<?=$version?>';
        wpse326013.type = 'text/css';
        var godefer = document.getElementsByTagName('link')[0];
        godefer.parentNode.insertBefore(wpse326013, godefer);
        var wpse326013_2 = document.createElement('link');
        wpse326013_2.rel = 'stylesheet';
        wpse326013_2.href =
            'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css?v=<?=$version?>';
        wpse326013_2.type = 'text/css';
        var godefer2 = document.getElementsByTagName('link')[0];
        godefer2.parentNode.insertBefore(wpse326013_2, godefer2);
    }, 500);
    </script>
    <noscript>
        <link rel="stylesheet" type="text/css"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css?v=<?=$version?>" />
        <link rel="stylesheet" type="text/css"
            href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css?v=<?=$version?>" />
    </noscript>
    <script></script>
</head>

<body data-page="page_anime">
    <div id="sidebar_menu_bg"></div>
    <div id="wrapper" data-page="page_home">
        <?php include('./_php/header.php'); ?>
        <div class="clearfix"></div>
        <div id="main-wrapper">
            <div class="container">
                <div id="main-content">
                    <section class="block_area block_area_category">
                        <div class="block_area-header">
                            <div class="float-left bah-heading mr-4">
                                <h2 class="cat-heading">SEARCH RESULTS</h2>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="tab-content">
                            <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">
                                <div class="film_list-wrap">

                                    <?php 
                                // Use AniList API search function
                                $results = search_anime($keyword2, $page, 20, 'POPULARITY_DESC');
                                
                                if (isset($results['data']) && isset($results['data']['Page']) && isset($results['data']['Page']['media'])) {
                                    $mediaList = $results['data']['Page']['media'];
                                    
                                    foreach($mediaList as $media) {
                                        // Determine if it's a dub by checking if title contains "(Dub)"
                                        $title = $media['title']['english'] ?: $media['title']['romaji'];
                                        $isDub = (strpos($title, '(Dub)') !== false);
                                        $displayTitle = $title;
                                        
                                        // Get image URL
                                        $imgUrl = $media['coverImage']['large'] ?: $media['coverImage']['medium'];
                                        
                                        // Get status
                                        $status = $media['status'] ? strtolower(str_replace('_', ' ', $media['status'])) : 'unknown';
                                        
                                        // Get media ID
                                        $mediaId = $media['id'];
                                        
                                        // Create URL-friendly title
                                        $url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $displayTitle);
                                        $url_title = preg_replace('/-+/', '-', $url_title);
                                        $url_title = trim($url_title, '-');
                                        
                                        // Determine URL prefix
                                        $urlPrefix = '/anime/';
                                ?>
                                    <div class="flw-item">
                                        <div class="film-poster">
                                            <div class="tick ltr">
                                                <div class="tick-item-<?php echo $isDub ? 'dub' : 'sub'; ?> tick-eps amp-algn">
                                                    <?php echo $isDub ? 'Dub' : 'Sub'; ?>
                                                </div>
                                            </div>
                                            <div class="tick rtl">
                                                <?php if (isset($media['format']) && !empty($media['format'])): ?>
                                                <div class="tick-item tick-format"><?=$media['format']?></div>
                                                <?php endif; ?>
                                            </div>
                                            <img class="film-poster-img lazyload" data-src="<?=$imgUrl?>"
                                                src="<?=$websiteUrl?>/files/images/no_poster.jpg"
                                                alt="<?=$displayTitle?>">
                                            <a class="film-poster-ahref" href="<?=$urlPrefix?><?=$mediaId?>/<?=$url_title?>"
                                                title="<?=$displayTitle?>" data-jname="<?=$displayTitle?>">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        </div>
                                        <div class="film-detail">
                                            <h3 class="film-name">
                                                <a href="<?=$urlPrefix?><?=$mediaId?>/<?=$url_title?>" title="<?=$displayTitle?>"
                                                    data-jname="<?=$displayTitle?>"><?=$displayTitle?></a>
                                            </h3>
                                            <div class="description">
                                                <?php if (isset($media['genres']) && is_array($media['genres']) && !empty($media['genres'])): ?>
                                                <div class="fd-genres">
                                                    <span class="fdi-item"><?=implode(', ', array_slice($media['genres'], 0, 3))?></span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="fd-infor">
                                                <span class="fdi-item"><?=$status?></span>
                                                <?php if (isset($media['averageScore']) && $media['averageScore'] > 0): ?>
                                                <span class="fdi-item fdi-score"><i class="fas fa-star mr-1"></i><?=number_format($media['averageScore'] / 10, 1)?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <?php 
                                    } 
                                } 
                                ?>

                                    <?php
                                if (!isset($results['data']) || !isset($results['data']['Page']) || !isset($results['data']['Page']['media']) || count($results['data']['Page']['media']) == 0) { ?>

                                    <div class="tab-content">
                                        <style>
                                        .marginLeft {
                                            margin-left: 10px;
                                        }

                                        @media screen and (max-width: 576px) {

                                            .marginLeft {
                                                margin-left: 40px;
                                            }
                                        }
                                        </style>
                                        <div
                                            class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">
                                            <div class="marginLeft">
                                                No results found for "<?=$keyword2?>" in anime
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="clearfix"></div>
                                <div class="pagination">
                                    <nav>
                                        <ul class="ulclear az-list">
                                            <?php 
                                            if (isset($results['data']) && isset($results['data']['Page']) && isset($results['data']['Page']['pageInfo'])) {
                                                $pageInfo = $results['data']['Page']['pageInfo'];
                                                
                                                // Create proper pagination URL
                                                $paginationBaseUrl = '/search';
                                                
                                                // First page navigation
                                                if ($pageInfo['currentPage'] > 1) {
                                                    echo '<li><a href="' . $paginationBaseUrl . '?keyword=' . urlencode($keyword2) . '&page=1" title="First Page"><i class="fas fa-angle-double-left"></i></a></li>';
                                                    echo '<li><a href="' . $paginationBaseUrl . '?keyword=' . urlencode($keyword2) . '&page=' . ($pageInfo['currentPage'] - 1) . '" title="Previous Page"><i class="fas fa-angle-left"></i></a></li>';
                                                }
                                                
                                                // Page numbers
                                                $startPage = max(1, $pageInfo['currentPage'] - 2);
                                                $endPage = min($pageInfo['lastPage'], $pageInfo['currentPage'] + 2);
                                                
                                                for ($i = $startPage; $i <= $endPage; $i++) {
                                                    $activeClass = ($i == $pageInfo['currentPage']) ? 'active' : '';
                                                    echo '<li class="' . $activeClass . '"><a href="' . $paginationBaseUrl . '?keyword=' . urlencode($keyword2) . '&page=' . $i . '" title="Page ' . $i . '">' . $i . '</a></li>';
                                                }
                                                
                                                // Next/Last page navigation
                                                if ($pageInfo['hasNextPage']) {
                                                    echo '<li><a href="' . $paginationBaseUrl . '?keyword=' . urlencode($keyword2) . '&page=' . ($pageInfo['currentPage'] + 1) . '" title="Next Page"><i class="fas fa-angle-right"></i></a></li>';
                                                    echo '<li><a href="' . $paginationBaseUrl . '?keyword=' . urlencode($keyword2) . '&page=' . $pageInfo['lastPage'] . '" title="Last Page"><i class="fas fa-angle-double-right"></i></a></li>';
                                                }
                                            } else {
                                                // Fallback pagination
                                                $currentPage = $page;
                                                $totalPages = 10; // Default to 10 pages if we don't know total
                                                
                                                if ($currentPage > 1) {
                                                    echo '<li><a href="/search?keyword=' . urlencode($keyword2) . '&page=1" title="First Page"><i class="fas fa-angle-double-left"></i></a></li>';
                                                    echo '<li><a href="/search?keyword=' . urlencode($keyword2) . '&page=' . ($currentPage - 1) . '" title="Previous Page"><i class="fas fa-angle-left"></i></a></li>';
                                                }
                                                
                                                $startPage = max(1, $currentPage - 2);
                                                $endPage = min($totalPages, $currentPage + 2);
                                                
                                                for ($i = $startPage; $i <= $endPage; $i++) {
                                                    $activeClass = ($i == $currentPage) ? 'active' : '';
                                                    echo '<li class="' . $activeClass . '"><a href="/search?keyword=' . urlencode($keyword2) . '&page=' . $i . '" title="Page ' . $i . '">' . $i . '</a></li>';
                                                }
                                                
                                                if ($currentPage < $totalPages) {
                                                    echo '<li><a href="/search?keyword=' . urlencode($keyword2) . '&page=' . ($currentPage + 1) . '" title="Next Page"><i class="fas fa-angle-right"></i></a></li>';
                                                    echo '<li><a href="/search?keyword=' . urlencode($keyword2) . '&page=' . $totalPages . '" title="Last Page"><i class="fas fa-angle-double-right"></i></a></li>';
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
        <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js?v=<?=$version?>"></script>

        <script type="text/javascript"
            src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js?v=<?=$version?>"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>
        <script type="text/javascript" src="<?=$websiteUrl?>/files/js/app.js?v=<?=$version?>"></script>
        <script type="text/javascript" src="<?=$websiteUrl?>/files/js/comman.js?v=<?=$version?>"></script>
        <script type="text/javascript" src="<?=$websiteUrl?>/files/js/movie.js?v=<?=$version?>"></script>
        <link rel="stylesheet" href="<?=$websiteUrl?>/files/css/jquery-ui.css?v=<?=$version?>">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js?v=<?=$version?>"></script>
        <script type="text/javascript" src="<?=$websiteUrl?>/files/js/function.js?v=<?=$version?>"></script>

        <div style="display:none;">
        </div>
    </div>
</body>

</html>