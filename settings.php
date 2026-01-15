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

	<meta property="og:title" content="Hermes - Read the web">
	<meta property="og:description" content="Keep an eye on what matters to you.">
	<meta property="og:image" content="/images/og_image.png">
	<meta property="og:url" content="https://hrmss.enocc.com">
	<meta property="og:type" content="website">
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
	<div style="padding-left: 1rem;">
		<h2 style="margin-bottom: 0;">My Feeds</h2>
	</div>



	<?php include "new_feed.php"; ?>

	<?php
	if (isset($feeds[0])) {
		$dt = new DateTime($feeds[0]['last_fetched_at'], new DateTimeZone('UTC'));
		$dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
	}
	?>

	<?php if (isset($feeds[0])): ?>
	<p style="padding-left: 1rem; color: gray;">
		<small><?= count($feeds) ?> <?= count($feeds) > 1 ? "feeds" : "feed" ?> ◦ Updated <?= $dt->format('h:ia') ?></small>
	</p>
	<?php else: ?>
		<p style="padding-left: 1rem; color: gray;">
		<small>Tip: You can use <a target="_blank" href="https://powrss.com" style="color: var(--color-link)">powRSS</a> to discover sites from around the web.</small>
		</p>
	<?php endif ?>

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
