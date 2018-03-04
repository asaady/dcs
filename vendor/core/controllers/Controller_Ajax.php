<?php
namespace Dcs\Vendor\Core\Controllers;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use Dcs\Vendor\Core\Models\DataManager;
use Dcs\Vendor\Core\Models\EntitySet;
use Dcs\Vendor\Core\Models\CollectionSet;
use Dcs\Vendor\Core\Models\Entity;
use Dcs\Vendor\Core\Models\Mdentity;
use Dcs\Vendor\Core\Models\CollectionItem;
use Dcs\Vendor\Core\Models\Mdproperty;
use Dcs\Vendor\Core\Models\MdentitySet;
use Dcs\Vendor\Core\Models\DcsException;
use Dcs\Vendor\Core\Models\Route;
use Dcs\Vendor\Core\Models\Common_data;

class Controller_Ajax extends Controller
{
    function __construct($context)
    {
        $id = $context['ITEMID'];
        if ($context['COMMAND'] == 'FIND') {
            $id = $context['DATA']['id']['id'];
        } elseif ($context['COMMAND'] == 'FIELD_SAVE') {
            $id = $context['DATA']['curid']['id'];
        }
        $validation = Common_data::check_uuid($id);
        if (!$validation) {
            throw new DcsException("Class ".get_called_class().
                    " constructor: id is not valid",DCS_ERROR_WRONG_PARAMETER);
        }
        $prefix = $context['PREFIX'];
        $cont = \Dcs\Vendor\Core\Models\Route::getContentByID($id,$prefix);
        $modelname = "\\Dcs\\Vendor\\Core\\Models\\".$cont['classname'];
        $this->model = new $modelname($id);
    }
    function action_view($context)
    {
        echo json_encode($this->model->getItemsByFilter($context));
    }
    function action_load($context)
    {
        $this->action_view($context);
    }
    function action_find($context)
    {
        echo json_encode($this->model->getItemsByName($context['DATA']['name']['name']));
    }
    function action_field_save($context)
    {
           $getdata = $idm->getdata();
            $data=array();
            $data[$getdata['propid']['id']]=array('name'=>$getdata['name']['name'],'id'=>$getdata['id']['id']);
            $ent = new Entity($idm->getitemid());
            $ent->update($data);
       
    }
    function action_list($context)
    {
        $data = $idm->getdata();
        $propid = $data['filter_id']['id'];
        if ($propid)
        {    
            $curid = $idm->getcurid();
            $arMD = Entity::getEntityDetails($curid);
            $mdprop = new Mdproperty($propid);
            $valmdentity = $mdprop->getpropstemplate()->getvalmdentity();
            $data = array();
            $data['curid'] = array('id'=>$curid,'name'=>'');
            $data['itemid'] = array('id'=>$valmdentity->getid(),'name'=>'');
            $data['docid'] = array('id'=>$idm->getitemid(),'name'=>'');
            $data['filter_id']= array('id'=>'','name'=>'');
            $data['filter_val']= array('id'=>'','name'=>'');
            $data['filter_min']= array('id'=>'','name'=>'');
            $data['filter_max']= array('id'=>'','name'=>'');
            $event_trig = DataManager::get_event_trigger('onSelect',$arMD['mdid'] , $mdprop->getpropstemplate()->getid());
            if ($event_trig)
            {
                die(var_dump($event_trig));
            }   
            else
            {    
                if (($valmdentity->getmdtypename()=='Cols')||($valmdentity->getmdtypename()=='Comps'))
                {    
                    return CollectionSet::getCollectionByFilter($data,$idm->getmode(),$idm->getaction());
                }
            }    
        }    
        return EntitySet::getEntitiesByFilter($data,$idm->getmode(),$idm->getaction());
        echo json_encode($this->model->getItemsByName($context['DATA']['name']['name']));
    }
    function action_print($context)
    {
        echo json_encode($this->model->getItemData($context));
    }
    function action_denyaccess($context)
    {
        echo json_encode(array('msg'=>'Deny access'));
    }
    function action_history($context)
    {
        $mdprop = new Mdproperty($context['DATA']['PROPID']['id']);
        echo json_encode($mdprop->get_history_data($context['ITEMID'],$context['MODE']));
    }
    function action_error($context)
    {
        echo json_encode(array('msg'=>'error'));
    }
}    
