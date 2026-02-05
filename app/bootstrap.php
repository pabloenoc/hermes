<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Hermes\Feed;

$db = new SQLITE3(__DIR__ . '/../db/hrmss.sqlite');
$db->exec('PRAGMA foreign_keys = ON;');

Feed::set_database($db);
