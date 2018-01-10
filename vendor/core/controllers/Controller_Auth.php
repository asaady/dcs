<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\View;
use Dcs\Vendor\Core\Models\User;

class Controller_Auth extends Controller
{

    	function __construct()
	{
		$this->view = new View();
	}

	function action_index($context)
	{       
                $data = array();
                if (User::isAuthorized())
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
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/views/auth_view.php";
		$this->view->generate($context, "template_view.php", $data);
	}
	function action_register($context)
	{       
            $this->action_index($context);
	}
}

