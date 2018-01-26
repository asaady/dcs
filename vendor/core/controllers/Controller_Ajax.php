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
        echo json_encode($this->model->getItemsByFilter($context));
    }
    function action_edit($context)
    {
        $this->action_view($context);
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
        $mdprop = new Mdproperty($context['CURID']);
        echo json_encode($mdprop->get_history_data($context['ITEMID'],$context['MODE']));
    }
}    
