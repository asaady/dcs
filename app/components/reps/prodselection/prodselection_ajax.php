<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).'/vendor/autoload.php';
use tzVendor\ProdSelection;
use tzVendor\InputDataManager;
use tzVendor\Entity;

function loadData()
{
    $idm = new InputDataManager;
    $data = $idm->getdata();
    $ps = new ProdSelection($idm->getitemid());
    $arData = $ps->get_selection($data['doc1']['id'],$data['doc2']['id'],$data['ref1']['id'],$data['ref2']['id'],$data['ref3']['id']);
    echo json_encode($arData);
}

loadData();    
?>

