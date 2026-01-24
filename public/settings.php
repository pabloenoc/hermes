<?php
// SETTINGS.PHP

// Displays all feeds
// Allow users to add new feeds
// Allow users to delete existing feeds
require_once(__DIR__.'/../app/includes/authentication.php');
require_once(__DIR__.'/../app/includes/database.php');

$result = $db->query('
	SELECT id, title, url, last_fetched_at
	FROM feeds
	');

$feeds = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
	$feeds[] = $row;
}

$page_title = 'Settings';
require __DIR__ . '/../app/shared/_head.php';
?>

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
	<h2 style="margin-bottom: 0;">My Feeds</h2>

	<?php if (isset($feeds[0])): ?>
		<?php 
			$dt = new DateTime($feeds[0]['last_fetched_at'], new DateTimeZone('UTC'));
			$dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
		?>
		<p style=" color: gray; margin-top: 0.15rem;">
			<small><?= count($feeds) ?> <?= count($feeds) > 1 ? "feeds" : "feed" ?> ◦ Updated <?= $dt->format('h:ia') ?></small>
		</p>
	<?php else: ?>
		<p style=" color: gray; margin-top: 0.15rem;">
			<small>Tip: You can use <a target="_blank" href="https://powrss.com" style="color: var(--color-link)">powRSS</a> to discover sites from around the web.</small>
		</p>
	<?php endif ?>

	<?php require __DIR__ . "/../app/feeds/_new.php"; ?>

	<?php foreach ($feeds as $feed): ?>
		<div class="feed flex justify-between">
			<div>
				<p class="feed__title" style="font-weight: normal;"><?= $feed['title'] ?></p>
				<p class="feed_url"><?= $feed['url'] ?></p>
			</div>
			<div>
				<?php require __DIR__ . "/../app/feeds/_delete.php" ?>
			</div>

		</div>
	<?php endforeach; ?>

	<h2 style="margin-block: 4rem">Account Options</h2>
	<form method='post' action='/logout.php' style="margin-top: 2rem;">
		<button type='submit' class='btn-submit'>Log out</button>
	</form>

</main>
</body>
</html>
