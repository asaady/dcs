<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;

trait T_Item 
{
    function item($id='') 
    {
        return NULL;
    }
    public function getItemsByName($name) 
    {
        return NULL;
    }
    public function getItemsProp($context) 
    {
        return array();
    }        
    public function getItems($context) 
    {
        return array();
    }
    public function loadProperties()
    {
        return array();
    }        
    public function getNameFromData($context, $data='')
    {
        if (!count($this->plist)) {
            $this->plist = $this->getplist($context);
        }
        if (!$data) {
            if (!count($this->data)) {
                $this->load_data($context);
            }
            $data = $this->data;
        }
        $artoStr = array();
        $isDocs = $this->mdtypename === 'Docs';
        foreach($this->plist as $prop)
        {
            if (!array_key_exists($prop['id'],$data)) {
                continue;
            }
            if ($prop['ranktostring'] > 0) 
            {
              $artoStr[$prop['id']] = $prop['ranktostring'];
            }
        }
        if (!count($artoStr)) {
            return array('name' => '',
                     'synonym' => '');
        }    
        asort($artoStr);
        $res = '';
        foreach($artoStr as $pr => $rank) {
            $pkey = array_search($pr, array_column($this->plist,'id'));
            if ($isDocs && ($this->plist[$pkey]['isenumber'] ||
                            $this->plist[$pkey]['isedate'])) {
                continue;
            }    
            if (!array_key_exists($pr,$data)) {
                continue;
            }
            $name = $data[$pr]['name'];
            if ($this->plist[$pkey]['name_type'] == 'date') {
                $name = substr($name,0,10);
            }
            $res .= ' '.$name;
        }
        if ($isDocs) {
            $datetime = new DateTime($this->edate);
            $res = $this->head->getsynonym()." №".$this->enumber." от ".$datetime->format('d-m-y').$res;
        } elseif ($res != '') {
                $res = substr($res, 1);
        }    
        return array('name' => $res,
                     'synonym' => $res);
    }
    public function before_save($context,$data) 
    {
        $sql = '';
        $objs = array();
        if (!count($this->plist)) {
            $this->getplist($context);
        }
        if (!count($this->data)) {
            $this->load_data($context);
        }
        foreach ($this->plist as $prop)
        {    
            $propid = $prop['id'];
            if ($propid == 'id') {
                continue;
            }    
            if (!array_key_exists($propid, $data)) {        
                continue;
            }
            $nval = $data[$propid]['name'];
            $nvalid = $data[$propid]['id'];
            $pvalid = '';
            $pval = '';
            if (array_key_exists($propid, $this->data)) {        
                $pval = $this->data[$propid]['name'];
                $pvalid = $this->data[$propid]['id'];
            }
            if ($prop['name_type'] == 'id') {
                if ($pvalid == $nvalid) {
                    continue;
                }    
                if (($pvalid == DCS_EMPTY_ENTITY)&&($nvalid=='')) {
                    continue;
                }
            } elseif ($prop['name_type'] == 'date') {
                if (substr($pval,0,19) == substr($nval,0,19)) 
                {
                    continue;
                }    
            } 
            elseif ($prop['name_type']=='bool') 
            {
                if ((bool)$pval==(bool)$nval) 
                {
                    continue;
                }   
            } 
            else 
            {
                if ($pval==$nval) 
                {
                    continue;
                }    
            }
            $objs[]=array('id'=>$propid, 'name'=>$prop['name'],'pvalid'=>$pvalid, 'pval'=>$pval, 'nvalid'=>$nvalid, 'nval'=>$nval);
        }       
	return $objs;
    }
    public function load_data($context,$data='')
    {
        if (!count($this->plist)) {
            $this->getplist($context);
        }
        if (!$data) {
            foreach($this->plist as $prow) {
                $this->data[$prow['id']] = array('id'=>'','name'=>'');
            }    
            $artemptable = $this->get_tt_sql_data();
            $sql = "select * from tt_out";
            $sth = DataManager::dm_query($sql);        
            $arr_e = array();
            while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $this->data['id'] = array('id'=>$row['id'],'name'=>$row['id']);
                foreach($this->plist as $prow) {
                    $rowname = $this->rowname($prow);
                    if (array_key_exists('id_'.$rowname, $row)) {
                        $this->data[$prow['id']] = array(
                            'id'=>$row["id_$rowname"],
                            'name'=>$row["name_$rowname"]);
                        if ($prow['name_type'] === 'id') {
                            if ($prow['valmdtypename'] !== 'Sets') {    
                                if (($row["id_$rowname"])&&
                                    ($row["id_$rowname"] != DCS_EMPTY_ENTITY)) {
                                    if (!in_array($row["id_$rowname"],$arr_e)){
                                        $arr_e[] = $row["id_$rowname"];
                                    }
                                }    
                            }    
                        }
                    } elseif (array_key_exists($rowname, $row)) {
                        $this->data[$prow['id']] = array('id'=>'','name'=>$row[$rowname]);
                    } else {
                        $this->data[$prow['id']] = array('id'=>'','name'=>'');
                    }
                }    
            }
            if (count($arr_e)) {
                $this->fill_entname($this->data,$arr_e);
            }
            DataManager::droptemptable($artemptable);
        } else {
            $this->data['id'] = array('id'=>'','name'=>$data['id']);
            foreach($this->plist as $prow) {
                if (array_key_exists("name_".$prow['id'], $data)) {
                    $this->data[$prow['id']] = array(
                        'id'=>$data[$prow['id']],
                        'name'=>$data["name_".$prow['id']]);
                } elseif (array_key_exists($prow['id'], $data)) {
                    $this->data[$prow['id']] = array('id'=>'','name'=>$data[$prow['id']]);
                } else {
                    $this->data[$prow['id']] = array('id'=>'','name'=>'');
                }
            }    
        }
        $this->version = time();
        $this->head = $this->get_head();
        $this->setnamesynonym();
        $this->check_right($context);
        return $this->data;
    }            
//    public function before_save($context,$data) 
//    {
//        $this->load_data($context);
//        $objs = array();
//        foreach($this->plist as $row)
//        {    
//            $key = $row['name'];
//            $id = $row['id'];
//            $type = $row['name_type'];
//            if ($key=='id') 
//            {
//                continue;
//            }    
//            if (array_key_exists($id, $data))
//            {
//                $dataname = $data[$id]['name'];
//                $valname = $this->data[$id]['name'];
//                $dataid = $data[$id]['id'];
//                $valid = $this->data[$id]['id'];
//                if (($type=='id')||($type=='cid')||($type=='mdid')) 
//                {
//                    if ($dataid==$valid)
//                    {
//                        continue;
//                    }    
//                }    
//                else
//                {
//                    if ($dataname==$valname)
//                    {
//                        continue;
//                    }    
//                }    
//                $objs[]=array('name'=>$key, 'pval'=>$valname, 'nval'=>$dataname);
//            }    
//        }
// 	return $objs;
//    }
}

