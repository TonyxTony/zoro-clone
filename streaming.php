<?php
require('./_config.php');
require('./_php/anilist_api.php');
session_start();

// Parse the URL to get the episode ID and title
$parts = parse_url($_SERVER['REQUEST_URI']);
$page_url = explode('/', $parts['path']);

// New URL format: /watch/{animeID}/{title}/episode-{number}
$animeID = $page_url[2]; // Get anime ID
$anime_title = urldecode($page_url[3]); // Get title
$episode_part = $page_url[4]; // Get episode part
$episodeNumber = intval(str_replace('episode-', '', $episode_part));

// Check for dub in the anime slug
$slug = explode('-', $animeID);
$dub = (end($slug) == 'dub') ? "dub" : "sub";

// Create episode ID in the format needed for the API
$url = $animeID . '-episode-' . $episodeNumber;

$pageID = $url;
$CurPageURL = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$pageUrl = $CurPageURL;


$anilistID = intval($animeID); // assume the route id is the AniList ID
$detailsResult = get_anime_details($anilistID);
if (!isset($detailsResult['data']['Media'])) {
    header('Location: home.php');
    exit;
}

$media = $detailsResult['data']['Media'];
$ANIME_name     = $media['title']['english']    ?? $media['title']['romaji'] ?? $media['title']['native'];
// Generate a URL-friendly slug from the anime title (letters, numbers, dashes only)
$url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $ANIME_name);
$url_title = preg_replace('/-+/', '-', $url_title);
$url_title = trim($url_title, '-');
$ANIME_NAME     = rtrim($ANIME_name);
$ANIME_IMAGE    = $media['coverImage']['extraLarge'] ?? $media['coverImage']['large'] ?? $media['coverImage']['medium'];
$ANIME_TYPE     = $media['format'] ?? '';
$ANIME_RELEASED = $media['seasonYear'] ?? '';
$synopsis       = strip_tags($media['description'] ?? '');

// recommendations and other extras
$animeDetails = [
    'anilist_data' => $media,
];

// dub status already determined from URL slug; keep existing value

// --- NEW IMPLEMENTATION USING GLOBAL MAPPER API ---
// Fetch full series + episode information from the mapper endpoint
$mapperUrl = $api . "/mapper/" . $anilistID;
$mapperResponse = @file_get_contents($mapperUrl);
$mapperData = json_decode($mapperResponse, true);

if (!$mapperData || !isset($mapperData['episodes'])) {
    // If mapper API fails fall back to home
    header('Location: home.php');
    exit;
}

// Sort episodes to ensure natural order (just in case)
$episodes = $mapperData['episodes'];
usort($episodes, function ($a, $b) {
    return $a['number'] <=> $b['number'];
});

// Find index of the requested episode
$currentIndex = array_search($episodeNumber, array_column($episodes, 'number'));

// Guard for invalid episode number
if ($currentIndex === false) {
    header('Location: home.php');
    exit;
}

$currentEpisode = $episodes[$currentIndex];
$prevEpisode   = $currentIndex > 0 ? $episodes[$currentIndex - 1] : null;
$nextEpisode   = $currentIndex < (count($episodes) - 1) ? $episodes[$currentIndex + 1] : null;
    
// Get like/dislike stats from database or initialise them
$id = $currentEpisode['id'];
// Ensure a row exists for this episode in pageview table
mysqli_query($conn, "INSERT INTO `pageview` (id, like_count, dislike_count) VALUES ('$id', 0, 0) ON DUPLICATE KEY UPDATE id=id");

$statsRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT like_count, dislike_count FROM `pageview` WHERE id='$id'"));
$like_count = isset($statsRow['like_count']) ? intval($statsRow['like_count']) : 0;
$dislike_count = isset($statsRow['dislike_count']) ? intval($statsRow['dislike_count']) : 0;
$totalVotes = $like_count + $dislike_count;
$counter = $totalVotes; // if you need separate view counter, adjust accordingly

// ------------------------------------------------------------------
// CONTINUE WATCHING: save progress for logged-in users
$EPISODE_NUMBER = $episodeNumber; // alias for clarity
if (isset($_COOKIE['userID'])) {
    $userID = $_COOKIE['userID'];
    $cleanAnimeID = $animeID; // anime id without episode suffix

    // Check if record exists
    $checkStmt = $conn->prepare("SELECT id FROM `user_history` WHERE user_id = ? AND anime_id = ? LIMIT 1");
    $checkStmt->bind_param("ss", $userID, $cleanAnimeID);
    $checkStmt->execute();
    $checkStmt->store_result();
    $exists = $checkStmt->num_rows > 0;
    $checkStmt->bind_result($historyId);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($exists) {
        // remove existing so we can insert updated progress
        $delStmt = $conn->prepare("DELETE FROM `user_history` WHERE id = ?");
        $delStmt->bind_param("s", $historyId);
        $delStmt->execute();
        $delStmt->close();
    }

    // Insert fresh row
    $insertStmt = $conn->prepare("INSERT INTO `user_history` (user_id, anime_id, anime_title, anime_ep, anime_image, anime_release, dubOrSub, anime_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("ssssssss", $userID, $cleanAnimeID, $ANIME_name, $EPISODE_NUMBER, $ANIME_IMAGE, $ANIME_RELEASED, $dub, $ANIME_TYPE);
    $insertStmt->execute();
    $insertStmt->close();
}
// ------------------------------------------------------------------

// Player ID is now built inline where required (see iframe & server1 links).

// Prepare full episode list (also normalise the title key to maintain compatibility with the template)
$episodeList = array_map(function ($ep) {
    $ep['title'] = $ep['name_english'] ?? $ep['name'] ?? '';
    return $ep;
}, $episodes);

// Create episode titles for meta tags, etc.
$animeNameWithEP = "$ANIME_name Episode $episodeNumber";

// Get recommendations data from AniList
$animeData = [];
if (isset($animeDetails['anilist_data'])) {
    $animeData['data'] = [
        'Media' => $animeDetails['anilist_data']
    ];
}

// Populate variables for HTML templates
$anilistData = isset($animeDetails['anilist_data']) ? $animeDetails['anilist_data'] : [];

// Player ID is now built inline where required (see iframe & server1 links).
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>Watch <?= $animeNameWithEP ?> on <?= $websiteTitle ?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="title" content="Watch <?= $animeNameWithEP ?> on <?= $websiteTitle ?>">
    <meta name="description" content="<?= substr($synopsis, 0, 150) ?> ... at <?= $websiteUrl ?>">
    <meta name="keywords" content="<?= $websiteTitle ?>, <?= $animeNameWithEP ?>, <?= $ANIME_name ?>, watch anime online, free anime, anime stream, anime hd, english sub">
    <meta name="charset" content="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="robots" content="index, follow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Language" content="en">
    <meta property="og:title" content="Watch <?= $animeNameWithEP ?> on <?= $websiteTitle ?>">
    <meta property="og:description" content="<?= substr($synopsis, 0, 150) ?> ... at <?= $websiteUrl ?>">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">
    <meta property="og:url" content="<?= $websiteUrl ?>/watch/<?= $animeID ?>/<?=$url_title?>/episode-<?=$episodeNumber?>">
    <meta itemprop="image" content="<?= $ANIME_IMAGE ?>">
    <meta property="og:image" content="<?= $ANIME_IMAGE ?>">
    <meta property="twitter:title" content="Watch <?= $animeNameWithEP ?> on <?= $websiteTitle ?>">
    <meta property="twitter:description" content="<?= substr($synopsis, 0, 150) ?> ... at <?= $websiteUrl ?>">
    <meta property="twitter:url" content="<?= $websiteUrl ?>/watch/<?= $animeID ?>/<?=$url_title?>/episode-<?=$episodeNumber?>">
    <meta property="twitter:card" content="summary">
    <meta name="apple-mobile-web-app-status-bar" content="#202125">
    <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-63430163bc99824a"></script>
    <meta name="theme-color" content="#202125">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css"
        type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css"
        type="text/css">
    <link rel="apple-touch-icon" href="<?=$websiteUrl?>/favicon.png?v=<?=$version?>" />
    <link rel="shortcut icon" href="<?=$websiteUrl?>/favicon.png?v=<?=$version?>" type="image/x-icon"/>
    <link rel="apple-touch-icon" sizes="180x180" href="<?=$websiteUrl?>/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?=$websiteUrl?>/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?=$websiteUrl?>/favicon-16x16.png">
    <link rel="mask-icon" href="<?=$websiteUrl?>/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="icon" sizes="192x192" href="<?=$websiteUrl?>/files/images/touch-icon-192x192.png?v=<?=$version?>">
    <link rel="stylesheet" href="<?= $websiteUrl ?>/files/css/style.css?v=<?= $version ?>">
    <link rel="stylesheet" href="<?= $websiteUrl ?>/files/css/min.css?v=<?= $version ?>">
</head>

<body data-page="movie_watch">
    <div id="sidebar_menu_bg"></div>
    <div id="wrapper" data-page="movie_watch">
        <?php include('./_php/header.php'); ?>
        <div class="clearfix"></div>
        <div id="main-wrapper" date-page="movie_watch" data-id="">
            <div id="ani_detail">
                <div class="ani_detail-stage">
                    <div class="container">
                        <div class="anis-cover-wrap">
                            <div class="anis-cover"
                                style="background-image: url('<?= $websiteUrl ?>/files/images/banner.webp')">
                            </div>
                        </div>
                        <div class="anis-watch-wrap">
                            <div class="prebreadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li itemprop="itemListElement" itemscope=""
                                            itemtype="http://schema.org/ListItem" class="breadcrumb-item">
                                            <a itemprop="item" href="/"><span itemprop="name">Home</span></a>
                                            <meta itemprop="position" content="1">
                                        </li>
                                        <li itemprop="itemListElement" itemscope=""
                                            itemtype="http://schema.org/ListItem" class="breadcrumb-item">
                                            <a itemprop="item" href="/anime"><span itemprop="name">Anime</span></a>
                                            <meta itemprop="position" content="2">
                                        </li>
                                        <li itemprop="itemListElement" itemscope=""
                                            itemtype="http://schema.org/ListItem" class="breadcrumb-item"
                                            aria-current="page">
                                            <a itemprop="item" href="/anime/<?= $animeID ?>/<?=$url_title?>"><span
                                                    itemprop="name"><?= $ANIME_name ?></span></a>
                                            <meta itemprop="position" content="3">
                                        </li>
                                        <li itemprop="itemListElement" itemscope=""
                                            itemtype="http://schema.org/ListItem" class="breadcrumb-item"
                                            aria-current="page">
                                            <a itemprop="item" href="<?= $websiteUrl ?>/watch/<?= $animeID ?>/<?=$url_title?>/episode-<?=$episodeNumber?>"><span
                                                    itemprop="name">Episode <?= $episodeNumber ?></span></a>
                                            <meta itemprop="position" content="4">
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="anis-watch anis-watch-tv">
                                <div class="watch-player">
                                    <div class="player-frame">
                                        <div class="loading-relative loading-box" id="embed-loading">
                                            <div class="loading">
                                                <div class="span1"></div>
                                                <div class="span2"></div>
                                                <div class="span3"></div>
                                            </div>
                                        </div>
                                        <!---recommended to use Anikatsu Servers only ---->
                                        <iframe name="iframe-to-load"
                                            src="<?=$websiteUrl?>/player/v1.php?id=<?= urlencode($anilistID . '/' . $episodeNumber . '/sub') ?>" frameborder="0"
                                            scrolling="no"
                                            allow="accelerometer;autoplay;encrypted-media;gyroscope;picture-in-picture"
                                            allowfullscreen="true" webkitallowfullscreen="true"
                                            mozallowfullscreen="true"></iframe>
                                    </div>
                                    <div class="player-controls">
                                        <div class="pc-item pc-resize">
                                            <a href="javascript:;" id="media-resize" class="btn btn-sm"><i
                                                    class="fas fa-expand mr-1"></i>Expand</a>
                                        </div>
                                        <div class="pc-item pc-toggle pc-light">
                                            <div id="turn-off-light" class="toggle-basic">
                                                <span class="tb-name"><i class="fas fa-lightbulb mr-2"></i>Light</span>
                                                <span class="tb-result"></span>
                                            </div>
                                        </div>
                                        <div class="pc-item pc-download">
                                            <a class="btn btn-sm pc-download" href="<?=$websiteUrl?>/download/<?= urlencode($currentEpisode['id']) ?>" target="_blank"><i
                                                    class="fas fa-download mr-2"></i>Download</a>
                                        </div>
                                        <div class="pc-right">
                                            <?php if ($prevEpisode): ?>
                                                <div class="pc-item pc-control block-prev">
                                                    <a class="btn btn-sm btn-prev"
                                                        href="/watch/<?= $animeID ?>/<?=$url_title?>/episode-<?=$prevEpisode['number']?>"><i
                                                            class="fas fa-backward mr-2"></i>Prev</a>
                                                </div>&nbsp;
                                            <?php endif; ?>
                                            <?php if ($nextEpisode): ?>
                                                <div class="pc-item pc-control block-next">
                                                    <a class="btn btn-sm btn-next"
                                                        href="/watch/<?= $animeID ?>/<?=$url_title?>/episode-<?=$nextEpisode['number']?>"><i
                                                            class="fas fa-forward ml-2"></i>Next</a>
                                                </div>
                                            <?php endif; ?>
                                            <div class="pc-item pc-fav" id="watch-list-content"></div>
                                            <div class="pc-item pc-download" style="display:none;">
                                                <a class="btn btn-sm pc-download"><i
                                                        class="fas fa-download mr-2"></i>Download</a>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                                <div class="player-servers">
                                    <div id="servers-content">
                                        <div class="ps_-status">
                                            <div class="content">
                                                <div class="server-notice"><strong>Currently watching <b>Episode
                                                            <?= $episodeNumber ?>
                                                        </b></strong> Switch to alternate
                                                    servers in case of error.</div>
                                            </div>
                                        </div>
                                        <div class="ps_-block ps_-block-sub servers-mixed">
                                            <div class="ps__-title"><i class="fas fa-server mr-2"></i>SUB:</div>
                                            <div class="ps__-list">
                                                <div class="item">
                                                    <a id="server1" href="<?=$websiteUrl?>/player/v1.php?id=<?= $anilistID ?>/<?= $episodeNumber ?>/sub"
                                                        target="iframe-to-load" class="btn btn-server active">pahe</a>
                                                </div>
                                                <div class="item">
                                                    <a id="server2"
                                                        href="<?=$websiteUrl?>/player/v2.php?id=<?= $anilistID ?>/<?= $episodeNumber ?>/sub"
                                                        target="iframe-to-load" class="btn btn-server">church</a>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div id="source-guide"></div>
                                        </div>
                                        <div class="ps_-block ps_-block-sub servers-mixed">
                                            <div class="ps__-title"><i class="fas fa-server mr-2"></i>DUB:</div>
                                            <div class="ps__-list">
                                            <div class="item">
                                                    <a id="pahe"
                                                        href="<?=$websiteUrl?>/player/v3.php?id=<?= $anilistID ?>/<?= $episodeNumber ?>/dub"
                                                        target="iframe-to-load" class="btn btn-server">pahe</a>
                                                </div>
                                                <div class="item">
                                                    <a id="pahe"
                                                        href="<?=$websiteUrl?>/player/v4.php?id=<?= $anilistID ?>/<?= $episodeNumber ?>/dub"
                                                        target="iframe-to-load" class="btn btn-server">church</a>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div id="source-guide"></div>
                                        </div>
                                        <div class="ps_-block ps_-block-sub servers-mixed">
                                            <div class="ps__-title"><i class="fas fa-server mr-2"></i>MULTY:</div>
                                            <div class="ps__-list">
                                            <div class="item">
                                                    <a id="hindi"
                                                        href="<?=$websiteUrl?>/player/v5.php?id=<?= $anilistID ?>/<?= $episodeNumber ?>/hindi"
                                                        target="iframe-to-load" class="btn btn-server">hindi</a>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div id="source-guide"></div>
                                        </div>
                                    </div>
                                </div>

                                <div id="episodes-content">
                                    <div class="seasons-block seasons-block-max">
                                        <div id="detail-ss-list" class="detail-seasons">
                                            <div class="detail-infor-content">
                                                <div style="min-height:43px;" class="ss-choice">
                                                    <div class="ssc-list">
                                                        <div id="ssc-list" class="ssc-button">
                                                            <div class="ssc-label">List of episodes:</div>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div id="episodes-page-1" class="ss-list ss-list-min" data-page="1"
                                                    style="display:block;">

                                                    <?php foreach ($episodeList as $episode): ?>
                                                        <a title="Episode <?= $episode['number'] ?>"
                                                            class="ssl-item ep-item <?= ($episodeNumber == $episode['number']) ? 'active' : '' ?>"
                                                            href="/watch/<?= $animeID ?>/<?=$url_title?>/episode-<?=$episode['number']?>">
                                                            <div class="ssli-order" title="">
                                                                <?= $episode['number'] ?>
                                                            </div>
                                                            <div class="ssli-detail">
                                                                <div class="ep-name dynamic-name" data-jname="" title="<?= isset($episode['title']) ? $episode['title'] : '' ?>">
                                                                    <?= isset($episode['title']) ? $episode['title'] : '' ?>
                                                                </div>
                                                            </div>
                                                            <div class="ssli-btn">
                                                                <div class="btn btn-circle"><i class="fas fa-play"></i>
                                                                </div>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="anis-watch-detail">
                                <div class="anis-content">
                                    <div class="anisc-poster">
                                        <div class="film-poster">
                                            <img src="<?= $ANIME_IMAGE ?>" data-src="<?= $ANIME_IMAGE ?>"
                                                class="film-poster-img ls-is-cached lazyloaded"
                                                alt="<?= $ANIME_name ?>">
                                        </div>
                                    </div>
                                    <div class="anisc-detail">
                                        <h2 class="film-name">
                                            <a href="/anime/<?= $animeID ?>/<?=$url_title?>" class="text-white dynamic-name"
                                                title="<?= $ANIME_name ?>" data-jname="<?= $ANIME_name ?>"
                                                style="opacity: 1;"><?= $ANIME_name ?></a>
                                        </h2>
                                        <div class="anilist-source" style="font-size: 12px; margin-top: -5px; color: #aaa; margin-bottom: 8px;">
                                            <span><i class="fas fa-database mr-1"></i>Data from AniList</span>
                                        </div>
                                        <div class="film-stats">
                                            <div class="tac tick-item tick-quality">HD</div>
                                            <div class="tac tick-item tick-dub"><?= strtoupper($dub) ?></div>
                                            <div class="tac tick-item tick-dub">
                                                <?php if ($counter) {
                                                    echo "VIEWS: " . $counter;
                                                } ?>
                                            </div>
                                            <span class="dot"></span>
                                            <span class="item">
                                                <?= isset($anilistData['status']) ? $anilistData['status'] : '' ?>
                                            </span>
                                            <span class="dot"></span>
                                            <span class="item">
                                                <?= $ANIME_RELEASED ?>
                                            </span>
                                            <span class="dot"></span>
                                            <span class="item">
                                                <?= isset($animeDetails['native_title']) ? $animeDetails['native_title'] : '' ?>
                                            </span>
                                            <span class="dot"></span>
                                            <span class="item">
                                                <?= $ANIME_TYPE ?>
                                            </span>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="film-description m-hide">
                                            <div class="text">
                                                <?= $synopsis ?>
                                            </div>
                                        </div>
                                        <div class="film-text m-hide mb-3">
                                            <?= $websiteTitle ?> is a site to watch online anime like
                                            <strong>
                                                <?= $ANIME_name ?>
                                            </strong> online, or you can even watch
                                            <strong>
                                                <?= $ANIME_name ?>
                                            </strong> in HD quality
                                        </div>
                                        <div class="block"><a href="/anime/<?= $animeID ?>/<?=$url_title?>" class="btn btn-xs btn-light"><i
                                                    class="fas fa-book-open mr-2"></i> View detail</a>
                                        </div>

                                        <?php
                                        $likeCookie = 'like_' . md5($id);
                                        $dislikeCookie = 'dislike_' . md5($id);
                                        $likeClass = isset($_COOKIE[$likeCookie]) ? 'fas' : 'far';
                                        $dislikeClass = isset($_COOKIE[$dislikeCookie]) ? 'fas' : 'far';
                                        ?>
                                        <div class="dt-rate">
                                            <div id="vote-info">
                                                <div class="block-rating">
                                                    <div class="rating-result">
                                                        <div class="rr-mark float-left">
                                                            <strong><i class="fas fa-star text-warning mr-2"></i><span
                                                                    id="ratingAnime"><?= $totalVotes > 0 ? round((10 * $like_count + 5 * $dislike_count) / ($totalVotes), 2) : 0 ?></span></strong>
                                                            <small id="votedCount">(
                                                                <?= $totalVotes ?> Voted)
                                                            </small>
                                                        </div>
                                                        <div class="rr-title float-right">Vote now</div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="description">What do you think about this anime?</div>
                                                    <div class="button-rate">
                                                        <button type="button"
                                                            onclick="setLikeDislike('dislike','<?= $id ?>')"
                                                            class="btn btn-emo rate-bad btn-vote" style="width:50%"
                                                            data-mark="dislike"><i id="dislike"
                                                                class="<?php echo $dislikeClass ?> fa-thumbs-down">
                                                            </i><span id="dislikeMsg"
                                                                class="ml-2">Dislike</span></button>
                                                        <button onclick="setLikeDislike('like','<?= $id ?>')"
                                                            type="button" class="btn btn-emo rate-good btn-vote"
                                                            style="width:50%"><i id="like"
                                                                class="<?php echo $likeClass ?> fa-thumbs-up"> </i><span
                                                                id="likeMsg" class="ml-2">Like</span></button>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="share-buttons share-buttons-detail">
                <div class="container">
                    <div class="share-buttons-block">
                        <div class="share-icon"></div>
                        <div class="sbb-title">
                            <span>Share Anime</span>
                            <p class="mb-0">to your friends</p>
                        </div>
                        <div class="addthis_inline_share_toolbox"></div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>

            <div class="container">
                <div id="main-content">
                    <section class="block_area block_area-comment">
                        <div class="block_area-header block_area-header-tabs">
                            <div class="float-left bah-heading mr-4">
                                <h2 class="cat-heading">Comments</h2>
                            </div>
                            <div class="float-left bah-setting">
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="tab-content">
                            <?php include('./_php/disqus.php'); ?>
                        </div>
                    </section>

                    <?php include('./_php/recommendations.php'); ?>
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
        <script type="text/javascript">
            $(".btn-server").click(function () {
                $(".btn-server").removeClass("active");
                $(this).closest(".btn-server").addClass("active");
            });
        </script>
        <script type="text/javascript">
            if ('<?= $likeClass ?>' === 'fas') {
                document.getElementById('likeMsg').innerHTML = "Liked"
            }
            if ('<?= $dislikeClass ?>' === 'fas') {
                document.getElementById('dislikeMsg').innerHTML = "Disliked"
            }

            function setLikeDislike(type, id) {
                jQuery.ajax({
                    url: '<?= $websiteUrl ?>/setLikeDislike.php',
                    type: 'post',
                    data: 'type=' + type + '&id=' + id,
                    success: function (result) {
                        result = jQuery.parseJSON(result);
                        if (result.opertion == 'like') {
                            jQuery('#like').removeClass('far');
                            jQuery('#like').addClass('fas');
                            jQuery('#dislike').addClass('far');
                            jQuery('#dislike').removeClass('fas');
                            jQuery('#likeMsg').html("Liked")
                            jQuery('#dislikeMsg').html("Dislike")
                        }
                        if (result.opertion == 'unlike') {
                            jQuery('#like').addClass('far');
                            jQuery('#like').removeClass('fas');
                            jQuery('#likeMsg').html("Like")
                        }

                        if (result.opertion == 'dislike') {
                            jQuery('#dislike').removeClass('far');
                            jQuery('#dislike').addClass('fas');
                            jQuery('#like').addClass('far');
                            jQuery('#like').removeClass('fas');
                            jQuery('#dislikeMsg').html("Disliked")
                            jQuery('#likeMsg').html("Like")
                        }
                        if (result.opertion == 'undislike') {
                            jQuery('#dislike').addClass('far');
                            jQuery('#dislike').removeClass('fas');
                            jQuery('#dislikeMsg').html("Dislike")
                        }


                        jQuery('#votedCount').html(
                            `(${parseInt(result.like_count) + parseInt(result.dislike_count)} Voted)`
                        );
                        jQuery('#ratingAnime').html(((parseInt(result.like_count) *
                            10 + parseInt(result.dislike_count) * 5) / (
                                parseInt(result.like_count) + parseInt(
                                    result.dislike_count))).toFixed(2));
                    }

                });
            }
        </script>
    </div>
</body>

</html>