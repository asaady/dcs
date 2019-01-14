<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Models\DcsContext;
use Dcs\Vendor\Core\Views\Print_View;
use Dcs\Vendor\Core\Views\Error_View;
use Dcs\Vendor\Core\Models\DcsException;
use Exception;
use DateTime;

class Controller_Sheet extends Controller
{

    function __construct()
    {
        $context = DcsContext::getcontext();
        $modelname = $context->getattr('CLASSNAME');
        if (strpos($modelname,"Dcs\\Vendor\\Core\\Models\\") === false) {
            $modelname = "Dcs\\Vendor\\Core\\Models\\".$modelname;
        }
        $modelname = "\\".$modelname;
        try {
            $this->model = new $modelname($context->getattr('ITEMID'));
        } catch (DcsException $e) {
            throw new DcsException("Class ".get_called_class().
                    " constructor: id is not valid",DCS_ERROR_WRONG_PARAMETER);
        }
        $this->view = new View();
    }
    function action_index()
    {
        $context = DcsContext::getcontext();
        $data = $this->model->get_data();
        if (array_key_exists('SETS', $data) !== false) {
            if (count($data['SETS']) == 1) {
                $context->setattr('PROPID',key($data['SETS']));
            }
        }
        $this->view->generate($data);
    }
    function action_set_view()
    {
        $this->action_index();
    }
    function action_view()
    {
        $this->action_index();
    }
    function action_edit()
    {
        $this->action_index();
    }
    function action_set_edit()
    {
        $this->action_index();
    }
    function action_print()
    {
        $data = $this->model->get_data();
        $this->view = new Print_View();
        $this->view->generate($data);
    }
    function action_create()
    {
        $data = $this->model->create();
        $this->view->generate($data);
    }
    function action_denyaccess()
    {
        $this->view = new Error_View();
        $data = array();
        $data['id'] = '';
        $data['name'] = 'Ошибка доступа к данным';
        $data['synonym'] = 'Доступ запрещен';
        $data['version'] = time();
        $data['navlist']=array();
        $this->view->generate($data);
    }
    function action_error() {
        $this->view = new Error_View();
        $data = array();
        $data['id'] = '';
        $data['name'] = 'Ошибка доступа к данным';
        $data['synonym'] = 'Доступ запрещен';
        $data['version'] = time();
        $data['navlist']=array();
        $this->view->generate($data);
    }
}

