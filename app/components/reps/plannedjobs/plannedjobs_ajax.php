<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).'/vendor/autoload.php';
use tzVendor\PlannedJobs;
use tzVendor\InputDataManager;
use tzVendor\Entity;

function loadData()
{
    $idm = new InputDataManager;
    $data = $idm->getdata();
    $cs = new PlannedJobs($idm->getitemid());
    $ent = new Entity($idm->getcurid());
    $mdid = $ent->getmdentity()->getid();
    if ($mdid==$cs->toper_mdid) //тех.операция
    {
        $arData = $cs->getPlanbyToper($idm->getcurid(), $data['mindate']['name']);
    }    
    else
    {
        $arData = array('status'=>'ERROR', 'msg'=>"нет обработчика для ".$ent->getmdentity()->getname());
    }    
    echo json_encode($arData);
}

loadData();    
?>

