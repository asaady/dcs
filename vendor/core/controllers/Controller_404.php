<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\C404_View;

class Controller_404 extends Controller
{
	function __construct()
	{
            $this->view = new C404_View();
	}
	function action_index($context)
	{
            $data = array();
            $data['id'] = '';
            $data['version'] = time();
            $data['navlist']=array();
            $this->view->setcontext($context);
            $this->view->generate($data);
	}
	function action_view($context)
	{
            $this->action_index($context);
        }
}
