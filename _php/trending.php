<div id="anime-trending">
    <div class="container">
        <section class="block_area block_area_trending">
            <div class="block_area-header">
                <div class="bah-heading">
                    <h2 class="cat-heading">Trending</h2>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="block_area-content">
                <div class="trending-list" id="trending-home">
                    <div class="swiper-container swiper-container-initialized swiper-container-horizontal">
                        <div class="swiper-wrapper">

                            <?php 
                                // Get trending anime from Anilist API
                                require_once('_php/anilist_api.php');
                                $result = get_popular_anime(1, 10);
                                
                                if (isset($result['data']) && isset($result['data']['Page']) && isset($result['data']['Page']['media'])) {
                                    $trending_anime = $result['data']['Page']['media'];
                                    
                                    foreach($trending_anime as $key => $anime) { 
                                        // Create URL-friendly title
                                        $title = !empty($anime['title']['english']) ? $anime['title']['english'] : $anime['title']['romaji'];
                                        $url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $title);
                                        $url_title = preg_replace('/-+/', '-', $url_title);
                                        $url_title = trim($url_title, '-');
                                        ?>

                            <div class="swiper-slide">
                                <div class="item">
                                    <div class="number">
                                        <?php $number = $key + 1; ?>
                                        <span><?= $number <= 9 ? '0' . $number : $number ?></span>
                                        <div class="film-title dynamic-name" data-jname="<?= $anime['title']['romaji'] ?>">
                                            <?= $title ?>
                                        </div>
                                    </div>
                                    <a href="/anime/<?= $anime['id'] ?>/<?= $url_title ?>" class="film-poster"
                                        title="<?= $title ?>">
                                        <img data-src="<?= $anime['coverImage']['large'] ?>"
                                            src="<?=$websiteUrl?>/files/images/no_poster.jpg"
                                            class="film-poster-img lazyload" alt="<?= $title ?>">
                                    </a>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <?php 
                                    }
                                } else {
                                    echo '<div class="swiper-slide"><div class="item">No trending anime found.</div></div>';
                                }
                            ?>

                        </div>
                        <div class="clearfix"></div>
                        <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
                    </div>
                    <div class="trending-navi">
                        <div class="navi-next swiper-button-disabled" tabindex="-1" role="button"
                            aria-label="Next slide" aria-disabled="true"><i class="fas fa-angle-right"></i>
                        </div>
                        <div class="navi-prev swiper-button-disabled" tabindex="-1" role="button"
                            aria-label="Previous slide" aria-disabled="true"><i class="fas fa-angle-left"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>