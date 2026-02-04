<?php

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/../app/includes/database.php');

use Hermes\Feed;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feed_id']))
{
    Feed::delete($_POST['delete_feed_id']);
    header('Location: /settings.php');
    exit;
}
