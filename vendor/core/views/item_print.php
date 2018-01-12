<?php
echo "<div class=\"tab-content\">";
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
        } 
        else 
        {
            if($t['rank']%2)
            {
                echo "<div class=\"row\">";
                \dcs\vendor\core\View::outfield($t,'col-md-6',$context['ACTION']);
                    if (($i+1) < $size)
                    {
                        if(($props[$i+1]['rank']%2)==0)
                        {
                            $i++;
                            $t=$props[$i];
                            \dcs\vendor\core\View::outfield($t,'col-md-6',$context['ACTION']);
                        }
                    }
                echo "</div>";
            } 
            else 
            {
                echo "<div class=\"row\">";
                    \dcs\vendor\core\View::outfield($t,'col-md-offset-6 col-md-6',$context['ACTION']);
                echo "</div>";        
            }
        }
    }
    echo "</form>";
    echo "</div>";
    for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
    {
        $t=$props[$i];
        if ($t['valmdtypename']!=='Sets')
        {
            continue;
        }    
        echo "<div id=\"$t[id]\" class=\"tab-pane\">";
            echo "<table class=\"table table-border table-hover\">";
                echo "<thead id=\"tablehead\">";
                    echo "<label class=\"tab_title\">$t[synonym]</label>";
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
                    echo "</tbody>";
            echo "</table>";           
        echo "</div>";
    }
echo "</div>";
?>
