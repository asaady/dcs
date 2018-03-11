<?php
namespace Dcs\Vendor\Core\Views;

use Exception;

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
    public function outfield($t,$hclass,$mode='')
    {        
        $type = $t['name_type'];
        echo "<div class=\"$hclass\">";
            echo "<div class=\"form-group\">";
                if ($t['class']!='hidden') {
                    echo "<label for=\"$t[id]\" class=\"control-label col-md-4\">$t[synonym]</label>";
                }   
                echo "<div class=\"col-md-8\">";
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
                            $itype = 'date';
                        }
                        if ($t['class'] == 'hidden') {
                            $itype = 'hidden';
                        } elseif ($t['class'] == 'readonly') {
                            $readonly = ' readonly';
                        }
                    }    
                    if (($type == 'id')||($type == 'cid')||($type == 'mdid')) {
                        echo "<input type=\"hidden\" class=\"form-control\" "
                        . "id=\"$t[id]\" name=\"$t[id]\" it=\"$t[valmdid]\" "
                                . "vt=\"$type\" value=\"\">\n";
                        echo "<input type=\"$itype\" class=\"form-control\" "
                                . "st=\"active\" id=\"name_$t[id]\" "
                                . "name=\"name_$t[id]\" it=\"$t[valmdid]\" "
                                . "vt=\"$type\" value=\"\"$readonly>\n";
                        if (($itype != 'hidden')||($readonly == '')) {
                            echo "<ul class=\"types_list\">";
                                echo "<li id=\"\"></li>";
                            echo "</ul>";
                        }    
                    } else {
                        if (($itype != 'hidden')||($readonly == '')) {
                            if ($type == 'date') {
                                echo "<input type=\"$itype\" class=\"form-control datepicker\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" it=\"\" valid=\"\" vt=\"$type\" value=\"\"$readonly>\n";
                            } else {
                                echo "<input type=\"$itype\" class=\"form-control\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" it=\"\" valid=\"\" vt=\"$type\" value=\"\"$readonly>\n";
                            }    
                            if ($type == 'bool') {    
                                echo "<ul class=\"types_list\">";
                                    echo "<li id=\"true\">true</li>";
                                    echo "<li id=\"false\">false</li>";
                                echo "</ul>";
                            }
                        } else {
                            echo "<input type=\"$itype\" class=\"form-control\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" valid=\"\" vt=\"$type\" value=\"\"$readonly>\n";                    
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
            if($t['rank'] == 0) continue;
            if($t['rank'] % 2) {
                $this->outfield($t,'col-md-4',$this->context['MODE']);
                if (($i+1) < $size) {
                    if(($props[$i+1]['rank'] % 2) == 0) {
                        $i++;
                        $t = $props[$i];
                        $this->outfield($t,'col-md-4',$this->context['MODE']);
                    }
                }
            } else {
                $this->outfield($t,'col-md-offset-4 col-md-4',$this->context['MODE']);
            }
        }
        if ($context['MODE'] != 'PRINT') {    
            echo "<div class=\"col-md-1\">";
            echo "<button id=\"build\" type=\"button\" class=\"btn btn-info\">Сформировать</button>";     
            echo "</div>";
        }    
        echo "</div>";
        echo "</form>";
        $this->set_view($pset);
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
                    if ((count($data['SETS']) == 1)&&
                    ($this->context['SETID'] !== '')) {
                        $key_set = array_search($this->context['SETID'], array_column($data['PLIST'],'id'));
                        $show_tabheader = TRUE;
                    } else {
                        $show_tab = TRUE;
                    }    
                }    
            }  
        }
        if ($show_tab) {
            echo "<ul id=\"dcsTab\" class=\"nav nav-tabs\">";
                $dop = " class=\"active\"";
                $dopfade = " in active";
                $propid = '';
                if (isset($this->context['DATA']['propid'])) {
                    if ($this->context['DATA']['propid']['id'] !== '') {
                        $dop = '';
                        $dopfade = '';
                        $propid = $this->context['DATA']['propid']['id'];
                    }    
                }    
                echo "<li$dop><a href=\"#entityhead\">Заголовок</a></li>";
                if ($this->context['ACTION'] !== 'CREATE')
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
                        echo "<li$dop><a href=\"#$t[id]\">$t[synonym]</a></li>";
                    }
                }    
            echo "</ul>";
            echo "<div class=\"tab-content\">";
            echo "<div id=\"entityhead\" class=\"tab-pane fade$dopfade\">";
        }   
        if ($show_head) {
            echo "<form class=\"form-inline\" role=\"form\">\n";

            for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
            {
              $t=$props[$i];
              if ($t['valmdtypename'] === 'Sets') {
                  continue;
              }    
              if ($t['rank'] == 0) {
                  continue;
              }    
              $type = $t['name_type'];
              if ($type=='text')
              {
                  echo "<div class=\"row\">";
                      echo "<div class=\"col-md-12\">";      
                          echo "<div class=\"form-group\">";
                              echo "<label for=\"$t[id]\" class=\"control-label col-md-2\">$t[synonym]</label>";
                              echo "<div class=\"col-md-10\">";
                                  echo "<textarea class=\"form-control\" rows=\"2\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" it=$type></textarea>";
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
                              if (($props[$i+1]['rank']%2 == 0)&&($props[$i+1]['rank'] > 0)) {
                                  $i++;
                                  $t=$props[$i];
                                  $type = $t['name_type'];
                                  $this->outfield($t,'col-md-6',$this->context['ACTION']);
                              }
                          }
                      echo "</div>";
                  } else {
                      echo "<div class=\"row\">";
                      $this->outfield($t,'col-md-offset-6 col-md-6',$this->context['ACTION']);
                      echo "</div>";        
                  }
              }
            }
            echo "</form>";
        }    
        if ($show_set||$show_tabheader) {
            if ($show_set) {
                echo "<div id=\"entityset\">";
                $this->set_view($data['PSET']);
            } elseif ($show_tabheader) {
                $title = $data['PLIST'][$key_set]['synonym'];
                echo "<p class=\"dcs-tabtitle\">$title</p>";
                echo "<div id=\"".$this->context['SETID']."\">";
                $this->set_view($data['SETS'][$this->context['SETID']],'dcs-items');
        }
            echo "</div>";
        } elseif ($show_tab) {
            echo "</div>";
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
                    if (($propid !== '')&&($propid == $t['id'])) {
                        $dop=" active in";
                    }    
                    echo "<div id=\"$t[id]\" class=\"tab-pane fade$dop\">";
                    $this->set_view($data['SETS'][$t['id']],'dcs-items');
                    echo "</div>";
                }
            }    
            echo "</div>";
        }
    }    
    public function set_view($pset,$tbodyid='dcs-list')
    {
        echo "<table class=\"table table-border table-hover\">";
            echo "<thead  id=\"tablehead\"><tr>";
                foreach($pset as $key=>$val)
                {    
                    $cls = $val['class'];
                    if ($cls == '') {
                        $cls = 'active';
                    }    
                    echo "<th class=\"$cls\" id=\"$key\">$val[synonym]</th>";
                }
            echo "</tr></thead>";
            echo "<tbody id=\"$tbodyid\" class=\"entitylist\"></tbody>";
        echo "</table>";
    }        
    public function out_navbar($data)
    {        
        $prefix = '';
        if ($this->context['PREFIX'] !== '') {
            $prefix = "/".$this->context['PREFIX'];
        }
        echo "<div class=\"navbar navbar-default\" role=\"navigation\">
               <div class=\"container\">";
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
                                  echo "<li><a href=\"$prefix/$ct[ID]\">$ct[SYNONYM]</a></li>";
                                }
                                if (\Dcs\Vendor\Core\Models\User::isAdmin()&&($this->context['PREFIX'] !== 'CONFIG'))
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
                <ol class=\"breadcrumb\"><li></li></ol></div></div>";
        echo "</div> <!-- /.container -->
        </div> <!-- /.navbar -->";
    }    
}
