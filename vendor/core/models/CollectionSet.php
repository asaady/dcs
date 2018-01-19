<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

class CollectionSet extends Head implements I_Head, I_Property
{
    use T_Head;
    use T_Collection;
    use T_CProperty;
    
    public function item() 
    {
        return new CollectionItem($this->id);
    }
    public function createtemptable_all($entities)
    {
        $str_entities = "('".implode("','", $entities)."')";
	$artemptable = array();
        $sql = DataManager::get_select_collections($str_entities);
        $artemptable[] = DataManager::createtemptable($sql,'tt_et');   
        
        return $artemptable;
    }
//      $filter: array 
//      id = property id (CProperties)
//      val = filter value
//      val_min = min filter value (optional)    
//      val_max = max filter value (optional)    
    public function findCollByProp($filter) 
    {
        $ftype='';
        $dbtable = '';
        $propid = $filter['filter_id']['id'];
        if ($propid != '') {
            $arprop = $this->properties[$propid];
            if ($arprop['type'] == 'text') {
                return array();
            }
            $dbtable = "CPropValue_$arprop[type]";
            $ftype = $arprop['type'];
        }
        $params = array();
        $strwhere = DataManager::getstrwhere($filter,$ftype,'pv.value',$params);
        if ($strwhere != '')
        {
            $sql = "SELECT DISTINCT pv.id as cid FROM \"$dbtable\" as pv WHERE $strwhere and pv.pid=:propid"; 
            $params['propid'] = $propid;
        }
        else
        {
            $sql = "SELECT et.id as cid FROM \"CTable\" as et WHERE et.mdid=:mdid LIMIT ".DCS_COUNT_REC_BY_PAGE; 
            $params = array('mdid'=>$this->id);
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
    public function getItemsByFilter($context, $filter) 
    {
        $prefix = $context['PREFIX'];
        $action = $context['ACTION'];
        $limit = $context['LIMIT'];
        $page = $context['PAGE'];
        $mdid = $this->id;
    	$objs = array();
	$objs['LDATA'] = array();
	$objs['PSET'] = array();
        $objs['actionlist'] = DataManager::getActionsbyItem('CollectionSet',$prefix,$action);
        if ($this->name == 'user_settings') {
            if (!User::isAdmin())
            {
                //это уид реквизита user в таблице user_settings
                $filter['filter_id']['id']='94f6b075-1536-4d16-a548-bc8128791127';
                $filter['filter_val']['id']=$_SESSION['user_id'];
                $filter['filter_val']['name']= User::getUserName($_SESSION['user_id']);
            }    
        }    
        $entities = $this->findCollByProp($filter);
        if (!count($entities))
        {
            $objs['RES']='list entities is empty';
            return $objs;
        }
	$offset=(int)($page-1)*$limit;
	$artemptable = $this->createtemptable_all($entities);
        $plist = $this->properties;
        $str0_req='SELECT et.id, et.name, et.synonym';
        $str_req='';
        $str_p = '';
        $filtername='';
        $filtertype='';
        $params = array();
        foreach($plist as $row) 
        {
            if (!$row['field']) {
                continue;
            }
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
        $dop = DataManager::get_col_access_text('et');
        if ($strwhere != '') {
            $sql .= " WHERE $strwhere";
            if ($dop!='') {
                $sql .= " AND ".$dop;
                $params['userid'] = $_SESSION['user_id'];
            }    
        } else {
            if ($dop != '') {
                $sql .= " WHERE ".$dop;
                $params['userid'] = $_SESSION['user_id'];
            }    
        }    
	$res = DataManager::dm_query($sql,$params);
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $objs['LDATA'][$row['id']]=array();
            $objs['LDATA'][$row['id']]['id']=array('name'=>$row['id'],'id'=>'');
            $objs['LDATA'][$row['id']]['name']=array('name'=>$row['name'],'id'=>'');
            $objs['LDATA'][$row['id']]['synonym']=array('name'=>$row['synonym'],'id'=>'');
            foreach($plist as $row_plist) 
            {
                if (!$row_plist['field']) {
                    continue;
                }
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
        $objs['PSET'] = $this->getProperties(TRUE,'toset');
   	$sql = "SELECT count(*) as countrec FROM tt_et";
	$res = DataManager::dm_query($sql);	
	$objs['CNT_REC']=0;
        $row = $res->fetch(PDO::FETCH_ASSOC);
        $objs['CNT_REC']=$row['countrec'];
	$objs['TOP_REC']=$offset+1;
	if ($objs['CNT_REC']<$objs['TOP_REC'])
	  $objs['TOP_REC']=$objs['CNT_REC'];
	$objs['BOT_REC']=$offset+DCS_COUNT_REC_BY_PAGE;
	if ($objs['CNT_REC']<$objs['BOT_REC'])
	  $objs['BOT_REC'] = $objs['CNT_REC'];
	
	DataManager::droptemptable($artemptable);
	return $objs;
    }
    public function getItemsByName($name)
    {
        $mdid = $this->id;
        $artt = array();
        if (!User::isAdmin())
        {
            $access_prop = self::get_access_prop();
            $arr_prop = array_unique(array_column($access_prop,'propid'));
        }
        else 
        {
            $access_prop = array();
            $arr_prop = array();
        }
        $mdentity = new Mdentity($this->id);
        if ($mdentity->getmditemname()=='Docs')
        {
            $sql = "select et.id, pv.value as name, it.dateupdate FROM \"PropValue_int\" as pv
                    inner join \"IDTable\" as it
                        inner join \"ETable\" as et
                        on it.entityid=et.id
                        inner join \"MDProperties\" as mp
                            inner join \"CTable\" as pt
                            on mp.propid=pt.id
                        on it.propid=mp.id
                    on pv.id=it.id";
            $str_where = " WHERE et.mdid=:mdid and mp.mdid = et.mdid and pv.value = :name LIMIT 30";  
            $params = array('mdid'=>$mdid, 'name'=>$name);
        }   
        else
        {
            $sql = "select et.id, pv.value as name, it.dateupdate FROM \"PropValue_str\" as pv
                    inner join \"IDTable\" as it
                        inner join \"ETable\" as et
                        on it.entityid=et.id
                        inner join \"MDProperties\" as mp
                            inner join \"CTable\" as pt
                            on mp.propid=pt.id
                        on it.propid=mp.id
                    on pv.id=it.id";
            $str_where = " WHERE et.mdid=:mdid and mp.mdid = et.mdid and pv.value ILIKE :name LIMIT 30";  
            $params = array('mdid'=>$mdid, 'name'=>"%$name%");
        }    
        $params_fin = array();
        $sql_rls = '';
        if (count($access_prop))
        {
            $arr_prop = array_unique(array_column($access_prop,'propid'));
            $arr_eprop = array_column($mdentity->getarProps(), 'propid','id');
            foreach ($arr_prop as $prop)
            {
                $isprop = array_search($prop, $arr_eprop);
                if ($isprop===FALSE)
                {
                    //в текущем объекте нет реквизита с таким значением $prop
                    continue;
                }    
                $str_val='';
                foreach ($access_prop as $ap)
                {
                    if ($prop!=$ap['propid'])
                    {
                       continue;
                    }    
                    $propname = $ap['propname'];
                    $rls_type = $ap['type'];
                    if (($ap['rd']===true)||($ap['wr']===true))
                    {
                        $str_val .= ",'"."$ap[value]"."'";
                    }    
                }    
                if ($str_val=='')
                {
                    return '';
                }    
                $str_val = "(".substr($str_val,1).")";
                $props_templ = new PropsTemplate($prop);
                if ($props_templ->getvalmdentity()->getid()==$mdid)    
                {
                    $sql_rls .= " INNER JOIN \"ETable\" as et_$propname ON et_$propname.id=et.id AND et_$propname.id IN $str_val";
                }    
                else
                {    
                    if (!in_array($prop, $params))
                    {        
                        $sql_rls .= " INNER JOIN \"IDTable\" as it_$propname inner join \"MDProperties\" as mp_$propname on it_$propname.propid=mp_$propname.id AND mp_$propname.propid=:$propname inner join \"PropValue_$rls_type\" as pv_$propname ON pv_$propname.id=it_$propname.id AND pv_$propname.value in $str_val ON it_$propname.entityid=et.id";
                        $params[$propname]=$prop;
                        $params_fin[$propname]=$prop;
                    }    
                }    
          }    
        }   
        if ($sql_rls<>'')
        {
            $sql .= $sql_rls;
        }    
        $sql .= $str_where;
        
        $artt[] = DataManager::createtemptable($sql,'tt_et',$params);   
        
//        $sqlr = "select * from tt_et";
//	$res = DataManager::dm_query($sqlr);
//	die(var_dump($res->fetchAll(PDO::FETCH_ASSOC))." sql = ".$sql. var_dump($params));
        
        
	$sql = "select id, max(dateupdate) as dateupdate FROM tt_et group by id";
        $artt[] = DataManager::createtemptable($sql,'tt_nml');   
        
	$sql = "select et.id, et.name FROM tt_et as et inner join tt_nml as nm on et.id=nm.id and et.dateupdate=nm.dateupdate";
        $artt[] = DataManager::createtemptable($sql,'tt_nm');   

        
	$sql = "select et.id, et.name, COALESCE(pv.value,TRUE) as activity, COALESCE(it.dateupdate,'epoch'::timestamp) as dateupdate FROM tt_nm as et 
                left join \"IDTable\" as it
                    inner join \"MDProperties\" as mp
                    on it.propid=mp.id
                    and mp.name='activity'
                    inner join \"PropValue_bool\" as pv
                    on it.id=pv.id
                on et.id=it.entityid";  
        $artt[] = DataManager::createtemptable($sql,'tt_act');   
        
	$sql = "select id, max(dateupdate) as dateupdate FROM tt_act group by id";
        $artt[] = DataManager::createtemptable($sql,'tt_actl');   
	$sql = "select et.id, et.name FROM tt_act as et inner join tt_actl as nm on et.id=nm.id and et.dateupdate=nm.dateupdate and et.activity";
        if ($sql_rls<>'')
        {
            $sql .= $sql_rls;
        }    
        $sql .= " LIMIT 5";
	$res = DataManager::dm_query($sql,$params_fin);
	$objs = $res->fetchAll(PDO::FETCH_ASSOC);
        DataManager::droptemptable($artt);
        return $objs;
    }
}

