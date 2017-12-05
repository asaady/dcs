<?php
namespace tzVendor;

use PDO;
use PDOStatement;
use tzVendor\Entity;

class CoverSheets extends Model 
{
    protected $entity;
    protected $tproc_mdid='def88585-c509-4200-8980-19ae0e164bd7';  //тех.процесс справочник
    protected $mdid='be0d47b9-2972-496c-a11b-0f3d38874aab';  //сопр лист справочник
    protected $prop_div='08d45b18-7207-4ad9-a4fa-a76bdb880c01';  //реквизит подразделение справочник
    protected $proptproc='9c26942a-7aa2-4082-ae9a-ef8daf030ee2'; //реквизит техпроцесс шаблон
    protected $prop_to = '79ea3c05-c94b-4161-b24b-f7667ab41e6a'; //реквизит техоперация шаблон
    protected $prop_act = '11cc9d05-d63e-4943-bb95-87149b4e9eff'; //реквизит активность шаблон
    protected $prop_mr = 'f06b5b81-aa70-42de-8d4e-1718d2033952'; //реквизит тех.маршрут шаблон
    protected $propstatus = '876fda77-c5e4-4948-bd81-2dd883fbbe40'; //реквизит Статус шаблон
    protected $propcs = '37a1e155-ed43-46e1-ade8-b75aff1a5031'; //реквизит Сопр.лист Шаблон
    protected $propdate = '43cba044-e85b-40f1-9c3d-a6a2af0deb9a'; //реквизит Дата Шаблон
    protected $prop_rank = '281f8a47-5fb2-4328-8320-e35493ef08e2'; // реквизит Порядок шаблон
    protected $mpprop_cs_start ='015b8d13-907d-46e8-8077-f3e1ae57899c'; //реквизит сопроводительного листа количество запуска
    protected $mpprop_cs_date ='965acb33-cf77-41de-9eac-b7847419b67e'; //реквизит сопроводительного листа дата запуска
    protected $mpprop_cs_tproc ='d05dd003-88eb-4006-acfe-b9ebd2400dec'; //реквизит сопроводительного листа техпроцесс
    protected $mpprop_dv_godn ='8ef436b2-155f-4316-957b-7c191e316d86'; //реквизит строки ТЧ "движение" - годные
    protected $mpprop_dv_brak ='2688adec-c959-4f24-80e4-588efb419238'; //реквизит строки ТЧ "брак" - количество
    protected $ar_prop_cs = "('65a3c995-09b2-49ee-85fc-b85bda107f52','9a25ad6b-136a-43b3-8b6b-a7687d956dc4','cfbeabf1-6e71-4f5d-b428-77ec804ff32c')";

    public function __construct($id) 
    {
        
        if ($id=='')
        {
            die("class.CoverSheet constructor: id is empty");
        }
        $this->entity = new CollectionItem($id);
        
        if ($this->entity->getname()!='CoverSheets')
        {
            die("class.CoverSheet constructor: bad id");
        }    
        $this->id = $id;
        $this->name = $this->entity->getsynonym();
        $this->version = time();        
    }
    public function get_data($data)
    {
        $sdata = array();
        $plist = array(
                array('id'=>'parameter','name'=>'parameter','synonym'=>'Подразделение','rank'=>1,'type'=>'id','valmdid'=>'50643d39-aec2-485e-9c30-bf29b04db75c','valmdtypename'=>'Refs','class'=>'active'),
                array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'active')
        );
        $pset=array(
            'tprocid'=>array('id'=>'tprocid','name'=>'tprocid','synonym'=>'Техпроцесс','type'=>'id', 'class'=>'active'),
            'startkol'=>array('id'=>'startkol','name'=>'startkol','synonym'=>'Запуск','type'=>'int', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Брак','type'=>'int', 'class'=>'active')
        );
        $mdname = '';
        if(array_key_exists('CURID', $data))
        {
            if ($data['CURID']!='')
            {    
                $ent = new Entity($data['CURID']);
                $mdname = $ent->getmdentity()->getname();
                $this->name = $ent->getname();
                if ($mdname=='Techproc_ref')
                {
                    $plist = array(
                            array('id'=>'parameter','name'=>'parameter','synonym'=>'Тех.процесс','rank'=>1,'type'=>'id','valmdid'=>'def88585-c509-4200-8980-19ae0e164bd7','valmdtypename'=>'Refs','class'=>'active'),
                            array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'active')
                    );
                    $pset=array(
                        'csid'=>array('id'=>'csid','name'=>'csid','synonym'=>'Сопр.лист','type'=>'id', 'class'=>'active'),
                        'startdate'=>array('id'=>'startdate','name'=>'startdate','synonym'=>'Дата запуска','type'=>'date', 'class'=>'active'),
                        'startkol'=>array('id'=>'startkol','name'=>'startkol','synonym'=>'Запуск','type'=>'int', 'class'=>'active'),
                        'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
                        'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Брак','type'=>'int', 'class'=>'active'),
                        'toperid'=>array('id'=>'toperid','name'=>'toperid','synonym'=>'Тех.операция','type'=>'id', 'class'=>'active')
                    );
                }    
                elseif ($mdname=='CoverSheets')
                {
                    $plist = array(
                            array('id'=>'parameter','name'=>'parameter','synonym'=>'Сопров.лист','rank'=>1,'type'=>'id','valmdid'=>'be0d47b9-2972-496c-a11b-0f3d38874aab','valmdtypename'=>'Refs','class'=>'active'),
                            array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'hidden')
                    );
                    $pset=array(
                        'toperid'=>array('id'=>'toperid','name'=>'toperid','synonym'=>'Тех.операция','type'=>'id', 'class'=>'active'),
                        'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
                        'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Брак','type'=>'int', 'class'=>'active'),
                        'docid'=>array('id'=>'docid','name'=>'docid','synonym'=>'Документ','type'=>'id', 'class'=>'active'),
                        'tdate'=>array('id'=>'startdate','name'=>'startdate','synonym'=>'Дата операции','type'=>'date', 'class'=>'hidden')
                    );
                }    
            }    
        }        

        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'mdname'=>$mdname,
          'version'=>$this->version,
          'PLIST' => $plist,   
          'PSET' => $pset,   
          'SDATA' => $sdata,   
          'LDATA' => array(),   
          'navlist' => array(
              $this->entity->getcollectionset()->getmditem()->getid()=>$this->entity->getcollectionset()->getmditem()->getsynonym(),
              $this->entity->getcollectionset()->getid()=>$this->entity->getcollectionset()->getsynonym(),
              $this->id=>$this->name
            )
          );
    }        
    public function getCSdata_byTO($csid,$show_empty=false)
    {
        
        $objs = array();
        $objs['actionlist'] = array(array('id'=>'print','name'=>'print','synonym'=>'Печать','icon'=>'print'));
        
        $ar_tt = array();
        $ent= new Entity($csid);
        $objs['SDATA']=array();
        $objs['SDATA']['parameter']=array('id'=>$csid,'name'=>$ent->getname());
        
        
        $params=array();
        $params['csid']=$csid;
        $sql = "SELECT id, id as entityid  FROM \"ETable\" as et WHERE id=:csid"; 
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_el0',$params);
        $objs['PLIST'] = array(
                'parameter'=>array('id'=>'parameter','name'=>'parameter','synonym'=>'Сопр.лист','rank'=>1,'type'=>'id','valmdid'=>'be0d47b9-2972-496c-a11b-0f3d38874aab','valmdtypename'=>'Refs','class'=>'active'),
                'mindate'=>array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'hidden')
                );

        //нашли техпроцесс сопроводительного листа
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_el','tt_el0',$this->mpprop_cs_tproc,'id');
        
        //нашли количество запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_st','tt_el0',$this->mpprop_cs_start,'int');
        //нашли даты запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_dat_st','tt_el0',$this->mpprop_cs_date,'date');
        
        //теперь ищем строки таб.частей документов в которых искомые сопр.листы
        $ar_tt[] = DataManager::getTT_from_ttprop('tt_sel',$this->ar_prop_cs,'id','tt_el');

        //теперь ищем последние значения реквизита годные в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_godn','tt_sel',$this->mpprop_dv_godn,'int');
        
        //теперь ищем последние значения реквизита брак в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_brak','tt_sel',$this->mpprop_dv_brak,'int');
        //здесь нашли сами таб.части и доки в которых искомые сопр.листы
        $sql = "select distinct sdl.parentid as setid, its.entityid as docid, ot.value as csid, ot.entityid as itemid FROM tt_sel as ot
                    INNER JOIN \"SetDepList\" as sdl
                        inner join \"PropValue_id\" as pv 
                            inner join \"IDTable\" AS its
                            on pv.id=its.id
                        on sdl.parentid=pv.value 
                    ON ot.entityid=sdl.childid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_doc');

        
        $sql = "select distinct docid as id FROM tt_doc";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dc');
        
        //теперь ищем последние значения реквизита тех.операция в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_oper','tt_dc',$this->prop_to,'id');

        //теперь ищем последние значения реквизита дата в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_date','tt_dc',$this->propdate,'date');

        //теперь ищем последние значения реквизита активность в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_act','tt_dc',$this->prop_act,'bool');
        
        //теперь ищем порядок техопераций в техпроцессах
        //здесь нашли таб.часть маршрут для техпроцессов
        $sql = "select et.value as tprocid, pv_mr.value as mrid from tt_el as et 
                    inner join \"IDTable\" as it_mr
                        inner join \"PropValue_id\" as pv_mr
                        on it_mr.id = pv_mr.id
                        inner join \"MDProperties\" as mp_mr
                        on it_mr.propid = mp_mr.id
                        and mp_mr.propid=:prop_mr
                    on et.value = it_mr.entityid";
        $params=array();
        $params['prop_mr']=$this->prop_mr;
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr0',$params);
        //здесь нашли строки таб.части маршрута
        $sql = "select mr.tprocid, mr.mrid, sdl.childid as id FROM tt_mr0 as mr
                    INNER JOIN \"SetDepList\" as sdl
                    ON mr.mrid=sdl.parentid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mri');
        
        //теперь ищем последние значения реквизита порядок в найденных строках маршрута
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_rank','tt_mri',$this->prop_rank,'int');
        
        //здесь нашли маршруты с последними значениями тех.операций   
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_to','tt_mri',$this->prop_to,'id');
        
        
        $sql = "select mr.tprocid, mr.id as mrid, top.value as toperid, rn.value as rank from tt_mri as mr
                    left join tt_mr_to as top
                    on mr.id=top.entityid
                    left join tt_mr_rank as rn
                    on mr.id=rn.entityid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr'); 
        
        $sql = "select mr.toperid, dc.docid, dc.itemid, dc.csid, dt.value as tdate, COALESCE(gd.value,0) as godn, COALESCE(br.value,0) as brak, COALESCE(da.value,true) as activity, mr.rank, mr.mrid from tt_mr as mr 
                inner join tt_oper as op
                    left join tt_doc as dc
                        left join tt_act as da
                        on dc.docid = da.entityid
                        left join tt_date as dt
                        on dc.docid = dt.entityid
                        left join tt_kol_godn as gd
                        on dc.itemid = gd.entityid
                        left join tt_kol_brak as br
                        on dc.itemid = br.entityid
                    on op.entityid = dc.docid
                on mr.toperid = op.value
                inner join tt_el as tp
                on mr.tprocid = tp.value
                where COALESCE(da.value,true)=true order by mr.rank";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_all'); 
        
        $sql = "select dc.mrid, dc.csid, max(tdate) as tdate, sum(dc.godn) as godn, sum(dc.brak) as brak from tt_all as dc group by dc.mrid, dc.csid"; 
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_sum'); 

        
        $sql = "select mr.toperid, dc.docid, dc.csid, dc.tdate, COALESCE(sm.godn,0) as godn, COALESCE(sm.brak,0) as brak, mr.rank, mr.mrid from tt_mr as mr "
                . "left join tt_all as dc inner join tt_sum as sm on dc.mrid=sm.mrid and dc.csid=sm.csid and dc.tdate=sm.tdate on mr.mrid = dc.mrid order by mr.rank"; 
        $res = DataManager::dm_query($sql);
        
        
        $objs['LDATA']=array();
        $objs['PSET']=array(
            'toperid'=>array('id'=>'toperid','name'=>'toperid','synonym'=>'Тех.операция','type'=>'id', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Брак','type'=>'int', 'class'=>'active'),
            'docid'=>array('id'=>'docid','name'=>'docid','synonym'=>'Документ','type'=>'id', 'class'=>'active'),
            'tdate'=>array('id'=>'tdate','name'=>'tdate','synonym'=>'Дата операции','type'=>'date', 'class'=>'hidden')
        );
        $arr_e= array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (!$show_empty)
            {
                if (($row['godn']+$row['brak'])==0)
                {
                    continue;
                }    
            }    
            $objs['LDATA'][$row['toperid']]=array();
            $objs['LDATA'][$row['toperid']]['godn']= array('name'=>$row['godn'], 'id'=>'');
            $objs['LDATA'][$row['toperid']]['brak']= array('name'=>$row['brak'], 'id'=>'');
            if ($row['toperid']!='')
            {
                if (!in_array($row['toperid'], $arr_e)) $arr_e[]=$row['toperid'];
                $objs['LDATA'][$row['toperid']]['toperid']= array('name'=>$row['toperid'], 'id'=>$row['toperid']);
            }    
            else
            {    
                $objs['LDATA'][$row['toperid']]['toperid']= array('name'=>'', 'id'=>'');
            }
            if ($row['docid']!='')
            {
                if (!in_array($row['docid'], $arr_e)) $arr_e[]=$row['docid'];
                $objs['LDATA'][$row['toperid']]['docid']= array('name'=>$row['docid'], 'id'=>$row['docid']);
            }  
            else
            {    
                $objs['LDATA'][$row['toperid']]['docid']= array('name'=>'', 'id'=>'');
            }
            if (isset($row['tdate']))
            {    
                $objs['LDATA'][$row['toperid']]['tdate']= array('name'=>$row['tdate'], 'id'=>'');
            }
            else
            {
               $objs['LDATA'][$row['toperid']]['tdate']= array('name'=>'', 'id'=>'');
            }    
        }
        DataManager::droptemptable($ar_tt);
        if (count($arr_e))
        {

            $arr_entities = EntitySet::getAllEntitiesToStr($arr_e);
            foreach($objs['LDATA'] as $toperid=>$row)
            {
                if (array_key_exists($toperid, $arr_entities))
                {
                    $objs['LDATA'][$toperid]['toperid']['name']=$arr_entities[$toperid]['name'];
                }    
                if (array_key_exists($row['docid']['id'], $arr_entities))
                {
                    $objs['LDATA'][$toperid]['docid']['name']=$arr_entities[$row['docid']['id']]['name'];
                }    
            }
        }
       
        return $objs;	
    }
    public function getNZPbyCS($tprocid, $mindate='',$show_empty=false)
    {
        $objs = array();
        $objs['actionlist'] = array(array('id'=>'print','name'=>'print','synonym'=>'Печать','icon'=>'print'));
   
        $ar_tt = array();
        $ent= new Entity($tprocid);
        $objs['SDATA']=array();
        $objs['SDATA']['parameter']=array('id'=>$tprocid,'name'=>$ent->getname());
        
        $params=array();
        $params['tprocid']=$tprocid;
        $sql = "SELECT id  FROM \"ETable\" as et WHERE id=:tprocid"; 
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_el00',$params);
        if ($mindate!='')
        {
            //выбрали сопроводительные листы у которых дата запуска больше или равно отбору
            $ar_tt[] = DataManager::getTT_entity('tt_el0',$this->mdid,$this->propdate,$mindate,'date','>=');
            $objs['SDATA']['mindate']=array('id'=>'','name'=>$mindate);
        }    
        else 
        {
            $params=array();
            $params['mdid']=$this->mdid;
            $sql = "SELECT id  FROM \"ETable\" WHERE mdid=:mdid"; 
            $ar_tt[] = DataManager::createtemptable($sql, 'tt_el0',$params);
            $objs['SDATA']['mindate']=array('id'=>'','name'=>'');
        }
        $objs['PLIST'] = array(
                'parameter'=>array('id'=>'parameter','name'=>'parameter','synonym'=>'Подразделение','rank'=>1,'type'=>'id','valmdid'=>'50643d39-aec2-485e-9c30-bf29b04db75c','valmdtypename'=>'Refs','class'=>'active'),
                'mindate'=>array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'active')
                );

        //выбрали сопроводительные листы у которых техпроцесс соответствует выбранным ранее
        $ar_tt[] = DataManager::getTT_from_ttent('tt_el','tt_el0',$this->proptproc,'id','tt_el00');

        //нашли количество запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_st','tt_el',$this->mpprop_cs_start,'int');
        //нашли даты запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_dat_st','tt_el',$this->mpprop_cs_date,'date');
        
        //теперь ищем строки таб.частей документов в которых искомые сопр.листы
        $ar_tt[] = DataManager::getTT_from_ttprop('tt_sel',$this->ar_prop_cs,'id','tt_el');
        //теперь ищем последние значения реквизита годные в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_godn','tt_sel',$this->mpprop_dv_godn,'int');
        
        //теперь ищем последние значения реквизита брак в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_brak','tt_sel',$this->mpprop_dv_brak,'int');
        //здесь нашли сами таб.части и доки в которых искомые сопр.листы
        $sql = "select distinct sdl.parentid as setid, its.entityid as docid, ot.value as csid, ot.entityid as itemid FROM tt_sel as ot
                    INNER JOIN \"SetDepList\" as sdl
                        inner join \"PropValue_id\" as pv 
                            inner join \"IDTable\" AS its
                            on pv.id=its.id
                        on sdl.parentid=pv.value 
                    ON ot.entityid=sdl.childid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_doc');
        $sql = "select distinct docid as id FROM tt_doc";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dc');
        
        //теперь ищем последние значения реквизита тех.операция в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_oper','tt_dc',$this->prop_to,'id');
        //теперь ищем последние значения реквизита дата в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_date','tt_dc',$this->propdate,'date');

        //теперь ищем последние значения реквизита активность в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_act','tt_dc',$this->prop_act,'bool');
        
        //теперь ищем порядок техопераций в техпроцессах
        //здесь нашли таб.часть маршрут для техпроцессов
        $sql = "select et.id as tprocid, pv_mr.value as mrid from tt_el00 as et 
                    inner join \"IDTable\" as it_mr
                        inner join \"PropValue_id\" as pv_mr
                        on it_mr.id = pv_mr.id
                        inner join \"MDProperties\" as mp_mr
                        on it_mr.propid = mp_mr.id
                        and mp_mr.propid=:prop_mr
                    on et.id = it_mr.entityid";
        $params=array();
        $params['prop_mr']=$this->prop_mr;
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr0',$params);

        //здесь нашли строки таб.части маршрута
        $sql = "select mr.tprocid, mr.mrid, sdl.childid as id FROM tt_mr0 as mr
                    INNER JOIN \"SetDepList\" as sdl
                    ON mr.mrid=sdl.parentid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mri');
        
        //теперь ищем последние значения реквизита порядок в найденных строках маршрута
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_rank','tt_mri',$this->prop_rank,'int');
        
        //теперь ищем последние значения реквизита активность в найденных строках маршрута
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_act','tt_mri',$this->prop_act,'bool','',FALSE);
        
        //здесь нашли маршруты с последними значениями тех.операций   
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_to','tt_mri',$this->prop_to,'id');
        
        $sql = "select mr.tprocid, mr.id as mrid, top.value as toperid, rn.value as rank from tt_mri as mr
                    left join tt_mr_act as ma
                    on mr.id=ma.entityid
                    left join tt_mr_to as top
                    on mr.id=top.entityid
                    left join tt_mr_rank as rn
                    on mr.id=rn.entityid 
                    where COALESCE(ma.value,true)=true";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr'); 
        
        
        $sql = "select dc.docid, dc.itemid, dc.csid, mr.mrid, op.value as toperid, COALESCE(da.value,true) as activity, dt.value as date, gd.value as godn, br.value as brak, tp.value as tprocid, mr.rank from tt_doc as dc 
                left join tt_oper as op
                on dc.docid = op.entityid
                left join tt_act as da
                on dc.docid = da.entityid
                left join tt_date as dt
                on dc.docid = dt.entityid
                left join tt_kol_godn as gd
                on dc.itemid = gd.entityid
                left join tt_kol_brak as br
                on dc.itemid = br.entityid
                left join tt_el as tp
                    left join tt_mr as mr
                    on tp.value = mr.tprocid
                on dc.csid = tp.entityid where op.value = mr.toperid and COALESCE(da.value,true)=true";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtmm');
        //$res = DataManager::dm_query($sql);
        //$rows = $res->fetchAll(PDO::FETCH_ASSOC);
        //die(var_dump($rows));
        $sql = "select dc.csid, dc.mrid, sum(dc.godn) as godn, sum(dc.brak) as brak  from tt_dtmm as dc group by dc.csid, dc.mrid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtmt');
        
        $sql = "select dc.csid, max(mm.rank) as rank from tt_dtmt as dc inner join tt_dtmm as mm on dc.csid=mm.csid and dc.mrid=mm.mrid group by dc.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_rank');
        

        $sql = "select dc.csid, sum(mm.brak) as brak from tt_dtmt as dc inner join tt_dtmm as mm on dc.csid=mm.csid and dc.mrid=mm.mrid group by dc.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_brak');

        $sql = "select dc.csid, mm.rank, dc.godn, mm.toperid from tt_dtmt as dc "
                . "inner join tt_dtmm as mm "
                . "inner join tt_cs_rank as cs "
                . "on mm.csid=cs.csid and mm.rank=cs.rank "
                . "on dc.csid = mm.csid and dc.mrid = mm.mrid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_godn');
        
        $sql = "select tp.entityid as csid, tp.value as tprocid, gd.toperid, gd.godn, br.brak, st.value as startkol, dt.value as startdate from tt_el as tp
                left join tt_cs_godn as gd
                on tp.entityid = gd.csid
                left join tt_cs_brak as br
                on tp.entityid = br.csid
                left join tt_kol_st as st
                on tp.entityid = st.entityid
                left join tt_dat_st as dt
                on tp.entityid = dt.entityid";
        $res = DataManager::dm_query($sql);
        $objs['LDATA']=array();
        $objs['PSET']=array(
            'csid'=>array('id'=>'csid','name'=>'csid','synonym'=>'Сопр.лист','type'=>'id', 'class'=>'active'),
            'startdate'=>array('id'=>'startdate','name'=>'startdate','synonym'=>'Дата запуска','type'=>'date', 'class'=>'active'),
            'startkol'=>array('id'=>'startkol','name'=>'startkol','synonym'=>'Запуск','type'=>'int', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Брак','type'=>'int', 'class'=>'active'),
            'toperid'=>array('id'=>'toperid','name'=>'toperid','synonym'=>'тех.операция','type'=>'id', 'class'=>'active')
        );
        $arr_e= array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (!$show_empty)
            {
                if (($row['godn']+$row['brak'])==0)
                {
                    continue;
                }    
            }    
            $objs['LDATA'][$row['csid']]=array();
            $objs['LDATA'][$row['csid']]['csid']= array('name'=>$row['tprocid'], 'id'=>$row['tprocid']);
            $objs['LDATA'][$row['csid']]['godn']= array('name'=>$row['godn'], 'id'=>'');
            $objs['LDATA'][$row['csid']]['brak']= array('name'=>$row['brak'], 'id'=>'');
            $objs['LDATA'][$row['csid']]['startkol']= array('name'=>$row['startkol'], 'id'=>'');
            $objs['LDATA'][$row['csid']]['startdate']= array('name'=>$row['startdate'], 'id'=>'');
            $objs['LDATA'][$row['csid']]['toperid']= array('name'=>$row['toperid'], 'id'=>$row['toperid']);
            if ($row['csid'])
            {
                if (!in_array($row['csid'], $arr_e)) $arr_e[]=$row['csid'];
            }
            if ($row['toperid'])
            {
                if (!in_array($row['toperid'], $arr_e)) $arr_e[]=$row['toperid'];
            }    
        }
       DataManager::droptemptable($ar_tt);
        if (count($arr_e))
        {
            $arr_entities = EntitySet::getAllEntitiesToStr($arr_e);
            foreach($objs['LDATA'] as $csid=>$row)
            {
                if (array_key_exists($csid, $arr_entities))
                {
                    $objs['LDATA'][$csid]['csid']['name']=$arr_entities[$csid]['name'];
                }    
                if (array_key_exists($row['toperid']['id'], $arr_entities))
                {
                    $objs['LDATA'][$csid]['toperid']['name']=$arr_entities[$row['toperid']['id']]['name'];
                }    
            }
        }
       
        return $objs;	
    }

    public function getNZPbyTProc($divid='', $mindate='',$show_empty=false)
    {
        $objs = array();
        $objs['actionlist'] = array(array('id'=>'print','name'=>'print','synonym'=>'Печать','icon'=>'print'));
        
        $objs['SDATA']=array();
        
        $ar_tt = array();
        if ($divid!='')
        {
           //выбрали техпроцессы у которых подразделение равно отбору
            $ent= new Entity($divid);
            $ar_tt[] = DataManager::getTT_entity('tt_el00',$this->tproc_mdid,$this->prop_div,$divid,'id','=');
            $objs['SDATA']['parameter']=array('id'=>$divid,'name'=>$ent->getname());
        }    
        else 
        {
            $params=array();
            $params['mdid']=$this->tproc_mdid;
            $sql = "SELECT id  FROM \"ETable\" as et WHERE mdid=:mdid)"; 
            $ar_tt[] = DataManager::createtemptable($sql, 'tt_el00',$params);
            $objs['SDATA']['parameter']=array('id'=>'','name'=>'');
        }
        if ($mindate!='')
        {
            //выбрали сопроводительные листы у которых дата запуска больше или равно отбору
            $ar_tt[] = DataManager::getTT_entity('tt_el0',$this->mdid,$this->propdate,$mindate,'date','>=');
            $objs['SDATA']['mindate']=array('id'=>'','name'=>$mindate);
        }    
        else 
        {
            $params=array();
            $params['mdid']=$this->mdid;
            $sql = "SELECT id  FROM \"ETable\" WHERE mdid=:mdid"; 
            $ar_tt[] = DataManager::createtemptable($sql, 'tt_el0',$params);
            $objs['SDATA']['mindate']=array('id'=>'','name'=>'');
        }
        $objs['PLIST'] = array(
                'parameter'=>array('id'=>'parameter','name'=>'parameter','synonym'=>'Подразделение','rank'=>1,'type'=>'id','valmdid'=>'50643d39-aec2-485e-9c30-bf29b04db75c','valmdtypename'=>'Refs','class'=>'active'),
                'mindate'=>array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'active')
                );
        //выбрали сопроводительные листы у которых техпроцесс соответствует выбранным ранее
        $ar_tt[] = DataManager::getTT_from_ttent('tt_el','tt_el0',$this->proptproc,'id','tt_el00');

        //нашли количество запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_st','tt_el',$this->mpprop_cs_start,'int');
        //нашли даты запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_dat_st','tt_el',$this->mpprop_cs_date,'date');
        
        //теперь ищем строки таб.частей документов в которых искомые сопр.листы
        $ar_tt[] = DataManager::getTT_from_ttprop('tt_sel',$this->ar_prop_cs,'id','tt_el');
        
        //теперь ищем последние значения реквизита годные в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_godn','tt_sel',$this->mpprop_dv_godn,'int');
        
        //теперь ищем последние значения реквизита брак в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_brak','tt_sel',$this->mpprop_dv_brak,'int');
        //здесь нашли сами таб.части и доки в которых искомые сопр.листы
        $sql = "select distinct sdl.parentid as setid, its.entityid as docid, ot.value as csid, ot.entityid as itemid FROM tt_sel as ot
                    INNER JOIN \"SetDepList\" as sdl
                        inner join \"PropValue_id\" as pv 
                            inner join \"IDTable\" AS its
                            on pv.id=its.id
                        on sdl.parentid=pv.value 
                    ON ot.entityid=sdl.childid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_doc');
        
        $sql = "select distinct docid as id FROM tt_doc";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dc');
        
        //теперь ищем последние значения реквизита тех.операция в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_oper','tt_dc',$this->prop_to,'id');
        
        //теперь ищем последние значения реквизита дата в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_date','tt_dc',$this->propdate,'date');
        
        //теперь ищем последние значения реквизита активность в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_act','tt_dc',$this->prop_act,'bool','',FALSE);
        
        
        //теперь ищем порядок техопераций в техпроцессах
        //здесь нашли таб.часть маршрут для техпроцессов
        $sql = "select et.id as tprocid, pv_mr.value as mrid from tt_el00 as et 
                    inner join \"IDTable\" as it_mr
                        inner join \"PropValue_id\" as pv_mr
                        on it_mr.id = pv_mr.id
                        inner join \"MDProperties\" as mp_mr
                        on it_mr.propid = mp_mr.id
                        and mp_mr.propid=:prop_mr
                    on et.id = it_mr.entityid";
        $params=array();
        $params['prop_mr']=$this->prop_mr;
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr0',$params);
        

        //здесь нашли строки таб.части маршрута
        $sql = "select mr.tprocid, mr.mrid, sdl.childid as id FROM tt_mr0 as mr
                    INNER JOIN \"SetDepList\" as sdl
                    ON mr.mrid=sdl.parentid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mri');
        
        //теперь ищем последние значения реквизита порядок в найденных строках маршрута
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_rank','tt_mri',$this->prop_rank,'int');
        
        //теперь ищем последние значения реквизита активность в найденных строках маршрута
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_act','tt_mri',$this->prop_act,'bool','',FALSE);
        
        //здесь нашли маршруты с последними значениями тех.операций   
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_to','tt_mri',$this->prop_to,'id');
        
        $sql = "select mr.tprocid, mr.id as mrid, top.value as toperid, rn.value as rank, COALESCE(ma.value,true) as activity from tt_mri as mr
                    left join tt_mr_act as ma
                    on mr.id=ma.entityid
                    left join tt_mr_to as top
                    on mr.id=top.entityid
                    left join tt_mr_rank as rn
                    on mr.id=rn.entityid
                    where COALESCE(ma.value,true)=true";
        
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr'); 
//        $sqlp = "select * from tt_mr";
//        $res = DataManager::dm_query($sqlp);
//        die(var_dump($res->fetchAll(PDO::FETCH_ASSOC)));    
        
        
        $sql = "select dc.docid, dc.itemid, mr.mrid, dc.csid, op.value as toperid, dt.value as date, COALESCE(da.value,true) as activity, gd.value as godn, br.value as brak, tp.value as tprocid, mr.rank from tt_doc as dc 
                left join tt_oper as op
                on dc.docid = op.entityid
                left join tt_act as da
                on dc.docid = da.entityid
                left join tt_date as dt
                on dc.docid = dt.entityid
                left join tt_kol_godn as gd
                on dc.itemid = gd.entityid
                left join tt_kol_brak as br
                on dc.itemid = br.entityid
                left join tt_el as tp
                    left join tt_mr as mr
                    on tp.value = mr.tprocid
                on dc.csid = tp.entityid where op.value=mr.toperid and COALESCE(da.value,true)=true";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtmm');
        
        $sql = "select dc.csid, dc.mrid, dc.docid, dc.toperid, dc.date, dc.tprocid, dc.rank, sum(dc.godn) as godn, sum(dc.brak) as brak from tt_dtmm as dc group by dc.csid, dc.mrid, dc.docid, dc.toperid, dc.date, dc.tprocid, dc.rank";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtms');
        
        $sql = "select dc.csid, dc.mrid, max(dc.rank) as rank, sum(dc.godn) as godn, sum(dc.brak) as brak  from tt_dtms as dc group by dc.csid, dc.mrid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtmt');
        
        $sql = "select dc.csid, max(dc.rank) as rank from tt_dtmt as dc group by dc.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_rank');

        $sql = "select dc.csid, sum(mm.brak) as brak from tt_dtmt as dc inner join tt_dtms as mm on dc.csid=mm.csid and dc.mrid=mm.mrid group by dc.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_brak');

        $sql = "select dc.csid, dc.rank, dc.godn, mm.toperid from tt_dtmt as dc "
                . "inner join tt_cs_rank as cs "
                . "on dc.csid=cs.csid and dc.rank=cs.rank "
                . "inner join tt_mr as mm "
                . "on dc.rank = mm.rank and dc.mrid = mm.mrid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_godn');
        
        
        $sql = "select tp.entityid as csid, tp.value as tprocid, gd.toperid, gd.godn, br.brak, st.value as startkol, dt.value as startdate from tt_el as tp
                left join tt_cs_godn as gd
                on tp.entityid = gd.csid
                left join tt_cs_brak as br
                on tp.entityid = br.csid
                left join tt_kol_st as st
                on tp.entityid = st.entityid
                left join tt_dat_st as dt
                on tp.entityid = dt.entityid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs');
        
        $sql = "select tp.tprocid, sum(tp.godn) as godn, sum(tp.brak) as brak, sum(tp.startkol) as startkol, min(tp.startdate) as startdate from tt_cs as tp group by tp.tprocid";
        $res = DataManager::dm_query($sql);
        $objs['LDATA']=array();
        $objs['PSET']=array(
            'tprocid'=>array('id'=>'tprocid','name'=>'tprocid','synonym'=>'tprocid','type'=>'id', 'class'=>'active'),
            'startkol'=>array('id'=>'startkol','name'=>'startkol','synonym'=>'Запуск','type'=>'int', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Брак','type'=>'int', 'class'=>'active')
        );
        $arr_e= array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (!$show_empty)
            {
                if (($row['godn']+$row['brak'])==0)
                {
                    continue;
                }    
            }    
            $objs['LDATA'][$row['tprocid']]=array();
            $objs['LDATA'][$row['tprocid']]['tprocid']= array('name'=>$row['tprocid'], 'id'=>$row['tprocid']);
            $objs['LDATA'][$row['tprocid']]['godn']= array('name'=>$row['godn'], 'id'=>'');
            $objs['LDATA'][$row['tprocid']]['brak']= array('name'=>$row['brak'], 'id'=>'');
            $objs['LDATA'][$row['tprocid']]['startkol']= array('name'=>$row['startkol'], 'id'=>'');
            $objs['LDATA'][$row['tprocid']]['startdate']= array('name'=>$row['startdate'], 'id'=>'');
            if ($row['tprocid'])
            {  
              if (!in_array($row['tprocid'], $arr_e)) $arr_e[]=$row['tprocid'];
            }      
        }
       DataManager::droptemptable($ar_tt);
        if (count($arr_e))
        {
            $arr_entities = EntitySet::getAllEntitiesToStr($arr_e);
            foreach($objs['LDATA'] as $tprocid=>$row)
            {
                if (array_key_exists($tprocid, $arr_entities))
                {
                    $objs['LDATA'][$tprocid]['tprocid']['name']=$arr_entities[$tprocid]['name'];
                }    
            }
        }
       
        return $objs;	
    }
}    