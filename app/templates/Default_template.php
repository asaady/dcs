<?php
namespace Dcs\App\Templates;

use Dcs\Vendor\Core\Views\Template;
use Dcs\Vendor\Core\Views\I_Template;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING) . "/app/dcs_const.php");

class Default_Template extends Template implements I_Template
{
    public function get_head($context)
    {
        return "<meta charset=\"utf-8\">\n"
        . "<meta name=\"author\" content=\"".DCS_COMPANY_NAME."\">\n"
        . "<meta name=\"description\" content=\"".DCS_COMPANY_NAME."\">\n"
        . "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=Edge\">\n"
        . "<meta name=\"viewport\" content=\"width=device-width,"
                . " initial-scale=1.0, maximum-scale=1.0, user-scalable=no\">\n"
        . "<title>".$context['TITLE']."</title>\n"
        . "<!-- css stylesheets -->\n"
        . "<link href=\"/public/css/normalize.css\" rel=\"stylesheet\" type=\"text/css\">\n"
        . "<link href=\"/public/css/bootstrap.min.css\" rel=\"stylesheet\" type=\"text/css\">"
        . "<link href=\"/public/css/bootstrap-select.min.css\" rel=\"stylesheet\" type=\"text/css\">\n"
        . "<link href=\"/public/css/default.css\" id=\"theme_base\" rel=\"stylesheet\">\n"
        . "<link href=\"/public/css/default.date.css\" id=\"theme_date\" rel=\"stylesheet\">\n"
        . "<link href=\"/public/css/default.time.css\" id=\"theme_time\" rel=\"stylesheet\">\n"
        . "<link href=\"/public/css/style.css\" rel=\"stylesheet\" type=\"text/css\">\n"
        . "<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->\n"
        . "<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->\n"
        . "<!--[if lt IE 9]>\n"
        . "<script src=\"https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js\"></script>\n"
        . "<script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>\n"
        . "<![endif]-->\n";
    }        
    public function get_body_header($context, $data)
    {
        $prefix = '';
        if ($context['PREFIX'] !== '') {
            $prefix = "/".$context['PREFIX'];
        }
        $result = "<div class=\"navbar navbar-default\" role=\"navigation\">\n"
                  . "<div class=\"container\">\n";
        if (($context['PREFIX'] === 'CONFIG')||
            (($context['ACTION'] !== 'EDIT')&&
             ($context['ACTION'] !== 'SET_EDIT'))) {
            $result .="<div class=\"navbar-header col-xs-12 col-sm-12 col-md-12\">\n"
                    . "<button type=\"button\" class=\"navbar-toggle\""
                    . " data-toggle=\"collapse\" data-target=\"#b-menu-1\">\n"
                    . "<span class=\"sr-only\">Toggle navigation</span>\n"
                    . "<span class=\"icon-bar\"></span>\n"
                    . "<span class=\"icon-bar\"></span>\n"
                    . "<span class=\"icon-bar\"></span>\n"
                    . "</button>\n"
                    . "<a class=\"navbar-brand\" href=\"/\">".DCS_COMPANY_NAME."</a>\n"
                    . "<div class=\"nav collapse navbar-collapse\" id=\"b-menu-1\">\n"
                    . "<ul class=\"nav navbar-nav pull-right\">\n";
            if (\Dcs\Vendor\Core\Models\User::isAuthorized()) {    
                foreach($context['MENU'] as $ct) {  
                    $result .= "<li><a href=\"$prefix/$ct[ID]\">$ct[SYNONYM]</a></li>\n";
                }
                if (\Dcs\Vendor\Core\Models\User::isAdmin()&&($context['PREFIX'] !== 'CONFIG')) {    
                    $result .= "<li>\n"
                             . "<a href=\"/config\">\n"
                             . "<i class=\"material-icons\">settings</i>\n"
                             . "</a>\n"
                             . "</li>\n";
                }
                $result .= "<li class=\"dropdown\">\n"
                         . "<a href=# class=\"dropdown-toggle\" data-toggle=\"dropdown\">\n"
                         . "<i class=\"material-icons\">account_box</i>\n"
                         . "<b class=\"caret\"></b>\n"
                         . "</a>\n"
                         . "<ul class=\"dropdown-menu\">\n"
                         . "<li><a href=/6accfac4-dc22-4d12-985b-946d3a61bbd1>Настройки</a></li>\n"
                         . "<li><a href=javascript:logout()>Выход</a></li>\n"
                         . "</ul>\n"
                         . "</li>\n";
            }        
            $result .= "</ul>\n"
                     . "</div> <!-- /.nav-collapse -->\n"
                     . "</div>\n";
        }            
        $result .= "<div class=\"navbar-inner\">\n"
                 . "<div class=\"col-xs-12 col-sm-12 col-md-12\">\n"
                 . "<ol class=\"breadcrumb\"><li></li></ol></div></div>\n"
                 . "</div> <!-- /.container -->\n"
                 . "</div> <!-- /.navbar -->\n";
        return $result;
    }    
    public function get_body_action_list()
    {
        return "<nav id=\"dcs-nav\" class=\"navbar\" data-spy=\"affix\" data-offset-top=\"150\">\n"
             . "<div class=\"container\">\n"
             . "<ul class=\"nav nav-tabs pull-right\" id=\"actionlist\"><li></li></ul>\n"
             . "</div>\n"
             . "</nav>\n";
    }        
    public function get_body_content($context, $data)
    {
        $result = "<div class=\"container\">\n"
                . "<div class=\"row-fluid\">\n"
                . "<div class=\"dcs-context\">\n"
                . "<input class=\"form-control\" name=\"prefix\" type=\"hidden\""
                . " value=\"".$context['PREFIX']."\">\n"
                . "<input class=\"form-control\" name=\"mode\" type=\"hidden\""
                . " value=\"".$context['MODE']."\">\n"
                . "<input class=\"form-control\" name=\"itemid\" type=\"hidden\""
                . " value=\"".$context['ITEMID']."\">\n"
                . "<input class=\"form-control ajax\" name=\"setid\" type=\"hidden\""
                . " value=\"".$context['SETID']."\">\n"
                . "<input class=\"form-control ajax\" name=\"curid\" type=\"hidden\""
                . " value=\"".$context['CURID']."\">\n"
                . "<input class=\"form-control ajax\" name=\"action\" type=\"hidden\""
                . " value=\"".$context['ACTION']."\">\n"
                . "<input class=\"form-control ajax\" name=\"version\" type=\"hidden\""
                . " value=\"".$data['version']."\">\n"
                . "<input class=\"form-control ajax\" name=\"page\" type=\"hidden\""
                . " value=\"".$context['PAGE']."\">\n"
                . "<input class=\"form-control ajax\" name=\"command\" type=\"hidden\""
                . " value=\"".$context['COMMAND']."\">\n"
                . "<input class=\"form-control ajax\" name=\"param_id\" type=\"hidden\""
                . " value=\"\">\n"
                . "<input class=\"form-control ajax\" name=\"param_val\" type=\"hidden\""
                . " value=\"\">\n"
                . "<input class=\"form-control ajax\" name=\"param_type\" type=\"hidden\""
                . " value=\"\">\n";
        $docid = '';
        if (array_key_exists('docid', $context['DATA']) !== FALSE) {
            $docid = $context['DATA']['docid']['id'];
        }   
        $result .= "<input class=\"form-control ajax\" name=\"docid\""
                . " type=\"hidden\" value=\"$docid\">\n";
        $propid = '';
        if (array_key_exists('propid', $context['DATA']) !== FALSE) {
            $propid = $context['DATA']['propid']['id'];
        }   
        $result .= "<input class=\"form-control ajax\" name=\"propid\""
                . " type=\"hidden\" value=\"$propid\">\n"
                . "</div>\n"; 
        $result .= "<!--body_items-->";
        $result .= "<br class=\"clearfix\" />\n"
                . "</div>\n"
                . "</div>\n"; 
        return $result;
    }
    public function get_body_ivalue()
    {
        return "<div id=\"ivalue\" class=\"input-group\"></div>\n";
    }
    public function get_body_form_result()
    {
        return "<div id=\"form_result\"></div>\n";
    }
    public function get_body_modal_form()
    {
        return "<div id=\"dcsModal\" class=\"modal fade\" tabindex=\"-1\""
        . " role=\"dialog\" aria-labelledby=\"dcsModalLabel\" aria-hidden=\"true\">\n"
        . "<div class=\"modal-dialog\">\n"
        . "<div class=\"modal-content\">\n"
        . "<div class=\"modal-header\">\n"
        . "<button type=\"button\" class=\"close\" data-dismiss=\"modal\""
                . " aria-hidden=\"true\">&times;</button>\n"
        . "<h4 class=\"modal-title\" id=\"dcsModalLabel\">"
                . "Saving the modified data</h4>\n"
        . "</div>\n"
        . "<div class=\"modal-body\">\n"
        . "<table class=\"table table-border\">\n"
        . "<caption></caption>\n"
        . "<thead id=\"modalhead\">\n"
        . "<tr><th id=\"name\">Props</th><th id=\"prev\">Prev.value</th>"
                . "<th id=\"value\">new value</th></tr>\n"
        . "</thead>\n"
        . "<tbody id=\"modallist\"><tr></tr></tbody>\n"
        . "</table>\n"
        . "</div>\n"
        . "<div class=\"modal-footer\">\n"
        . "<button type=\"button\" class=\"btn btn-default\""
                . " data-dismiss=\"modal\">Закрыть</button>\n"
        . "<button type=\"button\" id=\"dcsModalOK\""
                . " class=\"btn btn-primary\">OK</button>\n"
        . "</div>\n"
        . "</div><!-- /.modal-content -->\n"
        . "</div><!-- /.modal-dialog -->\n"
        . "</div><!-- /.modal -->\n";
    }  
    public function get_body_loader_form()
    {
        return "<div id=\"loader\">\n"
              . "<img  style=\"display: none;\" width=\"10\" height=\"10\""
              . "alt=\"loading\" src=\"data:image/gif;base64,R0lGODlhEAAQAPIAAP"
                . "///zqHrc/h6mylwjqHrYW0zJ7D1qrL2yH+GkNyZWF0ZWQgd2l0aCBhamF4bG9"
                . "hZC5pbmZvACH5BAAKAAAAIf8LTkVUU0NBUEUyLjADAQAAACwAAAAAEAAQAAAD"
                . "Mwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYD"
                . "AdKa+dIAAAh+QQACgABACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQl"
                . "FUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkEAAoAAgAsAAA"
                . "AABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMao"
                . "KwJZ7Rf8AYPDDzKpZBqfvwQAIfkEAAoAAwAsAAAAABAAEAAAAzMIumIlK8oyh"
                . "pHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIf"
                . "kEAAoABAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5"
                . "oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQACgAFACwAAAAAEAAQAAADMwi6"
                . "IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufb"
                . "SlKAAAh+QQACgAGACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3W"
                . "Gc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAAKAAcALAAAAAAQABA"
                . "AAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1"
                . "YhiCnlsRkAAAOwAAAAAAAAAAAA==\" />\n"
              . "</div>\n";    
    }        
    public function get_body_footer()
    {
        return "<div class=\"container\">\n"
             . "<div class=\"row-fluid\"><a href=\"/\">Copyright &copy;".DCS_COMPANY_NAME." 2017.</a></div>\n"
             . "</div>\n";
    }
    public function get_body_script($context)
    {
        //echo "<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->";
        $result = "<script src=\"/public/js/jquery-3.2.1.min.js\"></script>\n"
                . "<script src=\"/public/js/bootstrap.min.js\"></script>\n"
                . "<script src=\"/public/js/moment.js\"></script>\n"
                . "<script src=\"/public/js/core_app.js\"></script>\n";
        if (($context['ACTION'] == 'EDIT')||
            ($context['ACTION'] == 'CREATE')) {
            $result .= "<script src=\"/public/js/picker.js\"></script>\n"
                     . "<script src=\"/public/js/picker.date.js\"></script>\n"
                     . "<script src=\"/public/js/picker.time.js\"></script>";
        }
        return $result;
    }        
    public function auth_view()
    {
        $result = '';
        if (\Dcs\Vendor\Core\Models\User::isAuthorized()) {
            $result .= "<h1>Your are welcome!</h1>\n"
                     . "<input class=\"ajax\" type=\"hidden\" name=\"act\" value=\"logout\">\n"
                     . "<button id=\"submit\" type=\"button\" class=\"btn btn-info form-control-sm\">Выход</button>\n";
        } else {
            $result .=  "<div class=\"main-error alert alert-error hide\"></div>\n"
                    . "<h2>Пожалуйста, авторизуйтесь</h2>\n"
                    . "<input class =\"form-control-sm ajax\" name=\"username\""
                    . " type=\"text\" class=\"input-block-level\" placeholder=\"Логин\" autofocus>\n"
                    . "<input class =\"form-control-sm ajax\" name=\"password\""
                    . " type=\"password\" class=\"input-block-level\" placeholder=\"Пароль\">\n"
                    . "<input class =\"form-checkbox ajax\" name=\"remember-me\""
                    . " type=\"checkbox\" value=\"remember-me\" id=\"remember\" checked>\n"
                    . "<label class =\"label-control\" for = \"remember\">Запомнить меня</label>\n"
                    . "<input class=\"ajax\" type=\"hidden\" name=\"act\" value=\"login\">\n"
                    . "<button id=\"submit\" type=\"button\""
                    . " class=\"btn btn-info form-control-sm\">Войти</button>\n";
        }
        return $result;
    }        
}
