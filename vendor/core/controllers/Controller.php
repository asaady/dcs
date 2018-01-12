<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Models\Model;
use Dcs\Vendor\Core\Views\View;

abstract class Controller {
	
	public $model;
	public $view;
	
	function __construct($id='')
	{
//		$this->model = new Model($id);
//		$this->view = new View();
	}
	
	// действие (action), вызываемое по умолчанию
	function action_index($context)
	{
		$data = $this->model->get_data();		
		$this->view->generate($context, $data);
	}
}
