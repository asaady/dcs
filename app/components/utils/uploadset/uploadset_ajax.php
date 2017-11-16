<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
require filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).'/vendor/autoload.php';
use tzVendor\UploadSet;
use tzVendor\InputDataManager;
use tzVendor\Entity;

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
                    $objs = tzVendor\EntitySet::getEntitiesByName($data['mdid']['id'],$name);
                }    
                elseif ($type=='cid') 
                {
                    $objs = tzVendor\CollectionSet::getCollByName($id,$name);
                }
                elseif ($type=='mdid') 
                {
                    $objs = tzVendor\Mdentity::getMDbyName($name);
                }
                elseif ($type=='propid') 
                {
                    $objs = tzVendor\Mdproperty::getPropertyByName($name,$data['mdid']['id']);
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
                    $objs = \tzVendor\Mdentity::getMDbyName($name);
                }    
                elseif ($type=='cid') 
                {
                    $objs = tzVendor\CollectionSet::getMDCollectionByName($name);
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
                    $objs = \tzVendor\Mdentity::getMDbyName($name);
                }    
                elseif ($type=='cid') 
                {
                    $objs = tzVendor\CollectionSet::getMDCollectionByName($name);
                }
            }
            return $objs; 
        },
        '_IMPORT'=> function($idm)
        {
            $data = $idm->getdata();
            $objs=false;
            $target_id = $data['target_id']['id'];
            $target_mdid = $data['target_mdid']['id'];
            $setpropid = $data['setpropid']['id'];
            $uploadset = new UploadSet($idm->getitemid());
            $objs = $uploadset->import($target_id,$target_mdid,$setpropid);
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

