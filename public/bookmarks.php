<?php
// BOOKMARKS.PHP

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/includes/authentication.php';

$result = $db->query("
SELECT
    entries.*,
    feeds.title AS feed_title
FROM entries
JOIN feeds ON feeds.id = entries.feed_id
WHERE entries.bookmarked = 1
ORDER BY entries.published_date DESC
");

$feeds = [];
$has_bookmarks = false;

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $has_bookmarks = true;
    $feed_id = $row['feed_id'];

    if (!isset($feeds[$feed_id])) {
        $feeds[$feed_id] = [
            'id' => $feed_id,
            'title' => $row['feed_title'],
            'entries' => []
        ];
    }

    $feeds[$feed_id]['entries'][] = $row;
}

$page_title = 'Bookmarks';
require __DIR__ . '/../app/shared/_head.php';
require __DIR__ . '/../app/shared/_navbar.php';
?>


<main>
    <h2 style="margin-bottom: 0;">My Bookmarks</h2>

    <?php if (!$has_bookmarks): ?>
	<div style="color: gray; display: flex; flex-direction: column; justify-content: center; gap: 0.5rem; align-items: center; margin-top: 1rem; padding-block: 1rem;">
	    
	    <!--globe-->
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
		<path d="M7 9a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
		<path d="M5.75 15a8.015 8.015 0 1 0 9.25 -13" />
		<path d="M11 17v4" />
		<path d="M7 21h8" />
	    </svg>

	    <p style="margin: 0;">You have no bookmarks yet!</p>
	</div>
        <?php endif; ?>

<main class="grid-3" style="padding-inline: 0;">
    <?php foreach ($feeds as $feed): ?>

	<?php if (empty($feed['entries'])): ?>
	    <?php continue ?>
	<?php else: ?>

	    <div class="feed" >
		<details open>
		    <summary class="feed__title"><?= htmlspecialchars($feed['title']) ?></summary>
		    <?php foreach($feed['entries'] as $entry): ?>
			<div class="post">
			    <?php if (str_contains($entry['guid'], "ycombinator") || str_contains($entry['guid'], "lobste.rs")): ?>
				<p class="post__date"><a class="techie-site-link" href="<?= $entry['guid'] ?>"><?= htmlspecialchars(date("d M · h:ia", $entry['published_date'])) ?></a></p>
			    <?php else: ?>
				<p class="post__date"><?= htmlspecialchars(date("d M · h:ia", $entry['published_date'])) ?></p>
			    <?php endif ?>

			    <p class="post__title"><a target="_blank" href="<?= htmlspecialchars($entry['url']) ?>" class="post__link"><?= htmlspecialchars($entry['title']); ?></a></p>
			</div>
		    <?php endforeach; ?>
		</details>
	    </div>
	<?php endif ?>
	
	
    <?php endforeach; ?>
</main>

</main>
</body>
</html>
