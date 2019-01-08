<?php

namespace Dcs\Vendor\Core\Models;

use Dcs\Vendor\Core\Models\Entity;
use PDO;
use DateTime;
use Dcs\Vendor\Core\Models\DcsException;

class Sets extends Entity implements I_Sheet
{
    use T_Sheet;
    
    protected $docid;
    protected $propid;
    
    public function setnamesynonym()
    {
        $this->name = $this->mdname;
        $this->synonym = $this->mdsynonym;
    }        
    public function getaccessright_id($context)
    {
        return $this->get_head()->get_mdid();
    }        
    public function get_head_param()
    {
        $sql = "SELECT it.entityid, it.propid, max(it.dateupdate) from \"PropValue_id\" as pv "
                . "inner join \"IDTable\" as it on pv.id=it.id "
                . "where pv.value=:setid "
                . "group by it.entityid, it.propid";

        $res = DataManager::dm_query($sql,array('setid'=>$this->id));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getdocid()
    {
        return $this->docid;
    }        
    public function getpropid()
    {
        return $this->propid;
    }        
    public function head() 
    {
        $param = $this->get_head_param();
        if (!$param) {
            return NULL;
        }
        $this->docid = $param[0]['entityid'];
        $this->propid = $param[0]['propid'];
        return new Entity($this->docid);
    }
    function item($id,$hd='') 
    {
        return new Item($id,$hd);
    }
    public function item_classname()
    {
        return 'Item';
    }        
    public function gettoString() 
    {
        return $this->mdsynonym;
    }
    function __toString() 
    {
      return $this->mdsynonym;
    }
    public function txtsql_getproperties()
    {
        return "SELECT mp.id, mp.propid, pr.name as name_propid, mp.name, mp.synonym, 
            pst.value as type, pt.name as name_type, mp.length, mp.prec, mp.mdid, 
            mp.rank, mp.ranktostring, mp.ranktoset, mp.isedate, mp.isenumber, 
            mp.isdepend, pmd.value as valmdid, valmd.name AS name_valmdid, 
            valmd.synonym AS valmdsynonym, valmd.mditem as valmditem, 
            mi.name as valmdtypename, 1 as field,'active' as class FROM \"MDProperties\" AS mp
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
    }
    public function loadProperties()
    {
        $sql = $this->txtsql_getproperties();
        if ($sql === '')
        {
            return NULL;
        }    
        $properties = array();
        $params = array('mdid'=> $this->mdid);
        $res = DataManager::dm_query($sql,$params);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $properties[$row['id']] = $row;
        }    
        $key = array_search('Items', array_column($properties,'valmdtypename','id'));
        if ($key !== FALSE) {
            $params = array('mdid'=> $properties[$key]['valmdid']);
            $res = DataManager::dm_query($sql,$params);
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $properties[$row['id']] = $row;
            }    
        } else {
            $properties = array();
        }
        return $properties;
    }        
    function getItems($context)
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
        } else {
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
            if (($rowtype == 'id') || ($rowtype == 'cid')) {
                $objs[$row['entityid']][$row['id']] = array(
                       'id'=>$row[$rowtype.'_value'],
                       'name'=>$row['id_valuename']
                  );
                if ($rowtype == 'id') {    
                    if (($row['id_value']) && 
                            ($row['id_value'] != DCS_EMPTY_ENTITY)) {        
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
                } else {
                    $objs[$row['entityid']][$row['id']] = array(
                      'name' => $row[$rowtype.'_value'],
                      'id' => ''
                      );
                }    
                if ($activity_id !== FALSE) {
                    if ($row['id'] == $activity_id) {
                        if ($row['bool_value'] === FALSE) {    
                            $objs[$row['entityid']]['class'] = 'erased';
                        }    
                    }    
                }    
            }

        }
        if (count($arr_e)) {
            $this->fill_entsetname($objs,$arr_e);
        }    
	DataManager::droptemptable($artemptable);
	
	return $objs;
    }    
}
