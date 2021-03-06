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
            if ($arResult['MODE'] == 'PRINT') 
            {
                $arResult['content'] = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/item_print.php";
                $arResult['jscript'] = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/js/core_app.js";
		$this->view->generate($arResult, 'print_view.php', $data);
            }
            else
            {    
                if ($this->model->getmdentity()->getmdtypename()=='Vals')
                {
                    $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/public/main.html";
                }
                else 
                {
                    $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/item_view.php";
                }    
		$this->view->generate($arResult, 'template_view.php', $data);
            }    
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
