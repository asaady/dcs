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

class Controller_Ajax extends Controller
{
    function __construct($context)
    {
        $modelname = "\\Dcs\\Vendor\\Core\\Models\\".$context['CLASSNAME'];
        $this->model = new $modelname($context['ITEMID']);
    }
    function action_view($context)
    {
        $idm = new \Dcs\Vendor\Core\Models\InputDataManager();
        echo json_encode($this->model->getItemsByFilter($context, $idm->getdata()));
    }
    function action_edit($context)
    {
        $idm = new \Dcs\Vendor\Core\Models\InputDataManager();
        echo json_encode($this->model->getItemsByFilter($context, $idm->getdata()));
    }
    function action_set_view($context)
    {
        $cur_item = \Dcs\Vendor\Core\Models\Route::getContentByID($context['CURID']);
        $arData = array('status'=>'ERROR', 'msg'=>"нет обработчика для ".$cur_item['classname']);
        if ($cur_item['classname'] == 'EProperty') {
            $setid = $this->model->getattrid($context['CURID']);
            $set = new Entity($setid);
            $set->set_head($this->model); 
            $arData = $set->getSetData($context);
        }
        echo json_encode($arData);
    }
    function action_history($context)
    {
        $mdprop = new Mdproperty($context['CURID']);
        echo json_encode($mdprop->get_history_data($context['ITEMID'],$context['MODE']));
    }
}    
