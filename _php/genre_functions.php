<?php

require_once(__DIR__ . '/anilist_api.php');

function get_genre_mapping() {
    return [
        "Action" => "Action",
        "Adventure" => "Adventure",
        "Comedy" => "Comedy",
        "Drama" => "Drama",
        "Ecchi" => "Ecchi",
        "Fantasy" => "Fantasy",
        "Horror" => "Horror",
        "Mahou Shoujo" => "Mahou Shoujo",
        "Mecha" => "Mecha",
        "Music" => "Music",
        "Mystery" => "Mystery",
        "Psychological" => "Psychological",
        "Romance" => "Romance",
        "Sci-Fi" => "Sci-Fi",
        "Slice of Life" => "Slice of Life",
        "Sports" => "Sports",
        "Supernatural" => "Supernatural",
        "Thriller" => "Thriller",
        "4-koma" => "4-koma",
        "Achromatic" => "Achromatic",
        "Achronological Order" => "Achronological Order",
        "Boys' Love" => "Boys' Love",
        "Cute Boys Doing Cute Things" => "Cute Boys Doing Cute Things",
        "Cute Girls Doing Cute Things" => "Cute Girls Doing Cute Things",
        "E-Sports" => "E-Sports",
        "Eco-Horror" => "Eco Horror",
        "Ero Guro" => "Ero Guro",
        "Female Harem" => "Female Harem",
        "Full CGI" => "Full CGI",
        "Hip-hop Music" => "Hip Hop",
        "Kingdom Management" => "Kingdom Building",
        "LGBTQ+ Themes" => "LGBTQ Themes",
        "Love Triangle" => "Love Triangle",
        "Male Harem" => "Male Harem",
        "Memory Manipulation" => "Memory Manipulation",
        "Mixed Gender Harem" => "Mixed Gender Harem",
        "Monster Boy" => "Monster Boy",
        "Monster Girl" => "Monster Girl",
        "Musical Theater" => "Musical Theater",
        "No Dialogue" => "No Dialogue",
        "Non-fiction" => "Non-fiction",
        "Primarily Adult Cast" => "Primarily Adult Cast",
        "Primarily Animal Cast" => "Primarily Animal Cast",
        "Primarily Child Cast" => "Primarily Child Cast",
        "Primarily Female Cast" => "Primarily Female Cast",
        "Primarily Male Cast" => "Primarily Male Cast",
        "Primarily Teen Cast" => "Primarily Teen Cast",
        "Proxy Battle" => "Proxy Battles",
        "Super Power" => "Super Power",
        "Super Robot" => "Super Robot",
        "Surreal Comedy" => "Surreal Comedy",
        "Teens' Love" => "Teens Love",
        "Time Manipulation" => "Time Manipulation",
        "Urban Fantasy" => "Urban Fantasy",
        "Video Games" => "Video Game",
        "Virtual World" => "Virtual World",
        "Vocal Synth" => "Vocaloid"
    ];
}

function is_main_genre($genre) {
    $mapping = get_genre_mapping();
    $mainGenres = array_slice(array_keys($mapping), 0, 18); // The first 18 items are main genres
    
    return in_array($genre, $mainGenres);
}

function get_anilist_query_format($displayName) {
    $mapping = get_genre_mapping();
    
    if (isset($mapping[$displayName])) {
        return $mapping[$displayName];
    }
    
    return $displayName;
}

function get_custom_genres() {
    return [
        'Action', 'Adventure', 'Comedy', 'Drama', 'Ecchi', 'Fantasy', 'Horror', 'Mahou Shoujo',
        'Mecha', 'Music', 'Mystery', 'Psychological', 'Romance', 'Sci-Fi', 'Slice of Life',
        'Sports', 'Supernatural', 'Thriller', '4-koma', 'Achromatic', 'Achronological Order',
        'Acrobatics', 'Acting', 'Adoption', 'Advertisement', 'Afterlife', 'Age Gap',
        'Age Regression', 'Agender', 'Agriculture', 'Airsoft', 'Alchemy', 'Aliens',
        'Alternate Universe', 'American Football', 'Amnesia', 'Anachronism', 'Ancient China',
        'Angels', 'Animals', 'Anthology', 'Anthropomorphism', 'Anti-Hero', 'Archery',
        'Aromantic', 'Arranged Marriage', 'Artificial Intelligence', 'Asexual', 'Assassins',
        'Astronomy', 'Athletics', 'Augmented Reality', 'Autobiographical', 'Aviation',
        'Badminton', 'Band', 'Bar', 'Baseball', 'Basketball', 'Battle Royale', 'Biographical',
        'Bisexual', 'Blackmail', 'Board Game', 'Boarding School', 'Body Horror', 'Body Image',
        'Body Swapping', 'Bowling', 'Boxing', 'Boys\' Love', 'Bullying', 'Butler', 'Calligraphy',
        'Camping', 'Cannibalism', 'Card Battle', 'Cars', 'Centaur', 'CGI', 'Cheerleading',
        'Chibi', 'Chimera', 'Chuunibyou', 'Circus', 'Class Struggle', 'Classic Literature',
        'Classical Music', 'Clone', 'Coastal', 'Cohabitation', 'College', 'Coming of Age',
        'Conspiracy', 'Cosmic Horror', 'Cosplay', 'Cowboys', 'Creature Taming', 'Crime',
        'Criminal Organization', 'Crossdressing', 'Crossover', 'Cult', 'Cultivation', 'Curses',
        'Cute Boys Doing Cute Things', 'Cute Girls Doing Cute Things', 'Cyberpunk', 'Cyborg',
        'Cycling', 'Dancing', 'Death Game', 'Delinquents', 'Demons', 'Denpa', 'Desert',
        'Detective', 'Dinosaurs', 'Disability', 'Dissociative Identities', 'Dragons', 'Drawing',
        'Drugs', 'Dullahan', 'Dungeon', 'Dystopian', 'E-Sports', 'Eco-Horror', 'Economics',
        'Educational', 'Elderly Protagonist', 'Elf', 'Ensemble Cast', 'Environmental', 'Episodic',
        'Ero Guro', 'Espionage', 'Estranged Family', 'Exorcism', 'Fairy', 'Fairy Tale',
        'Fake Relationship', 'Family Life', 'Fashion', 'Female Harem', 'Female Protagonist',
        'Femboy', 'Fencing', 'Filmmaking', 'Firefighters', 'Fishing', 'Fitness', 'Flash', 'Food',
        'Football', 'Foreign', 'Found Family', 'Fugitive', 'Full CGI', 'Full Color', 'Gambling',
        'Gangs', 'Gender Bending', 'Ghost', 'Go', 'Goblin', 'Gods', 'Golf', 'Gore', 'Guns',
        'Gyaru', 'Handball', 'Harem', 'Henshin', 'Heterosexual', 'Hikikomori', 'Hip-hop Music',
        'Historical', 'Homeless', 'Horticulture', 'Ice Skating', 'Idol', 'Indigenous Cultures',
        'Inn', 'Isekai', 'Iyashikei', 'Jazz Music', 'Josei', 'Judo', 'Kaiju', 'Karuta',
        'Kemonomimi', 'Kids', 'Kingdom Management', 'Konbini', 'Kuudere', 'Lacrosse',
        'Language Barrier', 'LGBTQ+ Themes', 'Long Strip', 'Lost Civilization', 'Love Triangle',
        'Mafia', 'Magic', 'Mahjong', 'Maids', 'Makeup', 'Male Harem', 'Male Protagonist',
        'Marriage', 'Martial Arts', 'Matchmaking', 'Matriarchy', 'Medicine', 'Medieval',
        'Memory Manipulation', 'Mermaid', 'Meta', 'Metal Music', 'Military', 'Mixed Gender Harem',
        'Mixed Media', 'Monster Boy', 'Monster Girl', 'Mopeds', 'Motorcycles', 'Mountaineering',
        'Musical Theater', 'Mythology', 'Natural Disaster', 'Necromancy', 'Nekomimi', 'Ninja',
        'No Dialogue', 'Noir', 'Non-fiction', 'Nudity', 'Nun', 'Office', 'Office Lady', 'Oiran',
        'Ojou-sama', 'Orphan', 'Otaku Culture', 'Outdoor Activities', 'Pandemic', 'Parenthood',
        'Parkour', 'Parody', 'Philosophy', 'Photography', 'Pirates', 'Poker', 'Police', 'Politics',
        'Polyamorous', 'Post-Apocalyptic', 'POV', 'Pregnancy', 'Primarily Adult Cast',
        'Primarily Animal Cast', 'Primarily Child Cast', 'Primarily Female Cast',
        'Primarily Male Cast', 'Primarily Teen Cast', 'Prison', 'Proxy Battle', 'Psychosexual',
        'Puppetry', 'Rakugo', 'Real Robot', 'Rehabilitation', 'Reincarnation', 'Religion',
        'Rescue', 'Restaurant', 'Revenge', 'Robots', 'Rock Music', 'Rotoscoping', 'Royal Affairs',
        'Rugby', 'Rural', 'Samurai', 'Satire', 'School', 'School Club', 'Scuba Diving', 'Seinen',
        'Shapeshifting', 'Ships', 'Shogi', 'Shoujo', 'Shounen', 'Shrine Maiden', 'Skateboarding',
        'Skeleton', 'Slapstick', 'Slavery', 'Snowscape', 'Software Development', 'Space',
        'Space Opera', 'Spearplay', 'Steampunk', 'Stop Motion', 'Succubus', 'Suicide', 'Sumo',
        'Super Power', 'Super Robot', 'Superhero', 'Surfing', 'Surreal Comedy', 'Survival',
        'Swimming', 'Swordplay', 'Table Tennis', 'Tanks', 'Tanned Skin', 'Teacher', 'Teens\' Love',
        'Tennis', 'Terrorism', 'Time Loop', 'Time Manipulation', 'Time Skip', 'Tokusatsu',
        'Tomboy', 'Torture', 'Tragedy', 'Trains', 'Transgender', 'Travel', 'Triads', 'Tsundere',
        'Twins', 'Unrequited Love', 'Urban', 'Urban Fantasy', 'Vampire', 'Vertical Video',
        'Veterinarian', 'Video Games', 'Vikings', 'Villainess', 'Virtual World', 'Vocal Synth',
        'Volleyball', 'VTuber', 'War', 'Werewolf', 'Wilderness', 'Witch', 'Work', 'Wrestling',
        'Writing', 'Wuxia', 'Yakuza', 'Yandere', 'Youkai', 'Yuri', 'Zombie'
    ];
}

function get_formatted_genres($limit = 0) {
    $genres = get_custom_genres();
    $formatted = [];
    
    foreach ($genres as $genre) {
        $formatted[] = [
            'name' => $genre,
            'url' => str_replace(' ', '+', $genre),
            'query_format' => str_replace(' ', '%20', get_anilist_query_format($genre))
        ];
    }
    
    if ($limit > 0 && count($formatted) > $limit) {
        $formatted = array_slice($formatted, 0, $limit);
    }
    
    return $formatted;
}

function get_header_genres_html($limit = 0, $include_more = true) {
    global $websiteUrl;
    $genres = get_formatted_genres($limit);
    $html = '';
    
    foreach ($genres as $genre) {
        $html .= <<<HTML
                    <li class="nav-item"> <a class="nav-link" href="{$websiteUrl}/genre/{$genre['url']}"
                             title="{$genre['name']}">{$genre['name']}</a></li>
HTML;
        $html .= "\n";
    }
    
    if ($include_more) {
        $html .= <<<HTML
                    <li class="nav-item nav-more">
                        <a class="nav-link"><i class="fas fa-plus mr-2"></i>More</a>
                    </li>
HTML;
    }
    
    return $html;
}


function get_sidebar_genres_html($limit = 0) {
    global $websiteUrl;
    $genres = get_formatted_genres($limit);
    $html = '';
    
    foreach ($genres as $genre) {
        $html .= <<<HTML
                    <li class="nav-item"> <a class="nav-link" href="{$websiteUrl}/genre/{$genre['url']}" title="{$genre['name']}">{$genre['name']}</a> </li>
HTML;
        $html .= "\n";
    }
    
    return $html;
}


function display_header_genres($limit = 0, $include_more = true) {
    echo get_header_genres_html($limit, $include_more);
}

function display_sidebar_genres($limit = 0) {
    echo get_sidebar_genres_html($limit);
} 