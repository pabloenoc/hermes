<?php

// TODO: Database class ?

// Feed::refresh($feed_id);

declare(strict_types=1);

namespace Hermes;

final class Feed
{

    private static \SQLite3 $db;

    public static function connect()
    {
        self::$db = new \SQLite3(__DIR__ . '/../db/hrmss.sqlite');
        self::$db->exec('PRAGMA foreign_keys = ON;');
    }

    public static function all(): array
    {
        self::connect();
        $feeds = [];
        $result = self::$db->query('SELECT * FROM feeds');

        while($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $feeds[] = $row;
        }

        return $feeds;
    }

    public static function delete(int $id): \SQLite3Result | false
    {
        self::connect();
        $stmt = self::$db->prepare('DELETE FROM feeds WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public static function find_by_id(int $id): ?array
    {
        self::connect();
        $stmt = self::$db->prepare('SELECT * FROM feeds WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }
}
