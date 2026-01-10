<?php

require_once('vendor/Feed.php');
Feed::$cacheDir = __DIR__ . '/tmp';
Feed::$cacheExpire = '5 hours';

$db = new SQLITE3('./db/hrmss.sqlite');
$result = $db->query('
    SELECT url
    FROM feeds
');

$urls = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $urls[] = $row['url'];
}

$feeds = [];

foreach($urls as $url) {
    $feed = Feed::load($url);
    array_push($feeds, $feed);
}

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
	  <h1><a href="/">h<span>r</span>m<span>ss</span></a></h1>
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
            stroke-width="1"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
            <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
          </svg>
	  </a>
          
      </navbar>
      <main class="grid-3">
	  <?php foreach ($feeds as $feed): ?>
	      <?php if ($feed->entry): ?>

		  <div class="feed">
		      <details open>
			  <summary class="feed__title"><?= $feed->title ?></summary>
		      <?php foreach($feed->entry as $entry): ?>
			  <div class="post">
			      <p class="post__title"><a target="_blank" href="<?= $entry->url; ?>" class="post__link"><?= $entry->title; ?></a></p>
			  </div>
		      <?php endforeach; ?>
		      </details>
		  </div>

	      <?php endif; ?>

	      <?php if ($feed->item): ?>
		    <div class="feed">
		      <details open>
			  <summary class="feed__title"><?= $feed->title ?></summary>
		      <?php foreach($feed->item as $item): ?>
			  <div class="post">
			      <p class="post__title"><a target="_blank" href="<?= $item->link; ?>" class="post__link"><?= $item->title; ?></a></p>
			  </div>
		      <?php endforeach; ?>
		      </details>
		    </div>
	      <?php endif; ?>
	    
	  <?php endforeach; ?>





      
      </main>
    </body>
</html>
