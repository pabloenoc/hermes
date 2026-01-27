<?php

declare(strict_types=1);

final class HermesFeed
{

    private static SQLITE3 $db;

    public static function all(): array
    {

        self::$db = new SQLITE3(__DIR__ . '/../db/hrmss.sqlite');

        $feeds = [];
        $result = self::$db->query('SELECT * FROM feeds');

        while($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $feeds[] = $row;
        }

        return $feeds;
    }
}