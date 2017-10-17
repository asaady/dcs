<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
ini_set('display_errors', 1);
require_once 'app/route_start.php';
