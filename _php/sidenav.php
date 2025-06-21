<div id="main-sidebar">
    <section class="block_area block_area_sidebar block_area-genres">
        <div class="block_area-header">
            <div class="float-left bah-heading mr-4">
                <h2 class="cat-heading">Genres</h2>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="block_area-content">
            <div class="cbox cbox-genres">
                <ul class="ulclear color-list sb-genre-list sb-genre-less">
                    <?php 
                    // Include genre functions if they're not already included
                    require_once(__DIR__ . '/genre_functions.php');
                    
                    // Display genres dynamically from AniList API
                    display_sidebar_genres();
                    ?>
                </ul>
                <div class="clearfix"></div>
                <button class="btn btn-sm btn-block btn-showmore mt-2"></button>
            </div>
        </div>
    </section>

    <section class="block_area block_area_sidebar block_area-realtime">
        <div class="block_area-header">
            <div class="float-left bah-heading mr-2">
                <h2 class="cat-heading">Trending Anime</h2>
            </div>
            <div class="float-right bah-tab-min">
                <ul class="nav nav-pills nav-fill nav-tabs anw-tabs">
                    <li class="nav-item"><a data-toggle="tab" class="nav-link active">Today</a></li>
                </ul>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="block_area-content">
            <div class="cbox cbox-list cbox-realtime">
                <div class="cbox-content">
                    <div class="tab-content">
                        <div id="today" class="anif-block-ul anif-block-chart tab-pane active">
                            <ul class="ulclear">
                            <?php
                                // Get trending anime from Anilist API
                                require_once(__DIR__ . '/anilist_api.php');
                                $result = get_trending_anime(1, 10);
                                
                                if (isset($result['data']) && isset($result['data']['Page']) && isset($result['data']['Page']['media'])) {
                                    $trending_anime = $result['data']['Page']['media'];
                                    
                                    foreach($trending_anime as $key => $media) { 
                                        $title = !empty($media['title']['english']) ? $media['title']['english'] : $media['title']['romaji'];
                                        $url_title = preg_replace('/[^A-Za-z0-9\-]/', '-', $title);
                                        $url_title = preg_replace('/-+/', '-', $url_title);
                                        $url_title = trim($url_title, '-');
                                        ?>
                                        <li class="<?php if($key < 3) echo "item-top"?>">
                                            <div class="film-number"><span><?=$key + 1?></span></div>
                                            <div class="film-poster">
                                                <img data-src="<?=$media['coverImage']['large']?>"
                                                    class="film-poster-img lazyload tooltipEl" alt="<?=$title?>"
                                                    src="<?=$media['coverImage']['large']?>" title="<?=$title?>">
                                            </div>
                                            <div class="film-detail">
                                                <h3 class="film-name">
                                                    <a href="<?=$websiteUrl?>/anime/<?=$media['id']?>/<?=$url_title?>" 
                                                        title="<?=$title?>" 
                                                        data-jname="<?=$media['title']['romaji']?>"><?=$title?></a>
                                                </h3>
                                                <div class="fd-infor">
                                                    <span class="fdi-item"><?=$media['format']?></span>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </li>
                                        <?php 
                                    }
                                } else {
                                    echo '<li>No trending anime found.</li>';
                                }
                            ?>
                            </ul>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </section>
</div>
