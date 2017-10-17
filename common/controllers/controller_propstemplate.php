<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\PropsTemplate;

class Controller_PropsTemplate extends Controller
{

	function __construct($item)
	{
		$this->model = new PropsTemplate($item);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data();
                $arResult['jscript']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/js/propstemplate_view.js";
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/config/application/views/propstemplate_view.php";
		$this->view->generate($arResult, 'template_view.php', $data);
	}
}
