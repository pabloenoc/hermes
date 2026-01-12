<?php

// TODO: Fetch feed updates

require_once('vendor/Feed.php');
Feed::$cacheDir = __DIR__ . '/tmp';
Feed::$cacheExpire = '5 hours';

$db = new SQLITE3('./db/hrmss.sqlite');
$result = $db->query('
    SELECT id, title
    FROM feeds
');

$feeds = []; 
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $row['entries'] = []; // prepare slot so I can do e.g. $feed['entries'] to get array of entries
    $feeds[$row['id']] = $row; // make id=key so I can do $feeds[32] for item with id 32...
}

// TODO: Grab published_at timestamp to order by date
$result = $db->query('
    SELECT feed_id, title, url
    FROM entries
');

while($row = $result->fetchArray(SQLITE3_ASSOC)) {    
    $feeds[$row['feed_id']]['entries'][] = $row;
}

?>

<!doctype html>
<html>
    <head>
	<title>Hermes - Read the web</title>
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
		<h1>Home</h1>
	    </a>
	    
            <!--
		 category: System
		 tags: [cog, edit, gear, preferences, tools]
		 version: "1.0"
		 unicode: "eb20"
            -->
	    <a href="/settings.php">
		<svg class="ui-icon"
		     xmlns="http://www.w3.org/2000/svg"
		     width="32"
		     height="32"
		     viewBox="0 0 24 24"
		     fill="none"
		     stroke="currentColor"
		     stroke-width="1.5"
		     stroke-linecap="round"
		     stroke-linejoin="round"
		>
		    <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
		    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
		</svg>
	    </a>
            
	</navbar>
	
	<?php if (count($feeds) === 0): ?>
	    <main style="text-align: center;">
		<p class="mt-2">You have no feeds yet! </p>
		<p><a style="color:var(--color-link); text-decoration: none;" href="/settings.php">Add feed</a></p>
	    </main>
	<?php else: ?>
	    <main class="grid-3">
		<?php foreach ($feeds as $feed): ?>
			<div class="feed">
			    <details open>
				<summary class="feed__title"><?= htmlspecialchars($feed['title']) ?></summary>
				<?php foreach($feed['entries'] as $entry): ?>
				    <div class="post">
					<p class="post__title"><a target="_blank" href="<?= htmlspecialchars($entry['url']) ?>" class="post__link"><?= htmlspecialchars($entry['title']); ?></a></p>
				    </div>
				<?php endforeach; ?>
			    </details>
			</div>
		<?php endforeach; ?>
	    </main>
	<?php endif; ?>

	
    </body>
</html>
