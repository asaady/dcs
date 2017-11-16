<?php
use tzVendor\Mdproperty;
use tzVendor\Cproperty;
use tzVendor\Controller;
use tzVendor\Mdentity;
use tzVendor\View;
use tzVendor\EntitySet;
use tzVendor\Entity;

class Controller_Mdentity extends Controller
{

	function __construct($mdid)
	{
            $this->model = new Mdentity($mdid);
            $this->view = new View();
	}
	
	function action_index($arResult)
	{
            $data = $this->model->getPropData($arResult['MODE'],$arResult['ACTION']);
            $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/entityset_view.php";
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
	function action_create_old($arResult)
	{
            if ($this->model->getmdtypename()=='Cols')
            {
                $entity = new tzVendor\CollectionItem($this->model->getid());
                $data = $entity->get_data($arResult['MODE']);
                $arResult['ITEMID'] = $this->model->getid();
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/item_view.php";
                $this->view->generate($arResult, "template_view.php", $data);
            }   
            else 
            {
                $entity = new Entity($this->model->getid());
                $data = $entity->get_data($arResult['MODE']);
                $arResult['ITEMID'] = $this->model->getid();
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/item_view.php";
                $this->view->generate($arResult, "template_view.php", $data);
            }
	}
        function action_create($arResult) 
        {
            if (($this->model->getmdtypename()=='Cols')||($this->model->getmdtypename()=='Comps'))
            {
                $model = new Cproperty($arResult['ITEMID']);
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/cproperty_view.php";
            }   
            else
            {
                $model = new Mdproperty($arResult['ITEMID']);
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/mdproperty_view.php";
            }    
            $data = $model->get_data();

            $this->view->generate($arResult, 'template_view.php', $data);
        }
}
