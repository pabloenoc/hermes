<?php

declare(strict_types=1);

namespace Hermes;

final class Feed
{

    private static \SQLITE3 $db;

    public static function all(): array
    {

        self::$db = new \SQLITE3(__DIR__ . '/../db/hrmss.sqlite');
        self::$db->exec('PRAGMA foreign_keys = ON;');

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
        self::$db = new \SQLITE3(__DIR__ . '/../db/hrmss.sqlite');
        self::$db->exec('PRAGMA foreign_keys = ON;');

        $stmt = self::$db->prepare('DELETE FROM feeds WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }
}
