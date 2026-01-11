<?php
// SETTINGS.PHP


// Allow users to add new feeds
// Allow users to delete existing feeds

require_once('vendor/Feed.php');

$db = new SQLITE3('./db/hrmss.sqlite');

$result = $db->query('
    SELECT id, title, url
    FROM feeds
');

$feeds = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $feeds[] = $row;
}

// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_feed_url']))
{
    $url = trim($_POST['new_feed_url']);

    if ($url !== '')
    {
	// Prep with Feed.php
	$feed = Feed::load($url);
	$feed_title = $feed->title;
	
	// INSERT INTO [table] (column(s)) VALUES (value(s));
	$stmt = $db->prepare('INSERT INTO feeds (url, title) VALUES (:url, :title)');
	$stmt->bindValue(':url', $url, SQLITE3_TEXT);
	$stmt->bindValue(':title', $feed_title, SQLITE3_TEXT);
	$stmt->execute();	
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}


// DELETE

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feed_id']))
{
    $stmt = $db->prepare('DELETE FROM feeds WHERE id = :id');
    $stmt->bindValue(':id', (int)$_POST['delete_feed_id'], SQLITE3_INTEGER);
    $stmt->execute();

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// var_dump($feeds);

?>

<!doctype html>
<html>
    <head>
	<title>hrmss</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
    </head>
    <body>
	<navbar>
	    <a href="/" class="flex" style="gap: 0.5rem; align-items: center; text-decoration: none; color: inherit;">
		<img src="/images/hermes.webp" id="logo">
		<h1>Hermes</h1>
	    </a>
	    
	    <a href="/">
		<!--
		     category: Arrows
		     tags: [pointer, return, revert, reverse, undo, left]
		     version: "1.3"
		     unicode: "eb77"
		-->
		<svg
		    class="ui-icon"
		    xmlns="http://www.w3.org/2000/svg"
		    width="32"
		    height="32"
		    viewBox="0 0 24 24"
		    fill="none"
		    stroke="currentColor"
		    stroke-width="2"
		    stroke-linecap="round"
		    stroke-linejoin="round"
		>
		    <path d="M9 14l-4 -4l4 -4" />
		    <path d="M5 10h11a4 4 0 1 1 0 8h-1" />
		</svg>
	    </a>
            
	</navbar>
	<main>
	    <h2 style="padding-left: 1rem;">My Feeds</h2>
	    <form method="post" class="flex" style="padding: 1rem; gap: 0.5rem;">
		<input
		    type="url"
		    name="new_feed_url"
		    placeholder="https://example.com/feed.xml"
		    style="flex: 1;"
		    required
		>
		<button type="submit" class="btn-submit">
		    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
			<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
		    </svg>
		</button>
	    </form>
	    <?php foreach ($feeds as $feed): ?>
		<div class="feed flex justify-between">
		    <div>
			<p class="feed__title"><?= $feed['title'] ?></p>
			<p class="feed_url"><?= $feed['url'] ?></p>
		    </div>
		    <div>
			<form method="post" style="margin:0;">
			    <input type="hidden" name="delete_feed_id" value="<?= $feed['id'] ?>">
			    <button type="submit" title="Delete feed" class="btn-delete" onclick="return confirm('Delete feed?')">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
				    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
				    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
				</svg>
			    </button>
			</form>
		    </div>
		</div>
	    <?php endforeach; ?>
		</main>
    </body>
</html>
