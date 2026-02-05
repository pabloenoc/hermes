<?php

// TODO: Move to config
date_default_timezone_set('America/Los_Angeles');

require_once __DIR__ . '/../app/bootstrap.php';
require_once(__DIR__.'/../app/includes/authentication.php');

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
$home_has_entries = false; // Assume no entries for the day
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
	// skip read articles in today filter
	if ($filter === 'today' && $row['is_read'] === 1) {
		continue;
	} else {
		$feeds[$row['feed_id']]['entries'][] = $row;
		$home_has_entries = true;
	}
}

$page_title = 'Home';
require __DIR__ . '/../app/shared/_head.php';
require __DIR__ . '/../app/shared/_navbar.php';
?>

<?php if (empty($feeds)): ?>
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

		<?php if ($home_has_entries): ?>
		<div>
			<button id="toggle-details">
				Collapse All
			</button>
		</div>
		<?php endif ?>

		<div>
			<button id="random-entry">
			    <a target="_blank" href="/random_entry.php">Random</a>
			</button>
		</div>
	</div>

	<?php if (!$home_has_entries): ?>
		<div style="color: gray; display: flex; flex-direction: column; justify-content: center; gap: 0.5rem; align-items: center; margin-top: 1rem; padding-block: 1rem;">
			
			<!--emoticon-->
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
				<?= require __DIR__."/../lib/advice.php" ?>
			</p>
	</div>
<?php else: ?>
	<main class="grid-3">
		<?php foreach ($feeds as $feed): ?>

			<?php if (empty($feed['entries'])): ?>
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
							<?php if ($filter === 'today'): ?>
								<?php include "mark_entry_as_read.php" ?>
							<?php endif ?>
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
