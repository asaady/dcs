<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\PrnCoverSheets;

class Controller_PrnCoverSheets extends Controller
{

	function __construct($id)
	{
		$this->model = new PrnCoverSheets($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
            $arResult['TITLE'] = $this->model->getname();
            $arResult['ITEMID'] = $this->model->getid();
            $data = $this->model->get_data($arResult['MODE']);
            $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/prnforms/coversheets/prncoversheets_view.php";
            $arResult['jscript']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/prnforms/coversheets/prncoversheets.js";
            $this->view->generate($arResult, 'print_view.php', $data);
	}
}


