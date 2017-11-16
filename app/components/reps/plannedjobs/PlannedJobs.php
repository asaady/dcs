<?php
namespace tzVendor;

use PDO;
use PDOStatement;
use tzVendor\Entity;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");

class PlannedJobs extends Model 
{
    protected $entity;
    protected $tproc_mdid='def88585-c509-4200-8980-19ae0e164bd7';  //тех.процесс справочник
    protected $toper_mdid='0ddbfd14-55d1-42d4-a7da-93928c5046cb';  //тех.операции справочник
    protected $cs_mdid='be0d47b9-2972-496c-a11b-0f3d38874aab';  //сопр лист справочник
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
    protected $ar_prop_to = "('b632b043-7ade-4963-be4b-0ac1ac187c2d')";
    protected $mpprop_tm_rank ='aa82df0b-1da3-46ea-a5e6-d3d199913724'; //реквизит <порядок> техмаршрута 
    protected $mpprop_to_div ='7c864a79-2b6d-4e2e-beda-2e231a309dfa'; //реквизит <подразделение> техоперации

    public function __construct($id) 
    {
        
        if ($id=='')
        {
            die("class.PlannedJobs constructor: id is empty");
        }
        $this->entity = new CollectionItem($id);
        
        if ($this->entity->getname()!='PlannedJobs')
        {
            die("class.PlannedJobs constructor: bad id");
        }    
        $this->id = $id;
        $this->name = $this->entity->getsynonym();
        $this->version = time();        
    }
    public function get_data($data)
    {
        $sdata = array();
        $plist = array(
                array('id'=>'parameter','name'=>'parameter','synonym'=>'Подразделение','rank'=>1,'type'=>'id','valmdid'=>$this->toper_mdid,'valmdtypename'=>'Refs','class'=>'active'),
                array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы на','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'active')
        );
        $pset=array(
            'tprocid'=>array('id'=>'tprocid','name'=>'tprocid','synonym'=>'Техпроцесс','type'=>'id', 'class'=>'active'),
            'toperid'=>array('id'=>'toperid','name'=>'toperid','synonym'=>'Техоперация','type'=>'id', 'class'=>'active'),
            'csid'=>array('id'=>'csid','name'=>'csid','synonym'=>'Сопр.лист','type'=>'id', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Количество','type'=>'int', 'class'=>'active')
        );

        return array(
          'id'=>$this->id,
          'name'=>$this->name,
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
    public function getPlanbyToper($toperid, $curdate='')
    {
        
        $objs = array();
        
        $ar_tt = array();
        $ent= new Entity($toperid);
        $objs['SDATA']=array();
        $objs['SDATA']['parameter']=array('id'=>$toperid,'name'=>$ent->getname());
        $objs['PLIST'] = array(
                'parameter'=>array('id'=>'parameter','name'=>'parameter','synonym'=>'Тех.операция','rank'=>1,'type'=>'id','valmdid'=>$this->toper_mdid,'valmdtypename'=>'Refs','class'=>'active'),
                'mindate'=>array('id'=>'curdate','name'=>'curdate','synonym'=>'Cопр.листы на','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'hidden')
                );
        
        if ($curdate=='')
        {
            $date = new DateTime();
            $curdate = $date->format('Y-m-d H:i:s');
        }    
        else 
        {
            $date = new DateTime($curdate);
        }
        $objs['SDATA']['curdate']=array('id'=>'','name'=>$curdate);
        $date->modify('-2 month');
        
        $mindate = $date->format('Y-m-d H:i:s');
        
        $params=array();
        $params['toperid']=$toperid;
        //выбрали техоперацию
        $sql = "SELECT id, id as entityid  FROM \"ETable\" as et WHERE id=:toperid"; 
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_el0',$params);
        
        //нашли подразделение техоперации
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_div','tt_el0',$this->mpprop_to_div,'id');
        
        //ищем строки таб.части тех.маршрутов в которых искомая тех.операция
        $ar_tt[] = DataManager::getTT_from_ttprop('tt_sel',$this->ar_prop_to,'id','tt_el0');
        
        //нашли порядок нашей тех.операции в тех.маршрутах
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_rank_to','tt_sel',$this->mpprop_tm_rank,'int');
        
        //здесь нашли тех.маршруты (ТЧ) и техпроцессы (эл.справочник) в которых искомая тех.операция
        $sql = "select distinct sdl.parentid as setid, its.entityid as tprocid, ot.value as toperid, ot.entityid as itemid FROM tt_sel as ot
                    INNER JOIN \"SetDepList\" as sdl
                        inner join \"PropValue_id\" as pv 
                            inner join \"IDTable\" AS its
                            on pv.id=its.id
                        on sdl.parentid=pv.value 
                    ON ot.entityid=sdl.childid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_tm');
        
        //здесь выбрали сами тех.маршруты
        $sql = "select distinct tm.setid FROM tt_tm as tm";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_tml');

        //здесь выбрали сами тех.процессы
        $sql = "select distinct tm.trocid, tm.trocid as id  FROM tt_tm as tm";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_tpr');
        
        
        //выбрали все строки тех.маршрутов 
        $sql = "select tm.setid, sdl.childid as entityid, sdl.childid as itemid FROM tt_tml as tm
                    INNER JOIN \"SetDepList\" as sdl
                    ON tm.setid=sdl.parentid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_tma');
 
       //нашли порядок тех.операций в тех.маршрутах
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_rank_to_all','tt_tma',$this->mpprop_tm_rank,'int');

        //выбрали сопр.листы с датой запуска более мин.дата
        $ar_tt[] = DataManager::getTT_entity('tt_csa',$this->cs_mdid,$this->propdate,$mindate,'date','>=');
        
        //выбрали сопроводительные листы у которых техпроцесс соответствует выбранным ранее
        $ar_tt[] = DataManager::getTT_from_ttent('tt_cst','tt_csa',$this->proptproc,'id','tt_tpr');

        //нашли количество запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_st','tt_cst',$this->mpprop_cs_start,'int');
        //нашли даты запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_dat_st','tt_cst',$this->mpprop_cs_date,'date');
        
        //теперь ищем строки таб.частей документов в которых искомые сопр.листы
        $ar_tt[] = DataManager::getTT_from_ttprop('tt_cs_tm',$this->ar_prop_cs,'id','tt_cst');
        //теперь ищем последние значения реквизита годные в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_godn','tt_cs_tm',$this->mpprop_dv_godn,'int');
        
        //здесь нашли сами таб.части и доки в которых искомые сопр.листы
        $sql = "select distinct sdl.parentid as setid, its.entityid as docid, ot.value as csid, ot.entityid as itemid FROM tt_cst as ot
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
        
        //здесь нашли маршруты с последними значениями тех.операций   
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_to','tt_mri',$this->prop_to,'id');
        
        $sql = "select mr.tprocid, mr.id as mrid, top.value as toperid, rn.value as rank from tt_mri as mr
                    left join tt_mr_to as top
                    on mr.id=top.entityid
                    left join tt_mr_rank as rn
                    on mr.id=rn.entityid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr'); 
        
        
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
                on dc.csid = tp.entityid where op.value = mr.toperid and COALESCE(da.value,true)=true";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtmm');
        
/*           */        
        
        
        
    }    
    public function getNZPbyTProc($divid='', $mindate='')
    {
        $objs = array();
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
            $params['mdid']=$this->tproc_mdid;
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
        
        //здесь нашли маршруты с последними значениями тех.операций   
        $ar_tt[] = DataManager::getTT_from_ttent('tt_mr_to','tt_mri',$this->prop_to,'id');
        
        $sql = "select mr.tprocid, mr.id as mrid, top.value as toperid, rn.value as rank from tt_mri as mr
                    left join tt_mr_to as top
                    on mr.id=top.entityid
                    left join tt_mr_rank as rn
                    on mr.id=rn.entityid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr'); 
        
        
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
                on dc.csid = tp.entityid where op.value = mr.toperid and COALESCE(da.value,true)=true";
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
          $objs['LDATA'][$row['tprocid']]=array();
          $objs['LDATA'][$row['tprocid']]['tprocid']= array('name'=>$row['tprocid'], 'id'=>$row['tprocid']);
          $objs['LDATA'][$row['tprocid']]['godn']= array('name'=>$row['godn'], 'id'=>'');
          $objs['LDATA'][$row['tprocid']]['brak']= array('name'=>$row['brak'], 'id'=>'');
          $objs['LDATA'][$row['tprocid']]['startkol']= array('name'=>$row['startkol'], 'id'=>'');
          $objs['LDATA'][$row['tprocid']]['startdate']= array('name'=>$row['startdate'], 'id'=>'');
          if (!in_array($row['tprocid'], $arr_e)) $arr_e[]=$row['tprocid'];
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