<?php
function outfield($t,$hclass,$action)
{        
    echo "<div class=\"$hclass\">";
        echo "<div class=\"form-group\">";
            if ($t['class']!='hidden')
            {
                echo "<label for=\"$t[id]\" class=\"control-label col-md-4\">$t[synonym]</label>";
            }   
            echo "<div class=\"col-md-8\">";
                $itype='text';
                $readonly = ' readonly';
                if ($action!='VIEW')
                {    
                    $readonly = '';
                    if($t['type']=='int') 
                    {    
                        $itype = 'number';
                    } 
                    elseif($t['type']=='date') 
                    {    
                        $itype = 'date';
                    }
                    if ($t['class']=='readonly') 
                    {
                        $readonly = ' readonly';
                    }
                }   
                if ($t['class']=='hidden')
                {
                    $itype = 'hidden';
                }
                if (($t['type']=='id')||($t['type']=='cid')||($t['type']=='mdid')||($t['type']=='propid'))
                {
                    echo "<input type=\"hidden\" class=\"form-control\" id=\"$t[id]\" name=\"$t[id]\" it=\"$t[type]\" vt=\"$t[valmdid]\" value=\"\">\n";
                    echo "<input type=\"$itype\" class=\"form-control\" st=\"\" id=\"name_$t[id]\" name=\"name_$t[id]\" it=\"$t[type]\" vt=\"$t[valmdid]\" value=\"\"$readonly>\n";
                    echo "<ul class=\"types_list\">";
                        echo "<li id=\"\"></li>";
                    echo "</ul>";
                }
                else 
                {
                    if ($itype=='date')  
                    {
                        echo "<input type=\"date\" name=\"$t[id]\" st=\"\" id=\"$t[id]\" it=\"date\" valid=\"\" vt=\"\" value=\"\" class=\"form-control datepicker\"$readonly>\n";
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
            echo "</div>";
        echo "</div>";
    echo "</div>";
}    
    echo "<ul id=\"tzTab\" class=\"nav nav-tabs\">";
        echo "<li class=\"active\"><a href=\"#entityhead\">Заголовок</a></li>";
        if ($arResult['ACTION']!=='CREATE')
        {    
            for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
            {
                  $t=$props[$i];
                  if ($t['valmdtypename']!=='Sets')
                  {
                      continue;
                  }    
                  echo "<li><a href=\"#$t[id]\">$t[synonym]</a></li>";
            }
        }    
    echo "</ul>";
    echo "<div class=\"tab-content\">";
        echo "<div id=\"entityhead\" class=\"tab-pane fade active in\">";
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
                            outfield($t,'col-md-6',$arResult['ACTION']);
                            if (($i+1) < $size)
                            {
                                if(($props[$i+1]['rank']%2)==0)
                                {
                                    $i++;
                                    $t=$props[$i];
                                    outfield($t,'col-md-6',$arResult['ACTION']);
                                }
                            }
                        echo "</div>";
                    } else {
                        echo "<div class=\"row\">";
                            outfield($t,'col-md-offset-6 col-md-6',$arResult['ACTION']);
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
                echo "<div id=\"$t[id]\" class=\"tab-pane fade\">";
                    echo "<table class=\"table table-border table-hover\">";
                        echo "<thead id=\"tablehead\">";
                            echo "<tr>";
                                $arsetdata = $data['sets'][$t['id']]; 
                                //var_dump($arsetdata);
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
