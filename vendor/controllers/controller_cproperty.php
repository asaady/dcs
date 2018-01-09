<?php
use dcs\vendor\core\Controller;
use dcs\vendor\core\Cproperty;
use dcs\vendor\core\View;


class Controller_Cproperty extends Controller
{

	function __construct($id)
	{
		$this->model = new Cproperty($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data();
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/cproperty_view.php";
		$this->view->generate($arResult, 'template_view.php', $data);
	}
	function action_edit($arResult)
	{
		$this->action_index($arResult);
	}
	function action_view($arResult)
	{
		$this->action_index($arResult);
	}
}
