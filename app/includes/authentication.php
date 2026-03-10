<?php
// 1 week lifetime in seconds
ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_lifetime', 604800);
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
