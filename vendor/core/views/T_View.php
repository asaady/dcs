<?php
namespace Dcs\Vendor\Core\Views;

trait T_View {
    public function generate($data = null)
    {
        $skelet = "<!DOCTYPE html>\n"
                . "<html lang=\"ru\">\n"
                . "<head>\n"
                . "<!--head-->"
                . "</head>\n"
                . "<body>\n"
                . "<header>\n"
                . "<!--body_header-->"
                . "</header>\n"
                . "<main>\n"
                . "<!--body_action_list-->"
                . "<!--body_content-->"
                . "<!--body_ivalue-->"
                . "<!--body_form_result-->"
                . "<!--body_modal_form-->"
                . "<!--body_loader_form-->"
                . "</main>\n"
                . "<footer>\n"
                . "<!--body_footer-->"
                . "</footer>\n"
                . "<!--body_script-->"
                . "</body>\n"
                . "</html>";
        $skelet = str_replace("<!--head-->", $this->template->get_head($this->context), $skelet);
        $skelet = str_replace("<!--body_header-->", $this->template->get_body_header($this->context, $data), $skelet);
        $skelet = str_replace("<!--body_action_list-->", $this->template->get_body_action_list(), $skelet);
        $skelet = str_replace("<!--body_content-->", $this->template->get_body_content($this->context, $data), $skelet);
        $skelet = str_replace("<!--body_ivalue-->", $this->template->get_body_ivalue(), $skelet);
        $skelet = str_replace("<!--body_form_result-->", $this->template->get_body_form_result(), $skelet);
        $skelet = str_replace("<!--body_modal_form-->", $this->template->get_body_modal_form(), $skelet);
        $skelet = str_replace("<!--body_loader_form-->", $this->template->get_body_loader_form(), $skelet);
        $skelet = str_replace("<!--body_footer-->", $this->template->get_body_footer(), $skelet);
        $skelet = str_replace("<!--body_script-->", $this->template->get_body_script($this->context), $skelet);
        $skelet = str_replace("<!--body_items-->", $this->item_view($data), $skelet);
        echo $skelet;
    }
    public function outfield($t,$hclass,$mode='')
    {        
        $type = $t['name_type'];
        $result = "<div class=\"$hclass\">\n"
                . "<div class=\"form-group\">\n";
        if ($t['class']!='hidden') {
            $result .= "<label for=\"$t[id]\" class=\"control-label col-md-4\">$t[synonym]</label>\n";
        }   
        $result .= "<div class=\"col-md-8\">\n";
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
                $itype = 'text';
            }
            if ($t['class'] == 'hidden') {
                $itype = 'hidden';
            } elseif ($t['class'] == 'readonly') {
                $readonly = ' readonly';
            }
        }    
        if (($type == 'id')||($type == 'cid')||($type == 'mdid')) {
            $result .= "<input type=\"hidden\" class=\"form-control\" "
            . "id=\"$t[id]\" name=\"$t[id]\" it=\"$t[valmdid]\" "
                    . "vt=\"$type\" value=\"\" autocomplete=\"newvalue\">\n";
            $result .= "<input type=\"$itype\" class=\"form-control\" "
                    . "st=\"active\" id=\"name_$t[id]\" "
                    . "name=\"name_$t[id]\" it=\"$t[valmdid]\" "
                    . "vt=\"$type\" value=\"\"$readonly autocomplete=\"newvalue\">\n";
            if (($itype != 'hidden')||($readonly == '')) {
                $result .= "<ul class=\"types_list\">\n"
                         . "<li id=\"\"></li>\n"
                         . "</ul>\n";
            }    
        } else {
            if (($itype != 'hidden')||($readonly == '')) {
                if ($type == 'date') {
                    $result .= "<input type=\"$itype\" class=\"form-control datepicker\""
                            . " st=\"active\" id=\"$t[id]\" name=\"$t[id]\""
                            . " it=\"\" valid=\"\" vt=\"$type\" value=\"\"$readonly"
                            . " autocomplete=\"newvalue\">\n";
                } else {
                    $result .= "<input type=\"$itype\" class=\"form-control\""
                            . " st=\"active\" id=\"$t[id]\" name=\"$t[id]\""
                            . " it=\"\" valid=\"\" vt=\"$type\" value=\"\"$readonly"
                            . " autocomplete=\"newvalue\">\n";
                }    
                if ($type == 'bool') {    
                    $result .= "<ul class=\"types_list\">\n"
                        . "<li id=\"true\">true</li>\n"
                        . "<li id=\"false\">false</li>\n"
                        . "</ul>\n";
                }
            } else {
                $result .= "<input type=\"$itype\" class=\"form-control\" "
                        . "st=\"active\" id=\"$t[id]\" name=\"$t[id]\" valid=\"\""
                        . " vt=\"$type\" value=\"\"$readonly"
                        . " autocomplete=\"newvalue\">\n";
            }
        }
        $result .= "</div>\n"
            . "</div>\n"
            . "</div>\n";
        return $result;
    }    
    public function item_view($data) 
    {
        $show_tab = false;
        $show_head = false;
        $show_set = false;
        $show_tabheader = false;
        $key_set = '';
        if ((array_key_exists('PLIST', $data) !== false)&&(count($data['PLIST'])>0)) {
            $show_head = true;
        }  
        if ((array_key_exists('PSET', $data) === true)&&(count($data['PSET'])>0)) {
            $show_set = true;
        }   
        if (($show_head)&&(!$show_set)) {
            if (array_key_exists('SETS', $data) !== false) {
                if (count($data['SETS'])>0) {
                    if ((count($data['SETS']) == 1)&&
                    ($this->context['SETID'] !== '')) {
                        $key_set = array_search($this->context['SETID'], array_column($data['PLIST'],'id'));
                        $show_tabheader = true;
                    } else {
                        $show_tab = true;
                    }    
                }    
            }  
        }
        $result = '';
        if ($show_tab) {
            $result .= "<ul id=\"dcsTab\" class=\"nav nav-tabs\">\n";
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
                $result .= "<li$dop><a href=\"#entityhead\">Заголовок</a></li>\n";
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
                        $result .= "<li$dop><a href=\"#$t[id]\">$t[synonym]</a></li>\n";
                    }
                }    
            $result .= "</ul>\n"
                . "<div class=\"tab-content\">\n"
                . "<div id=\"entityhead\" class=\"tab-pane fade$dopfade\">\n";
        }   
        if ($show_head) {
            $result .= "<form class=\"form-inline\" role=\"form\" autocomplete=\"off\">\n";
            for($i=0, $props=$data['PLIST'], $size=count($props); $i<$size; $i++) {
                $t=$props[$i];
                if ($t['valmdtypename'] === 'Sets') {
                    continue;
                }    
                if ($t['rank'] == 0) {
                    continue;
                }    
                $type = $t['name_type'];
                if ($type=='text') {
                    $result .= "<div class=\"row\">\n"
                             . "<div class=\"col-md-12\">\n"      
                             . "<div class=\"form-group\">\n"
                             . "<label for=\"$t[id]\" class=\"control-label col-md-2\">$t[synonym]</label>\n"
                             . "<div class=\"col-md-10\">\n"
                             . "<textarea class=\"form-control\" rows=\"2\" st=\"active\" id=\"$t[id]\" name=\"$t[id]\" it=$type></textarea>\n"
                             . "</div>\n"
                             . "</div>\n"
                             . "</div>\n"
                             . "</div>\n";    
                } else {
                    if($t['rank']%2) {
                        $result .= "<div class=\"row\">\n";
                        $result .= $this->outfield($t,'col-md-6',$this->context['ACTION']);
                        if (($i+1) < $size) {
                            if (($props[$i+1]['rank']%2 == 0)&&($props[$i+1]['rank'] > 0)) {
                                $i++;
                                $t = $props[$i];
                                $type = $t['name_type'];
                                $result .= $this->outfield($t,'col-md-6',$this->context['ACTION']);
                            }
                        }
                        $result .= "</div>\n";
                    } else {
                        $result .= "<div class=\"row\">\n";
                        $result .= $this->outfield($t,'col-md-offset-6 col-md-6',$this->context['ACTION']);
                        $result .= "</div>\n";        
                    }
                }
            }
            $result .= "</form>\n";
        }    
        if ($show_set||$show_tabheader) {
            if ($show_set) {
                $result .= "<div id=\"entityset\">\n";
                $result .= $this->set_view($data['PSET']);
            } elseif ($show_tabheader) {
                $title = $data['PLIST'][$key_set]['synonym'];
                $result .= "<p class=\"dcs-tabtitle\">$title</p>\n"
                         . "<div id=\"".$this->context['SETID']."\">\n";
                $result .= $this->set_view($data['SETS'][$this->context['SETID']],'dcs-items');
            }
            $result .= "</div>\n";
        } elseif ($show_tab) {
            $result .= "</div>\n";
            if ($this->context['ACTION'] !== 'CREATE') {    
                for($i=0, $props = $data['PLIST'], $size = count($props); $i<$size; $i++) {
                    $t = $props[$i];
                    if ($t['valmdtypename'] !== 'Sets') {
                        continue;
                    }    
                    $dop='';
                    if (($propid !== '')&&($propid == $t['id'])) {
                        $dop=" active in";
                    }    
                    $result .= "<div id=\"$t[id]\" class=\"tab-pane fade$dop\">\n";
                    $result .= $this->set_view($data['SETS'][$t['id']],'dcs-items');
                    $result .= "</div>\n";
                }
            }    
            $result .= "</div>\n";
        }
        return $result;
    }    
    public function set_view($pset,$tbodyid='dcs-list')
    {
        $result = "<table class=\"table table-border table-hover\">\n"
                . "<thead  id=\"tablehead\"><tr>\n";
        foreach($pset as $key=>$val) {    
            $cls = $val['class'];
            if ($cls == '') {
                $cls = 'active';
            }    
            $result .= "<th class=\"$cls\" id=\"$key\">$val[synonym]</th>\n";
        }
        $result .= "</tr></thead>\n"
                 . "<tbody id=\"$tbodyid\" class=\"entitylist\"></tbody>\n"
                 . "</table>\n";
         return $result;
    }        
}
