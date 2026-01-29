<?php

declare(strict_types=1);

namespace Hermes;

// would be a good name for a php project about rss feeds lol
use \Feed as PhosphoRSS;

final class Feed
{

    private static ?\SQLite3 $db = null;

    public static function set_database(\SQLite3 $db): void
    {
        self::$db = $db;
        self::$db->exec('PRAGMA foreign_keys = ON;');
    }

    private static function db(): \SQLite3
    {
        if (!self::$db)
        {
            throw new \RuntimeException('Database not set');
        }

        return self::$db;
    }

    public static function all(): array
    {
        $feeds = [];
        $result = self::db()->query('SELECT * FROM feeds');

        while($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $feeds[] = $row;
        }

        return $feeds;
    }

    public static function delete(int $id): \SQLite3Result | false
    {
        $stmt = self::db()->prepare('DELETE FROM feeds WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public static function find_by_id(int $id): array
    {
        $stmt = self::db()->prepare('SELECT * FROM feeds WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if (!$row)
        {
            throw new \RuntimeException("Feed with ID {$id} not found\n");
        }

        return $row;
    }

    public static function save_entry(int $feed_id, array $entry): void
    {
        $stmt = self::db()->prepare('INSERT OR IGNORE INTO entries (feed_id, title, published_date, guid, url) VALUES (:feed_id, :title, :published_date, :guid, :url)');

        $stmt->bindValue(':feed_id', $entry['feed_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':title', $entry['title'], SQLITE3_TEXT);
        $stmt->bindValue(':published_date', $entry['published_date'], SQLITE3_INTEGER);
        $stmt->bindValue(':guid', $entry['guid'], SQLITE3_TEXT);
        $stmt->bindValue(':url', $entry['url'], SQLITE3_TEXT);
        $stmt->execute();
    }

    public static function refresh(int $id): void
    {
        $feed = self::find_by_id($id);

        try 
        {
            $parsed_feed = PhosphoRSS::load($feed['url']);
        }
        catch(Exception $e)
        {
            throw new \RuntimeException("Failed to load RSS feed {$feed['url']}");
        }

        if ($feed['format'] === 'rss')
        {
            foreach($parsed_feed->item as $item)
            {
                $payload = [
                    "feed_id" => $feed['id'],
                    "title" => $item->title ?? 'Post via ' . $feed['title'],
                    "published_date" => $item->timestamp,
                    "guid" => $item->guid,
                    "url" => $item->link
                ];

                self::save_entry($feed['id'], $payload);
            }

        }

        if ($feed['format'] === 'atom') {
            foreach($parsed_feed->entry as $entry) {

                $payload = [
                    "feed_id" => $feed['id'],
                    "title" => $entry->title ?? 'Post via ' . $feed['title'],
                    "published_date" => $entry->timestamp,
                    "guid" => $entry->id,
                    "url" => $entry->link['href']
                ];

                self::save_entry($feed['id'], $payload);
            }
        }

        $stmt = self::db()->prepare('UPDATE feeds SET last_fetched_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':id', $feed['id'], SQLITE3_INTEGER);
        $stmt->execute();
    }
}
