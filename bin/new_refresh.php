#!/usr/bin/php

<?php

require_once(__DIR__ . '/../app/includes/database.php');
require_once(__DIR__ . '/../lib/Feed.php');

function get_all_feeds(SQLite3 $db)
{
    $result = $db->query('SELECT * FROM feeds');
    $feeds = [];

    while($row = $result->fetchArray(SQLITE3_ASSOC))
    {
        $feeds[] = $row;
    }

    return $feeds;
}

