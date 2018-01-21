<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;

trait T_Entity {
    public function txtsql_getproperties() 
    {
        $sql = "SELECT mp.id, mp.propid, pr.name as name_propid, mp.name, 
                       mp.synonym, pst.value as typeid, pt.name as type, 
                       mp.length, mp.prec, mp.mdid, mp.rank, mp.ranktostring, 
                       mp.ranktoset, mp.isedate, mp.isenumber, mp.isdepend, 
                       pmd.value as valmdid, valmd.name AS name_valmdid, 
                       valmd.synonym AS valmdsynonym, valmd.mditem as valmditem, 
                       mi.name as valmdtypename, 1 as field FROM \"MDProperties\" AS mp
		  LEFT JOIN \"CTable\" as pr
		    LEFT JOIN \"CPropValue_mdid\" as pmd
        		INNER JOIN \"MDTable\" as valmd
                            INNER JOIN \"CTable\" as mi
                            ON valmd.mditem = mi.id
                        ON pmd.value = valmd.id
		    ON pr.id = pmd.id
		    LEFT JOIN \"CPropValue_cid\" as pst
                        INNER JOIN \"CProperties\" as cprs
                        ON pst.pid = cprs.id
                        AND cprs.name='type'
                        INNER JOIN \"CTable\" as pt
                        ON pst.value = pt.id
		    ON pr.id = pst.id
		  ON mp.propid = pr.id
		WHERE mp.mdid = :mdid 
		ORDER BY rank";
        return $sql;
    }
    public function loadProperties()
    {
        $sql = $this->txtsql_getproperties();
        if ($sql === '')
        {
            return 0;
        }    
        $params = array('mdid'=> $this->mdid);
        $res = DataManager::dm_query($sql,$params);
        $properties = array();
        $cnt = 0;
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $properties[$row['id']] = $row;
            $cnt++;
        }    
        if ($this->mdtypename === 'Sets') {
            $key = array_search('Items', array_column($properties,'valmdtypename','id'));
            if ($key !== FALSE) {
                $params = array('mdid'=> $properties[$key]['valmdid']);
                $res = DataManager::dm_query($sql,$params);
                $properties = array();
                $cnt = 0;
                while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    $properties[$row['id']] = $row;
                    $cnt++;
                }    
            }
        }
        $this->properties = $properties;
        return $cnt;
    }        
    public function get_EntitiesFromList($entities, $ttname) 
    {
//        if ($entities[0]=='') {
//            die(var_dump($this));
//        }
        $str_entities = "('".implode("','", $entities)."')";
        $sql = DataManager::get_select_entities($str_entities);
        return DataManager::createtemptable($sql,$ttname);
    }
    public function createtemptable_all($tt_entities,&$artemptable)
    {
        $sql = DataManager::get_select_properties(" WHERE mp.mdid=:mdid AND mp.rank>0 ");
        $artemptable[]= DataManager::createtemptable($sql,'tt_pt',array('mdid'=>$this->mdid));   
        
        $sql=DataManager::get_select_maxupdate($tt_entities,'tt_pt');
        $artemptable[] = DataManager::createtemptable($sql,'tt_id');   
        
        $sql=DataManager::get_select_lastupdate('tt_id','tt_pt');
        $artemptable[] = DataManager::createtemptable($sql,'tt_tv');   
    }
    public function createTempTableEntitiesToStr($entities,$count_req) 
    {
	$artemptable=array();
        
        $artemptable[] = self::get_EntitiesFromList($entities,'tt_t0');   
        
        $sql = DataManager::get_select_unique_mdid('tt_t0');
        $artemptable[] = DataManager::createtemptable($sql,'tt_t1');   
        
        $sql = DataManager::get_select_properties(" WHERE mp.mdid in (SELECT mdid FROM tt_t1) AND mp.ranktostring>0 ");
        $artemptable[] = DataManager::createtemptable($sql,'tt_t2');   
        
        $sql=DataManager::get_select_maxupdate('tt_t0','tt_t2');
        $artemptable[] = DataManager::createtemptable($sql,'tt_t3');   
        
        $sql=DataManager::get_select_lastupdateForReq($count_req,'tt_t3','tt_t0');
        $artemptable[] = DataManager::createtemptable($sql,'tt_t4');  
        
        return $artemptable;    
    }
    public function getEntitiesToStr($entities,&$all_entities,&$data,&$count_req) 
    {
        // entities - массив ссылок
        $artemptable = $this->createTempTableEntitiesToStr($entities,$count_req);
        $sql = "SELECT * FROM tt_t4";
	$res = DataManager::dm_query($sql);
        $objs = $res->fetchAll(PDO::FETCH_ASSOC);
            
        $data += $objs;
        $all_entities +=$entities;
          
        $sql = "SELECT DISTINCT pv_id.value as entityid FROM tt_t4 AS ts INNER JOIN \"PropValue_id\" AS pv_id ON ts.tid = pv_id.id";
	$res = DataManager::dm_query($sql);
        $objs = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (!in_array($row['entityid'],$all_entities ))
            {
                $objs[] = $row['entityid'];
            }

        }
      	DataManager::droptemptable($artemptable);
        if (count($objs))
        {
            $add_entities = $objs;
            if ($count_req<5) 
            {//ограничим глубину рекурсии до посмотреть
                ++$count_req;
                $add_entities = $this->getEntitiesToStr($add_entities,$all_entities,$data,$count_req);
            }
        }
        return $objs;
    }
    public function getAllEntitiesToStr($entities) 
    {
        $all_entities = array();
        $count_req = 1;
        $data = array();
        $add_entities = $this->getEntitiesToStr($entities,$all_entities, $data,$count_req);
        $str_entities = "('".implode("','", $all_entities)."')"; 
    	$sql = "SELECT DISTINCT et.mdid, md.name, md.synonym FROM \"ETable\" as et INNER JOIN \"MDTable\" as md ON et.mdid=md.id WHERE et.id in $str_entities"; 
	$res = DataManager::dm_query($sql);
        $armd = $res->fetchAll(PDO::FETCH_ASSOC);
        $str_md = "('".implode("','", array_column($armd,'mdid'))."')"; 
        // соберем список ссылок в представлении (ranktostring>0) 
    	$sql = "SELECT mp.rank, mp.id, mp.name, ct_type.name as type, mp.mdid, mp.synonym, mp.isenumber, mp.isedate FROM \"MDProperties\" as mp "
                . "INNER JOIN \"CTable\" as pr "
                . "INNER JOIN \"CPropValue_cid\" as pv_type "
                . "INNER JOIN \"CProperties\" as cp_type "
                . "ON pv_type.pid = cp_type.id "
                . "AND cp_type.name='type' "
                . "INNER JOIN \"CTable\" as ct_type "
                . "ON pv_type.value = ct_type.id "
                . "ON pr.id = pv_type.id "
                . "ON mp.propid = pr.id "
                . "WHERE mp.ranktostring>0 AND mp.mdid IN $str_md ORDER BY mp.ranktostring"; 
        
	$res = DataManager::dm_query($sql);
        $props = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $props[$row['id']] = $row;
        }
        $arr_tid = array_unique(array_column($data,'tid'));
        $str_tid = "('".implode("','", $arr_tid)."')"; 
	$sql = "SELECT t.id as tid, t.propid, t.entityid,
		       pv_str.value as str_value, 
		       pv_int.value as int_value, 
		       pv_id.value as id_value, 
		       ct_cid.synonym as cid_value, 
		       pv_date.value as date_value, 
		       pv_float.value as float_value, 
		       pv_file.value as file_value, 
		       pv_bool.value as bool_value, 
		       pv_text.value as text_value
		FROM \"IDTable\" AS t 
		LEFT JOIN \"PropValue_str\" AS pv_str
		ON t.id = pv_str.id	
		LEFT JOIN \"PropValue_id\" AS pv_id
		ON t.id = pv_id.id	
		LEFT JOIN \"PropValue_cid\" AS pv_cid
                INNER JOIN \"CTable\" as ct_cid
                ON pv_cid.value=ct_cid.id
		ON t.id = pv_cid.id	
		LEFT JOIN \"PropValue_int\" AS pv_int
		ON t.id = pv_int.id	
		LEFT JOIN \"PropValue_date\" AS pv_date
		ON t.id = pv_date.id	
		LEFT JOIN \"PropValue_bool\" AS pv_bool
		ON t.id = pv_bool.id	
		LEFT JOIN \"PropValue_file\" AS pv_file
		ON t.id = pv_file.id	
		LEFT JOIN \"PropValue_text\" AS pv_text
		ON t.id = pv_text.id	
		LEFT JOIN \"PropValue_float\" AS pv_float
		ON t.id = pv_float.id  
                WHERE t.id in $str_tid";
        
	$res = DataManager::dm_query($sql);
        $vals = $res->fetchAll(PDO::FETCH_ASSOC);
        $objs=array();
        for ($i=$count_req;$i>0;$i--){
            foreach ($armd as $mdrow) 
            {
                $mdid = $mdrow['mdid'];
                $filtered_prop = array_filter ($props, function ($item) use ($mdid) { return ($item['mdid']==$mdid); });
                $filtered_data = array_filter ($data, function ($item) use ($i, $mdid) { return (($item['creq']==$i)AND($item['mdid']==$mdid)); });

                foreach ($filtered_data as $row_data)
                {    
                    $entityid = $row_data['entityid'];
                    if (count($objs)) 
                    {
                        $filtered_objs = array_filter ($objs, function ($item) use ($entityid) { return ($item['id']==$entityid); });
                        if (count($filtered_objs))
                        {
                            continue;
                        }
                    }    
                    $objs[$entityid] = array();
                    $objs[$entityid]['name']=''; 
                    $objs[$entityid]['id']=$entityid; 
                    foreach ($filtered_prop as $row_prop)
                    {
                        $propid = $row_prop['id'];
                        $colname= "$row_prop[type]_value";
                        $filtered_vals = array_filter ($vals, function ($item) use ($entityid,$propid) { return (($item['entityid']==$entityid)AND($item['propid']==$propid)); });
                        if (count($filtered_vals))
                        {
                            foreach ($filtered_vals as $row_val)
                            {
                                if ($row_prop['type']=='id')
                                {
                                    $valid = $row_val[$colname];    
                                    if (array_key_exists($valid, $objs))
                                    {
                                        $cname = $objs[$valid];
                                        $objs[$entityid]['name'] .= " $cname[name]";
                                    }
                                }
                                else
                                {
                                    $name = $row_val[$colname];
                                    if ($row_prop['isenumber']===true)
                                    {    
                                        $name =$mdrow['synonym']." №$name";
                                    }
                                    elseif ($row_prop['isedate']===true)
                                    {
                                        $datetime = new DateTime($name);
                                        $name = " от ".$datetime->format('d-m-y');
                                    }    
                                    $objs[$entityid]['name'].=" $name";
                                }
                            }
                        }
                    }    
                    if ($objs[$entityid]['name']!='')
                    {    
                        $objs[$entityid]['name'] = trim($objs[$entityid]['name']);
                    }    
                }
            }    
        }
        return $objs;
    }
}            

