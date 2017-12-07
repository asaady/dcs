<?php
namespace tzVendor;
use PDO;
use DateTime;
use Exception;

class Entity extends Model {
    protected $mdentity;
    protected $activity;
    protected $edate;
    protected $enumber;
    protected $num;
    protected $data;
    protected $plist;
    protected $mode;
    
    public function __construct($id,$version=0,$mode='')
    {
        if ($id=='') {
            throw new Exception("Class Entity constructor: id is empty");
        }
        $arData = self::getEntityDetails($id);
            
        if ($arData['id']!='') {
            $this->id =$id; 
            $mdid = $arData['mdid'];
        } else {
            $this->id =''; 
            $mdid = $id;
        }
        $this->mdentity = new Mdentity($mdid);
        $this->plist = MdpropertySet::getMDProperties($mdid, 'CONFIG', " WHERE mp.mdid = :mdid ",true);
        $this->data = $this->entity_data();
        $this->edate = $this->getpropdate();
        $this->enumber = $this->getpropnumber();
        $this->synonym = $this->name;
        $this->name = $this->gettoString();
        $this->mode = $mode;
        $prop_activity = array_search("activity", array_column($this->plist,'name','id'));
        if ($prop_activity!==FALSE)
        {    
            $this->activity = $this->getattr($prop_activity); 
        }
        if ($this->activity!==FALSE)
        {
            $this->activity = TRUE;
        }
    }
    function getdata() 
    {
        return $this->data;
    }
    function entity_data() 
    {
        $arProp = array();
	if ($this->id!='') 
        {
            $arData = self::getEntityData($this->id,$this->mode);
            if (count($arData['SDATA'])) 
            {
                $arProp = $arData['SDATA'][$this->id];
            }
	}
        $this->version = time();
        $data = array();

        
	foreach($this->plist as $aritem)
        {
	    $v = $aritem['id'];
            $data[$v]=array();
	    if(array_key_exists($v,$arProp))
            {
	      $data[$v]['id']=$arProp[$v]['id'];
	      $data[$v]['name']=$arProp[$v]['name'];
	    }
            else 
            {
     	      $data[$v]['id']=TZ_EMPTY_ENTITY;
	      $data[$v]['name']='';
	    }  
	}
        return $data;
    }
    function getshortname()
    {
        $res = $this->name;
        if (strlen($res)>55)
        {    
            $res = substr($res, 0, 55);
            $end = strlen(strrchr($res, ' ')); // длина обрезка 
            $res = substr($res, 0, -$end) . '...';         
        }    
        return $res;
    }
    function getactivity()
    {
        return $this->activity;
    }
    function getmdentity()
    {
        return $this->mdentity;
    }
    function get_data($mode='') 
    {
        if ($this->mdentity->getmdtypename()!='Items')
        {
            $navlist = array(
                    $this->mdentity->getmditem()=>$this->mdentity->getmditemsynonym(),
                    $this->mdentity->getid()=>$this->mdentity->getsynonym(),
                    $this->id=>$this->getshortname()
                );
        }   
        else
        {
            $setid = self::get_set_by_item($this->id); 
            $entityid = self::get_entity_by_setid($setid);
            $entity = new Entity($entityid);
            $mdentity = $entity->getmdentity();
            $navlist = array(
                    $mdentity->getmditem()=>$mdentity->getmditemsynonym(),
                    $mdentity->getid()=>$mdentity->getsynonym(),
                    $entity->getid()=>$entity->getshortname(),
                    $this->id => $this->name
                );
        }    
        $plist = MdpropertySet::getMDProperties($this->mdentity->getid(),$mode," WHERE mp.mdid = :mdid ");
        $sets = array();
        foreach ($plist as $prop) 
        {
            if ($prop['valmdtypename']!='Sets')
            {
                continue;
            }
            $setprop = MdpropertySet::getMDProperties($prop['valmdid'],$mode," WHERE mp.mdid = :mdid ");
            foreach ($setprop as $sprop) 
            {    
                if ($sprop['valmdtypename']=='Items')
                {
                    $sets[$prop['id']]= MdpropertySet::getMDProperties($sprop['valmdid'],$mode," WHERE mp.mdid = :mdid and mp.ranktoset>0 ",true);
                    break;
                }    
            }
        }  
        return array('id'=>$this->id,
                'version'=>$this->version,
                'name'=>$this->name,
                'activity'=>$this->activity,
                'edate'=>$this->edate,
                'enumber'=>$this->enumber,
                'num'=>$this->num,
                'mdsynonym'=>$this->mdentity->getsynonym(),
                'mdtypedescription'=>$this->mdentity->getmdtypedescription(),
                'PLIST'=>$plist,
                'sets' => $sets,
                'navlist'=>$navlist
        );        
    }
    
    public static function get_set_by_item($itemid)
    {
        $sql="SELECT parentid, childid, rank FROM \"SetDepList\" where childid = :itemid";
        $res = DataManager::dm_query($sql,array('itemid'=>$itemid));
        if(!$res) {
            return TZ_EMPTY_ENTITY;
        }
        $row = $res->fetch(PDO::FETCH_ASSOC);
        if(!count($res)) 
        {
            return TZ_EMPTY_ENTITY;
        }
        return $row['parentid'];
    }
    public static function get_entity_by_setid($setid)
    {
        $sql="SELECT it.entityid, max(it.dateupdate) from \"PropValue_id\" as pv inner join \"IDTable\" as it on pv.id=it.id where pv.value=:setid group by it.entityid";

        $res = DataManager::dm_query($sql,array('setid'=>$setid));
        $row = $res->fetch(PDO::FETCH_ASSOC);
        if(!count($row)) 
        {
            return TZ_EMPTY_ENTITY;
        }
        return $row['entityid'];
    }
    function set_data($data) 
    {
	foreach($this->plist as $aritem)
        {
	    $v = $aritem['id'];
            $this->data[$v]=array();
	    if(array_key_exists($v,$data))
            {
                $this->data[$v]['name']=$data[$v]['name'];
                if (($aritem['type']==='id')||($aritem['type']==='cid')||($aritem['type']==='mdid'))
                {
                    if ($data[$v]['id']!=='')
                    {    
                        $this->data[$v]['id']=$data[$v]['id'];
                    }
                    else 
                    {
                        $this->data[$v]['name']='';
                        $this->data[$v]['id']=TZ_EMPTY_ENTITY;
                    }
                }
	    }
            else 
            {
                $this->data[$v]['name']='';
                $this->data[$v]['id']=TZ_EMPTY_ENTITY;
	    }  
	}
        $this->edate = $this->getpropdate();
        $this->enumber = $this->getpropnumber();
        $this->name = $this->gettoString();
        $this->synonym = $this->name;
        
    }
    
    public function gettoString() 
    {
        $artoStr=array();
        foreach($this->plist as $prop)
        {
            if ($prop['ranktostring']>0) 
            {
              $artoStr[$prop['id']]=$prop['ranktostring'];
            }
        }
        if (!count($artoStr)) 
        {
            foreach($this->plist as $prop)
            {
              if ($prop['rank']>0) 
              {
                $artoStr[$prop['id']]=$prop['rank'];
              }  
            }
            if (count($artoStr)) 
            {
              asort($artoStr);
              array_splice($artoStr,1);
            }  
        }
        else
        {
            asort($artoStr);
        }
        if (count($artoStr)) 
        {
            $res='';
            foreach($artoStr as $prop=>$rank)
            {
                if ($this->mdentity->getmdtypename()=='Docs')
                {
                    if ($this->plist[$prop]['isenumber'])
                    {
                        continue;
                    }    
                    if ($this->plist[$prop]['isedate'])
                    {
                        continue;
                    }    
                }    
                $name = $this->data[$prop]['name'];
                if ($this->plist[$prop]['type']=='date')
                {
                    $name =substr($name,0,10);
                }
                $res .=' '.$name;
            }
            if ($this->mdentity->getmdtypename()=='Docs')
            {
                $datetime = new DateTime($this->edate);
                $res = $this->mdentity->getsynonym()." №".$this->enumber." от ".$datetime->format('d-m-y').$res;
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
    public function getpropdate(){
	$res=$this->edate;
        foreach ($this->plist as $prow)
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
        foreach ($this->plist as $prow)
        {    
            if (($prow['isenumber']==true)||($prow['isenumber']=='t')||($prow['isenumber']=='true')) 
            {
              $res=$this->data[$prow['id']]['name'];
              break;
            }  
	}
	return $res;
    }
    
    function __toString() 
    {
      return $this->name;
    }
    
    public function properties() 
    {
	return $this->plist;
    }
    function getproperty($propid)
    {
        $val=array();
	if(array_key_exists($propid, $this->plist))
        {
	  $val=$this->plist[$propid];
	}  
	return $val;
    }
    
    public function getattr($propid) 
    {
        $val='';
	if(array_key_exists($propid, $this->data))
        {
	  $val=$this->data[$propid]['name'];
	}  
	return $val;
    }
    function getattrid($propid)
    {
        $val='';
	if(array_key_exists($propid, $this->data))
        {
	  $val=$this->data[$propid]['id'];
	}  
	return $val;
    }
    public function setattr($propid,$valname,$valid='') 
    {
        $val='';
	if(array_key_exists($propid, $this->data))
        {
	  $this->data[$propid]['name']=$valname;
          $this->data[$propid]['id']=$valid;
	}  
        return $this;
    }
    
    public function update($data)     
    {
        $objs = $this->before_save($data);
	$res = DataManager::dm_query("BEGIN");
        $id = $this->id;
        $cnt=0;
	foreach($objs as $propval){
            $propid = $propval['id'];
            if ($propid =='id')
            {
                continue;
            }
            if(!array_key_exists($propid,$this->plist))
            {
                continue;
            }
            $type = $this->plist[$propid]['type'];
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
            $t_val = $propval['nval'];
            if (($type=='id')||($type=='cid'))
            {
                $t_val = $propval['nvalid'];
                if ($t_val=='')
                {
                    $t_val=TZ_EMPTY_ENTITY;
                }    
            }    
	    $sql = "INSERT INTO \"PropValue_$type\" (id, value) VALUES ( :id, :value)";
            $params = array();
            if ($type=='file')
            {
                $params['value']= str_replace(" ","_",trim($this->name))."_".$this->plist[$propid]['name'].strrchr($t_val,'.');
            }    
            else
            {
                $params['value']=$t_val;
            }    
            $params['id']=$row['id'];
	    $res = DataManager::dm_query($sql,$params);
	    if(!$res) {
                $res = DataManager::dm_query("ROLLBACK");
                return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу PropValue_$type запись ".$sql);
	    }
            $cnt++;
	}
        if ($cnt>0)
        {    
            $res = DataManager::dm_query("COMMIT");	
            return array('status'=>'OK', 'id'=>$this->id, 'msg'=>$sql, 'obj'=>$objs, 'plist'=>$this->plist);
        }
        else
        {
            $res = DataManager::dm_query("ROLLBACK");
            return array('status'=>'NONE','msg'=>"Нет измененных записей ");
        }    
    }
    function before_delete() 
    {
        $nval="удалить";
        if (!$this->activity)
        {    
            $nval='снять пометку удаления';
        }   
        return array($this->id=>array('id'=>$this->id,'name'=>"Элемент ".$this->getmdentity()->getsynonym(),'pval'=>$this->name,'nval'=>$nval));
    }    
    function delete() 
    {
	$res = DataManager::dm_query("BEGIN");
        $id = $this->id;
        $propid = array_search('activity', array_column($this->plist,'name','id'));
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
                return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу PropValue_$type запись ".$sql);
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

    function before_save($data) {
        $sql = '';
        $objs = array();
        foreach ($this->plist as $prop)
        {    
            $propid = $prop['id'];
            if ($propid=='id') continue;
            if (!array_key_exists($propid, $data))
            {        
                continue;
            }
            $nval = $data[$prop['id']]['name'];
            $nvalid = $data[$prop['id']]['id'];
            $pval = $this->data[$prop['id']]['name'];
            $pvalid = '';
            if ($prop['type']=='id') 
            {
                $pvalid = $this->data[$prop['id']]['id'];
                if ($pvalid==$nvalid) 
                {
                    continue;
                }    
                if (($pvalid==TZ_EMPTY_ENTITY)&&($nvalid==''))
                {
                    continue;
                }
            }
            elseif ($prop['type']=='date') 
            {
                if (substr($pval,0,19)==substr($nval,0,19)) 
                {
                    continue;
                }    
            } 
            elseif ($prop['type']=='bool') 
            {
                if (substr($pval,0,1)==substr($nval,0,1)) 
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
            $objs[]=array('id'=>$prop['id'], 'name'=>$prop['name'],'pvalid'=>$pvalid, 'pval'=>$pval, 'nvalid'=>$nvalid, 'nval'=>$nval);
        }       
	return $objs;
    }
    public function createNew(){
        
	if ($this->id!=''){
            return array('status'=>'ERROR','msg'=>'this in not new object');
        }    
        if ($this->getname()==''){
            return array('status'=>'ERROR','msg'=>'name is empty');
        }    
        $edate = $this->edate;
        $enumber = $this->enumber;
        if ($this->mdentity->getmdtypename()=='Docs')
        {
            if ($edate=='') {
                return array('status'=>'ERROR','msg'=>'date is empty');
            }
            if ($enumber=='') {
                return array('status'=>'ERROR','msg'=>'number is empty');
            }
        }    
	$res = DataManager::dm_query("BEGIN");
        $mdid = $this->mdentity->getid();
        $sql = "INSERT INTO \"ETable\" (mdid) VALUES (:mdid) RETURNING \"id\"";
        $res=DataManager::dm_query($sql,array('mdid'=>$mdid));
        if(!$res) {
            return array('status'=>'ERROR','msg'=>'Невозможно добавить в таблицу запись '.$sql);
        }    
        $row = $res->fetch(PDO::FETCH_ASSOC);
        $this->id = $row['id'];
        $id = $this->id;
        foreach($this->plist as $prop)
        {
            $propid = $prop['id'];
            if ($propid=='id') continue;
            $valname = $this->data[$propid]['name'];
            $valid  = $this->data[$propid]['id'];
            if ($prop['type']=='id')
            {
                if (($valid!=TZ_EMPTY_ENTITY)&&($valid!=''))  
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
            elseif (($prop['type']=='cid')||($prop['type']=='mdid'))
            {
                if (($valid!=TZ_EMPTY_ENTITY)&&($valid!=''))  
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
                if ($prop['type']=='bool')
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
                elseif (($prop['type']=='int')||($prop['type']=='float'))
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
            $sql = "INSERT INTO \"PropValue_$prop[type]\" (id, value) VALUES ( :id, :value)";
            $params = array();
            $params['id']=$row['id'];
            $params['value']=$valname;
            $res = DataManager::dm_query($sql,$params);
            if(!$res) {
                $res = DataManager::dm_query("ROLLBACK");
                return array('status'=>'ERROR','msg'=>"Невозможно добавить в таблицу PropValue_$prop[type] запись ".$sql);
            }
	}
	$res = DataManager::dm_query("COMMIT");
        
        return array('status'=>'OK', 'id'=>$this->id);
    }
    public static function getEntityDetails($entityid) 
    {
	$sql = "select et.id, et.mdid , md.mditem as mditem, md.name as mdname, md.synonym as mdsynonym, tp.name as mdtypename, tp.synonym as mdtypedescription FROM \"ETable\" as et
		    INNER JOIN \"MDTable\" as md
			INNER JOIN \"CTable\" as tp
			ON md.mditem = tp.id
		      ON et.mdid = md.id 
		    WHERE et.id=:entityid";  
	$res = DataManager::dm_query($sql,array('entityid'=>$entityid));
        $objs = $res->fetch(PDO::FETCH_ASSOC);
	if(!count($objs)) {
            $objs = array('id'=>'','mdid'=>'','mditem'=>'');
	}
        return $objs;
    }
    static function createtemptable_allprop($entities,$mdid, $ver='')
    {
        $str_entities = "('".implode("','", $entities)."')"; 
        // соберем список ссылок в представлении (ranktostring>0) 
	$artemptable=array();
        $sql = DataManager::get_select_entities($str_entities,true);
        
        $artemptable[0] = DataManager::createtemptable($sql,'tt_et');   
        
        $sql = DataManager::get_select_properties(" WHERE mp.mdid=:mdid ");
        $artemptable[1] = DataManager::createtemptable($sql,'tt_pt',array('mdid'=>$mdid));   
        
        $sql=DataManager::get_select_maxupdate('tt_et','tt_pt');
        $artemptable[2] = DataManager::createtemptable($sql,'tt_id');   
        
        $sql=DataManager::get_select_lastupdate('tt_id','tt_pt');
        $artemptable[3] = DataManager::createtemptable($sql,'tt_tv');   
        
        return $artemptable;    
    }
    public static function getEntityData($id,$mode='',$edit_mode='',$curid='',$version=0) 
    {
	if ($version==0) 
        {
	  $ver="";
	}else{
	  $ver="HAVING max(dateupdate)<to_timestamp($version)";
	}  
    	$objs = array();
	$objs['MD'] = array();
	$objs['SDATA'] = array();
        $objs['actionlist'] = DataManager::getActionsbyItem('Entity',$mode,$edit_mode);
        $arMD = self::getEntityDetails($id);
        if ($arMD['id']=='')
        {
            $mdid = $id;
            $arMD = Mdentity::getMD($mdid);
            $objs['id']='';
        }    
        else 
        {
            $mdid = $arMD['mdid'];
            $objs['id']=$id;
        }
        $objs['PLIST'] = MdpropertySet::getMDProperties($mdid,$mode," WHERE mp.mdid = :mdid ",true);
        $objs['MD'] =  array(
                              'mdid'	=> $mdid,
                              'mditem'	=> $arMD['mditem'],
                              'mdsynonym'	=> $arMD['mdsynonym'],
                              'mdtypename'	=> $arMD['mdtypename'],
                              'mdtypedescription'	=> $arMD['mdtypedescription']
                              );
        if ($objs['id']=='')
        {
            $objs['SDATA'] = DataManager::getDefaultValue($objs['PLIST']);
            if ($curid!='')
            {
                $arr_e[] =$curid;
                $ent = new Entity($curid,$mode);
                foreach($objs['PLIST'] as $row) 
                {
                    if ($row['valmdid']==$ent->getmdentity()->getid())
                    {
                        $objs['SDATA'][TZ_EMPTY_ENTITY][$row['id']]['id']=$curid;
                        $objs['SDATA'][TZ_EMPTY_ENTITY][$row['id']]['name']=$ent->getname();
                    }    
                }    
            }    
            return $objs;
        }    
        $entities = array($id);
	$artemptable = self::createtemptable_allprop($entities,$mdid, $ver);
	$sql = "SELECT * FROM tt_pt";
	$res = DataManager::dm_query($sql);	
        $plist = $res->fetchAll(PDO::FETCH_ASSOC);
        $str0_req='SELECT et.id';
        $str_req='';
        $str_p = '';
        $arr_id=array();
        foreach($plist as $row) 
        {
            $rid = $row['id'];
            if ($row['type']=='id')
            {
                if ($row['valmdtypename']!='Sets')
                {
                    $arr_id[$rid]=$row;
                }
            }
            $rowname = "$row[id]";
            $rowname = str_replace("-","",$rowname);
            $str0_t = ", tv_$rowname.propid as propid_$rowname, pv_$rowname.value as name_$rowname, '' as id_$rowname";
            $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            if ($row['type']=='id')
            {
                $arr_id[$rid]=$row;
                $str0_t = ", tv_$rowname.propid as propid_$rowname, '' as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            }
            elseif ($row['type']=='cid') 
            {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, ct_$rowname.synonym as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value=ct_$rowname.id ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            }
            elseif ($row['type']=='mdid') 
            {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, ct_$rowname.synonym as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value=ct_$rowname.id ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            }
            elseif ($row['type']=='date') 
            {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, date_trunc('second',pv_$rowname.value) as name_$rowname, '' as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            }
                
            $str0_req .= $str0_t;
            $str_req .=$str_t;
            
        }
        $str0_req .=" FROM tt_et as et";
        $sql = $str0_req.$str_req." WHERE et.id=:id";
        $objs['SQL']=$sql;
        $res = DataManager::dm_query($sql, array('id'=>$id));
        $arr_e = array();
        $objs['ENT'] = array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs['ENT'][] = $row;
            $objs['SDATA'][$row['id']] = array();                
            $objs['SDATA'][$row['id']]['class'] ='active';               
            foreach($plist as $row_plist) 
            {
                $rid = $row_plist['id'];
                $rowname = str_replace("-","",$row_plist['id']);
                $field_id = "propid_$rowname";
                $rowid = "id_$rowname";
                $rowname = "name_$rowname";
                $objs['SDATA'][$row['id']][$row[$field_id]] = array();                
                if (strtolower($row_plist['name'])=='activity')
                {
                    if (!$row[$rowname])
                    {    
                        $objs['SDATA'][$row['id']]['class'] ='erased';               
                    }    
                }    
                if (array_key_exists($rowname,$row))
                {   
                    if ($row[$field_id])
                    {    
                        if ($row_plist['type']=='id')
                        {
                            if ($row_plist['valmdtypename']=='Sets')
                            {    
                                $objs['SDATA'][$row['id']][$row[$field_id]]['id']=$row[$rowid];
                                $objs['SDATA'][$row['id']][$row[$field_id]]['name']=$row_plist['synonym'];
                            }
                            else
                            {
                                if (($row[$rowid]!='')&&($row[$rowid]!=TZ_EMPTY_ENTITY))
                                {
                                    if (!in_array($row[$rowid],$arr_e))
                                    {
                                        $arr_e[]=$row[$rowid];
                                    }
                                }
                                $objs['SDATA'][$row['id']][$row[$field_id]]['id']=$row[$rowid];
                                $objs['SDATA'][$row['id']][$row[$field_id]]['name']='';
                            }
                        }
                        elseif ($row_plist['type']=='cid')
                        {
                            $objs['SDATA'][$row['id']][$row[$field_id]]['id']=$row[$rowid];
                            $objs['SDATA'][$row['id']][$row[$field_id]]['name']=$row[$rowname];
                        }    
                        else
                        {
                            $objs['SDATA'][$row['id']][$row[$field_id]] = array('id'=>'','name'=>$row[$rowname]);
                            $objs['SDATA'][$row['id']][$row[$field_id]]['id']='';
                            $objs['SDATA'][$row['id']][$row[$field_id]]['name']=$row[$rowname];
                        }    
                    }    
                }
            }
        }  
        if (count($arr_e))
        {
            $arr_entities = EntitySet::getAllEntitiesToStr($arr_e);
            foreach($arr_id as $rid=>$prow)
            {
                foreach($objs['SDATA'] as $id=>$row) 
                {
                    
                    if (array_key_exists($rid, $row))
                    {
                        $crow = $row[$rid];
                        if (array_key_exists($crow['id'], $arr_entities)){
                            if ($arr_entities[$crow['id']]['name']!='')
                            {    
                                $objs['SDATA'][$id][$rid]['name']=$arr_entities[$crow['id']]['name'];
                            }    
                        }    
                    }        
                }
            }
        }
	$msg = DataManager::droptemptable($artemptable);
        if ($msg!='')
        {
            die($msg);
        }   
	return $objs;
    }
    public static function getMDSetItem($mdid) 
    {
	$sql = DataManager::get_select_properties(" WHERE mi.name='Items' and mp.mdid = :mdid");
	$res = DataManager::dm_query($sql,array('mdid'=>$mdid));
        
	return $res->fetch(PDO::FETCH_ASSOC);
    }
    function getSetData($mode,$edit_mode='') 
    {
	$objs = array();
	$objs['LDATA']=array();
        $objs['PSET'] = array();
        $objs['actionlist'] = DataManager::getActionsbyItem('EntitySet',$mode,$edit_mode);
	$arSetItemProp = self::getMDSetItem($this->mdentity->getid());
	if (!$arSetItemProp)
        {    
            return $objs;
        }    
	$mdid = $arSetItemProp['valmdid'];
	$objs['PSET'] = MdpropertySet::getMDProperties($mdid,$mode," WHERE mp.mdid = :mdid and mp.ranktoset>0 ",true);
                
        if ($this->id=='')
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
			cv_name.name as type,
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
        $rank_id = array_search('rank', array_column($objs['PSET'],'name','id'));
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
        $activity_id = array_search('activity', array_column($objs['PSET'],'name','id'));
        $arr_e=array();
        foreach ($sobjs['rows'] as $row)
        {
            $objs['LDATA'][$row['id']]=array();
            foreach ($objs['PSET'] as $prop)
            {
                $objs['LDATA'][$row['id']][$prop['id']]= array('id'=>'', 'name'=>'');
                if ($activity_id!==FALSE)
                {
                    if ($prop['id']==$activity_id)
                    {
                        $objs['LDATA'][$row['id']]['class']= 'active';
                    }    
                }    
            }
        }
        $destPath = $_SERVER['DOCUMENT_ROOT'] . '/upload/';
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (($row['type']=='id')||($row['type']=='cid'))
            {
                $objs['LDATA'][$row['entityid']][$row['id']] = array(
                       'id'=>$row[$row['type'].'_value'],
                       'name'=>$row['id_valuename']
                  );
                if ($row['type']=='id')
                {    
                    if (isset($row['id_value']))
                    {        
                        if (!in_array($row['id_value'],$arr_e))
                        {
                            $arr_e[] = $row['id_value'];
                        }
                    }    
                }    
            }
            else
            {
                if ($row['type']=='file')
                {
                    $name = $row[$row['type'].'_value'];
                    $ext = strrchr($name,'.');
                    
                    $curm = date("Ym",strtotime($row['dateupdate']));
                    $objs['LDATA'][$row['entityid']][$row['id']] = array(
                      'name'=>$name,
                      'id'=>"/download/".$row['entityid']."/".$row['id']
                    );
                }   
                else
                {
                    $objs['LDATA'][$row['entityid']][$row['id']] = array(
                      'name'=>$row[$row['type'].'_value'],
                      'id'=>''
                      );
                }    
                if ($activity_id!==FALSE)
                {
                    if ($row['id']==$activity_id)
                    {
                        if ($row['bool_value']===FALSE)
                        {    
                            $objs['LDATA'][$row['entityid']]['class']= 'erased';
                        }    
                    }    
                }    
            }

        }
        if (count($arr_e))
        {
            $arr_entities = EntitySet::getAllEntitiesToStr($arr_e);
            
            foreach($objs['PSET'] as $rid=>$prow)
            {
                if ($prow['type']!='id')
                {
                    continue;
                }    
                if ($prow['valmdtypename']=='Sets')
                {
                    continue;
                }
                foreach($objs['LDATA'] as $id=>$row) 
                {
                    if (array_key_exists($rid, $row))
                    {
                        $crow = $row[$rid];
                        if (array_key_exists($crow['id'], $arr_entities)){
                            $objs['LDATA'][$id][$rid]['name']=$arr_entities[$crow['id']]['name'];
                        }    
                    }        
                }
            }    
        }    
        
	DataManager::droptemptable($artemptable);
	
	return $objs;
    }    
    public static function CopyEntity($id,$user)
    {
        $arEntity = self::getEntityDetails($id);
        $arnewid = self::saveNewEntity($arEntity['name'],$arEntity['mdid'], TZ_EMPTY_DATE);
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
            $val = $prop[$prop['type'].'_value'];
            $sql = "INSERT INTO \"IDTable\" (userid,entityid, propid) VALUES ('$user', '$id', '$propid) RETURNING \"id\"";
            $res = pg_query(self::_getConnection(), $sql);
            if(!$res) 
            {
              $sql_rb = "ROLLBACK";
              $res = pg_query(self::_getConnection(), $sql_rb);
              die("Невозможно добавить в таблицу IDTable запись ".$sql);
            }
            $row = pg_fetch_assoc($res);
            $sql = "INSERT INTO \"PropValue_{$prop['type']}\" (id, value) VALUES ('{$row['id']}','$val')";
            $res = pg_query(self::_getConnection(), $sql);
            if(!$res) 
            {
              $sql_rb = "ROLLBACK";
              $res = pg_query(self::_getConnection(), $sql_rb);
              die("Невозможно добавить в таблицу PropValue_{$prop['type']} запись ".$sql);
            }

    }	
    public static function getPropsUse($mditem) 
    {
        $sql="SELECT pu.id, pu.name, pu.synonym, pv_propid.value as propid, pv_type.value as type, ct_type.name as name_type, pv_len.value as length, pv_prc.value as prec, pv_valmd.value as valmdid, md_valmd.name as valmdname FROM \"CTable\" as pu 
                inner join \"CPropValue_cid\" as pv_propid 
                    inner join \"CProperties\" as cp_propid
                    ON pv_propid.pid=cp_propid.id
                    AND cp_propid.name='propid'
                    inner join \"CTable\" as ct_propid
                    ON pv_propid.value = ct_propid.id
                    
                    inner join \"CPropValue_cid\" as pv_type
                        inner join \"CProperties\" as cp_type
                        ON pv_type.pid=cp_type.id
                        AND cp_type.name='type'
                        inner join \"CTable\" as ct_type
                        ON pv_type.value = ct_type.id
                    ON pv_propid.value = pv_type.id
                    AND ct_propid.mdid = cp_type.mdid

                    left join \"CPropValue_int\" as pv_len
                        inner join \"CProperties\" as cp_len
                        ON pv_len.pid=cp_len.id
                        AND cp_len.name='length'
                    ON pv_propid.value = pv_len.id
                    AND ct_propid.mdid = cp_len.mdid
                    
                    left join \"CPropValue_int\" as pv_prc
                        inner join \"CProperties\" as cp_prc
                        ON pv_prc.pid=cp_prc.id
                        AND cp_prc.name='prec'
                    ON pv_propid.value = pv_prc.id
                    AND ct_propid.mdid = cp_prc.mdid
                    
                    left join \"CPropValue_mdid\" as pv_valmd
                        inner join \"CProperties\" as cp_valmd
                        ON pv_valmd.pid=cp_valmd.id
                        AND cp_valmd.name='valmdid'
                        inner join \"MDTable\" as md_valmd
                        ON pv_valmd.value = md_valmd.id
                    ON pv_propid.value = pv_valmd.id
                    AND ct_propid.mdid = cp_valmd.mdid
                    
                ON pu.id=pv_propid.id
                AND pu.mdid = cp_propid.mdid
                inner join \"CPropValue_cid\" as pv_mditem
                    inner join \"CProperties\" as cp_mditem
                    ON pv_mditem.pid=cp_mditem.id
                    AND cp_mditem.name='mditem'
                ON pu.id=pv_mditem.id
                AND pv_mditem.value = :mditem";
        $params = array();
        $params['mditem']=$mditem;
        $res = DataManager::dm_query($sql,$params); 
        return $res->fetchAll(PDO::FETCH_ASSOC);
        
    }
    public function createItem($name,$mode='')
    {
        $arSetItemProp = self::getMDSetItem($this->mdentity->getid());
        $mdid = $arSetItemProp['valmdid'];
        $objs = array();
        $objs['PSET'] = MdpropertySet::getMDProperties($mdid,$mode," WHERE mp.mdid = :mdid ",true);
        $sql = "INSERT INTO \"ETable\" (mdid, name) VALUES (:mdid, :name) RETURNING \"id\"";
        $params = array();
        $params['mdid']=$mdid;
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
                $arPropsUse = self::getPropsUse($item->mdentity->getmditem());
                $irank=0;
                foreach ($arPropsUse as $prop)
                {
                    $irank++;
                    $row = Mdproperty::IsExistTheProp($item->mdentity->getid(),$prop['propid']);
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
                        $row = Mdproperty::createMDProperty($data);
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
  }