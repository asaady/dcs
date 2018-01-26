<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

class Entity extends Sheet implements I_Sheet, I_Property 
{
    use T_Sheet;
    use T_Entity;
    use T_Item;
    use T_Property;
    use T_EProperty;
    
    protected $activity;
    protected $edate;
    protected $enumber;
    protected $num;
    
    public function __construct($id)
    {
        parent::__construct($id);
        $this->edate = $this->getpropdate();
        $this->enumber = $this->getpropnumber();
        $this->synonym = $this->name;
        $this->name = $this->gettoString();
        $prop_activity = array_search("activity", 
                                array_column($this->properties,'name','id'));
        if ($prop_activity !== FALSE)
        {    
            $this->activity = $this->getattr($prop_activity); 
        }
        if ($this->activity !== FALSE)
        {
            $this->activity = TRUE;
        }
    }
    public static function txtsql_forDetails() 
    {
        return "select et.id, '' as name, '' as synonym, 
                    et.mdid , md.name as mdname, md.synonym as mdsynonym, 
                    md.mditem, tp.name as mdtypename, tp.synonym as mdtypedescription 
                    FROM \"ETable\" as et
                        INNER JOIN \"MDTable\" as md
                            INNER JOIN \"CTable\" as tp
                            ON md.mditem = tp.id
                        ON et.mdid = md.id 
                    WHERE et.id = :id";  
    }
    function head() 
    {
        return new EntitySet($this->mdid);
    }
    function item() 
    {
        return NULL;
    }
    function __toString() 
    {
      return $this->name;
    }
    public function gettoString() 
    {
        $artoStr = array();
        if ($this->head->get_head()->getname() == 'Sets') {
            return $this->name;
        }
        foreach($this->properties as $prop)
        {
            if ($prop['ranktostring'] > 0) 
            {
              $artoStr[$prop['id']] = $prop['ranktostring'];
            }
        }
        if (!count($artoStr)) {
            foreach($this->properties as $prop) {
                if ($prop['rank'] > 0) {
                  $artoStr[$prop['id']] = $prop['rank'];
                }  
            }
            if (count($artoStr)) {
              asort($artoStr);
              array_splice($artoStr,1);
            }  
        } else {
            asort($artoStr);
        }
        $isDocs = $this->head->get_head()->getname() === 'Docs';
        if (count($artoStr)) {
            $res = '';
            foreach($artoStr as $prop => $rank)
            {
                if ($isDocs)
                {
                    if ($this->properties[$prop]['isenumber'])
                    {
                        continue;
                    }    
                    if ($this->properties[$prop]['isedate'])
                    {
                        continue;
                    }    
                }    
                $name = $this->data[$prop]['name'];
                if ($this->properties[$prop]['name_type'] == 'date')
                {
                    $name =substr($name,0,10);
                }
                $res .=' '.$name;
            }
            if ($isDocs)
            {
                $datetime = new DateTime($this->edate);
                $res = $this->head->getsynonym()." №".$this->enumber." от ".$datetime->format('d-m-y').$res;
            }
            else    
            {
                if ($res!='')
                {
                    $res = substr($res, 1);
                }    
            }    
            return $res;
        }
        else 
        {
            return $this->name;
        }
    }
    function getactivity()
    {
        return $this->activity;
    }
    //ret: array temp table names 
    public function get_tt_sql_data()
    {
        $artemptable = array();
        $sql = "SELECT max(it.dateupdate) AS dateupdate, it.entityid, it.propid "
                . "FROM \"IDTable\" as it "
                . "INNER JOIN \"MDProperties\" as mp "
                . "ON it.propid = mp.id AND mp.mdid = :mdid "
                . "WHERE it.entityid = :id "
                . "GROUP BY it.entityid, it.propid";
        $artemptable[] = DataManager::createtemptable($sql,'tt_id',
                array('mdid'=>$this->mdid,'id'=>$this->id));   
        $sql = "SELECT t.id as tid, t.userid, 
                ts.dateupdate, ts.entityid, ts.propid
		FROM \"IDTable\" AS t 
		INNER JOIN tt_id AS ts
                ON t.entityid=ts.entityid
		AND t.propid = ts.propid
		AND t.dateupdate=ts.dateupdate";
        $artemptable[] = DataManager::createtemptable($sql,'tt_tv');   
        $str0_req='SELECT et.id';
        $str_req='';
        $str_p = '';
        foreach($this->properties as $row) 
        {
            if ($row['id'] == 'id') {
                continue;
            }
            if ($row['field'] == 0) {
                continue;
            }
            $rid = $row['id'];
            $rowname = $this->rowname($row);
            $rowtype = $row['name_type'];
            $str0_t = ", tv_$rowname.propid as propid_$rowname, pv_$rowname.value as name_$rowname, '' as id_$rowname";
            $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            if ($rowtype=='id') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, '' as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            } elseif ($rowtype=='cid') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, ct_$rowname.synonym as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value=ct_$rowname.id ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            } elseif ($rowtype=='mdid') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, ct_$rowname.synonym as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value=ct_$rowname.id ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            } elseif ($rowtype=='date') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, to_char(pv_$rowname.value,'DD.MM.YYYY') as name_$rowname, '' as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$rowtype\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            }
            $str0_req .= $str0_t;
            $str_req .=$str_t;
        }
        $str0_req .=" FROM \"ETable\" as et";
        $sql = $str0_req.$str_req." WHERE et.id=:id";
        //die($sql.' id ='.$this->id);
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',array('id'=>$this->id));   
        return $artemptable;
    }    
    public static function get_set_by_item($itemid)
    {
        $sql = "SELECT parentid, childid, rank FROM \"SetDepList\" "
                . "where childid = :itemid";
        $res = DataManager::dm_query($sql,array('itemid'=>$itemid));
        if(!$res) {
            return DCS_EMPTY_ENTITY;
        }
        $row = $res->fetch(PDO::FETCH_ASSOC);
        if(!count($res)) 
        {
            return DCS_EMPTY_ENTITY;
        }
        return $row['parentid'];
    }
    public static function get_entity_by_setid($setid)
    {
        $sql = "SELECT it.entityid, max(it.dateupdate) from \"PropValue_id\" as pv "
                . "inner join \"IDTable\" as it on pv.id=it.id "
                . "where pv.value=:setid "
                . "group by it.entityid";

        $res = DataManager::dm_query($sql,array('setid'=>$setid));
        $row = $res->fetch(PDO::FETCH_ASSOC);
        if(!count($row)) 
        {
            return DCS_EMPTY_ENTITY;
        }
        return $row['entityid'];
    }
    public function getpropdate(){
	$res=$this->edate;
        foreach ($this->properties as $prow)
        {    
            if (($prow['isedate']==true)||($prow['isedate']=='t')||($prow['isedate']=='true')) 
            {
              $res=$this->data[$prow['id']]['name'];
              break;
            }  
	}
	return $res;
    }
    public function getpropnumber(){
	$res=0;
        foreach ($this->properties as $prow)
        {    
            if (($prow['isenumber']==true)||($prow['isenumber']=='t')||($prow['isenumber']=='true')) 
            {
              $res=$this->data[$prow['id']]['name'];
              break;
            }  
	}
	return $res;
    }
    public function update_dependent_properties($data)
    {
        $res = array();
        $ar_propid = array_column($this->properties(), 'propid','id');
        foreach ($data as $pid => $val) {
            $prop = $this->properties[$pid];
            $ar_rel = DataManager::get_related_fields($prop['propid']);
            foreach ($ar_rel as $rel) {
                $dep_pid = array_search($rel['depend'], $ar_propid);
                if ($dep_pid===FALSE) {
                    continue;
                }        
                //проверим найденный реквизит на свойство isdepend - зависимый
                $dep_prop = $this->properties[$dep_pid];
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
                            $arr_dep_ent_propid = array_column($dep_ent->properties,'propid','id');
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
                    $filter = array();
                    $filter['itemid'] =  array('id' => $dep_prop['valmdid'],'name' => '');
                    $filter['curid'] = array('id'=>$this->id,'name'=>'');
                    if ($this->getmdentity()->getmditemname() == 'Items') {
                        //это строка тч - в фильтр передадим объект владелец ТЧ
                        $ar_obj = DataManager::get_obj_by_item($this->id);
                        if (count($ar_obj)>0) {
                            $filter['docid'] = array('id'=>$ar_obj[0]['id'],'name'=>'');
                        }
                    }
                    $filter['filter_id']= array('id'=>'','name'=>'');
                    $filter['filter_val']= array('id'=>'','name'=>'');
                    $filter['filter_min']= array('id'=>'','name'=>'');
                    $filter['filter_max']= array('id'=>'','name'=>'');
//                    die(var_dump($data)." filter ". var_dump($filter));
                    $ar_dep_data = EntitySet::getEntitiesByFilter($filter,'ENTERPISE','VIEW');
                    if (count($ar_dep_data['LDATA']) > 0) {
                        foreach ($ar_dep_data['LDATA'] as $dep_entid => $obj) {
                            asort($ar_dep_data['LNAME'][$dep_entid]);
                            $fname = '';
                            foreach ($ar_dep_data['LNAME'][$dep_entid] as $rank => $cname) {
                                $fname .= ' '.trim($cname);
                            }
                            if ($fname != '') {
                                $fname = trim($fname);
                            }
                            $res[$dep_pid] = array('value'=>$dep_entid,'id'=>$dep_entid,'type'=>$dep_prop['name_type'], 'name'=>$fname);
                            break;
                        }
                    }
                    if (count($res) == 0) {
                        $res[$dep_pid] = array('value'=>DCS_EMPTY_ENTITY,'type'=>$dep_prop['name_type'], 'name'=>'');                    
                    }
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
            if(!array_key_exists($propid,$this->properties)) {
                continue;
            }
            $type = $this->properties[$propid]['name_type'];
            if ($type == 'id') {
                $n_name = '';
                $n_id = DCS_EMPTY_ENTITY;
                if (($propval['nvalid'] != DCS_EMPTY_ENTITY)&&($propval['nvalid'] != '')) {
                    $p_ent = new Entity($propval['nvalid']);
                    $n_name = $p_ent->getname();
                    $n_id = $propval['nvalid'];
                    //заполним пересекающиеся реквизиты ссылочного типа
                    $tpropid = $this->properties[$propid]['propid'];
                    foreach($this->properties as $prop) {
                        if ($prop['name_type'] != 'id') {
                            continue;
                        }    
                        $ctpropid = $prop['propid'];
                        if ($ctpropid == $tpropid) {
                            continue;
                        }    
                        foreach($p_ent->properties as $e_prop) {
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
	$res = DataManager::dm_query("BEGIN");
        $upd = array();
	foreach($objs as $propval){
            $propid = $propval['id'];
            if ($propid =='id')
            {
                continue;
            }
            if(!array_key_exists($propid,$this->properties))
            {
                continue;
            }
            $type = $this->properties[$propid]['name_type'];
            $params = array();
            $params['userid'] = $_SESSION['user_id'];
            $params['id'] = $id;
            $params['propid'] = $propid;
	    $sql = "INSERT INTO \"IDTable\" (userid, entityid, propid) VALUES (:userid, :id, :propid) RETURNING \"id\"";
	    $res = DataManager::dm_query($sql,$params);
	    if(!$res) {
		$res = DataManager::dm_query("ROLLBACK");
 		return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу IDTable запись ".$sql);
	    }
	    $row = $res->fetch(PDO::FETCH_ASSOC);
            $t_val = $propval['nval'];
            if (($type=='id')||($type=='cid'))
            {
                $t_val = $propval['nvalid'];
                if ($t_val == '')
                {
                    $t_val = DCS_EMPTY_ENTITY;
                }    
            }    
	    $sql = "INSERT INTO \"PropValue_$type\" (id, value) VALUES ( :id, :value)";
            $params = array();
            if ($type=='file')
            {
                $t_val = str_replace(" ","_",trim($this->name))."_".$this->properties[$propid]['name'].strrchr($t_val,'.');
            }    
            $params['value'] = $t_val;
            $params['id'] = $row['id'];
	    $res = DataManager::dm_query($sql,$params);
	    if(!$res) {
                $res = DataManager::dm_query("ROLLBACK");
                return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу PropValue_$type запись ".$sql);
	    }
            $cnt++;
            $upd[$propid] = array('value'=>$t_val,'type'=>$type, 'name'=>$vals[$propid]['name']);
	}
        if ($cnt>0)
        {    
            $res = DataManager::dm_query("COMMIT");	
            $status = 'OK';
        }
        else
        {
            $res = DataManager::dm_query("ROLLBACK");
            $status = 'NONE';
        }    
        return array('status'=>$status, 'id'=>$this->id, 'objs'=>$upd);
    }
    function delete() 
    {
	$res = DataManager::dm_query("BEGIN");
        $id = $this->id;
        $propid = array_search('activity', array_column($this->properties,'name','id'));
        if ($propid!==FALSE)
        {
            $params = array();
            $params['userid']=$_SESSION['user_id'];
            $params['id']=$id;
            $params['propid']=$propid;
	    $sql = "INSERT INTO \"IDTable\" (userid, entityid, propid) VALUES (:userid, :id, :propid) RETURNING \"id\"";
	    $res = DataManager::dm_query($sql,$params);
	    if(!$res) {
		$res = DataManager::dm_query("ROLLBACK");
 		return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу IDTable запись ".$sql);
	    }
	    $row = $res->fetch(PDO::FETCH_ASSOC);
	    $sql = "INSERT INTO \"PropValue_bool\" (id, value) VALUES ( :id, :value)";
            $params = array();
            $params['value']='true';
            if ($this->activity)
            {    
                $params['value']='false';
            }    
            $params['id']=$row['id'];
	    $res = DataManager::dm_query($sql,$params);
	    if(!$res) {
                $res = DataManager::dm_query("ROLLBACK");
                return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу PropValue_bool запись ".$sql);
	    }
            $res = DataManager::dm_query("COMMIT");	
            return array('status'=>'OK', 'id'=>$this->id);
        }    
        else
        {
            $res = DataManager::dm_query("ROLLBACK");
            return array('status'=>'NONE','msg'=>"Нет измененных записей ");
        }    
    }    
    public function save_new()
    {
	if ($this->id!=''){
            return array('status'=>'ERROR','msg'=>'this in not new object');
        }    
        if ($this->getname()==''){
            return array('status'=>'ERROR','msg'=>'name is empty');
        }    
        $edate = $this->edate;
        $enumber = $this->enumber;
        if ($this->head->getmditemname()=='Docs')
        {
            if ($edate=='') {
                return array('status'=>'ERROR','msg'=>'date is empty');
            }
        }    
	$res = DataManager::dm_query("BEGIN");
        $mdid = $this->head->getid();
        $sql = "INSERT INTO \"ETable\" (mdid) VALUES (:mdid) RETURNING \"id\"";
        $res=DataManager::dm_query($sql,array('mdid'=>$mdid));
        if(!$res) {
            return array('status'=>'ERROR','msg'=>'Невозможно добавить в таблицу запись '.$sql);
        }    
        $row = $res->fetch(PDO::FETCH_ASSOC);
        $this->id = $row['id'];
        $id = $this->id;
        foreach($this->properties as $prop)
        {
            $propid = $prop['id'];
            if ($propid=='id') continue;
            $type = $prop['name_type'];
            $valname = $this->data[$propid]['name'];
            $valid  = $this->data[$propid]['id'];
            if ($prop['isenumber'])
            {
                if ($valname=='')
                {
                    $valname = DataManager::getNumber($prop['id'])+1;
                }    
            }
            if ($type=='id')
            {
                if (($valid!=DCS_EMPTY_ENTITY)&&($valid!=''))  
                {
                    $curmd=self::getEntityDetails($valid);
                    if (($curmd['mdtypename']=='Sets') || ($curmd['mdtypename']=='Items'))
                    {
                        if ($this->id!='')
                        {
                            $tablename = "PropValue_id";
                            $filter = "value=:valid";
                            $params = array('value'=>$valid);
                            $res = DataManager::FindRecord($tablename,$filter,$params);
                            if (count($res))
                            {
                                continue;
                            }
                        }
                    }
                    $valname = $valid;
                }
                else 
                {
                    continue;
                }
            }
            elseif (($type=='cid')||($type=='mdid'))
            {
                if (($valid!=DCS_EMPTY_ENTITY)&&($valid!=''))  
                {
                    $valname = $valid;
                }
                else 
                {
                    continue;
                }
            }    
            else 
            {
                if ($type=='bool')
                {
                    if ($valname=='') 
                    {
                        if (strtolower($prop['name_propid'])=='activity')
                        {
                            $valname='true';
                        }    
                    }    
                    if ($valname=='t')
                     {
                         $valname = 'true';
                     }
                     if ($valname!='true')
                     {
                         $valname ='false';
                     }
                 }
                elseif (($type=='int')||($type=='float'))
                {
                    if ($valname=='') 
                    {
                        continue;
                    }    
                }
                
                if (!isset($valname)) 
                {
                    continue;
                }    
            }
            $sql = "INSERT INTO \"IDTable\" (userid, entityid, propid) VALUES (:userid, :id, :propid) RETURNING \"id\"";
            $params = array();
            $params['userid']=$_SESSION['user_id'];
            $params['id']=$id;
            $params['propid']=$propid;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) {
                $res = DataManager::dm_query("ROLLBACK");
                return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу IDTable запись ".$sql);
            }
            $row = $res->fetch(PDO::FETCH_ASSOC);
            $sql = "INSERT INTO \"PropValue_$type\" (id, value) VALUES ( :id, :value)";
            $params = array();
            $params['id']=$row['id'];
            $params['value']=$valname;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) {
                $res = DataManager::dm_query("ROLLBACK");
                return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу PropValue_$type запись ".$sql);
            }
	}
	$res = DataManager::dm_query("COMMIT");
        
        return array('status'=>'OK', 'id'=>$this->id);
    }
    public function createtemptable_allprop($entities)
    {
	$artemptable=array();
        $artemptable[] = $this->get_EntitiesFromList($entities,'tt_et');   
        $this->createtemptable_all('tt_et',$artemptable);
        
        return $artemptable;    
    }
    function getSetData()
    {
	$objs = array();
	$pset = $this->getProperties(true,'toset');
        if ($this->id == '')
        {
            return $objs;
        }    
        $artemptable=array();
        $entityid = $this->id;
	$sql = "SELECT it.childid as rowid, it.rank as rownum  FROM \"SetDepList\" as it WHERE it.parentid=:entityid and it.rank > 0";
        $artemptable[]= DataManager::createtemptable($sql, 'tt_it',array('entityid'=>$entityid));
        
        
        $sql = "SELECT et.id, et.name, et.mdid, it.rownum  FROM \"ETable\" as et INNER JOIN tt_it as it ON et.id=it.rowid";
        $artemptable[]= DataManager::createtemptable($sql, 'tt_et');
        
        $sql = "SELECT max(it.dateupdate) AS dateupdate, it.entityid, it.propid  FROM \"IDTable\" as it inner join tt_et AS et on it.entityid=et.id GROUP BY it.entityid, it.propid";
        $artemptable[]= DataManager::createtemptable($sql, 'tt_id');

	$sql = "SELECT 	t.id as tid, 
			t.userid,  
			ts.dateupdate,
			ts.entityid,
			it.rownum,
			ts.propid as id,
			pv_str.value as str_value, 
			pv_int.value as int_value, 
			pv_id.value as id_value, 
			ve.name as id_valuename, 
			pv_cid.value as cid_value, 
			ce.synonym as cid_valuename, 
			pv_date.value as date_value, 
			pv_float.value as float_value, 
			pv_bool.value as bool_value, 
			pv_text.value as text_value, 
			pv_file.value as file_value, 
			mp.synonym,
			cv_name.name as name_type,
			mp.ranktostring,
			mp.isedate,
			mp.isenumber,
			mp.rank
		FROM \"IDTable\" AS t 
		INNER JOIN tt_id AS ts
                    INNER JOIN tt_it as it
                    ON ts.entityid=it.rowid
		ON t.entityid=ts.entityid
		AND t.propid = ts.propid
		AND t.dateupdate=ts.dateupdate
		INNER JOIN \"MDProperties\" as mp
		    INNER JOIN \"CTable\" as pt
                        INNER JOIN \"CPropValue_cid\" as ct
                            INNER JOIN \"CProperties\" as cp
                            ON ct.pid=cp.id
                            AND cp.name='type'
                            INNER JOIN \"CTable\" as cv_name
                            ON ct.value = cv_name.id
                        ON pt.id=ct.id
		    ON mp.propid=pt.id
		ON t.propid=mp.id
                and mp.ranktoset>0
		LEFT JOIN \"PropValue_str\" AS pv_str
		ON t.id = pv_str.id	
		LEFT JOIN \"PropValue_id\" AS pv_id
		  INNER JOIN \"ETable\" as ve
		  ON pv_id.value=ve.id
		ON t.id = pv_id.id	
		LEFT JOIN \"PropValue_cid\" AS pv_cid
		  INNER JOIN \"CTable\" as ce
		  ON pv_cid.value=ce.id
		ON t.id = pv_id.id	
		LEFT JOIN \"PropValue_int\" AS pv_int
		ON t.id = pv_int.id	
		LEFT JOIN \"PropValue_date\" AS pv_date
		ON t.id = pv_date.id	
		LEFT JOIN \"PropValue_bool\" AS pv_bool
		ON t.id = pv_bool.id	
		LEFT JOIN \"PropValue_text\" AS pv_text
		ON t.id = pv_text.id	
		LEFT JOIN \"PropValue_float\" AS pv_float
		ON t.id = pv_float.id 
		LEFT JOIN \"PropValue_file\" AS pv_file
		ON t.id = pv_file.id";
        
        $artemptable[]= DataManager::createtemptable($sql, 'tt_lv');
        $rank_id = array_search('rank', array_column($pset,'name','id'));
        if ($rank_id!==FALSE)
        {
            $sql = "SELECT et.id, COALESCE(lv.int_value,999) as rank FROM tt_et AS et left join tt_lv as lv on et.id=lv.entityid and lv.id=:rankid order by rank"; 
            $params=array('rankid'=>$rank_id);
        }    
        else 
        {
            $sql = "SELECT et.id FROM tt_et AS et"; 
            $params='';
        }
        $res = DataManager::dm_query($sql, $params);
        $sobjs=array();
        $sobjs['rows']=$res->fetchAll(PDO::FETCH_ASSOC);
        
        $sql = "SELECT * FROM tt_lv"; 
	$res = DataManager::dm_query($sql);
        $activity_id = array_search('activity', array_column($pset,'name','id'));
        $arr_e=array();
        foreach ($sobjs['rows'] as $row)
        {
            $objs[$row['id']]=array();
            foreach ($pset as $prop)
            {
                $objs[$row['id']][$prop['id']]= array('id'=>'', 'name'=>'');
                if ($activity_id!==FALSE)
                {
                    if ($prop['id']==$activity_id)
                    {
                        $objs[$row['id']]['class']= 'active';
                    }    
                }    
            }
        }
        $destPath = $_SERVER['DOCUMENT_ROOT'] . '/upload/';
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $rowtype = $row['name_type'];
            if (($rowtype=='id')||($rowtype=='cid'))
            {
                $objs[$row['entityid']][$row['id']] = array(
                       'id'=>$row[$rowtype.'_value'],
                       'name'=>$row['id_valuename']
                  );
                if ($rowtype == 'id')
                {    
                    if (($row['id_value'])&&($row['id_value']!=DCS_EMPTY_ENTITY)) {        
                        if (!in_array($row['id_value'],$arr_e)) {
                            $arr_e[] = $row['id_value'];
                        }
                    }    
                }    
            } else {
                if ($rowtype == 'file')
                {
                    $name = $row[$rowtype.'_value'];
                    $ext = strrchr($name,'.');
                    
                    $curm = date("Ym",strtotime($row['dateupdate']));
                    $objs[$row['entityid']][$row['id']] = array(
                      'name'=>$name,
                      'id'=>"/download/".$row['entityid']."?propid=".$row['id']
                    );
                }   
                else
                {
                    $objs[$row['entityid']][$row['id']] = array(
                      'name'=>$row[$rowtype.'_value'],
                      'id'=>''
                      );
                }    
                if ($activity_id !== FALSE)
                {
                    if ($row['id'] == $activity_id)
                    {
                        if ($row['bool_value'] === FALSE)
                        {    
                            $objs[$row['entityid']]['class']= 'erased';
                        }    
                    }    
                }    
            }

        }
        if (count($arr_e))
        {
            $this->fill_entsetname($objs,$arr_e);
        }    
        
	DataManager::droptemptable($artemptable);
	
	return $objs;
    }    
    public static function CopyEntity($id,$user)
    {
        $arEntity = self::getEntityDetails($id);
        $arnewid = self::saveNewEntity($arEntity['name'],$arEntity['mdid'], DCS_EMPTY_DATE);
        $arData = self::getEntityData($id);
        foreach($arData as $prop)
        {
            self::CopyEntityProp($arnewid['id'], $prop, $user);
        }
        return $arnewid['id'];
    }
    public static function CopyEntityProp($id, $prop, $user) 
    {
            $propid = $prop['id'];
            $type = $prop['name_type'];
            $val = $prop[$type.'_value'];
            $sql = "INSERT INTO \"IDTable\" (userid,entityid, propid) VALUES ('$user', '$id', '$propid) RETURNING \"id\"";
            $res = pg_query(self::_getConnection(), $sql);
            if(!$res) 
            {
              $sql_rb = "ROLLBACK";
              $res = pg_query(self::_getConnection(), $sql_rb);
              die("Невозможно добавить в таблицу IDTable запись ".$sql);
            }
            $row = pg_fetch_assoc($res);
            $sql = "INSERT INTO \"PropValue_{$type}\" (id, value) VALUES ('{$row['id']}','$val')";
            $res = pg_query(self::_getConnection(), $sql);
            if(!$res) 
            {
              $sql_rb = "ROLLBACK";
              $res = pg_query(self::_getConnection(), $sql_rb);
              die("Невозможно добавить в таблицу PropValue_{$type} запись ".$sql);
            }

    }	
    public function createItem($name,$prefix='')
    {
        $arSetItemProp = self::getMDSetItem($this->mdentity->getid());
        $mdid = $arSetItemProp['valmdid'];
        $objs = array();
        $objs['PSET'] = $this->getProperties(true,'toset');
        $sql = "INSERT INTO \"ETable\" (mdid, name) VALUES (:mdid, :name) RETURNING \"id\"";
        $params = array();
        $params['mdid']=$this->head->getid;
        $params['name']= str_replace('Set','Item', $name);
        $res = DataManager::dm_query($sql,$params); 
        if ($res)
        {
            $row = $res->fetch(PDO::FETCH_ASSOC);
            $childid = $row['id'];
            
            $rank = DataManager::saveItemToSetDepList($this->id,$childid);
            if ($rank>=0)
            {    
                $item = new Entity($childid);
                $arPropsUse = self::getPropsUse($item->head->getmditem());
                $irank=0;
                foreach ($arPropsUse as $prop)
                {
                    $irank++;
                    $row = $this->isExistTheProp($prop['propid']);
                    if (!$row)
                    {    
                        $data = array();
                        $data['name'] = $prop['name'];
                        $data['synonym'] = $prop['synonym'];
                        $data['mdid']=$item->mdentity->getid();
                        $data['rank']=$irank;
                        $data['ranktoset']=$irank;
                        $data['ranktostring']=$irank;
                        if (isset($prop['length']))
                        {
                            $data['length'] = $prop['length'];
                        }   
                        if (isset($prop['prec']))
                        {
                            $data['prec'] = $prop['prec'];
                        }   
                        $data['pid'] = $prop['propid'];
                        if ($prop['name_type']=='date')
                        {    
                            $data['isedate']='true';
                        }
                        $row = $this->createProperty($data);
                    }    
                    if ($row)
                    {
                        if (strtolower($prop['name'])==='rank')
                        {
                            $sql="INSERT INTO \"IDTable\" (entityid, propid, userid) VALUES (:entityid, :propid, :userid) RETURNING \"id\"";
                            $params = array();
                            $params['entityid']=$childid;
                            $params['propid']=$row['id'];
                            $params['userid']=$_SESSION["user_id"];
                            $res = DataManager::dm_query($sql,$params); 
                            $rowid = $res->fetch(PDO::FETCH_ASSOC);
                            if ($rowid)
                            {
                                $sql="INSERT INTO \"PropValue_int\" (id, value) VALUES (:id, :value)";
                                $params = array();
                                $params['id']=$rowid['id'];
                                $params['value']=$rank;
                                $res = DataManager::dm_query($sql,$params); 
                                $rowid = $res->fetch(PDO::FETCH_ASSOC);
                            }    
                        }    
                    }    
                }    
            }    
        }    
    }        
    public function getItems($context) 
    {
        if ($context['SETID'] === '') {
            if ((!isset($context['DATA']['setid']))||(!isset($context['DATA']['propid']))) {
                return array();
            }    
            $propid = $context['DATA']['setid']['id'];
            if ($propid == '') {
                $propid = $context['DATA']['propid']['id'];
                if ($propid == '') {
                    return array();
                }
            }    
        } else {
            $propid = $context['SETID'];
        }
        $setid = $this->getattrid($propid);
        
        if ((!$setid)||($setid === DCS_EMPTY_ENTITY)) {
            return array();
        }    
        $set = new Entity($setid);
        return $set->getSetData();
    }
    public function getItemsByName($name) 
    {
        return NULL;
    }
}