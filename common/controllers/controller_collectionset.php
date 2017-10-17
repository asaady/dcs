<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\CollectionSet;
use tzVendor\CollectionItem;

class Controller_CollectionSet extends Controller
{

	function __construct($id)
	{
		$this->model = new CollectionSet($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data($arResult['MODE']);
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/views/collectionset_view.php";
		$this->view->generate($arResult, "template_view.php", $data);
	}
	function action_new($arResult)
	{

                $model = new CollectionItem($arResult['ITEMID']);
		$data = $model->get_blank();
                
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/views/collection_view.php";
		$this->view->generate($arResult, "template_view.php", $data);
	}
}

