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
        if ($context['COMMAND'] == 'FIND') {
            $id = $context['DATA']['dcs_param_id']['id'];
            $get_model = function($modelname) use ($id) { return new $modelname($id); };
        } elseif ($context['COMMAND'] == 'FIELD_SAVE') {
            $id = $context['DATA']['dcs_curid']['id'];
            $get_model = function($modelname) use ($id) { return new $modelname($id); };
        } elseif ($context['COMMAND'] == 'LIST') {
            $id = $context['DATA']['dcs_param_val']['id'];
            $get_model = function($modelname) use ($id) { 
                $ent = new $modelname($id); 
                return $ent->get_head();
            };
        } else {
            $id = $context['ITEMID'];
            $get_model = function($modelname) use ($id) { return new $modelname($id); };
        }
        $validation = Common_data::check_uuid($id);
        if (!$validation) {
            throw new DcsException("Class ".get_called_class().
                    " constructor: id is not valid",DCS_ERROR_WRONG_PARAMETER);
        }
        $prefix = $context['PREFIX'];
        $cont = Route::getContentByID($id,$prefix);
        if ($cont) {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\".$cont['classname'];
            if ($context['COMMAND'] == 'FIND') {
                if ($cont['classname'] == 'Mdcollection') {
                    $modelname = "\\Dcs\\Vendor\\Core\\Models\\CollectionSet";
                }
            }
        } else {
            $newobj = DataManager::getNewObjectById($id);
            if (!$newobj) {
                throw new DcsException("Class ".get_called_class().
                    " constructor: id is not valid",DCS_ERROR_WRONG_PARAMETER);
            } else {
                $modelname = "\\Dcs\\Vendor\\Core\\Models\\".$newobj['classname'];
            }
        }
        $this->model = $get_model($modelname);
   }
    function action_view($context)
    {
        echo json_encode($this->model->getItemsByFilter($context));
    }
    function action_load($context)
    {
        $this->action_view($context);
    }
    function action_create($context)
    {
        echo json_encode($this->model->create($context));
    }
    function action_find($context)
    {
        echo json_encode($this->model->getItemsByName($context['DATA']['dcs_param_val']['name']));
    }
    function action_before_save($context)
    {
        echo json_encode($this->model->before_save($context,$context['DATA']));
    }
    function action_before_delete($context)
    {
        $ent = $this->model->item($context['DATA']['dcs_curid']['id']);
        echo json_encode($ent->before_delete($context,$context['DATA']));
    }
    function action_delete($context)
    {
        $ent = $this->model->item($context['DATA']['dcs_curid']['id']);
        echo json_encode($ent->delete($context,$context['DATA']));
    }
    function action_save($context)
    {
        echo json_encode($this->model->update($context,$context['DATA']));
    }
    function action_field_save($context)
    {
        $data = array();
        $propid = $context['DATA']['dcs_propid']['id'];
        $valid = $context['DATA']['dcs_param_id']['id'];
        $valname = $context['DATA']['dcs_param_val']['name'];
        $data[$propid] = array('name'=>$valname,'id'=>$valid);
        $id = $context['ITEMID'];
        $res = array();
        if ($context['ACTION'] == 'SET_EDIT') {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\Item";
            $id = $context['DATA']['dcs_curid']['id'];
        } elseif ($context['ACTION'] == 'EDIT') {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\".$context['CLASSNAME'];
        } else {
            echo json_encode(array());
            return;
        }
        $res['id'] = $id;
        $ent = new $modelname($id);
        $res = $ent->update($context,$data);
        echo json_encode($res);
        
    }
    function action_choice($context)
    {
        $id = $context['DATA']['dcs_curid']['id'];
        $type = $context['DATA']['dcs_param_type']['name'];
        if ($type == 'id') {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\Entity";
        } elseif ($type == 'cid') {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\CollectionItem";
        } elseif ($type == 'mdid') {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\Mdentity";
        } else {
            echo json_encode(array('id'=>$context['DATA']['dcs_param_val']['id'],
                'name'=>$context['DATA']['dcs_curid']['id']));
            return;
        }
        $ent = new $modelname($id);
        echo json_encode(array('id'=>$id,
                'name'=>$ent->getNameFromData($context)['synonym']));
    }
    function action_after_choice($context)
    {
        echo json_encode($this->model->after_choice($context,$context['DATA']));
    }
    function action_list($context)
    {
        $context['DATA'] = array();
        echo json_encode($this->model->getItemsByFilter($context));
    }
    function action_print($context)
    {
        echo json_encode($this->model->getItemData($context));
    }
    function action_history($context)
    {
        $mdprop = $this->model->property($context['DATA']['dcs_propid']['id'],$this->model->head());
        echo json_encode($mdprop->get_history_data($context['ITEMID'],$context));
    }
}    
