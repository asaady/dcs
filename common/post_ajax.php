<?php
if (!empty($_COOKIE['sid'])) {
    // check session id in cookies
    session_id($_COOKIE['sid']);
}
session_start();
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");
require '../vendor/autoload.php';
use tzVendor\Entity;
use tzVendor\EntitySet;
use tzVendor\DataManager;
use tzVendor\InputDataManager;
use tzVendor\CollectionSet;
use tzVendor\CollectionItem;
use tzVendor\Mdproperty;
use tzVendor\MdentitySet;
use tzVendor\Mdentity;
use tzVendor\Cproperty;

function loadData()
{
    $idm = new InputDataManager;
    $load_handler = array(
    'EntitySet_VIEW_LOAD'=> function($idm){
        $md = new EntitySet($idm->getitemid());
        if (($md->getmditem()->getname()=='Cols')||($md->getmditem()->getname()=='Comps'))
        {    
            return CollectionSet::getCollectionByFilter($idm->getdata(),$idm->getmode(),$idm->getaction());
        }
        else 
        {
            return EntitySet::getEntitiesByFilter($idm->getdata(),$idm->getmode(),$idm->getaction());
        }
    },    
    'EntitySet_EDIT_LOAD'=> function($idm){
        $md = new EntitySet($idm->getitemid());
        if (($md->getmditem()->getname()=='Cols')||($md->getmditem()->getname()=='Comps'))
        {    
            return CollectionSet::getCollectionByFilter($idm->getdata(),$idm->getmode(),$idm->getaction());
        }
        else 
        {
            return EntitySet::getEntitiesByFilter($idm->getdata(),$idm->getmode(),$idm->getaction());
        }
    },
    'Mdproperty_Entity_VIEW_LOAD'=>function($idm){
        $mdprop = new Mdproperty($idm->getitemid());
        return $mdprop->get_history_data($idm->getcurid());
    },
    'EntitySet_CREATE_LOAD'=> function($idm){
        return Entity::getEntityData($idm->getitemid(),$idm->getmode(),'EDIT');
    },
    'EntitySet_Entity_CREATE_LOAD'=> function($idm){
        return Entity::getEntityData($idm->getitemid(),$idm->getmode(),'EDIT',$idm->getcurid());
    },
    'EntitySet_CREATE_SAVE'=> function($idm){
        $md = new Mdentity($idm->getitemid());
        if (($md->getmdtypename()=='Cols')||($md->getmdtypename()=='Comps'))
        {
            $colitem = new CollectionItem($idm->getitemid());
            return $colitem->create($idm->getdata());
        }    
        else
        {
            $entity = new Entity($idm->getitemid());
            $entity->set_data($idm->getdata());
            return $entity->createNew();
        }    
    },
    'EntitySet_Entity_CREATE_SAVE'=> function($idm){
        $md = new Mdentity($idm->getitemid());
        if (($md->getmdtypename()=='Cols')||($md->getmdtypename()=='Comps'))
        {
            $colitem = new CollectionItem($idm->getitemid());
            return $colitem->create($idm->getdata());
        }    
        else
        {
            $entity = new Entity($idm->getitemid());
            $entity->set_data($idm->getdata());
            return $entity->createNew();
        }    
    },
    'EntitySet_CollectionItem_EDIT_BEFORE_DELETE'=> function($idm){
        $coll = new CollectionItem($idm->getcurid());
        return $coll->before_delete();
    },
    'EntitySet_CollectionItem_EDIT_DELETE'=> function($idm){
        $coll = new CollectionItem($idm->getcurid());
        return $coll->delete();
    },
    'EntitySet_Entity_EDIT_BEFORE_DELETE'=> function($idm){
        $entity = new Entity($idm->getcurid());
        return $entity->before_delete();
    },
    'EntitySet_Entity_EDIT_DELETE'=> function($idm){
        $entity = new Entity($idm->getcurid());
        return $entity->delete();
    },
   'Entity_VIEW_LOAD'=> function($idm){
        return Entity::getEntityData($idm->getitemid(),$idm->getmode(),$idm->getaction());
    },
   'Entity_EDIT_LOAD'=> function($idm){
        return Entity::getEntityData($idm->getitemid(),$idm->getmode(),$idm->getaction());
    },
    'Entity_EDIT_BEFORE_SAVE'=> function($idm) {
        $entity = new Entity($idm->getitemid());
        return $entity->before_save($idm->getdata());
    },    
    'Entity_EDIT_SAVE'=> function( $idm) {
        $entity = new Entity($idm->getitemid());
        return $entity->update($idm->getdata());
    },    
    'Entity_Entity_SET_EDIT_LIST'=> function( $idm) {
        $data = $idm->getdata();
        $mdprop = new Mdproperty($data['filter_id']['id']);
        $arMD = Entity::getEntityDetails($idm->getcurid());
        $event_trig = DataManager::get_event_trigger('onSelect',$arMD['mdid'] , $mdprop->getpropstemplate()->getid());
        if ($event_trig)
        {
            die(var_dump($event_trig));
        }   
        else
        {
            $data = array();
            $data['itemid'] = array('id'=>$mdprop->getpropstemplate()->getvalmdentity()->getid(),'name'=>'');
            $data['filter_id']= array('id'=>'','name'=>'');
            $data['filter_val']= array('id'=>'','name'=>'');
            $data['filter_min']= array('id'=>'','name'=>'');
            $data['filter_max']= array('id'=>'','name'=>'');
            if (($mdprop->getpropstemplate()->getvalmdentity()->getmdtypename()=='Cols')||($mdprop->getpropstemplate()->getvalmdentity()->getmdtypename()=='Comps'))
            {    
                return CollectionSet::getCollectionByFilter($data,$idm->getmode(),$idm->getaction());
            }
            else 
            {
                return EntitySet::getEntitiesByFilter($data,$idm->getmode(),$idm->getaction());
            }
        }    
    },   
    'Entity_Entity_SET_EDIT_CHOICE'=> function( $idm) {
        $entity = new Entity($idm->getcurid());
        $arData = array();
        $arData['id'] = $idm->getcurid();
        $arData['name'] = $entity->getname();
        return $arData;
    },
    'Entity_Entity_SET_EDIT_BEFORE_DELETE'=> function($idm){
        $entity = new Entity($idm->getcurid());
        return $entity->before_delete();
    },
    'Entity_Entity_SET_EDIT_DELETE'=> function($idm){
        $entity = new Entity($idm->getcurid());
        return $entity->delete();
    },
    'Entity_Mdproperty_SET_EDIT_LOAD'=>function($idm)
    {
        $mdprop = new Mdproperty($idm->getcurid());
        $arData = array();
        if ($mdprop->getpropstemplate()->getvalmdentity()->getmdtypename()=='Sets')
        {
            //Это запрос на табличную часть сущности
            $entity = new Entity($idm->getitemid());
            $setid = $entity->getattrid($idm->getcurid());
            if (($setid == TZ_EMPTY_ENTITY)||($setid == ''))
            {
                $prop = Mdproperty::getProperty($idm->getcurid());
                $setid = $prop['valmdid'];
            }   
            $set = new Entity($setid);
            $arData = $set->getSetData($idm->getmode(),$idm->getaction());
            $arData['ITEMID']=$idm->getcurid();
        }
        return $arData;
    },   
    'Entity_Mdproperty_SET_EDIT_CREATE'=>function($idm)
    {
        $mdprop = new Mdproperty($idm->getcurid());
        $arData = array();
        if ($mdprop->getpropstemplate()->getvalmdentity()->getmdtypename()=='Sets')
        {
            //Это создание строки табличной части сущности
            $entity = new Entity($idm->getitemid());
            $setid = $entity->getattrid($idm->getcurid());
            if (($setid==TZ_EMPTY_ENTITY)||($setid==''))
            {
                //если табличной части пока нет создадим ее
                $set = new Entity($mdprop->getpropstemplate()->getvalmdentity()->getid());
                $set->setname($mdprop->getpropstemplate()->getname());
                $set->createNew();
                $setid = $set->getid();
                $data = array();
                $data[$idm->getcurid()]=array('name'=>$set->getname(),'id'=>$set->getid());
                $objs = $entity->update($data);
            }   
            else
            {
                $set = new Entity($setid);
            }
            $set->createItem($set->getname(),$idm->getmode());
            $arData = $set->getSetData($idm->getmode(),$idm->getaction());
            $arData['ITEMID']=$idm->getcurid();
        }    
        return $arData;
    },        
   'Entity_Mdproperty_SET_VIEW_LOAD'=>function($idm){
        $mdprop = new Mdproperty($idm->getcurid());
        $arData = array();
        if ($mdprop->getpropstemplate()->getvalmdentity()->getmdtypename()=='Sets')
        {
            //Это запрос на табличную часть сущности
            $entity = new Entity($idm->getitemid());
            $setid = $entity->getattrid($idm->getcurid());
            if ($setid==TZ_EMPTY_ENTITY)
            {
                $arData = array();
                $arData['ITEMID']=$idm->getcurid();
                $arData['LDATA']=array();
                $arData['PSET']=array();
                $arData['actionlist'] = DataManager::getActionsbyItem('EntitySet',$idm->getmode(),$idm->getaction());
            }   
            else
            {
                $set = new Entity($setid);
                $arData = $set->getSetData($idm->getmode(),$idm->getaction());
                $arData['ITEMID']=$idm->getcurid();
            }    
        }    
        return $arData;
    },
    'Entity_Mdproperty_HISTORY_LOAD'=>function($idm)
    {
        $mdprop = new Mdproperty($idm->getcurid());
        return $mdprop->get_history_data($idm->getitemid(),$idm->getmode());
    },       
    'Entity_Mdproperty_VIEW_HISTORY'=>function($idm)
    {        
        $mdprop = new Mdproperty($idm->getcurid());
        return $mdprop->get_history_data($idm->getitemid(),$idm->getmode());
    },    
    'Entity_Mdproperty_EDIT_HISTORY'=>function($idm)
    {        
        $mdprop = new Mdproperty($idm->getcurid());
        return $mdprop->get_history_data($idm->getitemid(),$idm->getmode());
    },    
    'CollectionSet_VIEW_LOAD'=>function($idm){
        $coll = new CollectionSet($idm->getitemid());
        return $coll->getCollectionByFilter($idm->getdata(),$idm->getmode(),$idm->getaction());
    },
    'CollectionItem_VIEW_LOAD'=>function($idm){
        $coll = new CollectionItem($idm->getitemid());
        return $coll->getData($idm->getmode(),$idm->getaction());
    },
    'CollectionItem_EDIT_LOAD'=>function($idm){
        $coll = new CollectionItem($idm->getitemid());
        return $coll->getData($idm->getmode(),$idm->getaction());
    },
    'CollectionItem_EDIT_BEFORE_SAVE'=>function($idm){
        $coll = new CollectionItem($idm->getitemid());
        return $coll->before_save($idm->getdata());
    },
    'CollectionItem_EDIT_SAVE'=>function($idm){
        $coll = new CollectionItem($idm->getitemid());
        return $coll->update($idm->getdata());
    },
    'CollectionItem_VIEW_SAVE'=>function($idm){
        $coll = new CollectionItem($idm->getitemid());
        return $coll->update($idm->getdata());
    },        
    'MdentitySet_VIEW_LOAD'=>function($idm){
        $mds = new MdentitySet($idm->getitemid());
        return $mds->getMdentity($idm->getmode(),$idm->getaction());
    },
    'MdentitySet_EDIT_LOAD'=>function($idm){
        $mds = new MdentitySet($idm->getitemid());
        return $mds->getMdentity($idm->getmode(),$idm->getaction());
    },
    'MdentitySet_CREATE_LOAD'=>function($idm){
        $md = new Mdentity($idm->getitemid());
        return $md->getPropData($idm->getmode(),'EDIT');
    },
    'MdentitySet_CREATE_SAVE'=>function($idm){
        $mds = new MdentitySet($idm->getitemid());
        return $mds->create($idm->getdata());
    },
    'Mdproperty_VIEW_LOAD'=>function($idm){
        $mds = new Mdproperty($idm->getitemid());
        return $mds->load_data($idm->getmode(),$idm->getaction());
    },
    'Mdproperty_EDIT_LOAD'=>function($idm){
        $mds = new Mdproperty($idm->getitemid());
        return $mds->load_data($idm->getmode(),$idm->getaction());
    },
    'Mdproperty_EDIT_SAVE'=>function($idm){
        $mds = new Mdproperty($idm->getitemid());
        return $mds->update($idm->getdata(),$idm->getmode());
    },
    'Mdproperty_EDIT_BEFORE_SAVE'=>function($idm){
        $mds = new Mdproperty($idm->getitemid());
        return $mds->before_save($idm->getdata(),$idm->getmode());
    },
    'Cproperty_VIEW_LOAD'=>function($idm){
        $mds = new Cproperty($idm->getitemid());
        return $mds->loadData($idm->getmode(),$idm->getaction());
    },      
    'Cproperty_EDIT_LOAD'=>function($idm){
        $mds = new Cproperty($idm->getitemid());
        return $mds->loadData($idm->getmode(),$idm->getaction());
    },      
    'Cproperty_EDIT_BEFORE_SAVE'=>function($idm){
        $mds = new Cproperty($idm->getitemid());
        return $mds->before_save($idm->getdata());
    },      
    'Cproperty_EDIT_SAVE'=>function($idm){
        $mds = new Cproperty($idm->getitemid());
        return $mds->update($idm->getdata());
    },
    'Mdentity_EDIT_LOAD'=>function($idm){
        $md = new Mdentity($idm->getitemid());
        return $md->getPropData($idm->getmode(),$idm->getaction());
    },    
    'Mdentity_VIEW_LOAD'=>function($idm){ //список объектов
        $md = new Mdentity($idm->getitemid());
        return $md->getPropData($idm->getmode(),$idm->getaction());
    },
    'Mdentity_EDIT_BEFORE_SAVE'=>function($idm){
        $md = new Mdentity($idm->getitemid());
        return $md->before_save($idm->getdata());
    },
    'Mdentity_EDIT_SAVE'=>function($idm){ //список объектов
        $md = new Mdentity($idm->getitemid());
        return $md->update($idm->getdata());
    },
    'Mdentity_CREATE_LOAD'=>function($idm){
        $objs = array();
        $objs['PLIST']=array();
        $objs['SDATA']=array();
        $objs['actionlist']= DataManager::getActionsbyItem('Entity',$idm->getmode(),$idm->getaction());
        return $objs;
    },
    'Mdentity_CREATE_SAVE'=>function($idm){
        $md = new Mdentity($idm->getitemid());
        if (($md->getmdtypename()=='Cols')||($md->getmdtypename()=='Comps'))
        {
            $pr = new Cproperty($idm->getitemid());
            return $pr->create($idm->getdata());
        }
        else
        {
            $pr = new Mdproperty($idm->getitemid());
            return $pr->create($idm->getdata());
        }    
    }
    );
    $arData = array();
    if ($idm->getitemid()!='')
    {
        $item_obj = array('classname'=>'','id'=>'','name'=>'');
        $cur_obj = array('classname'=>'','id'=>'','name'=>'');
        $mode = $idm->getmode();
        $item_obj = DataManager::getContentByID($idm->getitemid());
        $handlername=$item_obj['classname'];
        if ($idm->getcurid()!='')
        {
            if ($idm->getcurid()!=$idm->getitemid())
            {    
                $cur_obj = DataManager::getContentByID($idm->getcurid());
                $handlername .='_'.$cur_obj['classname'];
            }    
        }   
        $action = $idm->getaction();
        $command = $idm->getcommand();
        $handlername .='_'.strtoupper($action).'_'.strtoupper($command);
        if ($mode=='CONFIG')
        {
            $handlername = str_replace('EntitySet','Mdentity',$handlername);
        }   
        if (isset($load_handler[$handlername]))
        {
            $arData = $load_handler[$handlername]($idm);
        }
        else
        {
            $arData = array('status'=>'ERROR', 'msg'=>"нет обработчика для $handlername");
        }
        $arData['handlername']=$handlername;
    }    
    echo json_encode($arData);
}

loadData();    
?>
