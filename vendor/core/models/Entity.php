<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Dcs\Vendor\Core\Models\DcsException;

class Entity extends Sheet implements I_Sheet, I_Item 
{
    use T_Sheet;
    use T_Entity;
    use T_Item;
    
    protected $activity;
    protected $edate;
    protected $enumber;
    protected $num;
    
    public function getplist($context)
    {
        $sql = $this->txtsql_properties("mdid");
        $properties = array();
        $params = array('mdid'=> $this->mdid);
        $res = DataManager::dm_query($sql,$params);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $properties[] = $row;
        }  
        $this->plist = $properties;
        return $properties;
    }        
    public function setnamesynonym()
    {
        $this->edate = $this->getdate();
        $this->enumber = $this->getnumber();
    }        
    public static function txtsql_access() 
    {
        return self::etxtsql_access('RoleAccess', 'mdid');
    }
    public function getaccessright_id()
    {
        return $this->mdid;
    }        
    public function getArrayNew($newobj)
    {
        return array('id' => $newobj['id'], 
                    'name' => '_new_',
                    'synonym' => 'Новый',
                    'mdid' => $newobj['headid'],
                    'mdname' => $newobj['classname'],
                    'mdsynonym' => '',
                    'mditem' => '',
                    'mdtypename' => '',
                    'mdtypedescription' => '');
    }        
    function getactivity()
    {
        return $this->activity;
    }
    function head() 
    {
        return new EntitySet($this->mdid);
    }
    public function getprop_classname()
    {
        return 'EProperty';
    }
    public function item_classname()
    {
        return NULL;
    }        
    public function getdate(){
	$res=$this->edate;
        $key = array_search(TRUE, array_column($this->plist,'isedate','id'),TRUE);
        if ($key !== FALSE) {
            $res=$this->getattr($key);
        }
	return $res;
    }
    public function getnumber(){
	$res=0;
        $key = array_search(TRUE, array_column($this->plist,'isenumber','id'),TRUE);
        if ($key !== FALSE) {
            $res=$this->getattr($key);
        }
	return $res;
    }
    public function getsets($context) 
    {
        if (!count($this->plist)) {
            $this->plist = $this->getplist($context);
        }
        $psets = array_filter($this->plist,function($item){
                            return $item['valmdtypename'] === 'Sets';
                        });
        if (!count($psets)) {
            return array();
        }
        $propid = '';
        if (isset($context['DATA']['dcs_propid'])) {
            $propid = $context['DATA']['dcs_propid']['id'];
        } elseif (isset($context['DATA']['dcs_setid'])) {
            $propid = $context['DATA']['dcs_setid']['id'];
        }   
        $setid = '';
        $sets = array();
        foreach ($psets as $prop) {
            $mdset = new Mdentity($prop['valmdid']);
            $props = $mdset->get_items();
            $items = array_filter($props,function($item){
                            return $item['valmdtypename'] === 'Items';
                        });
            $sets[$prop['id']] = array();
            if (!count($items)) {
                continue;
            }
            foreach ($items as $item) {
                $mditem = new Mdentity($item['valmdid']);
                $sets[$prop['id']] = array_filter($mditem->get_items(),
                            function($item) {
                                return $item['ranktoset'] > 0;
                            });
            }
            if ($propid == $prop['id']) {
                $context['SETID'] = $propid;
            } elseif (count($psets) == 1) { 
                $context['SETID'] = $prop['id'];
                break;
            }  
        }  
        return $sets;
    }

    public function update_dependent_properties($context,$data)
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
                            $arr_dep_ent_propid = array_column($dep_ent->plist,'propid','id');
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
                    $filter['DATA']['dcs_itemid'] =  array('id' => $dep_prop['valmdid'],'name' => '');
                    $filter['DATA']['dcs_curid'] = array('id'=>$this->id,'name'=>'');
                    if ($this->mdtypename == 'Items') {
                        //это строка тч - в фильтр передадим объект владелец ТЧ
                        $ar_obj = DataManager::get_obj_by_item($this->id);
                        if (count($ar_obj)>0) {
                            $filter['DATA']['dcs_docid'] = array('id'=>$ar_obj[0]['id'],'name'=>'');
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
            $res = $this->update_properties($context,$res,1);
        }
        return $res;
    }        
    public function update_properties($context,$data,$n=0)     
    {
        $objs = $this->before_save($context,$data);
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
        $objs = $this->before_save($context,$vals);
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
    function delete() 
    {
	$res = DataManager::dm_query("BEGIN");
        $id = $this->id;
        $propid = array_search('activity', array_column($this->properties,'name','id'));
        if ($propid!==false)
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
    public function save_new_()
    {
	if ($this->id!=''){
            return array('status'=>'ERROR','msg'=>'this in not new object');
        }    
        if ($this->getname()==''){
            $this->setname('new object');
        }    
        $edate = $this->edate;
        $enumber = $this->enumber;
        if ($this->head->getmdtypename()=='Docs')
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
    public function create_object($name,$synonym='') 
    {
        if (!$this->mdid) {
            throw new DcsException("Class ".get_called_class().
                " create_object: mdid is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        if (!$this->id) {
            throw new DcsException("Class ".get_called_class().
                " create_object: id is empty",DCS_ERROR_WRONG_PARAMETER);
        }
        if (!$synonym) {
            if (!$name) {
                throw new DcsException("Class ".get_called_class().
                    " create_object: name is empty",DCS_ERROR_WRONG_PARAMETER);
            }
        } else {
            $name = $synonym;
        }
        $sql = "INSERT INTO \"ETable\" (id, mdid, name) "
                    . "VALUES (:id, :mdid, :name) RETURNING \"id\"";
        $params = array();
        $params['id']= $this->id;
        $params['mdid']= $this->mdid;
        $params['name']= $name;
      	DataManager::dm_beginTransaction();
        $res = DataManager::dm_query($sql,$params); 
        if ($res) {
            $rowid = $res->fetch(PDO::FETCH_ASSOC);
            DataManager::dm_commit();
            return $rowid['id'];
        }
        DataManager::dm_rollback();
        throw new DcsException("Class ".get_called_class().
            " create_object: unable to create new record",DCS_ERROR_WRONG_PARAMETER);
    }

    public function createItem($name,$prefix='')
    {
        $arSetItemProp = self::getMDSetItem($this->mdentity->getid());
        $mdid = $arSetItemProp['valmdid'];
        $objs = array();
        $objs['PSET'] = $this->getProperties(true,'toset');
        //$childid = $this->create_object('',$this->head->getid(),str_replace('Set','Item', $name));
        //need refactor
        $childid = $this->create_object(str_replace('Set','Item', $name));
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
    public function getItems($context) 
    {
        if ($context['SETID'] === '') {
            if ((!isset($context['DATA']['dcs_setid']))||(!isset($context['DATA']['dcs_propid']))) {
                return array();
            }    
            $propid = $context['DATA']['dcs_setid']['id'];
            if ($propid == '') {
                $propid = $context['DATA']['dcs_propid']['id'];
            }    
            if ($propid == '') {
                return array();
            }
        } else {
            $propid = $context['SETID'];
        }
        $setid = $this->getattrid($propid);
        if ((!$setid)||($setid === DCS_EMPTY_ENTITY)) {
            return array();
        }    
        $set = new Sets($setid);
        return $set->getItems($context);
    }
    public function get_navlist($context)
    {
        $navlist = array();
        $strval = 'Новый';
        $strkey = $this->id;
        if (!$this->isnew) {
            $strval = $this->getNameFromData($context)['synonym'];
        }    
        if ($context['PREFIX'] !== 'CONFIG') {
            if (isset($context['DATA']['dcs_setid'])) {
                $propid = $context['DATA']['dcs_setid']['id'];
                if ($propid !== '') {
                    $this->add_navlist($navlist);
                    $navlist[] = array('id'=>$this->id,'name' => sprintf("%s",$strval));
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
    public function get_valid($propid)
    {
        $sql = "SELECT it.entityid, it.propid, pv.value from \"IDTable\" as it "
                . "inner join (SELECT it.entityid, it.propid, "
                . "max(it.dateupdate) as dateupdate "
                . "from \"IDTable\" as it "
                . "where it.entityid = :id and it.propid = :propid "
                . "group by it.entityid, it.propid) as slc "
                . "on it.entityid = slc.entityid "
                . "and it.propid = slc.propid "
                . "and it.dateupdate = slc.dateupdate "
                . "inner join \"PropValue_id\" as pv on it.id=pv.id";
        $res = DataManager::dm_query($sql,array('id'=>$this->id, 'propid'=>$propid));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
    function before_delete() 
    {
        $nval="удалить";
        if (!$this->activity)
        {    
            $nval='снять пометку удаления';
        }   
        return array($this->id=>array(
            'id'=>$this->id,
            'name'=>"Элемент ".$this->get_mdsynonym(),
            'pval'=>$this->name,
            'nval'=>$nval
                ));
    }    
}