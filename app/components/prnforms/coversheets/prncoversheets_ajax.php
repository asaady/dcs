<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).'/vendor/autoload.php';
use tzVendor\PrnCoverSheets;
use tzVendor\InputDataManager;
use tzVendor\Entity;

function loadData()
{
    $idm = new InputDataManager;
    $mode = $idm->getmode();
    $cs = new PrnCoverSheets($idm->getitemid());
    $arData = $cs->getCSdata_byTO();
    echo json_encode($arData);
}

loadData();    
?>
