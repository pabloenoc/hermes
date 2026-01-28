#!/usr/bin/php

<?php

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__ . '/../app/includes/database.php');
// require_once(__DIR__ . '/../lib/Feed.php');

use Hermes\Feed;

$feeds = Feed::all();

$script_banner = <<<TEXT
===================================================
                R E F R E S H I N G 
===================================================

TEXT;

echo $script_banner;

foreach($feeds as $feed)
{
    echo "\nRefreshing {$feed['url']}...\n";

    try 
    {
        Feed::refresh($feed['id']);
    } 
    catch(Exception $e)
    {
        echo $e->getMessage();
        continue;
    }

}

echo "\nFinished.\n";