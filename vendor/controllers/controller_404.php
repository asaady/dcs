<?php
use tzVendor\Controller;

class Controller_404 extends Controller
{
	
	function action_index($arResult)
	{
                $data = array();
                $data['id'] = '';
                $data['version'] = time();
                $data['navlist']=array();
                $data['actionlist']=array();
                $data['plist']=array();
                $arResult['content'] = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/404_view.php"; 
		$this->view->generate($arResult, 'template_view.php',$data);
	}
}
