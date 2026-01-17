<?php

date_default_timezone_set('America/Los_Angeles');

$db = new SQLITE3('./db/hrmss.sqlite');
$result = $db->query('
	SELECT id, title
	FROM feeds
	');

$feeds = []; 
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $row['entries'] = []; // $feed['entries'] to get array of entries
    $feeds[$row['id']] = $row; // $feeds[32] for item with id 32...
}

// ENTRIES
$feed_has_entries = false; // Assume no entries for the day
$filter = $_GET['filter'] ?? 'today'; // entries sorting filter

$cutoffSQL = null;

switch ($filter) {
	case 'today':
	$cutoffSQL = "strftime('%s', 'now', 'localtime', 'start of day')";
	break;
	case '1w':
	$cutoffSQL = "strftime('%s', 'now', '-7 days')";
	break;
	case '1m':
	$cutoffSQL = "strftime('%s', 'now', '-30 days')";
	break;
	case 'all':
	default:
	$cutoffSQL = null;
}

$sql = '
SELECT feed_id, guid, title, url, published_date, is_read
FROM entries
';

if ($cutoffSQL) {
	$sql .= " WHERE published_date >= CAST($cutoffSQL AS INTEGER)";
}

$sql .= ' ORDER BY published_date DESC';
$result = $db->query($sql);

while($row = $result->fetchArray(SQLITE3_ASSOC)) {   
	$feed_has_entries = true;

	// skip read articles in today filter
	if ($filter === 'today' && $row['is_read'] === 1) {
		continue;
	} else {
		$feeds[$row['feed_id']]['entries'][] = $row;
	}
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
		<img src="images/welcome.svg" class="welcome-image">
		<p class="mt-2" style="font-size: 2rem;">Welcome to Hermes!</p>
		<p><a class="add-feed-btn" style="color:var(--color-link); text-decoration: none;" href="/settings.php">Add feed</a></p>
	</main>
<?php else: ?>
	<div class="toolbar">
		
		<form method="get">
			<select name="filter" id="display-filter" onchange="this.form.submit()">
				<option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
				<option value="today" <?= $filter === 'today' ? 'selected' : '' ?>>Today</option>
				<option value="1w" <?= $filter === '1w' ? 'selected' : '' ?>>1 Week</option>
				<option value="1m" <?= $filter === '1m' ? 'selected' : '' ?>>1 Month</option>
			</select>
		</form>
		<div>
			<button id="toggle-details">
				Collapse All
			</button>
		</div>
	</div>

	<?php if (!$feed_has_entries): ?>
		<div style="color: gray; display: flex; flex-direction: column; justify-content: center; gap: 0.5rem; align-items: center; margin-top: 1rem; padding-block: 1rem;">
                            <!--
tags: [emotion, feeling, happy, tick, accept, face]
category: Mood
version: "2.7"
unicode: "f7b3"
			-->
			<svg
			xmlns="http://www.w3.org/2000/svg"
			width="64"
			height="64"
			viewBox="0 0 24 24"
			fill="none"
			stroke="currentColor"
			stroke-width="0.75"
			stroke-linecap="round"
			stroke-linejoin="round"
			>
			<path d="M20.925 13.163a8.998 8.998 0 0 0 -8.925 -10.163a9 9 0 0 0 0 18" />
			<path d="M9 10h.01" />
			<path d="M15 10h.01" />
			<path d="M9.5 15c.658 .64 1.56 1 2.5 1s1.842 -.36 2.5 -1" />
			<path d="M15 19l2 2l4 -4" />
		</svg>
		<p style="margin: 0;">It's quiet right now...</p>
		<p style="margin-top: 0">
			<?= include "lib/advice.php" ?>
		</p>
	</div>
<?php else: ?>
	<main class="grid-3">
		<?php foreach ($feeds as $feed): ?>
			<?php 
			$empty_feed = count($feed['entries']) === 0 ? true : false;
			?>

			<?php if ($empty_feed): ?>
				<?php continue ?>
			<?php else: ?>

				<div class="feed" >
					<details open>
						<summary class="feed__title"><?= htmlspecialchars($feed['title']) ?></summary>

						<?php foreach($feed['entries'] as $entry): ?>
							<div class="post">
								<?php if ($filter === 'today'): ?>
									<?php if (str_contains($entry['guid'], "ycombinator") || str_contains($entry['guid'], "lobste.rs")): ?>
										<p class="post__date"><a class="techie-site-link" href="<?= $entry['guid'] ?>" ><?= htmlspecialchars(date("h:ia", $entry['published_date'])) ?></a></p>
									<?php else: ?>
									<p class="post__date"><?= htmlspecialchars(date("h:ia", $entry['published_date'])) ?></p>
									<?php endif; ?>

								<?php else: ?>
									<?php if (str_contains($entry['guid'], "ycombinator") || str_contains($entry['guid'], "lobste.rs")): ?>
										<p class="post__date"><a class="techie-site-link" href="<?= $entry['guid'] ?>"><?= htmlspecialchars(date("d M · h:ia", $entry['published_date'])) ?></a></p>
									<?php else: ?>
										<p class="post__date"><?= htmlspecialchars(date("d M · h:ia", $entry['published_date'])) ?></p>
									<?php endif ?>
								
								<?php endif; ?>
							<p class="post__title"><a target="_blank" href="<?= htmlspecialchars($entry['url']) ?>" class="post__link"><?= htmlspecialchars($entry['title']); ?></a></p>
							<?php include "mark_entry_as_read.php" ?>
						</div>
					<?php endforeach; ?>
				<?php endif ?>

			</details>
		</div>
	<?php endforeach; ?>
</main>
<?php endif ?>


<?php endif; ?>

<script>
	const btn = document.getElementById('toggle-details');
	const details = document.querySelectorAll('details');

	function updateLabel() {
		const allOpen = [...details].every(d => d.open);
		btn.textContent = allOpen ? 'Collapse All' : 'Expand All';
	}

	 // ensure correct label on load
	updateLabel();

	btn.addEventListener('click', () => {
		const shouldOpen = [...details].some(d => !d.open);

		details.forEach(d => {
			d.open = shouldOpen;
		});

		updateLabel();
	});
</script>



</body>
</html>
