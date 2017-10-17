<?php
namespace tzVendor;

use tzVendor\Mditem;
use tzVendor\CollectionItem;
use tzVendor\CpropertySet;
use PDO;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_const.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/common/tz_common.php");

class CollectionSet extends Model {
    protected $mditem;
    
    public function __construct($id='') 
    {
	if ($id=='') 
        {
            die("empty id collection");
	}
        $arPar = self::getMDCollection($id);
        if ($arPar)
        {
            $this->id = $id; 
            $this->name = $arPar['name']; 
            $this->synonym = $arPar['synonym']; 
            $this->mditem = new MDitem($arPar['mditem']);
        }    
        else 
        {
            $this->id = ''; 
            $this->name = ''; 
            $this->synonym = ''; 
            $this->mditem = new MDitem($id);
        }
        $this->version = time();
    }
    function getmditem() 
    {
      return $this->mditem;
    }
    function get_data($mode='') 
    {
        $plist = MdpropertySet::getMDProperties($this->id,$mode," WHERE mp.mdid = :mdid AND mp.rank>0 AND mp.ranktoset>0 ",true);
        if ($this->id=='')
        {
            $navlist = array($this->mditem->getid()=>$this->mditem->getsynonym(),'new'=>'Новый');
        }   
        else
        {
            $navlist = array($this->mditem->getid()=>$this->mditem->getsynonym(),$this->id=>$this->synonym);
        }    
        return array(
            'id'=>$this->id,
            'name'=>$this->name,
            'synonym'=>$this->synonym,
            'version'=>$this->version,
            'mditem'=>$this->mditem->getid(),
            'mdtypename'=>$this->mditem->getname(),
            'mdtypedescription'=>$this->mditem->getsynonym(),
            'PLIST' => $plist,   
            'navlist' => $navlist
            );
    }
    function create($data) {
      $colitem = new CollectionItem($this->id);
      return $colitem->create($data);
    }
    public static function getAllCollections() 
    {
	$sql = "SELECT md.id, md.name, md.synonym FROM \"MDTable\" as md 		    
                    INNER JOIN \"CTable\" as tp
                    ON md.mditem = tp.id
                    and tp.name='Cols'";
        $sth = DataManager::dm_query($sql);        
        $objs = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs[$row['id']] = $row;
        }
        return $objs;
    }
    public static function getMDCollection($id) 
    {
	$sql = "SELECT md.id, md.name, md.synonym, tp.id as mditem, tp.name as mdtypename FROM \"MDTable\" as md 		    
                    INNER JOIN \"CTable\" as tp
                    ON md.mditem = tp.id
                    and (tp.name='Cols' or tp.name='Comps')
                WHERE md.id=:id";
        $sth = DataManager::dm_query($sql,array('id'=>$id));        
        $res = $sth->fetch(PDO::FETCH_ASSOC);
        return $res;
    }
    public static function isExistCollItemByName($colname,$itemname)
    {
        $res = self::getMDCollectionByName($colname,true);
        if (count($res))
        {
            $sql = "SELECT ct.name, ct.id, ct.synonym FROM \"CTable\" as ct WHERE ct.mdid=:mdid and ct.name=:name";
            $sth = DataManager::dm_query($sql,array('mdid'=>$res[0]['id'],'name'=>$itemname));        
            $res = $sth->fetch(PDO::FETCH_ASSOC);
        }   
        return $res;
    }
    public static function getMDCollectionByName($name,$equal_only=false) 
    {
	$sql = "SELECT md.id, md.name, md.synonym, tp.name as mdtypename FROM \"MDTable\" as md 		    
                    INNER JOIN \"CTable\" as tp
                    ON md.mditem = tp.id
                    AND (tp.name='Cols' or tp.name='Comps')
                WHERE md.name ilike :name OR md.synonym ilike :name";
        if ($equal_only)
        {    
            $sql = str_replace('ilike', '=', $sql);
            $sth = DataManager::dm_query($sql,array('name'=>$name));        
        }    
        else 
        {
            $sth = DataManager::dm_query($sql,array('name'=>"%$name%"));        
        }
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }
    public static function getCollectionItemByID($itemid) 
    {
        $sql = "SELECT 'CollectionItem' as classname, ct.name, ct.id, ct.synonym, mc.id as setid, mc.name as setname, mc.synonym as setsynonym FROM \"CTable\" as ct INNER JOIN \"MDTable\" as mc ON ct.mdid = mc.id WHERE ct.id=:itemid";
        $sth = DataManager::dm_query($sql,array('itemid'=>$itemid));        
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
        public static function findCollByProp($filter) {
//      $filter: array 
//      id = property id (CProperties)
//      val = filter value
//      val_min = min filter value (optional)    
//      val_max = max filter value (optional)    
        $ftype='';
        $dbtable = '';
        $propid = $filter['filter_id']['id'];
        if ($propid!='')
        {
            $arprop = Cproperty::getCProperty($propid);
            if ($arprop['type']=='text')
            {
                return array();
            }
            $dbtable = "CPropValue_$arprop[type]";
            $ftype=$arprop['type'];
            $mdid = $arprop['mdid'];
        }
        else
        {
            if ($filter['itemid']['id']!='')
            {
                $mdid = $filter['itemid']['id'];
            }
            else 
            {
                return array();
            }
        }
        $params = array();
        $strwhere = DataManager::getstrwhere($filter,$ftype,'pv.value',$params);
        if ($strwhere!='')
        {
            $sql = "SELECT DISTINCT pv.id as cid FROM \"$dbtable\" as pv WHERE $strwhere and pv.pid=:propid"; 
            $params['propid']=$propid;
        }
        else
        {
            $sql = "SELECT et.id as cid FROM \"CTable\" as et WHERE et.mdid=:mdid LIMIT ".TZ_COUNT_REC_BY_PAGE; 
            $params=array('mdid'=>$mdid);
        }    
        $res = DataManager::dm_query($sql,$params);
        $objs = array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (!in_array($row['cid'],$objs))
            {
                $objs[] = $row['cid'];
            }
        }
        return $objs;
    }    

    function createtemptable_all($entities,$mdid, $ver='')
    {
        $str_entities = "('".implode("','", $entities)."')";
	$artemptable = array();
        $sql = DataManager::get_select_collections($str_entities);
        $artemptable[0] = DataManager::createtemptable($sql,'tt_et');   
        
        $sql = DataManager::get_select_cproperties(" WHERE mp.mdid=:mdid AND mp.rank>0 AND mp.ranktoset>0 ");
        $artemptable[1]= DataManager::createtemptable($sql,'tt_pt',array('mdid'=>$mdid));   
        
        return $artemptable;
    }
    
    public static function getCDetails($cid) 
    {
	$sql = "select et.id, et.name, et.synonym, et.mdid , md.mditem, md.name as mdname, md.synonym as mdsynonym, tp.name as mdtypename, tp.synonym as mdtypedescription FROM \"CTable\" as et
		    INNER JOIN \"MDTable\" as md
			INNER JOIN \"CTable\" as tp
			ON md.mditem = tp.id
		      ON et.mdid = md.id 
		    WHERE et.id=:cid";  
	$res = DataManager::dm_query($sql,array('cid'=>$cid));
        $objs = $res->fetch(PDO::FETCH_ASSOC);
	if(!count($objs)) {
            $objs = array('id'=>'','name'=>'','synonym'=>'');
	}
        return $objs;
    }
    
    public static function getCollectionByFilter($filter, $mode='',$edit_mode='', $limit=TZ_COUNT_REC_BY_PAGE, $page=1, $order='name') 
    {
        
    	$objs = array();
	$objs['MD'] = array();
	$objs['LDATA'] = array();
	$objs['PSET'] = array();
        $arMD = Mdentity::getMD($filter['itemid']['id']);
        $mdid = $arMD['id'];
        $objs['actionlist'] = DataManager::getActionList($mdid,$mode,$edit_mode);
        $objs['MD'] =  array(
                              'mdid'	=> $mdid,
                              'mditem'	=> $arMD['mditem'],
                              'mdsynonym'	=> $arMD['synonym'],
                              'mdtypename'	=> $arMD['mdtypename'],
                              'mdtypedescription'	=> $arMD['mdtypedescription']
                              );
        if ($arMD['name']=='user_settings')
        {
            if (!User::isAdmin())
            {
                //это уид реквизита user в таблице user_settings
                $filter['filter_id']['id']='94f6b075-1536-4d16-a548-bc8128791127';
                $filter['filter_val']['id']=$_SESSION['user_id'];
                $filter['filter_val']['name']= User::getUserName($_SESSION['user_id']);
            }    
        }    
        $entities = self::findCollByProp($filter);
        if (!count($entities))
        {
            $objs['RES']='list entities is empty';
            return $objs;
        }
	$offset=(int)($page-1)*$limit;
	$artemptable = self::createtemptable_all($entities,$mdid);
	$sql = "SELECT * FROM tt_pt";
	$res = DataManager::dm_query($sql);	
        $plist = $res->fetchAll(PDO::FETCH_ASSOC);
        $objs['ENT'] = array();
        $str0_req='SELECT et.id, et.name, et.synonym';
        $str_req='';
        $str_p = '';
        $filtername='';
        $filtertype='';
        $params = array();
        foreach($plist as $row) 
        {
            $rid = $row['id'];
            $rowname = str_replace("  ","",$row['name']);
            $rowname = str_replace(" ","",$rowname);
            if ($row['type']=='cid')
            {
                $str0_req .= ", '$rid' as pid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_cid\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }   
            elseif ($row['type']=='id')
            {
                $str0_req .= ", '$rid' as pid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.name as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_id\" as pv_$rowname INNER JOIN \"ETable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }   
            elseif ($row['type']=='mdid')
            {
                $str0_req .= ", '$rid' as pid_$rowname, pv_$rowname.value as id_$rowname, ct_$rowname.synonym as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_mdid\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value = ct_$rowname.id ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }   
            else
            {
                $str0_req .= ", '$rid' as pid_$rowname, '' as id_$rowname, pv_$rowname.value as name_$rowname";
                $str_req .=" LEFT JOIN \"CPropValue_$row[type]\" as pv_$rowname ON et.id = pv_$rowname.id AND pv_$rowname.pid=:$rowname";
            }    
            if ($filter['filter_id']!='')
            {
                if ($rid==$filter['filter_id'])
                {
                    $filtername = "pv_$rowname.value";
                    $filtertype = "$row[type]";
                }    
            }
            $params[$rowname]=$rid;
            
        }
        $strwhere='';
        if ($filtername!='')
        {
            $strwhere = DataManager::getstrwhere($filter,$filtertype,$filtername);
        }
        $str0_req .=" FROM tt_et as et";
        $sql = $str0_req.$str_req;
        if ($strwhere!='')
        {
            $sql .= " WHERE $strwhere";
        }
        $objs['SQL']=$sql;
	$res = DataManager::dm_query($sql,$params);
 
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $objs['ENT'][] = $row;
            $objs['LDATA'][$row['id']]=array();
            $objs['LDATA'][$row['id']]['id']=array('name'=>$row['id'],'id'=>'');
            $objs['LDATA'][$row['id']]['name']=array('name'=>$row['name'],'id'=>'');
            $objs['LDATA'][$row['id']]['synonym']=array('name'=>$row['synonym'],'id'=>'');
            foreach($plist as $row_plist) 
            {
                $rid = $row_plist['id'];    
                $field_val = str_replace(" ","",strtolower($row_plist['name']));
                $field_id = "pid_$field_val";
                $objs['LDATA'][$row['id']][$row[$field_id]] = array('id'=>$row['id_'.$field_val],'name'=>$row['name_'.$field_val]);
                if ($row_plist['type']=='date')
                {
                    $objs['LDATA'][$row['id']][$row[$field_id]] = array('id'=>'','name'=>substr($row['name_'.$field_val],0,10));
                }    
            }
        }
        $objs['PSET'] = MdpropertySet::getCPropList($plist,$edit_mode,true);
        
   	$sql = "SELECT count(*) as countrec FROM tt_et";
	$res = DataManager::dm_query($sql);	
	$objs['CNT_REC']=0;
        $row = $res->fetch(PDO::FETCH_ASSOC);
        $objs['CNT_REC']=$row['countrec'];
	$objs['TOP_REC']=$offset+1;
	if ($objs['CNT_REC']<$objs['TOP_REC'])
	  $objs['TOP_REC']=$objs['CNT_REC'];
	$objs['BOT_REC']=$offset+TZ_COUNT_REC_BY_PAGE;
	if ($objs['CNT_REC']<$objs['BOT_REC'])
	  $objs['BOT_REC'] = $objs['CNT_REC'];
	
	DataManager::droptemptable($artemptable);
	return $objs;
    }
    public static function getCollByName($mdid,$name) 
    {
        
	$sql = "select ct.id, ct.name, ct.synonym FROM \"CTable\" as ct
		WHERE ct.mdid=:mdid AND (ct.name ILIKE :name OR ct.synonym ILIKE :name) LIMIT 5";  
        
	$res = DataManager::dm_query($sql,array('mdid'=>$mdid, 'name'=>"%$name%"));
	$rows = $res->fetchAll(PDO::FETCH_ASSOC);
        if (!count($rows))
        {
            $sql = "select ct.id, ct.name, ct.synonym FROM \"CTable\" as ct inner join \"CTable\" as md on ct.mdid=md.mdid
                    WHERE md.id=:mdid AND (ct.name ILIKE :name OR ct.synonym ILIKE :name) LIMIT 5";  

            $res = DataManager::dm_query($sql,array('mdid'=>$mdid, 'name'=>"%$name%"));
            $rows = $res->fetchAll(PDO::FETCH_ASSOC);
        }  
        return $rows;
    }
}

