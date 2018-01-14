<?php
namespace Dcs\Vendor\Core\Views;

use Dcs\Vendor\Core\Models\User;

class Auth_View extends View implements I_View
{
    use T_View;
    
    public function item_view($data)
    {
        if (User::isAuthorized()) {
            echo "<h1>Your are welcome!</h1>";
            echo "<input type=\"hidden\" name=\"act\" value=\"logout\">";
            echo "<button id=\"submit\" type=\"button\" class=\"btn btn-info form-control-sm\">Выход</button>";
        } else {
            echo "<div class=\"main-error alert alert-error hide\"></div>";
            echo "<h2 class=\"form-signin-heading\">Пожалуйста, авторизуйтесь</h2>";
            echo "<input class =\"form-control-sm\" name=\"username\" type=\"text\" class=\"input-block-level\" placeholder=\"Логин\" autofocus>";
            echo "<input class =\"form-control-sm\" name=\"password\" type=\"password\" class=\"input-block-level\" placeholder=\"Пароль\">";
            echo "<input class =\"form-checkbox\" name=\"remember-me\" type=\"checkbox\" value=\"remember-me\" id=\"remember\" checked>";
            echo "<label class =\"label-control\" for = \"remember\">Запомнить меня</label>";
            echo "<input type=\"hidden\" name=\"act\" value=\"login\">";
            echo "<button id=\"submit\" type=\"button\" class=\"btn btn-info form-control-sm\">Войти</button>";
        }
    }        
}