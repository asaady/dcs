<?php
echo "<ul id=\"tzTab\" class=\"nav nav-tabs\">";
    $dop=" class=\"active\"";
    if (($arResult['ACTION']=='SET_EDIT')||($arResult['ACTION']=='SET_VIEW'))
    {
        $dop='';
    }    
    echo "<li$dop><a href=\"#entityhead\">Заголовок</a></li>";
    if ($arResult['ACTION']!=='CREATE')
    {    
        for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
        {
            $t=$props[$i];
            if ($t['valmdtypename']!=='Sets')
            {
                continue;
            }  
            $dop='';
            if ($arResult['CURID']==$t['id'])
            {
                $dop=" class=\"active\"";
            }    
            echo "<li$dop><a href=\"#$t[id]\">$t[synonym]</a></li>";
        }
    }    
echo "</ul>";
echo "<div class=\"tab-content\">";
    $dop=" in active";
    if (($arResult['ACTION']=='SET_EDIT')||($arResult['ACTION']=='SET_VIEW'))
    {
        $dop='';
    }    
    echo "<div id=\"entityhead\" class=\"tab-pane fade$dop\">";
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
                                echo "<textarea class=\"form-control\" rows=\"2\" st=\"\" id=\"$t[id]\" name=$t[id] it=$t[type]></textarea>";
                            echo "</div>";
                        echo "</div>";
                    echo "</div>";
                echo "</div>";    
            } else {
                if($t['rank']%2)
                {
                    echo "<div class=\"row\">";
                    tzVendor\View::outfield($t,'col-md-6',$arResult['ACTION']);
                        if (($i+1) < $size)
                        {
                            if(($props[$i+1]['rank']%2)==0)
                            {
                                $i++;
                                $t=$props[$i];
                                tzVendor\View::outfield($t,'col-md-6',$arResult['ACTION']);
                            }
                        }
                    echo "</div>";
                } else {
                    echo "<div class=\"row\">";
                        tzVendor\View::outfield($t,'col-md-offset-6 col-md-6',$arResult['ACTION']);
                    echo "</div>";        
                }
            }
          }
          echo "</form>";
    echo "</div>";
    if ($arResult['ACTION']!=='CREATE')
    {    
        for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
        {
            $t=$props[$i];
            if ($t['valmdtypename']!=='Sets')
            {
                continue;
            }    
            $dop='';
            if ($arResult['CURID']==$t['id'])
            {
                $dop=" in active";
            }    
            echo "<div id=\"$t[id]\" class=\"tab-pane fade$dop\">";
                echo "<table class=\"table table-border table-hover\">";
                    echo "<thead id=\"tablehead\">";
                        echo "<tr>";
                            $arsetdata = $data['sets'][$t['id']]; 
                            foreach($arsetdata as $key=>$val)
                            {    
                                $cls = $val['class'];
                                echo "<th class=\"$cls active\" id=\"$key\">$val[synonym]</th>";
                            }
                        echo "</tr>";

                    echo "</thead> ";
                        echo "<tbody id=\"entitylist\" class=\"list\">";
                            echo "<tr>";
                                echo "<td></td>";
                            echo "</tr>";
                        echo "</tbody>";
                echo "</table>";           
            echo "</div>";
        }
    }    
echo "</div>";
?>
