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
    public function getItemsProp() 
    {
        return array();
    }        
    public function getItems($filter = array()) 
    {
        return array();
    }
    public function loadProperties()
    {
        return array();
    }        
    public function getNameFromData($data='')
    {
        if (!count($this->plist)) {
            $this->plist = $this->getplist();
        }
        if (!$data) {
            if (!count($this->data)) {
                $this->load_data();
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
    public function load_data($data='')
    {
        if (!count($this->plist)) {
            $this->getplist();
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
                    $rowname = Filter::rowname($prow['id']);
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
        $this->check_right();
        return $this->data;
    }            
    public function update_dependent_properties($data)
    {
        $res = array('status'=>'', 'id'=>$this->id, 'objs'=>array());
        $ar_propid = array_column($this->plist, 'propid','id');
        foreach ($data as $pid => $val) {
            $p_arr = array_filter($this->plist,function($item) use ($pid){
                            return $item['id'] === $pid;
                        });
            $prop = current($p_arr);
            if (!$prop) {
                continue;
            }
            $ar_rel = DataManager::get_related_fields($prop['propid']);
            foreach ($ar_rel as $rel) {
                $dep_pid = array_search($rel['depend'], $ar_propid);
                if ($dep_pid===FALSE) {
                    continue;
                }        
                //проверим найденный реквизит на свойство isdepend - зависимый
                $pdep_arr = array_filter($this->plist,function($item) use ($dep_pid){
                                return $item['id'] === $dep_pid;
                            });
                $dep_prop = current($pdep_arr);
                if (!$dep_prop) {
                    continue;
                }
                if ($dep_prop['isdepend']) {
                    $dep_mdentity = new Mdentity($dep_prop['valmdid']);
                    //получим текущее значение зависимого реквизита
                    $curval = $this->data[$dep_pid]['id'];
                    if (($curval != DCS_EMPTY_ENTITY)&&($curval != '')) {
                        $dep_ent = new Entity($curval);
                        $cur_val_dep_ent = '';
                        //текущее значение ведущего реквизита у найденного значения зависимого реквизита
                        if ($dep_mdentity->getmditemname() == 'Items') {
                            //это строка тч - получим объект владелeц этой ТЧ
                            // получим массив ид метаданных которые имеют у себя такую строку ТЧ 
                            $ar_obj = DataManager::get_obj_by_item($curval);
                            foreach ($ar_obj as $ent_parent) {
                                $cur_val_dep_ent = $ent_parent['id'];
                                break;
                            }
                        } else {
                            $arr_dep_ent_propid = array_column($dep_ent->getplist(),'propid','id');
                            $dep_ent_pid = array_search($prop['propid'],$arr_dep_ent_propid);
                            if ($dep_ent_pid === FALSE) {
                                //среди реквизитов зависимого объекта нет шаблона реквизита текущего объекта
                                continue;
                            }
                            $cur_val_dep_ent = $dep_ent->getattrid($dep_ent_pid);
                        }
                        if ($cur_val_dep_ent != $this->data[$pid]['id']) {
                            //значение не совпало - сбрасываем значение зависимого реквизита
                            $res[$dep_pid] = array('value'=>DCS_EMPTY_ENTITY,'type'=>$dep_prop['name_type'], 'name'=>'');
                        }
                    }    
                    
                    //попробуем найти объекты зависимого реквизита  - в надежде установить единственное значение
                    //НАДО ПЕРЕДЕЛЫВАТЬ!!!!
//                    $context = DcsContext::getcontext();
//                    $context->data_setattr('dcs_itemid', array('id' => $dep_prop['valmdid'],'name' => ''));
//                    $context->getattr('DATA')['dcs_curid'] = array('id'=>$this->id,'name'=>'');
//                    if ($this->mdtypename == 'Items') {
//                        //это строка тч - в фильтр передадим объект владелец ТЧ
//                        $ar_obj = DataManager::get_obj_by_item($this->id);
//                        if (count($ar_obj)>0) {
//                            $filter['DATA']['dcs_docid'] = array('id'=>$ar_obj[0]['id'],'name'=>'');
//                        }
//                    }
//                    $es = new EntitySet($dep_prop['valmdid']);
//                    $ar_dep_data = $es->getItems($filter);
//                    foreach ($ar_dep_data as $dep_entid => $obj) {
//                        $res[$dep_pid] = array('value'=>$dep_entid,'id'=>$dep_entid,'type'=>$dep_prop['name_type'], 'name'=>$obj['name']);
//                        break;
//                    }
//                    if (count($res) == 0) {
//                        $res[$dep_pid] = array('value'=>DCS_EMPTY_ENTITY,'type'=>$dep_prop['name_type'], 'name'=>'');                    
//                    }
                }
            }
        }    
        if (count($res) > 0) {
            $res = $this->update_properties($res,1);
        }
        return $res;
    }        
    public function update_properties($data,$n=0)     
    {
        $objs = $this->before_save($data);
        $id = $this->id;
        $vals = array();
        //первый проход дополним значениями зависимых реквизитов
	foreach($objs as $propval) {
            $propid = $propval['id'];
            if ($propid == 'id') {
                continue;
            }
            $p_arr = array_filter($this->plist,function($item) use ($propid){
                            return $item['id'] === $propid;
                        });
            $prow = current($p_arr);
            if (!$prow) {
                continue;
            }
            $type = $prow['name_type'];
            if ($type == 'id') {
                $n_name = '';
                $n_id = DCS_EMPTY_ENTITY;
                if (($propval['nvalid'] != DCS_EMPTY_ENTITY)&&($propval['nvalid'] != '')) {
                    $p_ent = new Entity($propval['nvalid']);
                    $n_name = $p_ent->getname();
                    $n_id = $propval['nvalid'];
                    //заполним пересекающиеся реквизиты ссылочного типа
                    $tpropid = $prow['propid'];
                    foreach($this->plist as $prop) {
                        if ($prop['name_type'] != 'id') {
                            continue;
                        }    
                        $ctpropid = $prop['propid'];
                        if ($ctpropid == $tpropid) {
                            continue;
                        }    
                        foreach($p_ent->plist as $e_prop) {
                            if ($e_prop['propid'] != $ctpropid) {
                                continue;
                            }    
                            $vals[$prop['id']] = array('id' => $p_ent->getattrid($e_prop['id']), 'name' => $p_ent->getattr($e_prop['id']));
                            break;
                        }    
                    }    
                }
                $vals[$propid]=array('id'=>$n_id,'name'=>$n_name);
            }    
            elseif ($type=='cid')
            {
                $p_ent = new CollectionItem($propval['nvalid']);
                $vals[$propid]=array('id'=>$propval['nvalid'],'name'=>$p_ent->getname());
            }    
            else
            {
                $vals[$propid]=array('id'=>'','name'=>$propval['nval']);
            }    
	}
        $objs = $this->before_save($vals);
	DataManager::dm_beginTransaction();
        $upd = array();
        $cnt = 0;
	foreach($objs as $propval){
            $propid = $propval['id'];
            if ($propid =='id')
            {
                continue;
            }
            $p_arr = array_filter($this->plist,function($item) use ($propid){
                            return $item['id'] === $propid;
                        });
            $prow = current($p_arr);
            if (!$prow) {
                continue;
            }
            $type = $prow['name_type'];
            $params = array();
            $params['userid'] = $_SESSION['user_id'];
            $params['id'] = $id;
            $params['propid'] = $propid;
	    $sql = "INSERT INTO \"IDTable\" (userid, entityid, propid) VALUES (:userid, :id, :propid) RETURNING \"id\"";
	    $res = DataManager::dm_query($sql,$params);
	    if(!$res) {
		DataManager::dm_rollback();
 		return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу IDTable запись ".$sql);
	    }
	    $row = $res->fetch(PDO::FETCH_ASSOC);
            $t_val = $propval['nval'];
            if (($type=='id')||($type=='cid')) {
                $t_val = $propval['nvalid'];
                if ($t_val == '') {
                    $t_val = DCS_EMPTY_ENTITY;
                }    
            }    
	    $sql = "INSERT INTO \"PropValue_$type\" (id, value) VALUES ( :id, :value)";
            $params = array();
            if ($type=='file')
            {
                $t_val = str_replace(" ","_",trim($this->name))."_".$prow['name'].strrchr($t_val,'.');
            }    
            $params['value'] = $t_val;
            $params['id'] = $row['id'];
	    $res = DataManager::dm_query($sql,$params);
	    if(!$res) {
                DataManager::dm_rollback();
                return array(
                'status'=>'ERROR',
                'msg'=>"Невозможно добавить в таблицу PropValue_$type "
                        . "запись ".$sql
                );
	    }
            $cnt++;
            $upd[$propid] = array('value'=>$t_val,'type'=>$type, 'name'=>$vals[$propid]['name']);
	}
        if ($cnt > 0)
        {    
            DataManager::dm_commit();	
            $status = 'OK';
        }
        else
        {
            DataManager::dm_rollback();
            $status = 'NONE';
        }    
        return array('status'=>$status, 'id'=>$this->id, 'objs'=>$upd);
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

