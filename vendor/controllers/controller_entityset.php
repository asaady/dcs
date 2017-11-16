<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\EntitySet;
use tzVendor\Entity;

class Controller_EntitySet extends Controller
{

	function __construct($id)
	{
		$this->model = new EntitySet($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data($arResult['MODE']);
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/entityset_view.php";
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
            if (($this->model->getmditem()->getname()=='Cols')||($this->model->getmditem()->getname()=='Comps'))    
            {
                $entity = new tzVendor\CollectionItem($this->model->getid());
		$data = $entity->get_data($arResult['MODE']);
                $arResult['ITEMID'] = $this->model->getid();
            }   
            else
            {    
                $entity = new Entity($this->model->getid());
		$data = $entity->get_data($arResult['MODE']);
                $arResult['ITEMID'] = $this->model->getid();
            }    
            $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/item_view.php";
            $this->view->generate($arResult, "template_view.php", $data);
	}
}

