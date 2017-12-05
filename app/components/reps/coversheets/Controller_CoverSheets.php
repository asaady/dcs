<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\CoverSheets;

class Controller_CoverSheets extends Controller
{

	function __construct($id)
	{
		$this->model = new CoverSheets($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
            if ($arResult['MODE']=='PRINT') 
            {
                $data = $this->model->get_data($arResult);
                $arResult['TITLE']= 'Отчет '.$this->model->getname();
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/reps/coversheets/coversheets_view.php";
                $arResult['jscript']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/reps/coversheets/coversheets.js";
		$this->view->generate($arResult, 'print_view.php', $data);
            }
            else
            {    
		$data = $this->model->get_data($arResult);
                $arResult['ACTION']=$data['mdname'];
                $arResult['TITLE']= 'Отчет '.$this->model->getname();
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/reps/coversheets/coversheets_view.php";
                $arResult['jscript']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/reps/coversheets/coversheets.js";
		$this->view->generate($arResult, 'template_view.php', $data);
            }
	}
	function action_view($arResult)
	{
            $this->action_index($arResult);
        }
}

