#!/usr/bin/php

<?php

require_once(__DIR__.'/../vendor/autoload.php');

use Hermes\Feed;

$feeds = Feed::all();

$script_banner = <<<TEXT
===================================================
        R E F R E S H I N G   F E E D S
===================================================

TEXT;

echo $script_banner;

foreach($feeds as $feed)
{
    echo "\nRefreshing {$feed['url']}...\n";
    echo "===================================================";

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