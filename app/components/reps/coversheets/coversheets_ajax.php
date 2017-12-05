<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).'/vendor/autoload.php';
use tzVendor\CoverSheets;
use tzVendor\InputDataManager;
use tzVendor\Entity;

function loadData()
{
    $idm = new InputDataManager;
    $data = $idm->getdata();
    $cs = new CoverSheets($idm->getitemid());
    $ent = new Entity($idm->getcurid());
    $mdid = $ent->getmdentity()->getid();
    $mode = $idm->getmode();
    $show_empty = $mode!='PRINT';
    if ($mdid=='50643d39-aec2-485e-9c30-bf29b04db75c') //подразделения
    {
        $arData = $cs->getNZPbyTProc($idm->getcurid(), $data['mindate']['name'],$show_empty);
    }    
    elseif ($mdid=='def88585-c509-4200-8980-19ae0e164bd7') //техпроцессы
    {
        $arData = $cs->getNZPbyCS($idm->getcurid(), $data['mindate']['name'],$show_empty);
    }    
    elseif ($mdid=='be0d47b9-2972-496c-a11b-0f3d38874aab') //сопр.листы
    {
        $arData = $cs->getCSdata_byTO($idm->getcurid(),$show_empty);
    }    
    else
    {
        $arData = array('status'=>'ERROR', 'msg'=>"нет обработчика для ".$ent->getmdentity()->getname());
    }    
    echo json_encode($arData);
}

loadData();    
?>
