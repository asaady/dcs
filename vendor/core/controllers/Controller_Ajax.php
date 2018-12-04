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
        $get_model = function($modelname) use ($id) { return new $modelname($id); };
        if ($context['COMMAND'] == 'FIND') {
            $id = $context['DATA']['param_id']['id'];
        } elseif ($context['COMMAND'] == 'FIELD_SAVE') {
            $id = $context['DATA']['curid']['id'];
        } elseif ($context['COMMAND'] == 'LIST') {
            $id = $context['DATA']['param_val']['id'];
            $get_model = function($modelname) use ($id) { 
                $ent = new $modelname($id); 
                return $ent->head();
            };
        }
        $validation = Common_data::check_uuid($id);
        if (!$validation) {
            throw new DcsException("Class ".get_called_class().
                    " constructor: id is not valid",DCS_ERROR_WRONG_PARAMETER);
        }
        $prefix = $context['PREFIX'];
        $cont = Route::getContentByID($id,$prefix);
        $modelname = "\\Dcs\\Vendor\\Core\\Models\\".$cont['classname'];
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
    function action_find($context)
    {
        echo json_encode($this->model->getItemsByName($context['DATA']['param_val']['name']));
    }
    function action_before_save($context)
    {
        echo json_encode($this->model->before_save($context,$context['DATA']));
    }
    function action_save($context)
    {
        echo json_encode($this->model->update($context,$context['DATA']));
    }
    function action_field_save($context)
    {
        $data=array();
        $data[$context['DATA']['propid']['id']]=array('name'=>$context['DATA']['param_val']['name'],'id'=>$context['DATA']['param_id']['id']);
        $ent = new Entity($idm->getitemid());
        $ent->update($data);
    }
    function action_choice($context)
    {
        echo json_encode(array('msg'=>'OK'));
    }
    function action_list($context)
    {
        $context['DATA'] = array();
        echo json_encode($this->model->getItemData($context));
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
