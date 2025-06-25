<?php
require('../_config.php');

// Get and decode the ID
$id = isset($_GET['id']) ? urldecode($_GET['id']) : null;

if (!$id) {
    echo "No ID provided.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Anime Player</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: black;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <iframe 
        src="https://vidnest.fun/animepahe/<?= htmlspecialchars($id) ?>" 
        allowfullscreen 
        loading="lazy">
    </iframe>
</body>
</html>
