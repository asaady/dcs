<?php
if (!empty($_COOKIE['sid'])) 
{
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_save_path("/home/tmp/sm24/sessions/");
session_start();
ini_set('display_errors', 1);
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING). '/vendor/autoload.php' );
require_once('app/route_start.php');
