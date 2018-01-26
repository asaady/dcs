<?php
namespace Dcs\Vendor\Core\Views;
class Print_View extends View implements I_View 
{
    use T_View;
    public function body_header_view($data)
    {
        echo "<p class=\"dcs-printform-title\">$data[name]</p>";
    }
    public function body_footer_view()
    {
        
    }        
    public function content_view($data) 
    {
        echo "<div class=\"container\">";
        echo "<div class=\"row\">";
        $this->context_view($data);
        $this->item_view($data);
        echo "<br class=\"clearfix\" />";
        echo "</div>"; 
        echo "</div>"; 
    }
    public function outfield_print($t,$hclass)
    {        
        $type = $t['name_type'];
        echo "<div class=\"$hclass\">";
            echo "<div class=\"form-group\">";
                echo "<label for=\"$t[id]\" class=\"control-label col-md-4\">$t[synonym]</label>";
                echo "<div class=\"col-md-8\">";
                if (($type=='id')||($type=='cid')||($type=='mdid')) {
                    echo "<input type=\"hidden\" class=\"form-control\" "
                    . "id=\"$t[id]\" name=\"$t[id]\" it=\"$type\" "
                            . "vt=\"$t[valmdid]\" value=\"\">\n";
                    echo "<input type=\"text\" class=\"form-control\" "
                            . "st=\"active\" id=\"name_$t[id]\" "
                            . "name=\"name_$t[id]\" it=\"$type\" "
                            . "vt=\"$t[valmdid]\" value=\"\" readonly>\n";
                } else {
                    echo "<input type=\"text\" class=\"form-control\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" valid=\"\" vt=\"\" value=\"\" readonly>\n";                    
                }
                echo "</div>";
            echo "</div>";
        echo "</div>";
    }    
    public function item_view($data)
    {
        $props=$data['PLIST'];
        $size=count($props);
        if (!$size) {    
            return;
        }    
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
                    if ($this->context['SETID'] !== '') {
                        $key_set = array_search($this->context['SETID'], array_column($data['PLIST'],'id'));
                        $show_tabheader = TRUE;
                    }    
                }    
            }  
        }
        if ($show_head) {
            echo "<form class=\"form-inline\" role=\"form\">\n";
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
                    echo "<div class=\"row\">";
                        echo "<div class=\"col-md-12\">";      
                            echo "<div class=\"form-group\">";
                                echo "<label for=\"$t[id]\" class=\"control-label col-md-2\">$t[synonym]</label>";
                                echo "<div class=\"col-md-10\">";
                                    echo "<textarea class=\"form-control\" rows=\"2\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" it=\"text\"></textarea>";
                                echo "</div>";
                            echo "</div>";
                        echo "</div>";
                    echo "</div>";    
                    continue;
                }    
                echo "<div class=\"row\">";
                if($t['rank']%2)
                {
                    $this->outfield_print($t,'col-md-6');
                    if (($i+1) < $size)
                    {
                        if(($props[$i+1]['rank']%2)==0)
                        {
                            $i++;
                            $t=$props[$i];
                            $this->outfield_print($t,'col-md-6');
                        }
                    }
                } 
                else 
                {
                    $this->outfield_print($t,'col-md-offset-6 col-md-6');
                }
                echo "</div>";    
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
                $this->set_view($data['SETS'][$this->context['SETID']]);
            }
            echo "</div>";
        }    
    }        
    public function set_view($pset)
    {
        echo "<table class=\"table toprint\">";
            echo "<thead  id=\"tablehead\"><tr>";
                foreach($pset as $key => $val) {    
                    $cls = $val['class'];
                    if ($cls == 'hidden') {
                        continue;
                    }
                    echo "<th class=\"dcs-tablehead-print\" id=\"$key\">$val[synonym]</th>";
                }
            echo "</tr></thead>";
            echo "<tbody id=\"entitylist\" class=\"list\"></tbody>";
        echo "</table>";
    }        
    public function body_script_view()
    {
        echo "<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->";
        echo "<script src=\"/public/js/jquery-3.2.1.min.js\"></script>";
        echo "<script src=\"/public/js/bootstrap.min.js\"></script>";
        echo "<script src=\"/public/js/moment.js\"></script>";
        echo "<script src=\"/public/js/core_print.js\"></script>";
    }        
}
