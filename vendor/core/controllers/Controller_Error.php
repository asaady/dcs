<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\Error_View;

class Controller_Error extends Controller
{
    function __construct($context)
    {
        $this->view = new Error_View();
    }
    function action_index($context)
    {
        $data = array();
        $data['id'] = '';
        $data['name'] = 'Страница не найдена';
        $data['synonym'] = 'Страница устарела, была удалена или не существовала вовсе';
        $data['version'] = time();
        $data['navlist']=array();
        $this->view->setcontext($context);
        $this->view->generate($data);
    }
    function action_view($context)
    {
        $this->action_index($context);
    }
    function action_json($context)
    {
        echo json_encode(array('msg' => 'error'));
    }
    function action_error($context)
    {
        $this->action_index($context);
    }
    function action_denyaccess($context)
    {
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
