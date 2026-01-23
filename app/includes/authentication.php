<?php

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