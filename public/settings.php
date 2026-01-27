<?php
// SETTINGS.PHP

// Displays all feeds
// Allow users to add new feeds
// Allow users to delete existing feeds
require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/../app/includes/authentication.php');
require_once(__DIR__.'/../app/includes/database.php');


use Hermes\Feed;

$feeds = Feed::all();

$page_title = 'Settings';
require __DIR__ . '/../app/shared/_head.php';
require __DIR__ . '/../app/shared/_navbar.php';
?>

	
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
