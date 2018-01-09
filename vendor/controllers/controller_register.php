<?php
use dcs\vendor\core\Controller;
use dcs\vendor\core\View;

class Controller_Register extends Controller
{
	function __construct($id)
	{
		$this->model = new Register($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data($arResult['MODE']);
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/entityset_view.php";
                $this->view->generate($arResult, 'template_view.php', $data);
	}
	function action_view($arResult)
	{
                $this->action_index($arResult);
	}
}