<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\Error_View;

class Controller_Error extends Controller
{
    function __construct()
    {
        $this->view = new Error_View();
    }
    function action_index($data='')
    {
        $data = array();
        $data['id'] = '';
        $data['name'] = 'Страница не найдена';
        $data['synonym'] = 'Страница устарела, была удалена или не существовала вовсе';
        $data['version'] = time();
        $data['navlist']=array();
        $this->view->generate($data);
    }
    function action_view($data='')
    {
        $this->action_index($data);
    }
    function action_json($data='')
    {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');        
        echo json_encode($data);
    }
    function action_error($data='')
    {
        $this->action_index($data);
    }
    function action_denyaccess($data='')
    {
        $data = array();
        $data['id'] = '';
        $data['name'] = 'Ошибка доступа к данным';
        $data['synonym'] = 'Доступ запрещен';
        $data['version'] = time();
        $data['navlist']=array();
        $this->view->generate($data);
    }
}
