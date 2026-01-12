<?php
// SETTINGS.PHP


// Allow users to add new feeds
// Allow users to delete existing feeds

require_once('vendor/Feed.php');

$db = new SQLITE3('./db/hrmss.sqlite');
$db->exec('PRAGMA foreign_keys = ON;');

$result = $db->query('
    SELECT id, title, url, last_fetched_at
    FROM feeds
');

$feeds = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $feeds[] = $row;
}

// CREATE FEED
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_feed_url']))
{

    
    $url = trim($_POST['new_feed_url']);

    if ($url === '') {
	$errors[] = 'URL cannot be blank';
    } else if(!filter_var($url, FILTER_VALIDATE_URL)) {
	$errors[] = 'URL is not valid URL.';
    }

    if (empty($errors))
    {
	// Prep with Feed.php
	try {
	    $feed = Feed::load($url);
	    $feed_title = $feed->title;
	    $feed_format = $feed->item ? 'rss' : 'atom';

	    $stmt = $db->prepare('INSERT OR IGNORE INTO feeds (url, title, format) VALUES (:url, :title, :format)');
	    $stmt->bindValue(':url', $url, SQLITE3_TEXT);
	    $stmt->bindValue(':title', $feed_title, SQLITE3_TEXT);
	    $stmt->bindValue(':format', $feed_format, SQLITE3_TEXT);
	    $stmt->execute();

	    // Save feed entries to db...
	    $feed_id = $db->lastInsertRowID();

	    // Atom format first
	    if ($feed_format === 'atom') {
		foreach($feed->entry as $entry) {
		    $title = $entry->title;
		    $published_date = (int)$entry->timestamp; // TODO: fix if empty
		    $guid = $entry->id;
		    $url = $entry->link['href'];

		    $stmt = $db->prepare('INSERT OR IGNORE INTO entries (feed_id, title, published_date, guid, url) VALUES (:feed_id, :title, :published_date, :guid, :url)');
                    $stmt->bindValue(':feed_id', $feed_id, SQLITE3_INTEGER);
                    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                    $stmt->bindValue(':published_date', $published_date, SQLITE3_INTEGER);
                    $stmt->bindValue(':guid', $guid, SQLITE3_TEXT);
                    $stmt->bindValue(':url', $url, SQLITE3_TEXT);
                    $stmt->execute();
		}
	    } elseif ($feed_format === 'rss') {
		foreach($feed->item as $item) {
		    $title = $item->title;
                    $published_date = strtotime($item->pubDate);
                    $guid = $item->guid;
                    $url = $item->link;

		    $stmt = $db->prepare('INSERT OR IGNORE INTO entries (feed_id, title, published_date, guid, url) VALUES (:feed_id, :title, :published_date, :guid, :url)');
                    $stmt->bindValue(':feed_id', $feed_id, SQLITE3_INTEGER);
                    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                    $stmt->bindValue(':published_date', $published_date, SQLITE3_INTEGER);
                    $stmt->bindValue(':guid', $guid, SQLITE3_TEXT);
                    $stmt->bindValue(':url', $url, SQLITE3_TEXT);
                    $stmt->execute();
		}
		
	    }
	    
	    

	    header('Location: ' . $_SERVER['REQUEST_URI']);	    
	    exit;
	    
	} catch (Throwable $e) {
	    $errors[] = 'URL is not a valid RSS or Atom feed.';
	    // log error?
	}
	
	
    }

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
	<title>Hermes - Settings</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
	<link rel="icon" type="image/png" href="/images/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/images/favicon.svg" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png" />
	<meta name="apple-mobile-web-app-title" content="Hermes" />
	<link rel="manifest" href="/site.webmanifest" />
                </head>
    <body>
	<navbar>
	    <a href="/" class="flex" style="gap: 0.5rem; align-items: center; text-decoration: none; color: inherit;">
		<img src="/images/hermes.webp" id="logo">
		<h1>Settings</h1>
	    </a>
	    
	    <a href="/" style="color:inherit; text-decoration: none;">
		<!--
		     tags: [save, file, disk]
		     category: Devices
		     version: "1.2"
		     unicode: "eb62"
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
		    <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
		    <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
		    <path d="M14 4l0 4l-6 0l0 -4" />
		</svg>
	    </a>
            
	</navbar>
	<main>
	    <h2 style="padding-left: 1rem;">My Feeds</h2>


	    <!-- Display form errors -->
	    <?php if (!empty($errors)): ?>
		<div class="" style="color: var(--color-error); padding-left: 1rem;">
		    <p>This feed could not be added to Hermes.</p>
		    <ul>
			<?php foreach ($errors as $error): ?>
			    <li>
				<?= $error ?>
			    </li>
			<?php endforeach; ?>
		    </ul>
		</div>
	    <?php endif; ?>
	    
	    <form method="post" class="flex" style="padding: 1rem; gap: 0.5rem;">
		<input
		    type="url"
		    name="new_feed_url"
		    placeholder="https://example.com/feed.xml"
		    style="flex: 1;"
		    required
		    aria-invalid="<?= empty($errors) ? 'false' : 'true' ?>"
		    value="<?= empty($errors)? '' : $_POST['new_feed_url'] ?>"
		>
		<button type="submit" class="btn-submit">
		    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
			<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
		    </svg>
		</button>
	    </form>

	    <?php
	    $dt = new DateTime($feeds[0]['last_fetched_at'], new DateTimeZone('UTC'));
	    $dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
	    ?>

	    <p style="padding-left: 1rem; color: gray;">
		<small>Last updated: <?= $dt->format('h:ia') ?></small>
	    </p>
	    
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
