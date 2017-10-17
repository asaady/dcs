<?php
use tzVendor\Entity;
use tzVendor\Mdproperty;
use tzVendor\View;
use tzVendor\Controller;

class Controller_Entity extends Controller
{

	function __construct($id)
	{
		$this->model = new Entity($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data($arResult['MODE']);
                if ($this->model->getmdentity()->getmdtypename()=='Vals')
                {
                    $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/main.html";
                }
                else 
                {
                    $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/views/item_view.php";
                }    
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
}
