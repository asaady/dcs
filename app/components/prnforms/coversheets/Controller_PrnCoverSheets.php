<?php
use Dcs\Vendor\Core\Controllers\Controller;
use Dcs\Vendor\Core\Views\View;
use Dcs\App\Components\Prnforms\Coversheets\PrnCoverSheets;

class Controller_PrnCoverSheets extends Controller
{

	function __construct($id)
	{
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


