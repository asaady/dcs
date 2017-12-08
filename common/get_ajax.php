<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
require '../vendor/autoload.php';

use tzVendor\InputDataManager;
use tzVendor\Common_data;
use tzVendor\Entity;
use tzVendor\EntitySet;
use tzVendor\DataManager;
use tzVendor\PropsTemplate;

function getData()
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
                    $objs = tzVendor\EntitySet::getEntitiesByName($id,$name);
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
                    $objs = tzVendor\Mdproperty::getPropertyByName($name,$idm);
                }
            }    
            return $objs; 
        },  
        '_PROP_FIND'=> function($idm)
        {
            $data = $idm->getdata();
            $objs=false;
            $name = $data['name']['name'];
            if ($name!=="")
            { 
                $objs = tzVendor\Mdproperty::getPropertyByName($name,$idm);
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
    'FIELD_SAVE'=> function($idm)
        {
            $getdata = $idm->getdata();
            $data=array();
            $data[$getdata['propid']['id']]=array('name'=>$getdata['name']['name'],'id'=>$getdata['id']['id']);
            $ent = new Entity($idm->getitemid());
            $ent->update($data);
        },
    'GET_ACTIONLIST'=> function($idm)
        {
            $data = $idm->getdata();
            $mode = $idm->getmode();
            $objs=false;
            $id = $data['id']['id'];
            if ($id!=="")
            { 
                $objs = DataManager::getActionList($id,$mode,$idm->getaction()); 
            }
            else 
            {
                $objs = DataManager::getActionList($idm->getitemid(),$mode,$idm->getaction()); 
            }
            return $objs; 
        },
    'AFTER_CHOICE'=> function($idm)
        {
            $objs=array();
            $data = $idm->getdata();
            $itemid = $data['id']['id'];
            if (!Common_data::check_uuid($itemid))
            {
                return $objs;
            }    
            $mode = $idm->getmode();
            $name = $data['name']['name'];
            $type = $data['type']['name'];
            if (Common_data::check_uuid($name))
            { 
                if ($type=='id')
                {

                    try 
                    {
                        $ent = new Entity($name);
                    } 
                    catch (Exception $exc) 
                    {
                        return $objs;
                    }
                    $ar_md = Entity::getEntityDetails($itemid);
                    $ar_prop = \tzVendor\MdpropertySet::getMDProperties($ar_md['mdid'], $mode, " WHERE mp.mdid = :mdid ");
                    foreach ($ar_prop as $prop)
                    {
                        if ($prop['type']=='id')
                        {    
                            $propid = $prop['propid'];
                            foreach($ent->properties() as $e_prop)
                            {
                                $e_propid = $e_prop['propid'];
                                if ($e_propid!=$propid)
                                {
                                    continue;
                                }    
                                $objs[$prop['id']]=array('id'=>$ent->getattrid($e_prop['id']),'name'=>$ent->getattr($e_prop['id']));
                            }    
                        }    
                    }    
                }    
                elseif ($type=='cid') 
                {
                }
            }
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
};
getData();
?>
