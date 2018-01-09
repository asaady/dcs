<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");
require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).'/vendor/autoload.php';

use dcs\app\components\utils\uploadobject\UploadObject;
use dcs\vendor\core\InputDataManager;
use dcs\vendor\core\Entity;
use dcs\vendor\core\EntitySet;
use dcs\vendor\core\Mdentity;
use dcs\vendor\core\CollectionSet;
use dcs\vendor\core\Mdproperty;

function loadData()
{
    $idm = new InputDataManager;
    $action_handler = array(
        'FIELD_FIND'=> function($idm)
        {
            $data = $idm->getdata();
            $objs=false;
            $name = $data['name']['name'];
            $type = $data['type']['name'];
            $id = $data['id']['id'];
            if ($name!=="")
            { 
                if ($type=='id')
                {
                    $objs = EntitySet::getEntitiesByName($data['mdid']['id'],$name);
                }    
                elseif ($type=='cid') 
                {
                    $objs = CollectionSet::getCollByName($id,$name);
                }
                elseif ($type=='mdid') 
                {
                    $objs = Mdentity::getMDbyName($name);
                }
                elseif ($type=='propid') 
                {
                    $objs = Mdproperty::getPropertyByName($name,$data['mdid']['id']);
                }
            }    
            return $objs; 
        },  
        'MDNAME_FIND'=> function($idm)
        {
            $data = $idm->getdata();
            $objs=false;
            $name = $data['name']['name'];
            $type = $data['type']['name'];
            if ($name!=="")
            { 
                if ($type=='mdid')
                {
                    $objs = Mdentity::getMDbyName($name);
                }    
                elseif ($type=='cid') 
                {
                    $objs = CollectionSet::getMDCollectionByName($name);
                }
            }
            return $objs; 
        },
        'MDNAME_GET'=> function($idm)
        {
            $data = $idm->getdata();
            $objs=false;
            $name = $data['name']['name'];
            $type = $data['type']['name'];
            if ($name!=="")
            { 
                if ($type=='mdid')
                {
                    $objs = Mdentity::getMDbyName($name);
                }    
                elseif ($type=='cid') 
                {
                    $objs = CollectionSet::getMDCollectionByName($name);
                }
            }
            return $objs; 
        },
        '_IMPORT'=> function($idm)
        {
            $data = $idm->getdata();
            $objs=false;
            $target_mdid = $data['target_mdid']['id'];
            $uploadobject = new UploadObject($idm->getitemid());
            $objs = $uploadobject->import($target_mdid);
            return $objs; 
        }
    );
    $arData = array();    
    $action = $idm->getaction();
    $prefix = $idm->getprefix();
    $command = $idm->getcommand();
    
    $handlername =strtoupper($prefix).'_'.strtoupper($command);
    if (isset($action_handler[$handlername]))
    {
        $objs = $action_handler[$handlername]($idm);
        if ($objs)
        {
            $arData = array('items'=>$objs); 
        }
        else 
        {
            $arData = array('items'=>(array('id'=>"",'name'=>"LIST IS EMPTY"))); 
        }
    }
    else
    {
        $arData = array('status'=>'ERROR', 'msg'=>"нет обработчика для $handlername");
    }
    $arData['handlername']=$handlername;
    echo json_encode($arData);
}

loadData();    
?>

