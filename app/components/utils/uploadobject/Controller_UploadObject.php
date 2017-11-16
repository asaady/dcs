<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\UploadObject;

class Controller_UploadObject extends Controller
{

	function __construct($id)
	{
            $this->model = new UploadObject($id);
            $this->view = new View();
	}
	
	function action_index($arResult)
	{
            $data = $this->model->get_data($arResult);
            $arResult['ACTION']='LOAD';
            $arResult['TITLE']= 'Обработка '.$this->model->getname();
            $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/utils/uploadobject/uploadobject_view.php";
            $arResult['jscript']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/utils/uploadobject/uploadobject.js";
            $this->view->generate($arResult, 'template_view.php', $data);
	}
	function action_view($arResult)
	{
            $this->action_index($arResult);
        }
}


