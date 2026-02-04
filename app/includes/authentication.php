<?php
// 1 day lifetime
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_start();


$authenticated = isset($_SESSION['hermes_user_id']) ? true : false;

if (!$authenticated && $_SERVER['PHP_SELF'] !== '/login.php') {
    header('Location: login.php');
    exit;
}

if ($authenticated && $_SERVER['PHP_SELF'] === '/login.php') {
    header('Location: index.php');
    exit;
}
