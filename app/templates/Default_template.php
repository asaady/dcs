<?php
namespace Dcs\App\Templates;

use Dcs\Vendor\Core\Models\DcsContext;
use Dcs\Vendor\Core\Views\Template;
use Dcs\Vendor\Core\Views\I_Template;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING) . "/app/dcs_const.php");

class Default_Template extends Template implements I_Template
{
    public function get_head()
    {
        $context = DcsContext::getcontext();
        return "<meta charset=\"utf-8\">\n"
        . "<meta name=\"author\" content=\"".DCS_COMPANY_NAME."\">\n"
        . "<meta name=\"description\" content=\"".DCS_COMPANY_NAME."\">\n"
        . "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=Edge\">\n"
        . "<meta name=\"viewport\" content=\"width=device-width,"
                . " initial-scale=1.0, maximum-scale=1.0, user-scalable=no\">\n"
        . "<title>".$context->getattr('TITLE')."</title>\n"
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
    public function get_body_header($data)
    {
        $prefix = '';
        $context = DcsContext::getcontext();
        if ($context->getattr('PREFIX') !== '') {
            $prefix = "/".$context->getattr('PREFIX');
        }
        $result = "<div class=\"navbar navbar-default\" role=\"navigation\">\n"
                  . "<div class=\"container\">\n";
        if (($context->getattr('PREFIX') === 'CONFIG')||
            ($context->getattr('ACTION') !== 'EDIT')) {
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
                foreach($context->getattr('MENU') as $ct) {  
                    $result .= "<li><a href=\"$prefix/$ct[ID]\">$ct[SYNONYM]</a></li>\n";
                }
                if (\Dcs\Vendor\Core\Models\User::isAdmin()&&($context->getattr('PREFIX') !== 'CONFIG')) {    
                    $result .= "<li>\n"
                             . "<a href=\"/CONFIG/\">\n"
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
    public function get_body_context($data)
    {
        $context = DcsContext::getcontext();
        $result = "<div class=\"dcs-context\">\n"
                . "<input class=\"form-control\" name=\"dcs_prefix\" type=\"hidden\""
                . " value=\"".$context->getattr('PREFIX')."\">\n"
                . "<input class=\"form-control\" name=\"dcs_mode\" type=\"hidden\""
                . " value=\"".$context->getattr('MODE')."\">\n"
                . "<input class=\"form-control\" name=\"dcs_itemid\" type=\"hidden\""
                . " value=\"".$context->getattr('ITEMID')."\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_setid\" type=\"hidden\""
                . " value=\"".$context->getattr('SETID')."\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_curid\" type=\"hidden\""
                . " value=\"".$context->getattr('CURID')."\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_propid\" type=\"hidden\""
                . " value=\"".$context->getattr('PROPID')."\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_action\" type=\"hidden\""
                . " value=\"".$context->getattr('ACTION')."\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_version\" type=\"hidden\""
                . " value=\"".$data['version']."\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_page\" type=\"hidden\""
                . " value=\"".$context->getattr('PAGE')."\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_command\" type=\"hidden\""
                . " value=\"".$context->getattr('COMMAND')."\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_param_propid\" type=\"hidden\""
                . " value=\"\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_param_id\" type=\"hidden\""
                . " value=\"\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_param_val\" type=\"hidden\""
                . " value=\"\">\n"
                . "<input class=\"form-control ajax\" name=\"dcs_param_type\" type=\"hidden\""
                . " value=\"\">\n";
        $result .= "<input class=\"form-control ajax\" name=\"dcs_docid\""
                . " type=\"hidden\" value=\"".$context->data_getattr('dcs_docid')['id']."\">\n";
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
    public function get_body_script_toprint()
    {
        return "<script src=\"/public/js/jquery-3.2.1.min.js\"></script>\n"
                . "<script src=\"/public/js/bootstrap.min.js\"></script>\n"
                . "<script src=\"/public/js/moment.js\"></script>\n"
                . "<script src=\"/public/js/core_print.js\"></script>\n";
    }        
    public function get_body_script()
    {
        $context = DcsContext::getcontext();
        //echo "<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->";
        $result = "<script type=\"text/javascript\" src=\"/public/js/jquery-3.2.1.js\"></script>\n"
                . "<script type=\"text/javascript\" src=\"/public/js/bootstrap.min.js\"></script>\n"
                . "<script type=\"text/javascript\" src=\"/public/js/moment.js\"></script>\n"
                . "<script type=\"text/javascript\" src=\"/public/js/core_app.js\"></script>\n";
        if (($context->getattr('ACTION') == 'EDIT')||
            ($context->getattr('ACTION') == 'CREATE')) {
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
    public function error_view($data)
    {
        $result = "<div class=\"row\">\n"
                . "<h1>".$data['name']."</h1>\n"
                . "<h3>".$data['synonym']."</h3>\n"
                . "<a href=\"/\">на главную</a>\n"
                . "</div>\n";
        return $result;
    }        
    public function outfield($t,$hclass,$mode='')
    {        
        $type = $t['name_type'];
        $result = "<div class=\"$hclass\">\n"
                . "<div class=\"form-group\">\n";
        if ($t['class']!='hidden') {
            $result .= "<label for=\"$t[id]\" class=\"control-label col-md-4\">$t[synonym]</label>\n";
        }   
        $result .= "<div class=\"col-md-8\">\n";
        $itype = 'text';
        $readonly = '';
        if ($mode == 'VIEW') {    
            $readonly = ' readonly';
            if ($t['class'] == 'hidden') {
                $itype = 'hidden';
            }
        } else {    
            if($type == 'int') {    
                $itype = 'number';
            } elseif($type == 'float') {
                $itype = 'number\" step=\"any';
            } elseif($type == 'date') {    
                $itype = 'text';
            }
            if ($t['class'] == 'hidden') {
                $itype = 'hidden';
            } elseif ($t['class'] == 'readonly') {
                $readonly = ' readonly';
            }
        }    
        if (($type == 'id')||($type == 'cid')||($type == 'mdid')) {
            $result .= "<input type=\"hidden\" class=\"form-control ajax\" "
                    . "id=\"$t[id]\" name=\"$t[id]\" it=\"$t[valmdid]\" "
                    . "vt=\"$type\" value=\"\" autocomplete=\"newvalue\">\n";
            $result .= "<input type=\"$itype\" class=\"form-control ajax\" "
                    . "st=\"active\" id=\"name_$t[id]\" "
                    . "name=\"name_$t[id]\" it=\"$t[valmdid]\" "
                    . "vt=\"$type\" value=\"\"$readonly autocomplete=\"newvalue\">\n";
            if (($itype != 'hidden')||($readonly == '')) {
                $result .= "<ul class=\"types_list\">\n"
                         . "<li id=\"\"></li>\n"
                         . "</ul>\n";
            }    
        } else {
            if (($itype != 'hidden')||($readonly == '')) {
                if ($type == 'date') {
                    $result .= "<input type=\"$itype\" class=\"form-control datepicker ajax\""
                            . " st=\"active\" id=\"$t[id]\" name=\"$t[id]\""
                            . " it=\"\" valid=\"\" vt=\"$type\" value=\"\"$readonly"
                            . " autocomplete=\"newvalue\">\n";
                } else {
                    $result .= "<input type=\"$itype\" class=\"form-control ajax\""
                            . " st=\"active\" id=\"$t[id]\" name=\"$t[id]\""
                            . " it=\"\" valid=\"\" vt=\"$type\" value=\"\"$readonly"
                            . " autocomplete=\"newvalue\">\n";
                }    
                if ($type == 'bool') {    
                    $result .= "<ul class=\"types_list\">\n"
                        . "<li id=\"true\">true</li>\n"
                        . "<li id=\"false\">false</li>\n"
                        . "</ul>\n";
                }
            } else {
                $result .= "<input type=\"$itype\" class=\"form-control ajax\" "
                        . "st=\"active\" id=\"$t[id]\" name=\"$t[id]\" valid=\"\""
                        . " vt=\"$type\" value=\"\"$readonly"
                        . " autocomplete=\"newvalue\">\n";
            }
        }
        $result .= "</div>\n"
            . "</div>\n"
            . "</div>\n";
        return $result;
    }    
    public function get_body_content($data) 
    {
        $show_tab = false;
        $show_head = false;
        $show_set = false;
        $show_tabheader = false;
        $key_set = '';
        $context = DcsContext::getcontext();
        if ((array_key_exists('PLIST', $data) !== false)&&(count($data['PLIST'])>0)) {
            $show_head = true;
        }  
        if ((array_key_exists('PSET', $data) === true)&&(count($data['PSET'])>0)) {
            $show_set = true;
        }   
        if (($show_head)&&(!$show_set)) {
            if (array_key_exists('SETS', $data) !== false) {
                if (count($data['SETS'])>0) {
                    if ((count($data['SETS']) == 1)&&
                    ($context->getattr('PROPID') !== '')) {
                        $key_set = array_search($context->getattr('PROPID'), array_column($data['PLIST'],'id'));
                        $show_tabheader = true;
                    } else {
                        $show_tab = true;
                    }    
                }    
            }  
        }
        $result = '';
        if ($show_tab) {
            $result .= "<ul id=\"dcsTab\" class=\"nav nav-tabs\">\n";
                $dop = " class=\"active\"";
                $dopfade = " in active";
                $propid = $context->data_getattr('dcs_propid')['id'];
                if ($propid !== '') {
                    $dop = '';
                    $dopfade = '';
                }    
                $result .= "<li$dop><a data-toggle=\"tab\" href=\"#entityhead\">Заголовок</a></li>\n";
                if ($context->getattr('ACTION') !== 'CREATE')
                {    
                    for($i=0, $props = $data['PLIST'], $size = count($props); $i<$size; $i++)
                    {
                        $t=$props[$i];
                        if ($t['valmdtypename'] !== 'Sets') {
                            continue;
                        }  
                        $dop='';
                        if (($propid !== '')&&($propid == $t['id'])) {
                            $dop=" class=\"active\"";
                        }    
                        $result .= "<li$dop><a data-toggle=\"tab\" href=\"#$t[id]\">$t[synonym]</a></li>\n";
                    }
                }    
            $result .= "</ul>\n"
                . "<div class=\"tab-content\">\n"
                . "<div id=\"entityhead\" class=\"tab-pane fade$dopfade\">\n";
        }   
        if ($show_head) {
            $result .= "<form class=\"form-inline\" role=\"form\" autocomplete=\"off\">\n";
            for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++) {
                $t=$props[$i];
                if ($t['valmdtypename'] === 'Sets') {
                    continue;
                }    
                if ($t['rank'] == 0) {
                    continue;
                }    
                $type = $t['name_type'];
                if ($type=='text') {
                    $result .= "<div class=\"row\">\n"
                             . "<div class=\"col-md-12\">\n"      
                             . "<div class=\"form-group\">\n"
                             . "<label for=\"$t[id]\" class=\"control-label col-md-2\">$t[synonym]</label>\n"
                             . "<div class=\"col-md-10\">\n"
                             . "<textarea class=\"form-control\" rows=\"2\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" it=$type></textarea>\n"
                             . "</div>\n"
                             . "</div>\n"
                             . "</div>\n"
                             . "</div>\n";    
                } else {
                    if($t['rank']%2) {
                        $result .= "<div class=\"row\">\n";
                        $result .= $this->outfield($t,'col-md-6',$context->getattr('ACTION'));
                        if (($i+1) < $size) {
                            if (($props[$i+1]['rank']%2 == 0)&&($props[$i+1]['rank'] > 0)) {
                                $i++;
                                $t = $props[$i];
                                $type = $t['name_type'];
                                $result .= $this->outfield($t,'col-md-6',$context->getattr('ACTION'));
                            }
                        }
                        $result .= "</div>\n";
                    } else {
                        $result .= "<div class=\"row\">\n";
                        $result .= $this->outfield($t,'col-md-offset-6 col-md-6',$context->getattr('ACTION'));
                        $result .= "</div>\n";        
                    }
                }
            }
            $result .= "</form>\n";
        }  
        if ($show_set||$show_tabheader) {
            if ($show_set) {
                $result .= "<div id=\"entityset\">\n";
                $result .= $this->set_view($data['PSET']);
            } elseif ($show_tabheader) {
                $title = $data['PLIST'][$key_set]['synonym'];
                $result .= "<p class=\"dcs-tabtitle\">$title</p>\n"
                         . "<div id=\"".$context->getattr('PROPID')."\" "
                         . "it=\"".$context->getattr('SETID')."\">\n";
                $result .= $this->set_view($data['SETS'][$context->getattr('PROPID')],'dcs-items');
            }
            $result .= "</div>\n";
        } elseif ($show_tab) {
            $result .= "</div>\n";
            if ($context->getattr('ACTION') !== 'CREATE') {    
                for($i=0, $props = $data['PLIST'], $size = count($props); $i<$size; $i++) {
                    $t = $props[$i];
                    if ($t['valmdtypename'] !== 'Sets') {
                        continue;
                    }    
                    $dop='';
                    if (($propid !== '')&&($propid == $t['id'])) {
                        $dop=" active in";
                    }    
                    $result .= "<div id=\"$t[id]\" it=\"\"  class=\"tab-pane fade$dop\">\n";
                    $result .= $this->set_view($data['SETS'][$t['id']],'dcs-items');
                    $result .= "</div>\n";
                }
            }    
            $result .= "</div>\n";
        }
        return $result;
    }    
    public function set_view($pset,$tbodyid='dcs-list')
    {
        $result = "<table class=\"table table-border table-hover\">\n"
                . "<thead  id=\"tablehead\"><tr>\n";
        foreach($pset as $key=>$val) {    
            $cls = $val['class'];
            if ($cls == '') {
                $cls = 'active';
            }    
            $result .= "<th class=\"$cls\" id=\"$key\">".$val['synonym']."</th>\n";
        }
        $result .= "</tr></thead>\n"
                 . "<tbody id=\"$tbodyid\" class=\"entitylist\"></tbody>\n"
                 . "</table>\n";
         return $result;
    }        
    public function set_toprint($pset)
    {
        $result = "<table class=\"table toprint\">\n";
        $result .= "<thead  id=\"tablehead\">\n";
        $result .= "<tr>\n";
        foreach($pset as $key => $val) {    
            $cls = $val['class'];
            if ($cls == 'hidden') {
                continue;
            }
            $result .= "<th class=\"dcs-tablehead-print\" id=\"$key\">".$val['synonym']['name']."</th>\n";
        }
        $result .= "</tr>\n";
        $result .= "</thead>\n";
        $result .= "<tbody id=\"entitylist\" class=\"list\"></tbody>\n";
        $result .= "</table>\n";
        return $result;
    }        
    public function outfield_toprint($t,$hclass)
    {        
        $result = '';
        $type = $t['name_type'];
        $result .= "<div class=\"$hclass\">\n";
        $result .= "<div class=\"form-group\">\n";
        $result .= "<label for=\"$t[id]\" class=\"control-label col-md-4\">$t[synonym]</label>\n";
        $result .= "<div class=\"col-md-8\">\n";
        if (($type=='id')||($type=='cid')||($type=='mdid')) {
            $result .= "<input type=\"hidden\" class=\"form-control\""
                    . "id=\"$t[id]\" name=\"$t[id]\" it=\"$type\" "
                    . "vt=\"$t[valmdid]\" value=\"\">\n";
            $result .= "<input type=\"text\" class=\"form-control\" "
                    . "st=\"active\" id=\"name_$t[id]\" "
                    . "name=\"name_$t[id]\" it=\"$type\" "
                    . "vt=\"$t[valmdid]\" value=\"\" readonly>\n";
        } else {
            $result .= "<input type=\"text\" class=\"form-control\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" valid=\"\" vt=\"\" value=\"\" readonly>\n";                    
        }
        $result .= "</div>\n";
        $result .= "</div>\n";
        $result .= "</div>\n";
        return $result;
    }    
    public function get_body_toprint_content($data)
    {
        $props=$data['PLIST'];
        $size=count($props);
        if (!$size) {    
            return;
        }    
        $context = DcsContext::getcontext();
        $show_tab = FALSE;
        $show_head = FALSE;
        $show_set = FALSE;
        $show_tabheader = FALSE;
        $key_set = '';
        if ((array_key_exists('PLIST', $data) !== FALSE)&&(count($data['PLIST'])>0)) {
            $show_head = TRUE;
        }  
        if ((array_key_exists('PSET', $data) === TRUE)&&(count($data['PSET'])>0)) {
            $show_set = TRUE;
        }   
        if (($show_head)&&(!$show_set)) {
            if (array_key_exists('SETS', $data) !== FALSE) {
                if (count($data['SETS'])>0) {
                    if ($context->getattr('SETID') !== '') {
                        $key_set = array_search($context->getattr('SETID'), array_column($data['PLIST'],'id'));
                        $show_tabheader = TRUE;
                    }    
                }    
            }  
        }
        $result = '';
        if ($show_head) {
            $result .= "<form class=\"form-inline\" role=\"form\">\n";
            for($i=0 ; $i<$size; $i++)
            {
                $t=$props[$i];
                $type = $t['name_type'];
                if ($t['class'] == 'hidden') {
                    continue;
                }
                if($t['rank']==0) {
                    continue;
                }
                if ($t['valmdtypename'] === 'Sets') {
                    continue;
                }    
                if ($type == 'text')
                {
                    $result .= "<div class=\"row\">\n";
                    $result .= "<div class=\"col-md-12\">\n";      
                    $result .= "<div class=\"form-group\">\n";
                    $result .= "<label for=\"$t[id]\" class=\"control-label col-md-2\">$t[synonym]</label>\n";
                    $result .= "<div class=\"col-md-10\">\n";
                    $result .= "<textarea class=\"form-control\" rows=\"2\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" it=\"text\"></textarea>\n";
                    $result .= "</div>\n";
                    $result .= "</div>\n";
                    $result .= "</div>\n";
                    $result .= "</div>\n";    
                    continue;
                }    
                $result .= "<div class=\"row\">\n";
                if($t['rank']%2)
                {
                    $result .= $this->outfield_toprint($t,'col-md-6');
                    if (($i+1) < $size)
                    {
                        if(($props[$i+1]['rank']%2)==0)
                        {
                            $i++;
                            $t=$props[$i];
                            $result .= $this->outfield_toprint($t,'col-md-6');
                        }
                    }
                } else {
                    $result .= $this->outfield_toprint($t,'col-md-offset-6 col-md-6');
                }
                $result .= "</div>\n";    
            }
            $result .= "</form>\n";
        }
        if ($show_set||$show_tabheader) {
            if ($show_set) {
                $result .= "<div id=\"entityset\">\n";
                $result .= $this->set_toprint($data['PSET']);
            } elseif ($show_tabheader) {
                $title = $data['PLIST'][$key_set]['synonym'];
                $result .= "<p class=\"dcs-tabtitle\">$title</p>\n";
                $result .= "<div id=\"".$context->getattr('SETID')."\">\n";
                $result .= $this->set_toprint($data['SETS'][$context->getattr('SETID')]);
            }
            $result .= "</div>\n";
        }    
        return $result;
    }        
}
