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

function save_feed_entry($db, $feed_id, $title, $published_date, $guid, $url) {
    $stmt = $db->prepare('INSERT OR IGNORE INTO entries (feed_id, title, published_date, guid, url) VALUES (:feed_id, :title, :published_date, :guid, :url)');
    $stmt->bindValue(':feed_id', $feed_id, SQLITE3_INTEGER);
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':published_date', $published_date, SQLITE3_INTEGER);
    $stmt->bindValue(':guid', $guid, SQLITE3_TEXT);
    $stmt->bindValue(':url', $url, SQLITE3_TEXT);
    $stmt->execute();
}

foreach ($feeds as $feed) {
    echo "\nRefreshing {$feed['url']} ...\n";

    $feed_id = $feed['id'];

    try {
        $parsed_feed = Feed::load($feed['url']);
    }
    catch(Exception $e) {
        echo $e->getMessage();
        continue;
    }

    if ($feed['format'] === 'rss') {
        foreach($parsed_feed->item as $item) {
            $title = $item->title ?? 'A post from ' . $feed['title'];
            $published_date = strtotime($item->pubDate);
            $guid = $item->guid;
            $url = $item->link;

            save_feed_entry($db, $feed_id, $title, $published_date, $guid, $url);
        }
    }

    if ($feed['format'] === 'atom') {
        foreach($parsed_feed->entry as $entry) {
            $title = $entry->title ?? 'A post from ' . $feed['title'];
            $published_date = strtotime($entry->published);
            $guid = $entry->id;
            $url = $entry->link['href'];

            save_feed_entry($db, $feed_id, $title, $published_date, $guid, $url);
        }
    }

    // Update last_fetched_at in feeds table
    $stmt = $db->prepare('UPDATE feeds SET last_fetched_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->bindValue(':id', $feed_id, SQLITE3_INTEGER);
    $stmt->execute();
}

echo "\nFinished.\n";
