<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;

class Controller_Head extends Controller
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
	function action_create($context)
	{
            $data = $this->model->create($context);
            $this->view->setcontext($context);
            $this->view->generate($data);
	}
}

