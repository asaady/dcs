<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Models\Model;
use Dcs\Vendor\Core\Views\View;

abstract class Controller implements I_Controller {
	
	public $model;
	public $view;
	
	// действие (action), вызываемое по умолчанию
	function action_index($context)
	{
		$data = $this->model->get_data();		
		$this->view->generate($context, $data);
	}
}
