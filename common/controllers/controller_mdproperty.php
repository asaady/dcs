<?php
use tzVendor\Controller;
use tzVendor\Mdproperty;
use tzVendor\View;


class Controller_Mdproperty extends Controller
{

	function __construct($id)
	{
		$this->model = new Mdproperty($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data();
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/views/item_view.php";
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
