<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\MdentitySet;
use tzVendor\Mdentity;
use tzVendor\CollectionSet;

class Controller_MdentitySet extends Controller
{

	function __construct($mditem)
	{
		$this->model = new MdentitySet($mditem);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data($arResult['MODE']);
                
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/views/entityset_view.php";
		$this->view->generate($arResult, "template_view.php", $data);
	}
	function action_view($arResult)
	{
            $this->action_index($arResult);
        }
	function action_edit($arResult)
	{
            $this->action_index($arResult);
        }
	function action_create($arResult)
	{
            $mdentity = new Mdentity($this->model->getid());
            $data = $mdentity->get_data($arResult['MODE']);
            $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/views/mdentity_view.php";
            $this->view->generate($arResult, 'template_view.php', $data);
	}
}

