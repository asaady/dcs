<?php
namespace tzVendor;

class View
{

        //public $template_view; // здесь можно указать общий вид по умолчанию.

        /*
	$content_file - виды отображающие контент страниц;
	$template_file - общий для всех страниц шаблон;
	$data - массив, содержащий элементы контента страницы. Обычно заполняется в модели.
	*/
	function generate($arResult, $template_view, $data = null)
	{
		
		/*
		if(is_array($data)) {
			
			// преобразуем элементы массива в переменные
			extract($data);
		}
		*/
		
		/*
		динамически подключаем общий шаблон (вид),
		внутри которого будет встраиваться вид
		для отображения контента конкретной страницы.
		*/
		include filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/vendor/core/".$template_view;
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
            echo "</tbody>";
            echo "</table>";
        }        
}
