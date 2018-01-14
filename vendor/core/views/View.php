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
}
