<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Dcs\Vendor\Core\Models\DcsException;

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
    
    public function setnamesynonym($context)
    {
        $this->edate = $this->getdate();
        $this->enumber = $this->getnumber();
        $this->name = $this->head->getname();
        $this->synonym = $this->gettoString();
    }        
    public static function txtsql_access() 
    {
        return self::etxtsql_access('RoleAccess', 'mdid');
    }
    public function getaccessright_id()
    {
        return $this->mdid;
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
    function getactivity()
    {
        return $this->activity;
    }
    function head() 
    {
        return new EntitySet($this->mdid);
    }
    function item() 
    {
        return NULL;
    }
    public function gettoString() 
    {
        $artoStr = array();
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
        $isDocs = $this->mdtypename === 'Docs';
        if (count($artoStr)) {
            $res = '';
            foreach($artoStr as $prop => $rank) {
                if ($isDocs && ($this->properties[$prop]['isenumber'] ||
                                $this->properties[$prop]['isedate'])) {
                    continue;
                }    
                $name = $this->data[$prop]['name'];
                if ($this->properties[$prop]['name_type'] == 'date') {
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
            return $res;
        } else {
            return $this->name;
        }
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
    public function getdate(){
	$res=$this->edate;
        $key = array_search(TRUE, array_column($this->properties,'isedate','id'),TRUE);
        if ($key !== FALSE) {
            $res=$this->getattr($key);
        }
	return $res;
    }
    public function getnumber(){
	$res=0;
        $key = array_search(TRUE, array_column($this->properties,'isenumber','id'),TRUE);
        if ($key !== FALSE) {
            $res=$this->getattr($key);
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
                    $filter = $context;
                    $filter['DATA']['itemid'] =  array('id' => $dep_prop['valmdid'],'name' => '');
                    $filter['DATA']['curid'] = array('id'=>$this->id,'name'=>'');
                    if ($this->mdtypename == 'Items') {
                        //это строка тч - в фильтр передадим объект владелец ТЧ
                        $ar_obj = DataManager::get_obj_by_item($this->id);
                        if (count($ar_obj)>0) {
                            $filter['DATA']['docid'] = array('id'=>$ar_obj[0]['id'],'name'=>'');
                        }
                    }
                    $es = new EntitySet($dep_prop['valmdid']);
                    $ar_dep_data = $es->getItems($filter);
                    foreach ($ar_dep_data as $dep_entid => $obj) {
                        $res[$dep_pid] = array('value'=>$dep_entid,'id'=>$dep_entid,'type'=>$dep_prop['name_type'], 'name'=>$obj['name']);
                        break;
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
            elseif (($type == 'cid') || ($type == 'mdid')) {
                if (($valid != DCS_EMPTY_ENTITY) && ($valid != '')) {
                    $valname = $valid;
                } else {
                    continue;
                }
            } else {
                if ($type == 'bool') {
                    if ($valname == '') {
                        if (strtolower($prop['name_propid']) == 'activity') {
                            $valname='true';
                        }    
                    }    
                    if ($valname == 't') {
                         $valname = 'true';
                    }
                    if ($valname != 'true') {
                         $valname = 'false';
                    }
                 }
                elseif (($type == 'int') || ($type == 'float')) {
                    if ($valname == '') {
                        continue;
                    }    
                }
                
                if (!isset($valname)) {
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
        $set = new Sets($setid);
        return $set->getSetData();
    }
    public function getItemsByName($name) 
    {
        return NULL;
    }
    public function get_navlist($context)
    {
        $navlist = array();
        $strkey = 'new';
        $strval = 'Новый';
        if ($this->id) {
            $strkey = $this->id;
            $strval = sprintf("%s",$this);
        }    
        if ($context['PREFIX'] !== 'CONFIG') {
            if (isset($context['DATA']['setid'])) {
                $propid = $context['DATA']['setid']['id'];
                if ($propid !== '') {
                    $this->add_navlist($navlist);
                    $navlist[] = array('id'=>$this->id,'name' => sprintf("%s",$this));
                    $strkey .= "?propid=".$propid;
                    $strval = $this->properties[$propid]['synonym'];
                }    
            }
        }
        if (!count($navlist)) {    
            $this->add_navlist($navlist);
        }    
        $navlist[] = array('id' => $strkey,'name' => sprintf("%s",$strval));
        return $navlist;
    }        
}