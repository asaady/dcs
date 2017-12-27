<?php
namespace tzVendor;
use PDO;
use DateTime;
use tzVendor\Filter;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");

class EntitySet extends Model {
    protected $mditem;     
    
    
    public function __construct($id='') {
	if ($id=='') {
            throw new Exception("empty id entityset");
	}
        $arPar = Mdentity::getMD($id);
        $this->id = $id; 
        $this->name = $arPar['name']; 
        $this->synonym = $arPar['synonym']; 
        $this->mditem = new Mditem($arPar['mditem']); 
        $this->version = time();
    }
    function getmditem()
    {
        return $this->mditem;
    }
    function get_data($mode='') 
    {
        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'synonym'=>$this->synonym,
          'version'=>$this->version,
          'mdtype'=>$this->mditem->getname(),
          'mditem'=>$this->mditem->getid(),
          'mditemsynonym'=>$this->mditem->getsynonym(),
          'PSET' => MdpropertySet::getMDProperties($this->id,$mode," WHERE mp.mdid = :mdid AND mp.ranktoset>0 ",true),
          'navlist' => array(
              $this->mditem->getid() => $this->mditem->getsynonym(),
              $this->id => $this->synonym
            )
        );
    }
    function create($data) 
    {
        $entity = new Entity($this->id);
        $entity->set_data($data);
        return $entity->createNew();
    }
    
    function createtemptable_all($tt_entities,$mdid)
    {
	$artemptable = array();
        
        $sql = DataManager::get_select_properties(" WHERE mp.mdid=:mdid AND mp.rank>0 ");
        $artemptable[1]= DataManager::createtemptable($sql,'tt_pt',array('mdid'=>$mdid));   
        
        $sql=DataManager::get_select_maxupdate($tt_entities,'tt_pt');
        $artemptable[2] = DataManager::createtemptable($sql,'tt_id');   
        
        
        $sql=DataManager::get_select_lastupdate('tt_id','tt_pt');
        $artemptable[3] = DataManager::createtemptable($sql,'tt_tv');   
        
        return $artemptable;
    }
    
    public static function createTempTableEntitiesToStr($entities,$count_req) 
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

    public static function get_EntitiesFromList($entities,$ttname) 
    {
        $str_entities = "('".implode("','", $entities)."')";
        $sql = DataManager::get_select_entities($str_entities);
        return DataManager::createtemptable($sql,$ttname);
    }
    public static function get_findEntitiesByProp($ttname, $mdid, $propid, $ptype, $access_prop, $filter ,$limit) 
    {
//      $filter: array 
//      id = property id (MDProperties)
//      val = filter value
//      val_min = min filter value (optional)    
//      val_max = max filter value (optional)    
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
            $key_edate = array_search(true, array_column($mdentity->getarProps(), 'isedate','id'));
            if ($key_edate!==FALSE)
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
                $isprop = array_search($prop, array_column($mdentity->getarProps(), 'propid','id'));
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
    
    public static function get_access_prop()
    {
        $userid=$_SESSION['user_id'];
        $sql = "select pv_group.value as user_group, pv_prop.value as propid, ct_prop.name as propname, pt.name as type, pv_val.value as value, pv_rd.value as rd, pv_wr.value as wr from \"CTable\" as ct
                    inner join \"MDTable\" as mt
                    on ct.mdid = mt.id
                    and mt.name='access_rights'
                    inner join \"CPropValue_cid\" as pv_group
                        inner join \"CProperties\" as cp_group
                        on pv_group.pid=cp_group.id
                        and cp_group.name='user_group'
                        inner join \"CTable\" as ct_gr
                            inner join \"MDTable\" as mt_gr
                            on ct_gr.mdid = mt_gr.id
                            and mt_gr.name='usergroup'
                            inner join \"CPropValue_cid\" as pv_grp
                                    inner join \"CProperties\" as cp_grp
                                    on pv_grp.pid=cp_grp.id
                                    and cp_grp.name='group'
                            on ct_gr.id=pv_grp.id
                            inner join \"CPropValue_cid\" as pv_usr
                                    inner join \"CProperties\" as cp_usr
                                    on pv_usr.pid=cp_usr.id
                                    and cp_usr.name='user'
                            on ct_gr.id=pv_usr.id
                            and pv_usr.value = :userid
                        on pv_group.value = pv_grp.value
                    on ct.id=pv_group.id
                    left join \"CPropValue_cid\" as pv_prop
                            inner join \"CProperties\" as cp_prop
                            on pv_prop.pid=cp_prop.id
                            and cp_prop.name='prop_template'
                            inner join \"CPropValue_cid\" as pst
                                INNER JOIN \"CProperties\" as cprs
                                ON pst.pid = cprs.id
                                AND cprs.name='type'
                                INNER JOIN \"CTable\" as pt
                                ON pst.value = pt.id
                            on pv_prop.value = pst.id
                            inner join \"CTable\" as ct_prop
                            on pv_prop.value = ct_prop.id
                    on ct.id=pv_prop.id
                    left join \"CPropValue_str\" as pv_val
                            inner join \"CProperties\" as cp_val
                            on pv_val.pid=cp_val.id
                            and cp_val.name='value'
                    on ct.id=pv_val.id
                    left join \"CPropValue_bool\" as pv_rd
                            inner join \"CProperties\" as cp_rd
                            on pv_rd.pid=cp_rd.id
                            and cp_rd.name='read'
                    on ct.id=pv_rd.id
                    left join \"CPropValue_bool\" as pv_wr
                            inner join \"CProperties\" as cp_wr
                            on pv_wr.pid=cp_wr.id
                            and cp_wr.name='write'
                    on ct.id=pv_wr.id";
	$res = DataManager::dm_query($sql, array('userid'=>$userid));	
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
    
    static function fill_ent_name($arr_e,$arr_id,&$ldata)
    {
        $arr_entities = self::getAllEntitiesToStr($arr_e);
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
        $plist = MdpropertySet::getMDProperties($mdid, 'CONFIG', " WHERE mp.mdid = :mdid AND mp.ranktoset>0 ",true);
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
    public static function get_tt_items($docid,$mdid,$curid,&$ent_filter,$entities)
    {        
        $filter_id = '';
        $filter_pid = '';
        $tt_et = '';
        //надо найти Объекты у которых есть ТЧ к которой относятся запрошенные строки ТЧ
        $sql = "select mi.name as mditem, mdp.name as mdname, mdp.id as mdid, mp.id as pid, mp.propid, pv.value as setmdid from \"MDTable\" as mdp
                    inner join \"CTable\" as mi
                    on mdp.mditem = mi.id
                    inner join \"MDProperties\" as mp 
                        inner join \"CTable\" as ct
                            inner join \"MDTable\" as md
                            on ct.mdid=md.id
                            inner join \"CProperties\" as cp
                                inner join \"CPropValue_mdid\" as pv
                                on cp.id=pv.pid
                                and pv.value in (
                                    select mdp.id from \"MDTable\" as mdp
                                        inner join \"CTable\" as mi
                                        on mdp.mditem = mi.id
                                        and mi.name='Sets'
                                        inner join \"MDProperties\" as mp 
                                            inner join \"CTable\" as ct
                                                inner join \"MDTable\" as md
                                                on ct.mdid=md.id
                                                inner join \"CProperties\" as cp
                                                        inner join \"CPropValue_mdid\" as pv
                                                        on cp.id=pv.pid
                                                        and pv.value=:mdid
                                                on ct.mdid=cp.mdid
                                                and cp.name='valmdid'
                                                and pv.id=ct.id
                                            and md.name = 'PropsTemplate'
                                            on mp.propid = ct.id
                                        on mp.mdid=mdp.id
                                    )
                            on ct.mdid=cp.mdid
                            and cp.name='valmdid'
                            and pv.id=ct.id
                        and md.name = 'PropsTemplate'
                        on mp.propid = ct.id
                    on mp.mdid=mdp.id";
        $params = array();
        $params['mdid'] = $mdid;
        $res = DataManager::dm_query($sql,$params);
        $ar_obj = array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $ar_obj[$row['mdid']] = $row;
        }
        $ent_obj = new Entity($curid); // получим объект хозяин 
        $ent_plist = $ent_obj->properties();
        foreach ($ent_plist as $prop)
        {
            if ($prop['type']<>'id')
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
        if ($filter_id<>'')
        {
            $item = new Entity($ent_obj->getattrid($filter_id));
            self::add_filter_val($item,$mdid,$ent_filter);
            if ($ent_obj->getmdentity()->getmdtypename()=='Items')
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
    static function arr_rls($propid, $access_prop,$edit_mode)
    {
        $rls=array();
        foreach ($access_prop as $prop)
        {
            if ($prop['propid']==$propid)
            {
                if (($edit_mode==='EDIT')||($edit_mode==='SET_EDIT')||($edit_mode==='CREATE')||($edit_mode==='CREATE_PROPERTY'))
                {
                    if ($prop['wr']===true)
                    {    
                        $rls[]=$prop['value'];
                    }    
                }    
                else 
                {
                    if (($prop['rd']===true)||($prop['wr']===true))
                    {    
                        $rls[]=$prop['value'];
                    }    
                }
            }    
        }    
        return $rls;
    }

    static function sqltext_entitylist($mdid,$plist,$filter,$arr_prop,$access_prop,$edit_mode,&$params)
    {
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
                    $rls = self::arr_rls($row['propid'], $access_prop, $edit_mode);
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

    public static function getEntitiesByFilter($filter, $mode='', $edit_mode='',$limit=TZ_COUNT_REC_BY_PAGE, $page=1, $order='name') 
    {
    	$objs = array();
	$objs['LDATA'] = array();
	$objs['PSET'] = array();
        $objs['actionlist'] = DataManager::getActionsbyItem('EntitySet',$mode,$edit_mode);
        
        $curid = $filter['curid']['id']; //это уид объекта для которого запрошена выборка
        $propid = $filter['filter_id']['id']; //это уид реквизита отбора для выборки
        $docid = (array_key_exists('docid', $filter) ? $filter['docid']['id'] : '');  
        $ptype = '';
        $mdid = '';
        $ent_filter = array();
        if ($propid!='')
        {
            $ent_filter[$propid] = new Filter($propid,$filter['filter_val']['id']);
        }
        else
        {
            if ($filter['itemid']['id']!='')
            {
                $mdid = $filter['itemid']['id'];
            }
            else 
            {
                return $objs;
            }
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
        $tpropid = $arprop['propid']; // ид шаблона реквизита который выбираем
        $mdentity = new Mdentity($mdid); //метаданные объекта который выбираем
        if ($mdentity->getmdtypename()=='Items') //запрошены строки ТЧ?
        {
            $tt_et = self::get_tt_items($docid,$mdid,$curid,$ent_filter,$entities);
        }    
        else
        {
            if (count($entities))
            {
                $tt_et = self::get_EntitiesFromList($entities,'tt_et',$limit);
            }    
        }    
        if ($tt_et=='')
        {
            $tt_et = self::get_findEntitiesByProp('tt_et', $mdid, $propid, $ptype, $access_prop, $ent_filter ,$limit);
        }    
        if ($tt_et=='')
        {
            $objs['RES']='list entities is empty';
            return $objs;
        }
        
	$artemptable = self::createtemptable_all($tt_et,$mdid);
        $artemptable[] = $tt_et;
	$sql = "SELECT * FROM tt_pt";
	$res = DataManager::dm_query($sql);	
        $plist = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC))
        {
            if ($expr($row))
            {    
                continue;
            }
            $plist[] = $row;
        }    
        $arr_id = array();
        //получим ассоциированный массив реквизитов с ссылочного типа
        foreach($plist as $row) 
        {
            $rid = $row['id'];
            if ($row['type']=='id')
            {
                $arr_id[$rid]=$row;
            }
        }    
        
        $params = array();
        $sql = self::sqltext_entitylist($mdid,$plist,$ent_filter,$arr_prop,$access_prop,$edit_mode,$params);
        if ($sql=='')
        {
            $objs['RES']='access denied';
            return $objs;
        }    
	$res = DataManager::dm_query($sql,$params);
        
        
        $arr_e = array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $objs['LDATA'][$row['id']]=array();
            $objs['LDATA'][$row['id']]['id'] = array('id'=>$row['id'],'name'=>'');
            $objs['LDATA'][$row['id']]['class'] ='active';               
            foreach($plist as $row_plist) 
            {
                $rid = $row_plist['id'];
                $field_val = strtolower(str_replace(" ","",strtolower($row_plist['name'])));
                $field_id = "propid_$field_val";
                $rowid = "id_$field_val";
                $rowname = "name_$field_val";
                $r_name = $row[$rowname];
                $r_id = $row[$rowid];
                if (strtolower($row_plist['name'])=='activity')
                {
                    if ($row[$rowname]===false)
                    {    
                        $objs['LDATA'][$row['id']]['class'] ='erased';               
                    }    
                }    
                if ($row_plist['type']=='id')
                {
                    if ($row[$rowid]!='') {
                        if (!in_array($row[$rowid],$arr_e)){
                            $arr_e[]=$row[$rowid];
                        }
                    }
                    $r_name = '';
                } else {
                    if ($row_plist['type'] == 'date')
                    {
                        $r_name =substr($rname,0,10);
                    }    
                }    
                $objs['LDATA'][$row['id']][$row[$field_id]] = array('id'=>$r_id,'name'=>$r_name);
                if ($row_plist['ranktostring'] > 0) {
                    $arr_name[$row_plist['ranktostring']] = $r_name;
                }
            }
        }
        if (count($arr_e))
        {
            $arr_entities = self::getAllEntitiesToStr($arr_e);
            foreach($arr_id as $rid=>$prow)
            {
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
        $objs['PSET'] = MdpropertySet::getMDProperties($mdid,$mode," WHERE mp.mdid = :mdid AND mp.ranktoset>0 ",true);
        $sql = "SELECT count(*) as countrec FROM tt_tv";
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
    public static function getEntitiesToStr($entities,&$all_entities,&$data,&$count_req) 
    {
        // entities - массив ссылок
        $artemptable = self::createTempTableEntitiesToStr($entities,$count_req);
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
                $add_entities = self::getEntitiesToStr($add_entities,$all_entities,$data,$count_req);
            }
        }
        return $objs;
    }
    public static function getAllEntitiesToStr($entities) 
    {
        $all_entities = array();
        $count_req = 1;
        $data = array();
        $add_entities = self::getEntitiesToStr($entities,$all_entities, $data,$count_req);
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
    public static function getEntitiesByName($mdid,$name) 
    {
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
        $mdentity = new Mdentity($mdid);
        if ($mdentity->getmdtypename()=='Docs')
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
    public static function search_by_name($mdid,$type,$name)
    {
        $objs = array();
        if ($type=='id')
        {
            $objs = self::getEntitiesByName($mdid,$name);
        }    
        elseif ($type=='cid') 
        {
            $objs = tzVendor\CollectionSet::getCollByName($mdid,$name);
        }
        elseif ($type=='mdid') 
        {
            $objs = tzVendor\Mdentity::getMDbyName($name);
        }
        return $objs;
    }        
    
}

