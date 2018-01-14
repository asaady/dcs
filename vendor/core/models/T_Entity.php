<?php
namespace Dcs\Vendor\Core\Models;

use PDO;

trait T_Entity {
    public function gettoString() 
    {
        $artoStr = array();
        foreach($this->plist as $prop)
        {
            if ($prop['ranktostring'] > 0) 
            {
              $artoStr[$prop['id']] = $prop['ranktostring'];
            }
        }
        if (!count($artoStr)) {
            foreach($this->plist as $prop) {
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
                if ($this->head->getmdtypename() == 'Docs')
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
            if ($this->head->getmdtypename()=='Docs')
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
    public function createtemptable_all($tt_entities)
    {
	$artemptable = array();
        
        $sql = DataManager::get_select_properties(" WHERE mp.mdid=:mdid AND mp.rank>0 ");
        $artemptable[1]= DataManager::createtemptable($sql,'tt_pt',array('mdid'=>$this->id));   
        
        $sql=DataManager::get_select_maxupdate($tt_entities,'tt_pt');
        $artemptable[2] = DataManager::createtemptable($sql,'tt_id');   
        
        
        $sql=DataManager::get_select_lastupdate('tt_id','tt_pt');
        $artemptable[3] = DataManager::createtemptable($sql,'tt_tv');   
        
        return $artemptable;
    }
    public function createTempTableEntitiesToStr($entities,$count_req) 
    {
        // делаем строку разделенных запятыми уидов в одинарных кавычках заключенная в круглые скобки
        $str_entities = "('".implode("','", $entities)."')"; 
        // соберем список ссылок в представлении (ranktostring>0) 
	$artemptable=array();
        $sql = DataManager::get_select_entities($str_entities,true);
        $artemptable[0] = DataManager::createtemptable($sql,'tt_t0');   
        
        $sql = DataManager::get_select_unique_mdid('tt_t0');
        $artemptable[1] = DataManager::createtemptable($sql,'tt_t1');   
        
        $sql = DataManager::get_select_properties(" WHERE mp.mdid in (SELECT mdid FROM tt_t1) AND mp.ranktostring>0 ");
        $artemptable[2] = DataManager::createtemptable($sql,'tt_t2');   
        
        $sql=DataManager::get_select_maxupdate('tt_t0','tt_t2');
        $artemptable[3] = DataManager::createtemptable($sql,'tt_t3');   
        
        $sql=DataManager::get_select_lastupdateForReq($count_req,'tt_t3','tt_t0');
        $artemptable[4] = DataManager::createtemptable($sql,'tt_t4');  
        
        return $artemptable;    
    }
    public function get_EntitiesFromList($entities,$ttname) 
    {
        $str_entities = "('".implode("','", $entities)."')";
        $sql = DataManager::get_select_entities($str_entities);
        return DataManager::createtemptable($sql,$ttname);
    }
    public function get_findEntitiesByProp($ttname, $propid, $ptype, $access_prop, $filter ,$limit) 
    {
        $mdid = $this->id;
        $params = array();
        $rec_limit = $limit*2;
        $prop_templ_id = '';
        $strwhere = '';
        $arprop = array();
        $mdentity = new Mdentity($mdid);
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

