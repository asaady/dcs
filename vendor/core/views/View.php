<?php
namespace Dcs\Vendor\Core\Views;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING) . "/app/dcs_const.php");

class View implements I_View
{
    use T_View;
    
    protected $mode;
    protected $action;
    protected $views_path;
    protected $template_view;
    protected $context;
    
    function __construct() {
        $this->views_path = "/vendor/core/views";
        $this->template_view = "/vendor/core/views/template_view.php";
        $this->context = array();
    }
    public function setcontext($context) 
    {
        $this->context = $context;
    }

    public function getcontextdata($context) 
    {
        return $this->context;
    }
    
    public function set_template_view($val) 
    {
        $this->template_view = $val;
    }
    public function get_template_view() 
    {
        return $this->template_view;
    }
    public function set_views_path($val) 
    {
        $this->views_path = $val;
    }
    public function get_views_path() 
    {
        return $this->views_path;
    }

    public function getmode()
    {
        return $this->mode;
    }
    public function setmode($val)
    {
        $this->mode = $val;
    }
    public function setaction($val)
    {
        $this->action = $val;
    }
    public function head_view()
    {
        echo "<meta charset=\"utf-8\">";
        echo "<meta name=\"author\" content=\"".DCS_COMPANY_NAME."\">";
        echo "<meta name=\"description\" content=\"".DCS_COMPANY_NAME."\">";
        echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=Edge\">";
        echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\">";
        echo "<title>".$this->context['TITLE']."</title>";
        echo "<!-- css stylesheets -->";
        echo "<link href=\"/public/css/normalize.css\" rel=\"stylesheet\" type=\"text/css\">";
        echo "<link href=\"/public/css/bootstrap.min.css\" rel=\"stylesheet\" type=\"text/css\">";
        echo "<link href=\"/public/css/bootstrap-select.min.css\" rel=\"stylesheet\" type=\"text/css\">";
        echo "<link href=\"/public/css/default.css\" id=\"theme_base\" rel=\"stylesheet\">";
        echo "<link href=\"/public/css/default.date.css\" id=\"theme_date\" rel=\"stylesheet\">";
        echo "<link href=\"/public/css/default.time.css\" id=\"theme_time\" rel=\"stylesheet\">";
        echo "<link href=\"/public/css/style.css\" rel=\"stylesheet\" type=\"text/css\">";
        echo "<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->";
        echo "<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->";
        echo "<!--[if lt IE 9]>";
        echo "<script src=\"https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js\"></script>";
        echo "<script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>";
        echo "<![endif]-->";
    }        
    public function outfield($t,$hclass,$mode)
    {        
        echo "<div class=\"$hclass\">";
            echo "<div class=\"form-group\">";
                if ($mode!='PRINT')
                {
                    if ($t['class']!='hidden')
                    {
                        echo "<label for=\"$t[id]\" class=\"control-label col-md-4\">$t[synonym]</label>";
                    }   
                }    
                echo "<div class=\"col-md-8\">";
                    $itype='text';
                    $readonly = '';
                    if ($mode=='PRINT')
                    {
                        $itype='hidden';
                        $readonly = ' readonly';
                    }   
                    elseif ($mode=='VIEW')
                    {    
                        $readonly = ' readonly';
                        if ($t['class']=='hidden')
                        {
                            $itype = 'hidden';
                        }
                    }
                    else
                    {    
                        if($t['type']=='int') 
                        {    
                            $itype = 'number';
                        } 
                        elseif($t['type']=='float') 
                        {
                            $itype = 'number\" step=\"any';
                        }    
                        elseif($t['type']=='date') 
                        {    
                            $itype = 'date';
                        }
                        if ($t['class']=='hidden')
                        {
                            $itype = 'hidden';
                        }
                        elseif ($t['class']=='readonly') 
                        {
                            $readonly = ' readonly';
                        }
                    }    
                    if (($t['type']=='id')||($t['type']=='cid')||($t['type']=='mdid'))
                    {
                        echo "<input type=\"hidden\" class=\"form-control\" id=\"$t[id]\" name=\"$t[id]\" it=\"$t[type]\" vt=\"$t[valmdid]\" value=\"\">\n";
                        echo "<input type=\"$itype\" class=\"form-control\" st=\"\" id=\"name_$t[id]\" name=\"name_$t[id]\" it=\"$t[type]\" vt=\"$t[valmdid]\" value=\"\"$readonly>\n";
                        if (($itype != 'hidden')||($readonly == ''))
                        {
                            echo "<ul class=\"types_list\">";
                                echo "<li id=\"\"></li>";
                            echo "</ul>";
                        }    
                    }
                    else 
                    {
                        if (($itype != 'hidden')||($readonly == ''))
                        {
                            if ($t['type']=='date')  
                            {
                                echo "<input type=\"$itype\" class=\"form-control datepicker\" st=\"\" id=\"$t[id]\" name=\"$t[id]\" it=\"$t[type]\" valid=\"\" vt=\"\" value=\"\"$readonly>\n";
                            }
                            else
                            {
                                echo "<input type=\"$itype\" class=\"form-control\" st=\"\" id=\"$t[id]\" name=\"$t[id]\" it=\"$t[type]\" valid=\"\" vt=\"\" value=\"\"$readonly>\n";
                            }    
                            if ($t['type']=='bool') 
                            {    
                                echo "<ul class=\"types_list\">";
                                    echo "<li id=\"true\">true</li>";
                                    echo "<li id=\"false\">false</li>";
                                echo "</ul>";
                            }
                        }
                        else 
                        {
                            echo "<input type=\"$itype\" class=\"form-control\" st=\"\" id=\"$t[id]\" name=\"$t[id]\" valid=\"\" vt=\"\" value=\"\"$readonly>\n";                    
                        }
                    }
                echo "</div>";
            echo "</div>";
        echo "</div>";
    }    
    public function outContent($data)
    {
        echo "<form class=\"form-inline\" role=\"form\">\n";
        echo "<div class=\"row\">";
        for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
        {
            $t=$props[$i];
            if($t['rank']==0) continue;
            if($t['rank']%2)
            {
                $this->outfield($t,'col-md-4',$this->context['MODE']);
                if (($i+1) < $size)
                {
                    if(($props[$i+1]['rank']%2)==0)
                    {
                        $i++;
                        $t=$props[$i];
                        $this->outfield($t,'col-md-4',$this->context['MODE']);
                    }
                }
            } 
            else 
            {
                $this->outfield($t,'col-md-offset-4 col-md-4',$this->context['MODE']);
            }
        }
        if ($context['MODE'] != 'PRINT')
        {    
            echo "<div class=\"col-md-1\">";
            echo "<button id=\"build\" type=\"button\" class=\"btn btn-info\">Сформировать</button>";     
            echo "</div>";
        }    
        echo "</div>";
        echo "</form>";
        $this->set_view($pset);
    }        
    public function outContentToPrint($data)
    {
        echo "<div class=\"row\">";
        $props=$data['PLIST'];
        $size=count($props);
        if ($size)
        {    
            echo "<form class=\"form-inline\" role=\"form\">\n";
            echo "<div class=\"row\">";
            for($i=0 ; $i<$size; $i++)
            {
                $t=$props[$i];
                if($t['rank']==0) continue;
                if($t['rank']%2)
                {
                    $this->outfield($t,'col-md-4',$this->context['MODE']);
                    if (($i+1) < $size)
                    {
                        if(($props[$i+1]['rank']%2)==0)
                        {
                            $i++;
                            $t=$props[$i];
                            $this->outfield($t,'col-md-4',$this->context['MODE']);
                        }
                    }
                } 
                else 
                {
                    $this->outfield($t,'col-md-offset-4 col-md-4',$this->context['MODE']);
                }
            }
            echo "</div>";
            echo "</form>";
        }    
        $this->set_view($pset,"table toprint");
        echo "</div>";
    }        
    public function out_navbar($data)
    {        
        echo "<div class=\"navbar navbar-default\" role=\"navigation\">
               <div class=\"container-fluid\">";
        if (($this->context['MODE'] === 'CONFIG')||
            (($this->context['ACTION'] !== 'EDIT')&&
             ($this->context['ACTION'] !== 'SET_EDIT')))
        {
           echo "<div class=\"navbar-header col-xs-12 col-sm-12 col-md-12\">
                        <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\"#b-menu-1\">
                            <span class=\"sr-only\">Toggle navigation</span>
                            <span class=\"icon-bar\"></span>
                            <span class=\"icon-bar\"></span>
                            <span class=\"icon-bar\"></span>
                        </button>
                        <a class=\"navbar-brand\" href=\"/\">".DCS_COMPANY_NAME."</a>
                        <div class=\"nav collapse navbar-collapse\" id=\"b-menu-1\">
                            <ul class=\"nav navbar-nav pull-right\">";
                            if (\Dcs\Vendor\Core\Models\User::isAuthorized())
                            {    
                                foreach($this->context['MENU'] as $ct)
                                {    
                                  echo "<li><a href=\"".$this->context['PREFIX']."/".$ct['ID']."\">$ct[SYNONYM]</a></li>";
                                }
                                if (\Dcs\Vendor\Core\Models\User::isAdmin()&&($this->context['MODE'] !== 'CONFIG'))
                                {    
                                    echo "<li>";
                                    echo "<a href=\"/config\">";
                                    echo "<i class=\"material-icons\">settings</i>";
                                    echo "</a>";    
                                    echo "</li>";
                                }
                                echo "<li class=\"dropdown\">";
                                echo "<a href=# class=\"dropdown-toggle\" data-toggle=\"dropdown\">";
                                echo "<i class=\"material-icons\">account_box</i>";
                                echo "<b class=\"caret\"></b>";
                                echo "</a>";
                                echo "<ul class=\"dropdown-menu\">";
                                echo "<li><a href=/6accfac4-dc22-4d12-985b-946d3a61bbd1>Настройки</a></li>";
                                echo "<li><a href=javascript:logout()>Выход</a></li>";
                                echo "</ul>";
                                echo "</li>";
                            }        
                    echo "</ul>
                        </div> <!-- /.nav-collapse -->
                    </div>";
        }            
        echo "<div class=\"navbar-inner\">
               <div class=\"col-xs-12 col-sm-12 col-md-12\">
                <ol class=\"breadcrumb\">";
                    echo "<li><a href=\"/\"><i class=\"material-icons\">home</i></a></li>";
                    foreach($data['navlist'] as $key=>$val)
                    {    
                      echo "<li><a href=\"".$this->context['PREFIX']."/$key\">$val</a></li>";
                    }
                echo "</ol>
                    </div>
                </div> 
            </div> <!-- /.container -->
        </div> <!-- /.navbar -->";
    }    

    public function setclass($data, $mode = '', $edit_mode = '')
    {
        $idclass = 'hidden';
        if ($mode === 'CONFIG')
        {    
            $idclass = 'readonly';
        }
        $class = 'active';
        if ($edit_mode === 'EDIT')
        {    
            $class = 'readonly';
        }
        foreach ($data as $key=>$val) {
            if ($val['class'] !== '')
            {
                continue;
            }    
            if ($key == 'id') {
                $val['class'] = $idclass;
                continue;
            }
            $val['class'] = $class;
        }
        return $data; 
    }        
    public function item_view($data) 
    {
        $show_tab = FALSE;
        $show_head = FALSE;
        if (array_key_exists('PLIST', $data) === TRUE) {
            $show_head = TRUE;
            if (array_search('Sets', array_column($data['PLIST'], 'valmdtypename')) === TRUE)
            {
                $show_tab = TRUE;
            }   
        }    
        if ($show_tab) {
            echo "<ul id=\"dcsTab\" class=\"nav nav-tabs\">";
                $dop=" class=\"active\"";
                if (($this->context['ACTION']=='SET_EDIT')||($this->context['ACTION']=='SET_VIEW'))
                {
                    $dop='';
                }    
                echo "<li$dop><a href=\"#entityhead\">Заголовок</a></li>";
                if ($this->context['ACTION'] !== 'CREATE')
                {    
                    for($i=0, $props = $data['PLIST'], $size = count($props); $i<$size; $i++)
                    {
                        $t=$props[$i];
                        if ($t['valmdtypename'] !== 'Sets')
                        {
                            continue;
                        }  
                        $dop='';
                        if ($this->context['CURID']==$t['id'])
                        {
                            $dop=" class=\"active\"";
                        }    
                        echo "<li$dop><a href=\"#$t[id]\">$t[synonym]</a></li>";
                    }
                }    
            echo "</ul>";
            echo "<div class=\"tab-content\">";
            $dop=" in active";
            if (($this->context['ACTION'] == 'SET_EDIT')||($this->context['ACTION'] == 'SET_VIEW'))
            {
                $dop='';
            }    
            echo "<div id=\"entityhead\" class=\"tab-pane fade$dop\">";
        }   
        if ($show_head) {
            echo "<form class=\"form-inline\" role=\"form\">\n";
            for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
            {
              $t=$props[$i];
              if ($t['valmdtypename']==='Sets')
              {
                  continue;
              }    
              if ($t['type']=='text')
              {
                  echo "<div class=\"row\">";
                      echo "<div class=\"col-md-12\">";      
                          echo "<div class=\"form-group\">";
                              echo "<label for=\"$t[id]\" class=\"control-label col-md-2\">$t[synonym]</label>";
                              echo "<div class=\"col-md-10\">";
                                  echo "<textarea class=\"form-control\" rows=\"2\" st=\"\" id=\"$t[id]\" name=\"$t[id]\" it=$t[type]></textarea>";
                              echo "</div>";
                          echo "</div>";
                      echo "</div>";
                  echo "</div>";    
              } else {
                  if($t['rank']%2)
                  {
                      echo "<div class=\"row\">";
                      $this->outfield($t,'col-md-6',$this->context['ACTION']);
                          if (($i+1) < $size)
                          {
                              if(($props[$i+1]['rank']%2)==0)
                              {
                                  $i++;
                                  $t=$props[$i];
                                  $this->outfield($t,'col-md-6',$this->context['ACTION']);
                              }
                          }
                      echo "</div>";
                  } else {
                      echo "<div class=\"row\">";
                      $this->outfield($t,'col-md-offset-6 col-md-6',$context['ACTION']);
                      echo "</div>";        
                  }
              }
            }
            echo "</form>";
        }    
        if (count($data['PSET'])>0) {
            $this->set_view($data['PSET']);
        }
        if ($show_tab) {
            echo "</div>";
            if ($context['ACTION'] !== 'CREATE')
            {    
                for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
                {
                    $t=$props[$i];
                    if ($t['valmdtypename']!=='Sets')
                    {
                        continue;
                    }    
                    $dop='';
                    if ($context['CURID']==$t['id'])
                    {
                        $dop=" in active";
                    }    
                    echo "<div id=\"$t[id]\" class=\"tab-pane fade$dop\">";
                    $this->set_view($data['sets'][$t['id']]);
                    echo "</div>";
                }
            }    
            echo "</div>";
        }
    }    
    public function set_view($pset,$classtable = "table table-border table-hover")
    {
        $toprint = TRUE;
        if (strpos($classtable,"toprint") === FALSE) {
            $toprint = FALSE;
        }
        echo "<table class=\"$classtable\">";
            echo "<thead  id=\"tablehead\"><tr>";
                foreach($pset as $key=>$val)
                {    
                    $cls = $val['class'];
                    if (($cls == 'hidden')&&($toprint))
                    {
                        continue;
                    }    
                    echo "<th class=\"$cls active\" id=\"$key\">$val[synonym]</th>";
                }
            echo "</tr></thead>";
            echo "<tbody id=\"entitylist\" class=\"list\"></tbody>";
        echo "</table>";
    }        
    public function context_view($data)
    {
        echo "<div class=\"container-fluid\">";
        echo "<div class=\"row\">";
        echo "<div class=\"col-xs-12 col-md-12\">";
        echo "<input class=\"form-control\" name=\"curid\" type=\"hidden\" value=\"".$this->context['CURID']."\">";
        echo "<input class=\"form-control\" name=\"version\" type=\"hidden\" value=\"".$data['version']."\">";
        echo "<input class=\"form-control\" name=\"page\" type=\"hidden\" value=\"".$this->context['PAGE']."\">";
        echo "<input class=\"form-control\" name=\"itemid\" type=\"hidden\" value=\"".$this->context['ITEMID']."\">";
        echo "<input class=\"form-control\" name=\"mode\" type=\"hidden\" value=\"".$this->context['MODE']."\">";
        echo "<input class=\"form-control\" name=\"action\" type=\"hidden\" value=\"".$this->context['ACTION']."\">";
        echo "<input class=\"form-control\" name=\"command\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"filter_id\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"filter_val\" type=\"hidden\" value=\"".$this->context['PARAM']."\">";
        echo "<input class=\"form-control\" name=\"filter_min\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"filter_max\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"sort_id\" type=\"hidden\" value=\"\">";
        echo "<input class=\"form-control\" name=\"sort_dir\" type=\"hidden\" value=\"\">";
        $this->item_view($data);
        echo "<br class=\"clearfix\" />";
        echo "</div>"; 
        echo "</div>"; 
        echo "</div>"; 
    }        
    public function modal_view()
    {
        echo "<div id=\"dcsModal\" class=\"modal fade\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"dcsModalLabel\" aria-hidden=\"true\">";
        echo "<div class=\"modal-dialog\">";
        echo "<div class=\"modal-content\">";
            echo "<div class=\"modal-header\">";
            echo "<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>";
            echo "<h4 class=\"modal-title\" id=\"dcsModalLabel\">Saving the modified data</h4>";
            echo "</div>";
            echo "<div class=\"modal-body\">";
                echo "<table class=\"table table-border\">";
                    echo "<caption></caption>";
                    echo "<thead id=\"modalhead\">";
                    echo "<tr><th id=\"name\">Props</th><th id=\"prev\">Prev.value</th><th id=\"value\">new value</th></tr>";
                    echo "</thead>"; 
                    echo "<tbody id=\"modallist\"><tr></tr></tbody>";
                echo "</table>";
            echo "</div>";
            echo "<div class=\"modal-footer\">";
                echo "<button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Закрыть</button>";
                echo "<button type=\"button\" id=\"dcsModalOK\" class=\"btn btn-primary\">OK</button>";
            echo "</div>";
        echo "</div><!-- /.modal-content -->";
        echo "</div><!-- /.modal-dialog -->";
        echo "</div><!-- /.modal -->";
    }        
    public function body_header_view($data)
    {
        $this->out_navbar($data);
    }
    public function body_main_view($data)
    {
        $this->context_view($data);
        echo "<div id=\"ivalue\" class=\"input-group\"></div>";
        echo "<div id=\"form_result\"></div>";
        $this->modal_view();    
        echo "<div id=\"loader\">";
        echo "<img  style=\"display: none;\" width=\"10\" height=\"10\" "
                    . "alt=\"loading\" src=\"data:image/gif;base64,R0lGODlhEAAQAPIAAP///zqHrc/h6mylwjqHrYW0zJ7D1qrL2yH+GkNyZWF0ZWQgd2l0aCBhamF4bG9hZC5pbmZvACH5BAAKAAAAIf8LTkVUU0NBUEUyLjADAQAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQACgABACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkEAAoAAgAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkEAAoAAwAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkEAAoABAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQACgAFACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQACgAGACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAAKAAcALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==\" />";
        echo "</div>";    
    }
    public function body_footer_view()
    {
        echo "<div class=\"container-fluid\">";
        echo "<div class=\"row\"><a href=\"/\">Copyright &copy;".DCS_COMPANY_NAME." 2017.</a></div>";
        echo "</div>";
    }
    public function body_script_view()
    {
        echo "<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->";
        echo "<script src=\"/public/js/jquery-3.2.1.min.js\"></script>";
        echo "<script src=\"/public/js/bootstrap.min.js\"></script>";
        echo "<script src=\"/public/js/moment.js\"></script>";
        echo "<script src=\"/public/js/core_app.js\"></script>";
        if (($this->context['ACTION'] == 'EDIT')||
            ($this->context['ACTION'] == 'CREATE')) {
            echo "<script src=\"/public/js/picker.js\"></script>";
            echo "<script src=\"/public/js/picker.date.js\"></script>";
            echo "<script src=\"/public/js/picker.time.js\"></script>";
        }
    }        
}
