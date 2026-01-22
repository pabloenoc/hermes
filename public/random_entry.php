<?php

require(__DIR__.'/../app/includes/database.php');

$result= $db->query('SELECT * FROM entries ORDER BY RANDOM() LIMIT 1;');
$random_entry = $result->fetchArray(SQLITE3_ASSOC);
header('Location:'.$random_entry['url']);
die();

