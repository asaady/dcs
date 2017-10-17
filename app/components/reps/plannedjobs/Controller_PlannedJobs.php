<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\PlannedJob;

class Controller_PlannedJobs extends Controller
{

	function __construct($id)
	{
		$this->model = new PlannedJob($id);
		$this->view = new View();
	}
	
	function action_index($arResult)
	{
		$data = $this->model->get_data($arResult);
                $arResult['ACTION']=$data['mdname'];
                $arResult['TITLE']= 'Отчет '.$this->model->getname();
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/reps/plannedjobs/plannedjobs_view.php";
                $arResult['jscript']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/components/reps/plannedjobs/plannedjobs.js";
		$this->view->generate($arResult, 'template_view.php', $data);
	}
}


