<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Models\Register;

class Controller_Register extends Controller
{
	function __construct($id)
	{
            $this->model = new Register($id);
            $this->view = new View();
	}
	
	function action_index()
	{
		$data = $this->model->get_data();
                $this->view->generate($data);
	}
	function action_view()
	{
                $this->action_index();
	}
}