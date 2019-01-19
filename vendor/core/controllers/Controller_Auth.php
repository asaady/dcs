<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\Auth_View;
use Dcs\Vendor\Core\Models\User;
use Dcs\Vendor\Core\Models\InputDataManager;
use Dcs\Vendor\Core\Models\DcsContext;

class Controller_Auth extends Controller
{

    function __construct()
    {
        $this->view = new Auth_View();
    }

    function action_index()
    {       
        $data = array();
        if (User::isAuthorized())
        {    
            $data['curid']=$_SESSION['user_id'];
        }
        else
        {
            $data['curid']='';
        }    
        $data['version']=time();
        $data['navlist']=array();
        $this->view->generate($data);
    }
    function action_view()
    {       
        $this->action_index();
    }
    function action_login()
    {  
        $context = DcsContext::getcontext();
        $username = $context->data_getattr('username');
        $password = $context->data_getattr('password');
        $rememberme = $context->data_getattr('remember-me');
        setcookie("sid", "");
        if (empty($username['name'])) {
            $arData = array('status'=>'ERROR', 'msg'=>"Введите имя пользователя");
        } elseif (empty($password['name'])) {
            $arData = array('status'=>'ERROR', 'msg'=>"Введите пароль");
        } else {
            $remember = (bool)$rememberme['name'];
            $user = new User();
            $auth_result = $user->authorize($username['name'], $password['name'], $remember);
            if (!$auth_result) {
                $arData = array('status'=>'ERROR', 'msg'=>"Invalid username or password");
            } else {
                $arData = array('status'=>'OK', 'redirect'=>".");
            }
        }    
        echo json_encode($arData);
    }
    function action_logout()
    {       
        $user = new User();
        $user->logout();
        $arData = array('status'=>'OK', 'redirect'=>".");
        echo json_encode($arData);
    }
    function action_register()
    {       
        $this->action_index();
    }
    function action_denyaccess()
    {
        echo json_encode(array('msg'=>'Deny access'));
    }
}

