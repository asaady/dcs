<?php
namespace Dcs\Vendor\Core\Controllers;

use Dcs\Vendor\Core\Views\Auth_View;
use Dcs\Vendor\Core\Models\User;
use Dcs\Vendor\Core\Models\InputDataManager;

class Controller_Auth extends Controller
{

    function __construct($context)
    {
        $this->view = new Auth_View();
    }

    function action_index($context)
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
        $this->view->setcontext($context);
        $this->view->generate($data);
    }
    function action_view($context)
    {       
        $this->action_index($context);
    }
    function action_login($context)
    {  
        $data = $context['DATA'];
        setcookie("sid", "");
        if (empty($data['username']['name'])) {
            $arData = array('status'=>'ERROR', 'msg'=>"Введите имя пользователя");
        } elseif (empty($data['password']['name'])) {
            $arData = array('status'=>'ERROR', 'msg'=>"Введите пароль");
        } else {
            $remember = false;
            if (array_key_exists('remember-me', $data)) {
                $remember = (bool)$data['remember-me']['name'];
            }
            $user = new User();
            $auth_result = $user->authorize($data['username']['name'], $data['password']['name'], $remember);
            if (!$auth_result) {
                $arData = array('status'=>'ERROR', 'msg'=>"Invalid username or password");
            } else {
                $arData = array('status'=>'OK', 'redirect'=>".");
            }
        }    
        echo json_encode($arData);
    }
    function action_logout($context)
    {       
        $user = new User();
        $user->logout();
        $arData = array('status'=>'OK', 'redirect'=>".");
        echo json_encode($arData);
    }
    function action_register($context)
    {       
        $this->action_index($context);
    }
    function action_denyaccess($context)
    {
        echo json_encode(array('msg'=>'Deny access'));
    }
}

