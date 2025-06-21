<?php
// Check if anilist_api.php is included, include if not
if (!function_exists('get_anime_details')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/_php/anilist_api.php');
}

// Get recommendations from the anime details
$recommendations = [];
if (isset($animeData['data']) && isset($animeData['data']['Media']) && isset($animeData['data']['Media']['recommendations'])) {
    $recommendations = $animeData['data']['Media']['recommendations']['nodes'];
}

// Only show section if we have recommendations
if (!empty($recommendations)): ?>
    <section class="block_area block_area_category">
        <div class="block_area-header">
            <div class="float-left bah-heading mr-4">
                <h2 class="cat-heading">Recommended Anime</h2>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="tab-content">
            <div class="block_area-content block_area-list film_list film_list-grid film_list-wfeature">
                <div class="film_list-wrap">
                <?php 
                foreach ($recommendations as $recommendation) {
                    $anime = $recommendation['mediaRecommendation'];
                    
                    // Get the title (prefer English, fallback to romaji)
                    $title = !empty($anime['title']['english']) ? $anime['title']['english'] : $anime['title']['romaji'];
                    
                    // Create URL-friendly title
                    $url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $title);
                    $url_title = preg_replace('/-+/', '-', $url_title);
                    $url_title = trim($url_title, '-');
                    
                    // Get the cover image
                    $coverImage = $anime['coverImage']['large'];
                ?>
                    <div class="flw-item">
                        <div class="film-poster">
                            <img class="film-poster-img lazyload"
                                data-src="<?=$coverImage?>"
                                src="<?=$websiteUrl?>/files/images/no_poster.jpg"
                                alt="<?=$title?>">
                            <a class="film-poster-ahref"
                                href="/anime/<?=$anime['id']?>/<?=$url_title?>"
                                title="<?=$title?>"
                                data-jname="<?=$anime['title']['romaji']?>"><i class="fas fa-play"></i></a>
                        </div>
                        <div class="film-detail">
                            <h3 class="film-name">
                                <a href="/anime/<?=$anime['id']?>/<?=$url_title?>"
                                    title="<?=$title?>"
                                    data-jname="<?=$anime['title']['romaji']?>"><?=$title?></a>
                            </h3>
                            <div class="fd-infor">
                                <span class="fdi-item">Similar</span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                <?php 
                }
                ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </section>
<?php endif; ?> 