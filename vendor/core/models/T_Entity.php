<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;

trait T_Entity {
    public function gettoString() 
    {
        $artoStr = array();
        if ($this->head->getmditemname() == 'Sets') {
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
        if (count($artoStr)) {
            $res = '';
            foreach($artoStr as $prop => $rank)
            {
                if ($this->head->getmditemname() == 'Docs')
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
                if ($this->properties[$prop]['type'] == 'date')
                {
                    $name =substr($name,0,10);
                }
                $res .=' '.$name;
            }
            if ($this->head->getmditemname()=='Docs')
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
    public function getDetails($entityid) 
    {
	$sql = "select et.id, '' as name, '' as synonym, 
                et.mdid , md.mditem as mditem, md.name as mdname, md.synonym as mdsynonym, 
                tp.name as mdtypename, tp.synonym as mdtypedescription 
                FROM \"ETable\" as et
		    INNER JOIN \"MDTable\" as md
			INNER JOIN \"CTable\" as tp
			ON md.mditem = tp.id
		    ON et.mdid = md.id 
		WHERE et.id=:entityid";  
	$res = DataManager::dm_query($sql,array('entityid'=>$entityid));
        $objs = $res->fetch(PDO::FETCH_ASSOC);
	if(!$objs) {
            $objs = array('id'=>'','mdid'=>'','mditem'=>'');
	}
        return $objs;
    }
    public function get_tt_sql_data()
    {
        $artemptable = array();
        $sql = "SELECT max(it.dateupdate) AS dateupdate, it.entityid, it.propid "
                . "FROM \"IDTable\" as it "
                . "INNER JOIN \"MDProperties\" as mp "
                . "ON it.propid = mp.id AND mp.mdid = :mdid "
                . "WHERE it.entityid = :id "
                . "GROUP BY it.entityid, it.propid";
        $artemptable[] = DataManager::createtemptable($sql,'tt_id',array('mdid'=>$this->head->getid(),'id'=>$this->id));   
        $sql = "SELECT t.id as tid, t.userid, ts.dateupdate, ts.entityid
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
            $rid = $row['id'];
            $rowname = "$row[id]";
            $rowname = str_replace("-","",$rowname);
            $str0_t = ", tv_$rowname.propid as propid_$rowname, pv_$rowname.value as name_$rowname, '' as id_$rowname";
            $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            if ($row['type']=='id') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, '' as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            } elseif ($row['type']=='cid') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, ct_$rowname.synonym as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname INNER JOIN \"CTable\" as ct_$rowname ON pv_$rowname.value=ct_$rowname.id ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            } elseif ($row['type']=='mdid') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, ct_$rowname.synonym as name_$rowname, pv_$rowname.value as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname INNER JOIN \"MDTable\" as ct_$rowname ON pv_$rowname.value=ct_$rowname.id ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            } elseif ($row['type']=='date') {
                $str0_t = ", tv_$rowname.propid as propid_$rowname, to_char(pv_$rowname.value,'DD.MM.YYYY') as name_$rowname, '' as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            }
            $str0_req .= $str0_t;
            $str_req .=$str_t;
        }
        $str0_req .=" FROM \"Entity\" as et";
        $sql = $str0_req.$str_req." WHERE et.id=:id";
        $artemptable[] = DataManager::createtemptable($sql,'tt_out',array('id'=>$this->id));   
        return $artemptable;
    }    
    public function createtemptable_all($tt_entities,&$artemptable)
    {
        $sql = DataManager::get_select_properties(" WHERE mp.mdid=:mdid AND mp.rank>0 ");
        $artemptable[]= DataManager::createtemptable($sql,'tt_pt',array('mdid'=>($this->head) ? $this->head->getid() : $this->id));   
        
        $sql=DataManager::get_select_maxupdate($tt_entities,'tt_pt');
        $artemptable[] = DataManager::createtemptable($sql,'tt_id');   
        
        $sql=DataManager::get_select_lastupdate('tt_id','tt_pt');
        $artemptable[] = DataManager::createtemptable($sql,'tt_tv');   
    }
    
    public function createtemptable_allprop($entities)
    {
	$artemptable=array();
        $artemptable[] = self::get_EntitiesFromList($entities,'tt_et');   
        $this->createtemptable_all('tt_et',$artemptable);
        
        return $artemptable;    
    }
    public function get_findEntitiesByProp($ttname, $propid, $ptype, $access_prop, $filter ,$limit) 
    {
        $mdid = ($this->head) ? $this->head->getid() : $this->id;
        $params = array();
        $rec_limit = $limit*2;
        $prop_templ_id = '';
        $strwhere = '';
        $arprop = array();
        if ($propid!='')
        {
            if ($ptype<>'text')
            {
                $prop_templ_id = $arprop['propid'];
                $strwhere = DataManager::getstrwhere($filter,$ptype,'pv.value',$params);
            }
        }
        if ($strwhere!='')
        {
            $strjoin = "it.entityid";
            $sql = "SELECT DISTINCT it.entityid as id FROM \"PropValue_$ptype\" as pv INNER JOIN \"IDTable\" as it ON pv.id=it.id AND it.propid=:propid"; 
            $params['propid']=$propid;
        }
        else
        {
            $key_edate = array_search(true, array_column($this->properties, 'isedate','id'));
            if ($key_edate !== FALSE)
            {
                //если есть реквизит с установленным флагом isedate сортируем по этому реквизиту по убыванию
                $strjoin = "et.id";
                $sql = "SELECT et.id, COALESCE(pv.value,'epoch'::timestamp) as value FROM \"ETable\" as et LEFT JOIN \"IDTable\" as it  INNER JOIN \"PropValue_date\" as pv ON pv.id=it.id AND it.propid=:propid ON et.id=it.entityid "; 
                $strwhere = " et.mdid=:mdid";
                $params['propid'] = $key_edate;
                $params['mdid'] = $mdid;
            }        
            else 
            {
                $strwhere = " et.mdid=:mdid";
                $strjoin = "et.id";
                $sql = "SELECT et.id FROM \"ETable\" as et"; 
                $params['mdid'] = $mdid;
            }
        }   
        $sql_rls = '';
        if (count($access_prop))
        {
            $arr_prop = array_unique(array_column($access_prop,'propid'));
            foreach ($arr_prop as $prop)
            {
                if ($prop==$prop_templ_id)
                {
                    continue;
                }    
                $isprop = array_search($prop, array_column($this->properties, 'propid','id'));
                if ($isprop===FALSE)
                {
                    //в текущем объекте нет реквизита с таким значением $prop
                    continue;
                }    
                $str_val='';
                $propname='';
                $prop_id= '';
                foreach ($access_prop as $ap)
                {
                    if ($prop<>$ap['propid'])
                    {
                        continue;
                    }    
                    $rls_type = $ap['type'];
                    if (($ap['rd']===true)||($ap['wr']===true))
                    {
                        $str_val .= ",'"."$ap[value]"."'";
                    }    
                    $propname=$ap['propname'];
                    $prop_id=$ap['propid'];                    
                }    
                if ($str_val=='')
                {
                    return '';
                }    
                $str_val = "(".substr($str_val,1).")";
                $props_templ = new PropsTemplate($prop);
                if ($props_templ->getvalmdentity()->getid()==$mdid)    
                {
                    $sql_rls .= " INNER JOIN \"ETable\" as et_$propname ON et_$propname.id=$strjoin AND et_$propname.id IN $str_val";
                }    
                else
                {    
                    if (!in_array($ap['propid'], $params))
                    {        
                        $sql_rls .= " INNER JOIN \"IDTable\" as it_$propname inner join \"MDProperties\" as mp_$propname on it_$propname.propid=mp_$propname.id AND mp_$propname.propid=:$propname inner join \"PropValue_$rls_type\" as pv_$propname ON pv_$propname.id=it_$propname.id AND pv_$propname.value in $str_val ON it_$propname.entityid=$strjoin";
                        $params[$propname]=$prop_id;
                    }    
                }    
            }    
        }   
        if ($sql_rls<>'')
        {
            $sql .= $sql_rls;
        }    
        if ($strwhere<>'')
        {
            $sql .= " WHERE $strwhere";
        }    
        $sql .= " LIMIT $rec_limit";
        
        return DataManager::createtemptable($sql,$ttname,$params);
    }    
}            

