<?php
namespace Dcs\App\Components\Utils\Uploadobject;

use Dcs\Vendor\Core\Controllers\Controller;
use Dcs\Vendor\Core\Models\DcsContext;
use Dcs\Vendor\Core\Models\DcsException;
use Dcs\Vendor\Core\Models\DataManager;

class Controller_UploadObject extends Controller
{

    function __construct()
    {
        if (!\Dcs\Vendor\Core\Models\User::isAuthorized()) {
            throw new DcsException('anonymous user: Uploadobject closed');
        }    
        $this->model = new UploadObject();
        $this->view = new Uploadobject_view();
    }

    function action_index()
    {
        $data = $this->model->get_data();
        $this->view->generate($data);
    }
    function action_exec()
    {
        $context = DcsContext::getcontext();
        if ($context->getattr('MODE') == 'AJAX') {
            echo json_encode($this->model->exec()); 
            return;
        }
        $this->action_index();
    }
    function action_view()
    {
        $this->action_index();
    }
    function action_find()
    {
        $context = DcsContext::getcontext();
        echo json_encode($this->model->getItemsByName($context->data_getattr('dcs_param_val')['name']));
    }  
    function action_import()
    {
        $context = DcsContext::getcontext();
        $objs=false;
        $target_mdid = $context->data_getattr('target_mdid')['id'];
        $objs = $this->model->import($target_mdid);
        echo json_encode($objs); 
    }
    function action_load()
    {
        $context = DcsContext::getcontext();
    	$objs = array();
        $objs['PLIST'] = $this->model->getplist();
        $objs['LDATA'] = array();
        $objs['LDATA'][$this->model->getid()] = $this->model->load_data();
        $objs['PSET'] = array();
        $objs['SDATA'] = array();
        $prefix = $context->getattr('PREFIX');
        $action = $context->getattr('ACTION');
        $objs['actionlist'] = DataManager::getActionsbyItem('Utils',$prefix,$action);
        $objs['navlist'] = $this->model->get_navlist();
	echo json_encode($objs);
    }
}


