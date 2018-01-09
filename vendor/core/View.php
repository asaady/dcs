<?php
namespace Dcs\Vendor\Core;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING) . "/app/dcs_const.php");

class View
{
    protected $mode;
    protected $action;
    
    public function generate($arResult, $template_view, $data = null)
    {
        include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/core/".$template_view;
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
    
    public static function outfield($t,$hclass,$mode)
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
    public static function outContent($arResult, $data)
    {
        echo "<form class=\"form-inline\" role=\"form\">\n";
        echo "<div class=\"row\">";
        for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
        {
            $t=$props[$i];
            if($t['rank']==0) continue;
            if($t['rank']%2)
            {
                self::outfield($t,'col-md-4',$arResult['MODE']);
                if (($i+1) < $size)
                {
                    if(($props[$i+1]['rank']%2)==0)
                    {
                        $i++;
                        $t=$props[$i];
                        self::outfield($t,'col-md-4',$arResult['MODE']);
                    }
                }
            } 
            else 
            {
                self::outfield($t,'col-md-offset-4 col-md-4',$arResult['MODE']);
            }
        }
        if ($arResult['MODE']!='PRINT')
        {    
            echo "<div class=\"col-md-1\">";
            echo "<button id=\"build\" type=\"button\" class=\"btn btn-info\">Сформировать</button>";     
            echo "</div>";
        }    
        echo "</div>";
        echo "</form>";
        echo "<table class=\"table table-border table-hover\">";
        echo "<thead>";
        echo "<tr>";
        foreach($data['PSET'] as $key=>$val)
        {    
            $cls = $val['class'];
            echo "<th class=\"$cls active\" id=\"$key\">$val[synonym]</th>";
        }
        echo "</tr>";
        echo "</thead>";
        echo "<tbody id=\"entitylist\" class=\"list\">";
        echo "</tbody>";
        echo "</table>";
    }        
    public static function outContentToPrint($arResult, $data)
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
                    self::outfield($t,'col-md-4',$arResult['MODE']);
                    if (($i+1) < $size)
                    {
                        if(($props[$i+1]['rank']%2)==0)
                        {
                            $i++;
                            $t=$props[$i];
                            self::outfield($t,'col-md-4',$arResult['MODE']);
                        }
                    }
                } 
                else 
                {
                    self::outfield($t,'col-md-offset-4 col-md-4',$arResult['MODE']);
                }
            }
            echo "</div>";
            echo "</form>";
        }    
        echo "<table class=\"table toprint\">";
        echo "<thead>";
        echo "<tr>";
        foreach($data['PSET'] as $key=>$val)
        {  
            $cls = $val['class'];
            if ($cls=='hidden')
            {
                continue;
            }    
            echo "<th id=\"$key\" vt=\"$val[type]\" $cls>$val[synonym]</th>";
        }
        echo "</tr>";
        echo "</thead>";
        echo "<tbody id=\"entitylist\" class=\"list\">";
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }        
    public static function out_navbar($arResult,$data)
    {        
        echo "<div class=\"navbar navbar-default\" role=\"navigation\">
               <div class=\"container-fluid\">";
        if (($arResult['MODE'] === 'CONFIG')||(($arResult['ACTION'] !== 'EDIT')&&($arResult['ACTION'] !== 'SET_EDIT')))
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
                            if (\dcs\vendor\core\User::isAuthorized())
                            {    
                                foreach($arResult['MENU'] as $ct)
                                {    
                                  echo "<li><a href=\"$arResult[PREFIX]/$ct[ID]\">$ct[SYNONYM]</a></li>";
                                }
                                if (\dcs\vendor\core\User::isAdmin()&&($arResult['MODE'] !== 'CONFIG'))
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
                      echo "<li><a href=\"$arResult[PREFIX]/$key\">$val</a></li>";
                    }
                echo "</ol>
                    </div>
                </div> 
            </div> <!-- /.container -->
        </div> <!-- /.navbar -->
        <nav id=\"nav\" class=\"navbar\" data-spy=\"affix\" data-offset-top=\"150\">
            <ul class=\"nav nav-tabs pull-right\" id=\"actionlist\">
               <li></li>  
            </ul>
        </nav>";
    }    

    public static function setclass($data, $mode = '', $edit_mode = '')
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
}
