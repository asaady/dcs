<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).'/vendor/autoload.php';
use dcs\app\components\prnforms\coversheets\PrnCoverSheets;
use dcs\vendor\core\InputDataManager;

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
