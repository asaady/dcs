<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\CollectionItem;

class Controller_CollectionItem extends Controller
{

	function __construct($id)
	{
		$this->model = new CollectionItem($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data($arResult['MODE']);
                
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/item_view.php";
		$this->view->generate($arResult, 'template_view.php', $data);
	}
	function action_view($arResult)
	{
                $this->action_index($arResult);
	}
	function action_edit($arResult)
	{
                $this->action_index($arResult);
	}
	function action_del($arResult)
	{
		$data = $this->model->get_data($arResult['MODE']);
                
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/collection_del.php";
		$this->view->generate($arResult, 'template_view.php', $data);
	}
}
