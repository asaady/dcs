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
                $context['content']=filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING).$this->view->get_views_path()."/auth_view.php";
		$this->view->generate($context, $data);
	}
	function action_register($context)
	{       
            $this->action_index($context);
	}
}

