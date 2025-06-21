<?php
include('../../_config.php');
include '../../_php/anilist_api.php'; // Include the new Anilist API functions
session_start();
if(isset($_COOKIE['userID'])){
    $user_id = $_COOKIE['userID'];
}

$animeID = $_POST['btnValue'];

$query = mysqli_query($conn, "SELECT * FROM `watch_later` WHERE (user_id ,anime_id) = ('$user_id', '$animeID')"); 
$row = mysqli_fetch_array($query); 

if(isset($row['id'])){
    $id = $row['id'];
    mysqli_query($conn,"DELETE FROM `watch_later` WHERE id = $id"); 
    echo " &nbsp;<i class='fas fa-plus mr-2'></i>&nbsp;Add to List&nbsp;";
}else{
    // Get anime details using Anilist API
    $animeDetails = get_anime_details($animeID);
    
    if(isset($animeDetails['data']['Media'])) {
        $anime = $animeDetails['data']['Media'];
        
        // Get the appropriate title
        $name = $anime['title']['english'] ?? $anime['title']['romaji'];
        $type = $anime['format'];
        $image = $anime['coverImage']['large'];
        $release = $anime['seasonYear'] . ' ' . $anime['season'];
        
        // Add to watchlist
        mysqli_query($conn,"INSERT INTO `watch_later` (user_id, name, anime_id, image, type, released) 
            VALUES('$user_id', '$name', '$animeID', '$image', '$type', '$release')"); 
        
        echo " &nbsp;<i class='fas fa-minus mr-2'></i>&nbsp;Remove from List&nbsp;";
    } else {
        echo "Error: Could not fetch anime details";
    }
}
?>