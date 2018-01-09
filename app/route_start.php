<?php
require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).'/vendor/autoload.php';
use Dcs\Vendor\Core\Route;

$route = new Route;
$route->start(); 
