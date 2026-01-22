#!/usr/bin/php

<?php

require_once(__DIR__. '/../app/includes/database.php');
require_once(__DIR__ . '/../lib/Feed.php');

Feed::$cacheDir = __DIR__ . '/../tmp/';
Feed::$cacheExpire = '1 hour';

$result = $db->query('
    SELECT id, url, format
    FROM feeds
    ');

$feeds = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $feeds[] = $row;
}

foreach ($feeds as $feed) {
    echo "\nRefreshing {$feed['url']} ...\n";

    try {
        $parsed_feed = Feed::load($feed['url']);
        $feed_id = $feed['id'];

        if ($feed['format'] === 'atom') {
            foreach ($parsed_feed->entry as $entry) {
                if (empty($entry->id)) { // ?? this skips if no url
                    continue;
                }

                // Prep Variables
                $title = $entry->title;
                $published_date = (int)$entry->timestamp;
                $guid = $entry->id;
                $url = $entry->link['href'];

                $stmt = $db->prepare('
                    INSERT OR IGNORE INTO entries
                    (feed_id, title, published_date, guid, url)
                    VALUES (:feed_id, :title, :published_date, :guid, :url)
                    ');

                $stmt->bindValue(':feed_id', $feed_id, SQLITE3_INTEGER);
                $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                $stmt->bindValue(':published_date', $published_date, SQLITE3_INTEGER);
                $stmt->bindValue(':guid', $guid, SQLITE3_TEXT);                                  
                $stmt->bindValue(':url', $url, SQLITE3_TEXT);
                $stmt->execute();
            }
        }

        if ($feed['format'] === 'rss') {
           foreach ($parsed_feed->item as $item) {
              $title = $item->title;
              $published_date = strtotime($item->pubDate);
              $guid = $item->guid;
              $url = $item->link;

              $stmt = $db->prepare('INSERT OR IGNORE INTO entries
                (feed_id, title, published_date, guid, url)
                VALUES (:feed_id, :title, :published_date, :guid, :url)
                ');

              $stmt->bindValue(':feed_id', $feed_id, SQLITE3_INTEGER);
              $stmt->bindValue(':title', $title, SQLITE3_TEXT);
              $stmt->bindValue(':published_date', $published_date, SQLITE3_INTEGER);
              $stmt->bindValue(':guid', $guid, SQLITE3_TEXT);
              $stmt->bindValue(':url', $url, SQLITE3_TEXT);
              $stmt->execute();
          }
        }

    	// Update last_fetched_at timestamp in feeds table
      $stmt = $db->prepare('
       UPDATE feeds
           SET last_fetched_at = CURRENT_TIMESTAMP
           WHERE id = :id
           ');

      $stmt->bindValue(':id', $feed_id, SQLITE3_INTEGER);
      $stmt->execute();

  } catch (Throwable $t) {
    fwrite(STDERR, "Failed: {$feed['url']}\n");
}

}

echo "\nFinished.\n";
