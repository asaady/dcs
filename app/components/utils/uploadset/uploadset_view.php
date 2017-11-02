<?php
function outfield($t,$hclass)
{        
    echo "<div class=\"$hclass\">";
        echo "<div class=\"form-group\">";
            if ($t['class']!='hidden')
            {
                echo "<label for=\"$t[id]\" class=\"control-label col-md-4\">$t[synonym]</label>";
            }   
            echo "<div class=\"col-md-8\">";
                $itype='text';
                $readonly = '';
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
            echo "</div>";
        echo "</div>";
    echo "</div>";
}    
    echo "<form class=\"form-inline\" role=\"form\">\n";
    echo "<div class=\"row\">";
    for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++)
    {
        $t=$props[$i];
        if($t['rank']==0) continue;
        if($t['type']=='text') 
        {
            echo "<div class=\"row\">";
            echo "<div class=\"col-md-12\">";      
                echo "<div class=\"form-group\">";
                    echo "<label for=\"$t[id]\" class=\"control-label col-md-2\">$t[synonym]</label>";
                    echo "<div class=\"col-md-10\">";
                    echo "<input type=\"file\" accept=\"text/csv\" class=\"form-control\" st=\"\" id=\"$t[id]\" name=$t[id] it=$t[type]>";
                    echo "</div>";
                echo "</div>";
                echo "<div class=\"form-group\">";
                echo "</div>";
            echo "</div>";
            echo "</div>";
        }   
        else 
        {
            if($t['rank']%2)
            {
                echo "<div class=\"row\">";
                outfield($t,'col-md-6');
                if (($i+1) < $size)
                {
                    if(($props[$i+1]['rank']%2)==0)
                    {
                        $i++;
                        $t=$props[$i];
                        outfield($t,'col-md-6');
                    }
                }
                echo "</div>";
            } 
            else 
            {
                echo "<div class=\"row\">";
                outfield($t,'col-md-offset-6 col-md-6');
                echo "</div>";
            }
        }    
    }
    echo "<div class=\"row\">";
    echo "<div class=\"col-md-1\">";
    echo "<button id=\"build\" type=\"button\" class=\"btn btn-info\">Сформировать</button>";     
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</form>";
?>
<table class="table table-border table-hover">
    <thead>
      <tr>
<?php
        foreach($data['PSET'] as $key=>$val)
        {    
            $cls = $val['class'];
            echo "<th class=\"$cls active\" id=\"$key\">$val[synonym]</th>";
        }
?>
      </tr>

    </thead> 
      <tbody id="entitylist" class="list">
<?php
        foreach($data['LDATA'] as $key=>$val)
        {    
            echo "<tr id=\"$key\">";
            foreach($data['PSET'] as $pkey=>$pval)
            {    
                $cls = $pval['class'];
                echo "<td class=\"$cls active\" id=\"$pkey\">$val[$pkey][name]</td>";
            }
            echo "</tr>";
        }    
?>
      </tbody>
</table>

