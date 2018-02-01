<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Views\Print_View;
use Dcs\Vendor\Core\Views\Error_View;
use Dcs\Vendor\Core\Models\DcsException;
use DateTime;

class Controller_Sheet extends Controller
{

    function __construct($context)
    {
        $modelname = "\\Dcs\\Vendor\\Core\\Models\\".$context['CLASSNAME'];
        $this->model = new $modelname($context['ITEMID']);
        $this->view = new View();
    }
    function action_index($context)
    {
        $data = $this->model->get_data($context);
        $this->view->setcontext($context);
        $this->view->generate($data);
    }
    function action_view($context)
    {
        $this->action_index($context);
    }
    function action_edit($context)
    {
        $this->action_index($context);
    }
    function action_print($context)
    {
        $data = $this->model->get_data($context);
        $this->view = new Print_View();
        $this->view->setcontext($context);
        $this->view->generate($data);
    }
    function action_create($context)
    {
        $data = $this->model->create($context);
        $this->view->setcontext($context);
        $this->view->generate($data);
    }
    function action_denyaccess($context)
    {
        $this->view = new Error_View();
        $data = array();
        $data['id'] = '';
        $data['name'] = 'Ошибка доступа к данным';
        $data['synonym'] = 'Доступ запрещен';
        $data['version'] = time();
        $data['navlist']=array();
        $this->view->setcontext($context);
        $this->view->generate($data);
    }
    function action_error($context) {
        $this->view = new Error_View();
        $data = array();
        $data['id'] = '';
        $data['name'] = 'Ошибка доступа к данным';
        $data['synonym'] = 'Доступ запрещен';
        $data['version'] = time();
        $data['navlist']=array();
        $this->view->setcontext($context);
        $this->view->generate($data);
    }
}

