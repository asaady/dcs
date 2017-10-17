<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\ItemActions;

class Controller_ItemActions extends Controller
{

	function __construct($id)
	{
		$this->model = new ItemActions($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data();
                $arResult['jscript']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/js/entity_view.js";
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/config/application/views/itemactions_view.php";
		$this->view->generate($arResult, 'template_view.php', $data);
	}
}
