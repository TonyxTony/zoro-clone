<?php
// Fallback definitions when caching is disabled to prevent undefined constant errors.
if (!defined('ANILIST_CACHE_DIR')) {
    define('ANILIST_CACHE_DIR', sys_get_temp_dir());
}

/**
 * Function to make GraphQL queries to the Anilist API with caching
 * 
 * @param string $query GraphQL query
 * @param array $variables Variables for the query
 * @param int $cache_ttl Cache time-to-live in seconds (0 to disable caching)
 * @return array Response data
 */
function anilist_query($query, $variables = [], $cache_ttl = 0) {
    // --- CACHING DISABLED ---
    // Force $cache_ttl to 0 so no cache files are read or written.
    $cache_ttl = 0;
    // Generate cache key based on query and variables
    $cache_key = md5($query . serialize($variables));
    $cache_file = ANILIST_CACHE_DIR . '/' . $cache_key . '.json';
    
    // Try to get from cache first if caching is enabled
    if ($cache_ttl > 0 && file_exists($cache_file)) {
        $file_time = filemtime($cache_file);
        if (time() - $file_time < $cache_ttl) {
            $cached_data = file_get_contents($cache_file);
            if ($cached_data) {
                return json_decode($cached_data, true);
            }
        }
    }
    
    $url = 'https://graphql.anilist.co';
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    $data = [
        'query' => $query,
        'variables' => $variables
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // 15 seconds timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 seconds connect timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        $error = ['error' => curl_error($ch), 'code' => $http_code];
        curl_close($ch);
        return $error;
    }
    
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    // Save to cache if the response is successful and caching is enabled
    if ($cache_ttl > 0 && isset($result['data']) && !isset($result['errors'])) {
        file_put_contents($cache_file, $response);
    }
    
    return $result;
}

/**
 * Get anime by specific formats (OVA, ONA, SPECIAL, etc)
 * 
 * @param array $formats Array of formats to filter by (e.g. ['OVA', 'ONA', 'SPECIAL'])
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @param string $sort Sorting method (e.g. POPULARITY_DESC, SCORE_DESC, START_DATE_DESC)
 * @return array Anime with specified formats
 */
function get_anime_by_formats($formats = ['OVA', 'ONA'], $page = 1, $perPage = 20, $sort = 'POPULARITY_DESC') {
    // Ensure formats are uppercase and valid
    $validFormats = ['TV', 'TV_SHORT', 'MOVIE', 'SPECIAL', 'OVA', 'ONA', 'MUSIC'];
    $validSortOptions = ['POPULARITY_DESC', 'SCORE_DESC', 'START_DATE_DESC', 'TRENDING_DESC', 'EPISODES_DESC'];
    
    // Validate sort option
    if (!in_array($sort, $validSortOptions)) {
        $sort = 'POPULARITY_DESC'; // Default sort
    }
    
    $formats = array_map('strtoupper', $formats);
    $formats = array_intersect($formats, $validFormats);
    
    if (empty($formats)) {
        $formats = ['OVA', 'ONA']; // Default if none are valid
    }

    $query = '
    query ($page: Int, $perPage: Int, $formats: [MediaFormat], $sort: [MediaSort]) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            media(type: ANIME, format_in: $formats, sort: $sort, isAdult: false) {
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
                duration
                status
                season
                seasonYear
                format
                genres
                tags {
                    name
                    rank
                }
                averageScore
                popularity
                studios {
                    nodes {
                        name
                        isAnimationStudio
                    }
                }
                nextAiringEpisode {
                    episode
                    airingAt
                    timeUntilAiring
                }
            }
        }
    }';
    
    $variables = [
        'page' => intval($page),
        'perPage' => intval($perPage),
        'formats' => $formats,
        'sort' => [$sort]
    ];
    
    return anilist_query($query, $variables);
}

/**
 * Get the latest anime releases (sorted by episode update time)
 * 
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @param string $status Filter by status (RELEASING, FINISHED, NOT_YET_RELEASED, CANCELLED, HIATUS)
 * @return array Latest anime releases
 */
function get_latest_releases($page = 1, $perPage = 20, $status = 'RELEASING') {
    $validStatus = ['RELEASING', 'FINISHED', 'NOT_YET_RELEASED', 'CANCELLED', 'HIATUS', null];
    
    if (!in_array($status, $validStatus)) {
        $status = 'RELEASING'; // Default status
    }
    
    $statusFilter = $status ? [$status] : null;
    
    $query = '
    query ($page: Int, $perPage: Int, $status: [MediaStatus]) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            media(type: ANIME, sort: UPDATED_AT_DESC, status_in: $status, isAdult: false) {
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
                popularity
                nextAiringEpisode {
                    episode
                    airingAt
                    timeUntilAiring
                }
            }
        }
    }';
    
    $variables = [
        'page' => intval($page),
        'perPage' => intval($perPage),
        'status' => $statusFilter
    ];
    
    return anilist_query($query, $variables);
}

/**
 * Get latest subbed anime releases
 * 
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @return array Latest subbed anime releases
 */
function get_latest_subbed($page = 1, $perPage = 20) {
    return get_latest_releases($page, $perPage);
}

/**
 * Get latest dubbed anime releases (approximation)
 * Note: Anilist doesn't directly indicate if an anime is dubbed
 * 
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @return array Latest dubbed anime releases
 */
function get_latest_dubbed($page = 1, $perPage = 20) {
    // Since Anilist doesn't directly offer dubbed info, we're approximating
    // In a real implementation, you would need to combine this with another API
    $query = '
    query ($page: Int, $perPage: Int) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            media(type: ANIME, sort: POPULARITY_DESC, status: RELEASING, isAdult: false) {
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
                averageScore
                popularity
                nextAiringEpisode {
                    episode
                    airingAt
                    timeUntilAiring
                }
            }
        }
    }';
    
    $variables = [
        'page' => intval($page),
        'perPage' => intval($perPage)
    ];
    
    return anilist_query($query, $variables);
}

/**
 * Get Chinese anime releases
 * 
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @return array Chinese anime releases
 */
function get_chinese_anime($page = 1, $perPage = 20) {
    $query = '
    query ($page: Int, $perPage: Int) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            media(type: ANIME, countryOfOrigin: "CN", sort: UPDATED_AT_DESC, isAdult: false) {
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
                averageScore
                nextAiringEpisode {
                    episode
                    airingAt
                    timeUntilAiring
                }
            }
        }
    }';
    
    $variables = [
        'page' => intval($page),
        'perPage' => intval($perPage)
    ];
    
    return anilist_query($query, $variables);
}

/**
 * Get trending anime
 * 
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @return array Trending anime
 */
function get_trending_anime($page = 1, $perPage = 10) {
    $query = '
    query ($page: Int, $perPage: Int) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            media(type: ANIME, sort: TRENDING_DESC, isAdult: false) {
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
                popularity
                trending
            }
        }
    }';
    
    $variables = [
        'page' => intval($page),
        'perPage' => intval($perPage)
    ];
    
    // Use a shorter cache time for trending (30 minutes)
    return anilist_query($query, $variables, 1800);
}

/**
 * Get popular anime
 * 
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @return array Popular anime
 */
function get_popular_anime($page = 1, $perPage = 20) {
    $query = '
    query ($page: Int, $perPage: Int) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            media(type: ANIME, sort: POPULARITY_DESC, isAdult: false) {
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
                duration
                status
                season
                seasonYear
                format
                genres
                averageScore
                popularity
            }
        }
    }';
    
    $variables = [
        'page' => intval($page),
        'perPage' => intval($perPage)
    ];
    
    return anilist_query($query, $variables);
}

/**
 * Get anime details by ID
 * 
 * @param int $id Anilist anime ID
 * @return array Anime details
 */
function get_anime_details($id) {
    $query = '
    query ($id: Int) {
        Media(id: $id, type: ANIME, isAdult: false) {
            id
            idMal
            title {
                romaji
                english
                native
            }
            description
            coverImage {
                large
                medium
                extraLarge
                color
            }
            bannerImage
            episodes
            status
            season
            seasonYear
            format
            duration
            genres
            tags {
                name
                rank
                description
                category
            }
            averageScore
            meanScore
            popularity
            favourites
            studios {
                nodes {
                    name
                    isAnimationStudio
                }
            }
            relations {
                edges {
                    relationType
                    node {
                        id
                        title {
                            romaji
                            english
                        }
                        format
                        type
                        status
                        coverImage {
                            large
                            medium
                        }
                        isAdult
                    }
                }
            }
            streamingEpisodes {
                title
                thumbnail
                url
                site
            }
            trailer {
                id
                site
                thumbnail
            }
            characters(sort: ROLE, perPage: 10) {
                edges {
                    role
                    node {
                        id
                        name {
                            full
                        }
                        image {
                            large
                            medium
                        }
                    }
                }
            }
            staff(perPage: 5) {
                edges {
                    role
                    node {
                        id
                        name {
                            full
                        }
                        image {
                            large
                            medium
                        }
                    }
                }
            }
            recommendations(perPage: 12) {
                nodes {
                    mediaRecommendation {
                        id
                        title {
                            romaji
                            english
                        }
                        coverImage {
                            large
                            medium
                        }
                        isAdult
                    }
                }
            }
        }
    }';
    
    $variables = [
        'id' => intval($id)
    ];
    
    $result = anilist_query($query, $variables);
    
    // Additional post-processing to filter related content and recommendations
    if (isset($result['data']) && isset($result['data']['Media'])) {
        // Filter out adult-rated related anime
        if (isset($result['data']['Media']['relations']) && isset($result['data']['Media']['relations']['edges'])) {
            $filteredRelations = [];
            foreach ($result['data']['Media']['relations']['edges'] as $relation) {
                if (!isset($relation['node']['isAdult']) || $relation['node']['isAdult'] == false) {
                    // Remove isAdult field
                    if (isset($relation['node']['isAdult'])) {
                        unset($relation['node']['isAdult']);
                    }
                    $filteredRelations[] = $relation;
                }
            }
            $result['data']['Media']['relations']['edges'] = $filteredRelations;
        }
        
        // Filter out adult-rated recommendations
        if (isset($result['data']['Media']['recommendations']) && isset($result['data']['Media']['recommendations']['nodes'])) {
            $filteredRecommendations = [];
            foreach ($result['data']['Media']['recommendations']['nodes'] as $recommendation) {
                if (!isset($recommendation['mediaRecommendation']['isAdult']) || $recommendation['mediaRecommendation']['isAdult'] == false) {
                    // Remove isAdult field
                    if (isset($recommendation['mediaRecommendation']['isAdult'])) {
                        unset($recommendation['mediaRecommendation']['isAdult']);
                    }
                    $filteredRecommendations[] = $recommendation;
                }
            }
            $result['data']['Media']['recommendations']['nodes'] = $filteredRecommendations;
        }
    }
    
    return $result;
}

/**
 * Search anime by query
 * 
 * @param string $query Search query
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @param string $sort Sort method (default: POPULARITY_DESC)
 * @return array Search results
 */
function search_anime($query, $page = 1, $perPage = 20, $sort = 'POPULARITY_DESC') {
    $validSortOptions = ['POPULARITY_DESC', 'SCORE_DESC', 'TRENDING_DESC', 'START_DATE_DESC'];
    
    // Validate sort option
    if (!in_array($sort, $validSortOptions)) {
        $sort = 'POPULARITY_DESC'; // Default sort
    }

    $graphql_query = '
    query ($page: Int, $perPage: Int, $search: String, $sort: [MediaSort]) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            media(type: ANIME, search: $search, sort: $sort, isAdult: false) {
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
                duration
                status
                season
                seasonYear
                format
                genres
                averageScore
                popularity
            }
        }
    }';
    
    $variables = [
        'search' => $query,
        'page' => intval($page),
        'perPage' => intval($perPage),
        'sort' => [$sort]
    ];
    
    // Short cache time for searches
    return anilist_query($graphql_query, $variables, 900); // 15 minutes
}

/**
 * Get anime airing schedule
 * 
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @param int $notYetAired Only include anime that haven't aired yet (optional)
 * @return array Airing schedule
 */
function get_anime_airing_schedule($page = 1, $perPage = 20, $notYetAired = true) {
    // Calculate current timestamp
    $now = time();
    
    // Calculate start and end times (7-day window)
    $start = $now;
    $end = $now + (7 * 24 * 60 * 60); // 7 days ahead
    
    $query = '
    query ($page: Int, $perPage: Int, $start: Int, $end: Int, $sort: [AiringSort]) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            airingSchedules(airingAt_greater: $start, airingAt_lesser: $end, sort: $sort) {
                id
                airingAt
                timeUntilAiring
                episode
                media {
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
                    status
                    season
                    seasonYear
                    format
                    episodes
                    genres
                    averageScore
                    isAdult
                }
            }
        }
    }';
    
    $variables = [
        'page' => intval($page),
        'perPage' => intval($perPage),
        'start' => $start,
        'end' => $end,
        'sort' => ['TIME']
    ];
    
    // Short cache time for airing schedule
    $result = anilist_query($query, $variables, 1800); // 30 minutes
    
    // Filter out adult content from the results since we can't filter in the query
    if (isset($result['data']) && isset($result['data']['Page']) && isset($result['data']['Page']['airingSchedules'])) {
        $filtered = [];
        foreach ($result['data']['Page']['airingSchedules'] as $schedule) {
            if (!isset($schedule['media']['isAdult']) || $schedule['media']['isAdult'] == false) {
                // Remove the isAdult field to keep the data structure consistent
                if (isset($schedule['media']['isAdult'])) {
                    unset($schedule['media']['isAdult']);
                }
                $filtered[] = $schedule;
            }
        }
        $result['data']['Page']['airingSchedules'] = $filtered;
    }
    
    return $result;
}

/**
 * Format pagination HTML
 * 
 * @param array $pageInfo Page information
 * @param string $baseUrl Base URL for pagination links
 * @return string Formatted pagination HTML
 */
function format_pagination($pageInfo, $baseUrl) {
    $pagination = '';
    
    if ($pageInfo['currentPage'] > 1) {
        $pagination .= '<li><a href="' . $baseUrl . '?page=1" title="First Page"><i class="fas fa-angle-double-left"></i></a></li>';
        $pagination .= '<li><a href="' . $baseUrl . '?page=' . ($pageInfo['currentPage'] - 1) . '" title="Previous Page"><i class="fas fa-angle-left"></i></a></li>';
    }
    
    $startPage = max(1, $pageInfo['currentPage'] - 2);
    $endPage = min($pageInfo['lastPage'], $pageInfo['currentPage'] + 2);
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $pageInfo['currentPage']) {
            $pagination .= '<li class="active"><a href="' . $baseUrl . '?page=' . $i . '" title="Page ' . $i . '">' . $i . '</a></li>';
        } else {
            $pagination .= '<li><a href="' . $baseUrl . '?page=' . $i . '" title="Page ' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($pageInfo['hasNextPage']) {
        $pagination .= '<li><a href="' . $baseUrl . '?page=' . ($pageInfo['currentPage'] + 1) . '" title="Next Page"><i class="fas fa-angle-right"></i></a></li>';
        $pagination .= '<li><a href="' . $baseUrl . '?page=' . $pageInfo['lastPage'] . '" title="Last Page"><i class="fas fa-angle-double-right"></i></a></li>';
    }
    
    return $pagination;
}

/**
 * Clear AniList API cache
 * 
 * @param string $pattern Optional file pattern to match specific cache files
 * @return int Number of files deleted
 */
function clear_anilist_cache($pattern = '*') {
    $count = 0;
    $files = glob(ANILIST_CACHE_DIR . '/' . $pattern . '.json');
    
    foreach ($files as $file) {
        if (is_file($file) && unlink($file)) {
            $count++;
        }
    }
    
    return $count;
}

/**
 * Get all genres available in the AniList database
 * 
 * @return array List of genres
 */
function get_all_genres() {
    // CACHING DISABLED: always fetch fresh genre list
    
    // Fetch genres using GraphQL query
    $query = '
    query {
        GenreCollection(isAdult: false)
    }';
    
    $result = anilist_query($query, [], 0); // No caching for this query as we handle it ourselves
    
    if (isset($result['data']) && isset($result['data']['GenreCollection'])) {
        $genres = $result['data']['GenreCollection'];
        
        // Filter out any genres that might be associated with adult content
        $adultGenres = ['Hentai', 'Ecchi', 'Adult'];
        $genres = array_diff($genres, $adultGenres);
        
        // Sort genres alphabetically
        sort($genres);
        

        
        return ['genres' => $genres];
    }
    
    // Return empty array if failed
    return ['genres' => []];
}

/**
 * Get upcoming anime for the next season
 * 
 * @param int $page Page number
 * @param int $perPage Number of items per page
 * @return array Upcoming anime
 */
function get_upcoming_anime($page = 1, $perPage = 12) {
    // Calculate next season
    $currentMonth = intval(date('m'));
    $currentYear = intval(date('Y'));
    
    $season = '';
    $year = $currentYear;
    
    // Determine next season based on current month
    if ($currentMonth >= 1 && $currentMonth <= 3) {
        $season = 'SPRING'; // Next season is Spring (Apr-Jun)
    } else if ($currentMonth >= 4 && $currentMonth <= 6) {
        $season = 'SUMMER'; // Next season is Summer (Jul-Sep)
    } else if ($currentMonth >= 7 && $currentMonth <= 9) {
        $season = 'FALL'; // Next season is Fall (Oct-Dec)
    } else {
        $season = 'WINTER'; // Next season is Winter (Jan-Mar of next year)
        $year = $currentYear + 1; // Next year
    }
    
    $query = '
    query ($page: Int, $perPage: Int, $season: MediaSeason, $year: Int) {
        Page(page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            media(type: ANIME, season: $season, seasonYear: $year, sort: POPULARITY_DESC, isAdult: false) {
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
                popularity
                nextAiringEpisode {
                    airingAt
                    timeUntilAiring
                    episode
                }
            }
        }
    }';
    
    $variables = [
        'page' => intval($page),
        'perPage' => intval($perPage),
        'season' => $season,
        'year' => $year
    ];
    
    // Cache for 1 hour since this doesn't change frequently
    return anilist_query($query, $variables, 3600);
}

/**
 * Get anime for the CURRENT season ("New Season" page)
 * This fetches popular titles airing in the present season/year.
 *
 * @param int $page     Page number
 * @param int $perPage  Items per page
 * @return array        Current-season anime list
 */
function get_current_season_anime($page = 1, $perPage = 20) {
    // Determine current season based on month
    $seasons = ['WINTER', 'SPRING', 'SUMMER', 'FALL'];
    $currentMonth = intval(date('n')); // 1-12
    $currentSeasonIndex = floor(($currentMonth - 1) / 3);
    $season = $seasons[$currentSeasonIndex];
    $year = intval(date('Y'));

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
            media(type: ANIME, season: $season, seasonYear: $seasonYear, sort: POPULARITY_DESC, isAdult: false) {
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
                popularity
            }
        }
    }';

    $variables = [
        'page'    => intval($page),
        'perPage' => intval($perPage),
        'season'  => $season,
        'seasonYear' => $year
    ];

    // Cache for 1 hour
    return anilist_query($query, $variables, 3600);
}