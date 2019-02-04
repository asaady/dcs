<?php
namespace Dcs\Vendor\Core\Controllers;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

use Dcs\Vendor\Core\Models\DcsContext;
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
    function __construct()
    {
        $context = DcsContext::getcontext();
        $id = $context->getattr('ITEMID');
        if ($context->getattr('COMMAND') == 'FIND') {
            $id = $context->data_getattr('dcs_param_id')['id'];
        } elseif ($context->getattr('COMMAND') == 'CHOICE') {
            $id = $context->data_getattr('dcs_curid')['id'];
        } elseif ($context->getattr('COMMAND') == 'LIST') {
            $id = $context->data_getattr('dcs_param_propid')['id'];
        } elseif ($context->getattr('COMMAND') == 'FIELD_SAVE') {
            $id = $context->data_getattr('dcs_curid')['id'];
        }
        
        $validation = Common_data::check_uuid($id);
        if (!$validation) {
            throw new DcsException("Class ".get_called_class().
                    " constructor: id is not valid",DCS_ERROR_WRONG_PARAMETER);
        }
        $prefix = $context->getattr('PREFIX');
        $cont = Route::getContentByID($id,$prefix);
        if ($cont) {
            $modelname = "\\Dcs\\Vendor\\Core\\Models\\".$cont['classname'];
            if ($context->getattr('ACTION') == 'EXEC') {
                $ent = new CollectionItem($id);
                $modelname = "\\Dcs\\App\\Components\\Utils\\"
                        .$ent->getname().'\\'.$ent->getname();
            } else {    
                if ($context->getattr('COMMAND') == 'FIND') {
                    if ($cont['classname'] == 'Mdcollection') {
                        $modelname = "\\Dcs\\Vendor\\Core\\Models\\CollectionSet";
                    }
                } elseif (($context->getattr('COMMAND') == 'CREATE')&&
                      ($context->data_getattr('dcs_setid')['id'])) {
                    $modelname = "\\Dcs\\Vendor\\Core\\Models\\Sets";
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
        $this->model = new $modelname($id);
    }
    function action_view()
    {
        echo json_encode($this->model->getItemsByFilter());
    }
    function action_load()
    {
        $this->action_view();
    }
    function action_create()
    {
        echo json_encode($this->model->create());
    }
    function action_find()
    {
        $context = DcsContext::getcontext();
        echo json_encode($this->model->getItemsByName($context->data_getattr('dcs_param_val')['name']));
    }
    function action_before_save()
    {
        echo json_encode($this->model->before_save());
    }
    function action_before_delete()
    {
        $context = DcsContext::getcontext();
        $ent = $this->model->item($context->data_getattr('dcs_curid')['id']);
        echo json_encode($ent->before_delete());
    }
    function action_delete()
    {
        $context = DcsContext::getcontext();
        $ent = $this->model->item($context->data_getattr('dcs_curid')['id']);
        echo json_encode($ent->delete());
    }
    function action_save()
    {
        $context = DcsContext::getcontext();
        echo json_encode($this->model->update($context->getattr('DATA')));
    }
    function action_field_save()
    {
        $data = array();
        $context = DcsContext::getcontext();
        $propid = $context->data_getattr('dcs_param_propid')['id'];
        $valid = $context->data_getattr('dcs_param_id')['id'];
        $valname = $context->data_getattr('dcs_param_val')['name'];
        $data[$propid] = array('name'=>$valname,'id'=>$valid);
        $res = $this->model->update($data);
        echo json_encode($res);
        
    }
    function action_choice()
    {
        echo json_encode(array('id'=>$this->model->getid(),
                'name'=>$this->model->getNameFromData()['synonym']));
    }
    function action_after_choice()
    {
        echo json_encode($this->model->after_choice());
    }
    function action_list()
    {
        //$filter = new Filter($propid,
        //                         $context->data_getattr('dcs_param_val')['name']);
        $this->model->load_data();
        $valmdid = $this->model->getattrid('valmdid');
        $entset = new EntitySet($valmdid);
        echo json_encode($entset->getListItemsByFilter());
    }
    function action_print()
    {
        echo json_encode($this->model->getItemData());
    }
    function action_history()
    {
        $context = DcsContext::getcontext();
        $mdprop = $this->model->property($context->data_getattr('dcs_propid')['id'],$this->model->head());
        echo json_encode($mdprop->get_history_data($context->getattr('ITEMID')));
    }
    function action_exec() {
        echo json_encode($this->model->exec());
//        if ($context->getattr('CLASSNAME') == 'CollectionItem') {
//            if ($context->getattr('CLASSTYPE') == 'Utils') {
//                $ent = new CollectionItem($context->getattr('ITEMID'));
//                $this->controller_path = "/app/utils/".strtolower($ent->getname());
//                return 'Controller_'.$ent->getname();
//            }
//        }
    }
}    
