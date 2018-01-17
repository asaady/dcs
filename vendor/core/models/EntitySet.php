<?php
namespace Dcs\Vendor\Core\Models;

use PDO;
use DateTime;
use Exception;

class EntitySet extends Head implements I_Head, I_Property
{
    use T_Entity;
    use T_EProperty;
    
    public function item() 
    {
        return new Entity($this->id);
    }
    public function head($mdid='') 
    {
        return NULL;
    }
    public static function fill_ent_name($arr_e,$arr_id,&$ldata)
    {
        $arr_entities = $this->getAllEntitiesToStr($arr_e);
        foreach($arr_id as $rid=>$prow)
        {
            foreach($ldata as $id=>$row) 
            {
                if (array_key_exists($rid, $row))
                {
                    $crow = $row[$rid];
                    if (array_key_exists($crow['id'], $arr_entities))
                    {
                        $ldata[$id][$rid]['name'] = $arr_entities[$crow['id']]['name'];
                    }    
                }        
            }
        }    
    }
    public static function add_filter_val($ent_obj,$mdid,&$ent_filter)
    {
        $ent_plist = $ent_obj->properties();
        //определим нужно ли фильтровать строки по реквизитам
        //поищем среди реквизитов объекта-хозяина реквизиты совпадающие по шаблону с реквизитами выбираемых строк
        //это реквизиты выбираемых строк
        $arr_ent_propid = array_column($ent_plist, 'propid','id'); // это массив шаблонов реквизитов объекта хозяина
        $plist = $this->getProperties(true,'toset');
        foreach ($plist as $prop)
        {
            if ($prop['type']=='id') //фильтруем только по полям ссылочного типа
            {
                $propid = $prop['propid']; // ид шаблона реквизита
                $key = array_search($propid, $arr_ent_propid);
                if ($key!==FALSE)
                {
                    //нашли реквизит по которому надо отфильтровать строки и добавили к массиву фильтров
                    $ent_filter[$prop['id']] = new Filter($prop['id'],$ent_obj->getattrid($key));
                }        
            }    
        }   
    }   
    
    //получаем объекты владельцы строк ТЧ
    public function get_tt_items($docid, $curid,&$ent_filter,$entities)
    {        
        $filter_id = '';
        $filter_pid = '';
        $tt_et = '';
        $mdid = $this->id;
        //надо найти Объекты у которых есть ТЧ к которой относятся запрошенные строки ТЧ
        $ar_obj = DataManager::get_parentmd_by_item($mdid);
        if (count($ar_obj) == 0) {
            return tt_et;
        }
        $ent_obj = new Entity($curid); // получим объект хозяин 
        $ent_plist = $ent_obj->properties();
        foreach ($ent_plist as $prop)
        {
            if ($prop['type'] != 'id')
            {
                continue;
            }    
            //проверка: у объекта хозяина есть реквизит тогоже mdid что и у владельца выбираемой сущности
            if (!array_key_exists($prop['valmdid'],$ar_obj))
            {
                continue;
            }   
            //нашли реквизит который имеет в себе ТЧ и строки ТЧ которые надо выбрать
            $filter_id = $prop['id'];
            $filter_pid = $ar_obj[$prop['valmdid']]['pid'];
            break;    
        }   
        if ($filter_id != '')
        {
            $item = new Entity($ent_obj->getattrid($filter_id));
            self::add_filter_val($item,$mdid,$ent_filter);
            if ($ent_obj->head->getmditemname()=='Items')
            {
                //хозяин объект сам является строкой
                $doc = new Entity($docid);
                self::add_filter_val($doc,$mdid,$ent_filter);
            }   
            $dop='';
            $params = array();
            if (count($entities))
            {
                $dop = " and it.childid in :str_entities";
                $params['str_entities'] =  "('".implode("','", $entities)."')"; 
            }    
            $setid = $item->getattrid($filter_pid); // это ИД ТЧ к которой проинадлежат искомые строки
            $sql = "SELECT it.childid as id, it.rank as rownum  FROM \"SetDepList\" as it WHERE it.parentid=:entityid and it.rank > 0";
            $params['entityid'] = $setid;
            $tt_et = DataManager::createtemptable($sql, 'tt_et',$params);
        }   
        return $tt_et;
    }
    public function sqltext_entitylist($filter,$arr_prop,$access_prop,$action,&$params)
    {
        $mdid = $this->id;
        $plist = $this->properties;
        $str0_req='SELECT et.id';
        $str_req='';
        $str_p = '';
        $orderstr='';
        $activity_id = array_search('activity', array_column($plist,'name','id'));
        foreach($plist as $row) 
        {
            $rid = $row['id'];
            $rowname = str_replace(" ","",strtolower($row['name']));
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
            elseif ($row['type']=='int') 
            {
                if (strtolower($row['name'])=='rank') 
                {
                    $orderstr= ' order by name_'.$rowname;
                }    
            }
            elseif ($row['type']=='date') 
            {
                if ($row['isedate']) 
                {
                    $orderstr= ' order by name_'.$rowname.' DESC';
                }    
                $str0_t = ", tv_$rowname.propid as propid_$rowname, date_trunc('second', COALESCE(pv_$rowname.value,'epoch'::timestamp)) as name_$rowname, '' as id_$rowname";
                $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
            }
            if ($activity_id!==FALSE)
            {
                if ($rid==$activity_id)
                {
                    $str0_t = ", tv_$rowname.propid as propid_$rowname, COALESCE(pv_$rowname.value,true) as name_$rowname, '' as id_$rowname";
                    $str_t =" LEFT JOIN tt_tv as tv_$rowname LEFT JOIN \"PropValue_$row[type]\" as pv_$rowname ON tv_$rowname.tid = pv_$rowname.id ON et.id=tv_$rowname.entityid AND tv_$rowname.propid='$rid'";
                }    
            }    
            $str0_req .= $str0_t;
            $str_req .=$str_t;
            
            if ($row['valmdid']==$mdid)    
            {
                //rls совпал с объектом - в таком случае фильтруем по id объекта а не по реквизиту объекта
                continue;
            }  
            else
            {
                if (in_array($row['propid'], $arr_prop))
                {
                    $rls = self::arr_rls($row['propid'], $access_prop, $action);
                    if (!count($rls))
                    {    
                        //rls есть а доступных значений реквизита нет - значит доступ к списку запрещен
                        return $objs;
                    }
                    else 
                    {
                        $filter[$rid] = new Filter($rid, $rls);
                    }    
                }        
            }    
        }
        $str0_req .=" FROM tt_et as et";
        $sql = $str0_req.$str_req;
        
        $strw = Filter::getstrwhere($filter, 'pv_','.value',$params);
        $show_erased='';
        if ($activity_id!==FALSE)
        {
            $show_erased = DataManager::getSetting('show_deleted_rows');
        }    
        if (strtolower($show_erased)=='false')
        {
            $strw .= ' AND COALESCE(pv_activity.value,true)';
        }    
        if ($strw!='')
        {    
            $strw = substr($strw,5);
            $sql .= " WHERE $strw";
        }
        $sql .= $orderstr;
        return $sql;
    }
    public function getItemsByFilter($context, $filter) 
    {
        $prefix = $context['PREFIX'];
        $action = $context['ACTION'];
        $limit = $context['LIMIT'];
        $page = $context['PAGE'];
    	$objs = array();
	$objs['LDATA'] = array();
	$objs['PSET'] = array();
        $objs['actionlist'] = DataManager::getActionsbyItem('EntitySet',$prefix,$action);
        $propid = $filter['filter_id']['id']; //это уид реквизита отбора для выборки
        $docid = (array_key_exists('docid', $filter) ? $filter['docid']['id'] : '');  
        $curid = (array_key_exists('curid', $filter) ? $filter['curid']['id'] : '');  
        $ptype = '';
        $mdid = $this->id;
        $ent_filter = array();
        if ($propid != '') {
            $ent_filter[$propid] = new Filter($propid,$filter['filter_val']['id']);
        }
	$offset=(int)($page-1)*$limit;
        $access_prop = array();
        $arr_prop = array();
        $entities = array();
        if (!User::isAdmin())
        {
            //вкл rls: добавим поля отбора в список реквизитов динамического списка
            $access_prop = self::get_access_prop();
            $arr_prop = array_unique(array_column($access_prop,'propid'));
            $expr = function($row) use ($arr_prop) { return (($row['ranktoset']==0)&&(!in_array($row['propid'], $arr_prop))); };            
        }    
        else
        {
            $expr = function($row){ return ($row['ranktoset']==0); };
        }    
        $tt_et = '';
        if (count($arr_prop))
        {    
            foreach ($arr_prop as $prop)
            {
                $props_templ = new PropsTemplate($prop);
                if ($props_templ->getvalmdentity()->getid()==$mdid)
                {
                    //на объект есть список доступа - тогда просто выбираем эти объекты из списка
                    $entities = array_unique(array_column(array_filter($access_prop,function($row) use ($prop) { return ($row['propid']==$prop); }),'value'));
                }    
            }    
        }
        if ($this->getmditemname()=='Items') //запрошены строки ТЧ?
        {
            $tt_et = $this->get_tt_items($docid, $curid, $ent_filter, $entities);
        }    
        else
        {
            if (count($entities))
            {
                $tt_et = $this->get_EntitiesFromList($entities,'tt_et',$limit);
            }    
        }    
        if ($tt_et == '')
        {
            $tt_et = $this->get_findEntitiesByProp('tt_et', $propid, $ptype, $access_prop, $ent_filter ,$limit);
        }    
        if ($tt_et == '')
        {
            $objs['RES'] = 'list entities is empty';
            return $objs;
        }
        $artemptable = array();
	$this->createtemptable_all($tt_et,$artemptable);
        $artemptable[] = $tt_et;
        $params = array();
        $sql = $this->sqltext_entitylist($ent_filter,$arr_prop,$access_prop,$action,$params);
        if ($sql=='')
        {
            $objs['RES']='access denied';
            return $objs;
        }    
	$res = DataManager::dm_query($sql,$params);
        $arr_e = array();
        $arr_name = array();
        $arr_id = array_filter($this->properties, function ($prow) { return $prow['type'] == 'id'; });
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $objs['LDATA'][$row['id']]= array('id'=>$row['id'],'name'=>'','class' => 'active');
            $arr_name[$row['id']] = array();
            foreach($this->properties as $rid => $row_plist) {
                $field_val = strtolower(str_replace(" ","",strtolower($row_plist['name'])));
                $field_id = "propid_$field_val";
                $rowid = "id_$field_val";
                $rowname = "name_$field_val";
                $r_name = $row[$rowname];
                $r_id = $row[$rowid];
                if (strtolower($row_plist['name']) == 'activity')
                {
                    if ($row[$rowname]===false)
                    {    
                        $objs['LDATA'][$row['id']]['class'] ='erased';               
                    }    
                }    
                if ($row_plist['type'] == 'id') {
                    if ($row[$rowid]!='') {
                        if (!in_array($row[$rowid],$arr_e)){
                            $arr_e[]=$row[$rowid];
                        }
                    }
                    $r_name = '';
                } else {
                    if ($row_plist['type'] == 'date') {
                        $r_name = substr($r_name,0,10);
                    }    
                }    
                $objs['LDATA'][$row['id']][$row[$field_id]] = array('id'=>$r_id,'name'=>$r_name);
                if ($row_plist['ranktostring'] > 0) {
                    $arr_name[$row['id']][$row_plist['ranktostring']] = $r_name;
                }
            }
        }
        if (count($arr_e))
        {
            $arr_entities = $this->getAllEntitiesToStr($arr_e);
            foreach($arr_id as $rid=>$prow)
            {
                foreach($objs['LDATA'] as $id=>$row) 
                {
                    if (array_key_exists($rid, $row))
                    {
                        $crow = $row[$rid];
                        if (array_key_exists($crow['id'], $arr_entities)){
                            $objs['LDATA'][$id][$rid]['name'] = $arr_entities[$crow['id']]['name'];
                            if ($prow['ranktostring'] > 0) {
                                $arr_name[$row['id']][$prow['ranktostring']] = $arr_entities[$crow['id']]['name'];
                            }
                            
                        }    
                    }        
                }
            }    
        }
        $objs['LNAME'] = $arr_name;
        $objs['PSET'] = $this->getProperties(true,'toset');
        $sql = "SELECT count(*) as countrec FROM tt_tv";
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

