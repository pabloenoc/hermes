<?php

$db = new SQLITE3(__DIR__.'/../../db/hrmss.sqlite');
$db->exec('PRAGMA foreign_keys = ON;');