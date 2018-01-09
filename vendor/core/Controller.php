<?php
namespace Dcs\Vendor\Core;

class Controller {
	
	public $model;
	public $view;
	
	function __construct($id='')
	{
		$this->model = new Model($id);
		$this->view = new View();
	}
	
	// действие (action), вызываемое по умолчанию
	function action_index($arResult)
	{
		$data = $this->model->get_data();		
		$this->view->generate($arResult, 'template_view.php', $data);
	}
}
