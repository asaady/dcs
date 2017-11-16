<?php
use tzVendor\Controller;
use tzVendor\View;
use tzVendor\AuthorizationAjaxRequest;

class Controller_Auth extends Controller
{

    	function __construct()
	{
		$this->view = new View();
	}

	function action_index($arResult)
	{       
                $data = array();
                if (tzVendor\User::isAuthorized())
                {    
                    $data['id']=$_SESSION['user_id'];
                }
                else
                {
                    $data['id']='';
                }    
                $data['version']=time();
                $data['actionlist']=array();
                $data['navlist']=array();
                $data['plist']=array();
                $data['ardata']=array();
                $arResult['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/auth_view.php";
		$this->view->generate($arResult, "template_view.php", $data);
	}
	function action_register($arResult)
	{       
            $this->action_index($arResult);
	}
}

