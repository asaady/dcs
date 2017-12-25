<?php
namespace tzVendor;

use PDO;
use PDOStatement;
use tzVendor\Entity;

class CoverSheets extends Model 
{
    protected $entity;
    protected $troute_mdid='def88585-c509-4200-8980-19ae0e164bd7';  //тех.маршруты справочник
    protected $cs_mdid='be0d47b9-2972-496c-a11b-0f3d38874aab';  //сопр лист справочник
    protected $prop_div='08d45b18-7207-4ad9-a4fa-a76bdb880c01';  //реквизит подразделение справочник
    protected $proptroute='9c26942a-7aa2-4082-ae9a-ef8daf030ee2'; //реквизит техмаршрут шаблон
    protected $prop_to = '79ea3c05-c94b-4161-b24b-f7667ab41e6a'; //реквизит техоперация шаблон
    protected $prop_act = '11cc9d05-d63e-4943-bb95-87149b4e9eff'; //реквизит активность шаблон
    protected $prop_head = 'c1a7a6b3-63e9-48b9-b159-7acefe34a697'; //реквизит ответственный шаблон user
    protected $propstatus = '876fda77-c5e4-4948-bd81-2dd883fbbe40'; //реквизит Статус шаблон
    protected $propcs = '37a1e155-ed43-46e1-ade8-b75aff1a5031'; //реквизит Сопр.лист Шаблон
    protected $propdate = '43cba044-e85b-40f1-9c3d-a6a2af0deb9a'; //реквизит Дата Шаблон
    protected $prop_rank = '281f8a47-5fb2-4328-8320-e35493ef08e2'; // реквизит Порядок шаблон
    protected $mpprop_cs_name = 'ecc906a9-67d0-4026-a576-1462567493c6'; //реквизит сопроводительного листа номер
    protected $mpprop_cs_prod = 'c9b89d1c-86c3-4fe5-98cb-8044ea7187da'; //реквизит сопроводительного листа изделие
    protected $mpprop_cs_start ='015b8d13-907d-46e8-8077-f3e1ae57899c'; //реквизит сопроводительного листа количество запуска
    protected $mpprop_cs_date ='965acb33-cf77-41de-9eac-b7847419b67e'; //реквизит сопроводительного листа дата запуска
    protected $mpprop_cs_depart = '6296c846-bdd6-4705-9dad-92d070152947'; //реквизит сопроводительного листа подразделение
    protected $mpprop_cs_troute ='d05dd003-88eb-4006-acfe-b9ebd2400dec'; //реквизит сопроводительного листа техмаршрут
    protected $mpprop_tr_rank = 'aa82df0b-1da3-46ea-a5e6-d3d199913724'; //реквизит строки точка техмаршрута - порядок
    protected $mpprop_tr_name = '76a6657e-2bfb-436c-8b36-9b2061cca698'; //реквизит строки точка техмаршрута - наименование
    protected $mpprop_tr_act = '89c8e1be-dbce-4b21-b92e-4a3301c63aef'; //реквизит строки точка техмаршрута - активность
    
    protected $mpprop_ref_troute_set = 'a07bd922-b8f5-4f26-b934-f80923c2149e'; //реквизит ТЧ "Маршрут" - справочника Техмаршруты
    protected $mpprop_dv_cs = '65a3c995-09b2-49ee-85fc-b85bda107f52';
    protected $mpprop_dv_begn = 'c5531714-94e0-4fb9-87b4-68892ebcf54a';
    protected $mpprop_dv_godn = '8ef436b2-155f-4316-957b-7c191e316d86';
    protected $mpprop_dv_brak = '170fcbc8-22b8-4a38-903c-f21bb1a3b102';
    protected $mpprop_dv_repl = 'c6f7d841-190b-478e-9af1-c4b5637e1da8';
    protected $mpprop_dv_ptroute = '945335f3-6c7a-411a-bad4-337bf5519e62';
    protected $mpprop_dv_act = 'fff643b8-b754-44ba-b26f-ff1b4189eb13';
    
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
        $ldata = array();
        $title = "Отчет ". $this->name;
        $plist = array(
                array('id'=>'parameter','name'=>'parameter','synonym'=>'Подразделение','rank'=>1,'type'=>'id','valmdid'=>'50643d39-aec2-485e-9c30-bf29b04db75c','valmdtypename'=>'Refs','class'=>'active'),
                array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'active')
        );
        $pset=array(
            'trouteid'=>array('id'=>'trouteid','name'=>'trouteid','synonym'=>'Техмаршрут','type'=>'id', 'class'=>'active'),
            'startkol'=>array('id'=>'startkol','name'=>'startkol','synonym'=>'Запуск','type'=>'int', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Тех.потери','type'=>'int', 'class'=>'active'),
            'repl'=>array('id'=>'repl','name'=>'repl','synonym'=>'Перенос','type'=>'int', 'class'=>'active')
        );
        $mdname = '';
        if(array_key_exists('CURID', $data))
        {
            if ($data['CURID']!='')
            {    
                $ent = new Entity($data['CURID']);
                $mdname = $ent->getmdentity()->getname();
                $this->name = $ent->getname();
                $title = "Незавершенное производство по тех.маршрутам для ".$this->name;
                if ($mdname=='TechRoute_ref')
                {
                    $title = "Сопроводительные листы по тех.маршруту ".$this->name;
                    $plist = array(
                            array('id'=>'parameter','name'=>'parameter','synonym'=>'Тех.маршрут','rank'=>1,'type'=>'id','valmdid'=>'def88585-c509-4200-8980-19ae0e164bd7','valmdtypename'=>'Refs','class'=>'active'),
                            array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'active')
                    );
                    $pset=array(
                        'csid'=>array('id'=>'csid','name'=>'csid','synonym'=>'Сопр.лист','type'=>'id', 'class'=>'active'),
                        'startdate'=>array('id'=>'startdate','name'=>'startdate','synonym'=>'Дата запуска','type'=>'date', 'class'=>'active'),
                        'startkol'=>array('id'=>'startkol','name'=>'startkol','synonym'=>'Запуск','type'=>'int', 'class'=>'active'),
                        'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
                        'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Тех.потери','type'=>'int', 'class'=>'active'),
                        'repl'=>array('id'=>'repl','name'=>'repl','synonym'=>'Перенос','type'=>'int', 'class'=>'active'),
                        'routepoint'=>array('id'=>'routepoint','name'=>'routepoint','synonym'=>'Точка маршрута','type'=>'id', 'class'=>'active')
                    );
                }    
                elseif ($mdname=='CoverSheets')
                {
                    $title = "Сопроводительный лист ".$this->name;
                    $plist = array(
                            array('id'=>'parameter','name'=>'parameter','synonym'=>'Сопров.лист','rank'=>1,'type'=>'id','valmdid'=>'be0d47b9-2972-496c-a11b-0f3d38874aab','valmdtypename'=>'Refs','class'=>'active'),
                            array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'hidden')
                    );
                    $pset=array(
                        'routepoint'=>array('id'=>'routepoint','name'=>'routepoint','synonym'=>'Точка маршрута','type'=>'id', 'class'=>'active'),
                        'tdate'=>array('id'=>'startdate','name'=>'startdate','synonym'=>'Дата операции','type'=>'date', 'class'=>'active'),
                        'begn'=>array('id'=>'begn','name'=>'begn','synonym'=>'Поступило','type'=>'int', 'class'=>'active'),
                        'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
                        'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Тех.потери','type'=>'int', 'class'=>'active'),
                        'repl'=>array('id'=>'repl','name'=>'repl','synonym'=>'Перенос','type'=>'int', 'class'=>'active'),
                        'docid'=>array('id'=>'docid','name'=>'docid','synonym'=>'Документ','type'=>'id', 'class'=>'hidden'),
                        'head'=>array('id'=>'head','name'=>'head','synonym'=>'Ответственный','type'=>'cid', 'class'=>'active')
                    );
                }    
            }    
        }
        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'mdname'=>$mdname,
          'version'=>$this->version,
          'TITLE' => $title,     
          'PLIST' => $plist,   
          'PSET' => $pset,   
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
        $objs['SDATA']['parameter']=array('id'=>$csid,'name'=>$ent->getattr($this->mpprop_cs_name));
        
        
        //ищем сопроводительный лист по ид
        $params=array();
        $params['csid']=$csid;
        $sql = "SELECT id, id as entityid  FROM \"ETable\" as et WHERE id=:csid"; 
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_el0',$params);
        $objs['PLIST'] = array(
                'parameter'=>array('id'=>'parameter','name'=>'parameter','synonym'=>'Сопр.лист','rank'=>1,'type'=>'id','valmdid'=>'be0d47b9-2972-496c-a11b-0f3d38874aab','valmdtypename'=>'Refs','class'=>'active'),
                'mindate'=>array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'hidden')
                );

        //нашли техмаршрут сопроводительного листа
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_el','tt_el0',$this->mpprop_cs_troute,'id');
        
        $sql = "SELECT el.value as id, el.value as entityid FROM tt_el as el";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_tm');
        
        //выберем все точки тех маршрута 
        // нашли значение реквизита Маршрут (ТЧ) у техмаршрута
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_set_tr','tt_tm',$this->mpprop_ref_troute_set,'id');
//        $sql = "SELECT * FROM tt_set_tr"; 
//        $res = DataManager::dm_query($sql);
//        $rows = $res->fetchAll(PDO::FETCH_ASSOC);
//        die(var_dump($rows));
        
        // нашли строки ТЧ Маршрут (ТЧ) техмаршрута - все точки техмаршрута
        $sql = "select sdl.childid as entityid, sdl.childid as itemid, tr.entityid as trouteid from \"SetDepList\" as sdl
                    inner join tt_set_tr as tr
                    ON tr.value=sdl.parentid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_route');

        //собрали значения реквизита Порядок для точек маршрута
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_route_rank','tt_route',$this->mpprop_tr_rank,'int');
        
        //собрали значения реквизита Наименование для точек маршрута
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_route_name','tt_route',$this->mpprop_tr_name,'str');

        //собрали значения реквизита Активность для точек маршрута
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_route_act','tt_route',$this->mpprop_tr_act,'bool');
        
        $sql = "select mr.trouteid, mr.itemid, top.value as tname, rn.value as rank, COALESCE(ac.value, TRUE) as activity from tt_route as mr
                    left join tt_route_name as top
                    on mr.itemid=top.entityid
                    left join tt_route_rank as rn
                    on mr.itemid=rn.entityid
                    left join tt_route_act as ac
                    on mr.itemid=ac.entityid WHERE COALESCE(ac.value, true)=true"; // ORDER BY rn.value
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_routepoint_all');
        
        //теперь ищем строки таб.частей документов в которых искомые сопр.листы
        $ar_tt[] = DataManager::getTT_from_ttprop('tt_sel',$this->mpprop_dv_cs,'id','tt_el0');


        //теперь ищем последние значения реквизита поступило в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_begn','tt_sel',$this->mpprop_dv_begn,'int');
        
        //теперь ищем последние значения реквизита годные в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_godn','tt_sel',$this->mpprop_dv_godn,'int');
        
        //теперь ищем последние значения реквизита брак в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_brak','tt_sel',$this->mpprop_dv_brak,'int');
        
        //теперь ищем последние значения реквизита перемещение в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_repl','tt_sel',$this->mpprop_dv_repl,'int');

        //теперь ищем последние значения реквизита точка техмаршрута в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_ptroute','tt_sel',$this->mpprop_dv_ptroute,'id');

        //теперь ищем последние значения реквизита Активность в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_proute_act','tt_sel',$this->mpprop_dv_act,'bool');
        
        
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
        
        //теперь ищем последние значения реквизита дата в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_date','tt_dc',$this->propdate,'date');

        //теперь ищем последние значения реквизита активность в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_act','tt_dc',$this->prop_act,'bool');

        //теперь ищем последние значения реквизита ответственный в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_head','tt_dc',$this->prop_head,'cid');
        
        
        $sql = "select dc.docid, dc.itemid, pr.value as routepoint, dt.dateupdate, dt.value as tdate, COALESCE(bg.value,0) as begn, COALESCE(gd.value,0) as godn, COALESCE(br.value,0) as brak, COALESCE(rp.value,0) as repl, COALESCE(da.value,true) as activity, COALESCE(pr_act.value,true) as item_act from tt_doc as dc
                        inner join tt_ptroute as pr
                        on dc.itemid = pr.entityid
                        left join tt_proute_act as pr_act
                        on dc.itemid = pr_act.entityid
                        left join tt_kol_godn as gd
                        on dc.itemid = gd.entityid
                        left join tt_kol_brak as br
                        on dc.itemid = br.entityid
                        left join tt_kol_repl as rp
                        on dc.itemid = rp.entityid
                        left join tt_kol_begn as bg
                        on dc.itemid = bg.entityid
                        left join tt_act as da
                        on dc.docid = da.entityid
                        left join tt_date as dt
                        on dc.docid = dt.entityid
                where COALESCE(da.value,true) AND COALESCE(pr_act.value,true)";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_all'); 
        
        $sql = "select dc.routepoint, max(dc.dateupdate) as dateupdate, max(dc.tdate) as tdate, max(dc.godn) as godn, max(dc.begn) as begn, sum(dc.repl) as repl, sum(dc.brak) as brak from tt_all as dc group by dc.routepoint"; 
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_sum'); 
        
        
        $sql = "select mr.itemid as routepoint, dc.docid, to_char(sm.tdate,'DD-MM-YYYY') as tdate, ch.synonym as head, COALESCE(sm.godn,0) as godn, COALESCE(sm.brak,0) as brak, COALESCE(sm.repl,0) as repl, COALESCE(sm.begn,0) as begn, mr.rank from tt_routepoint_all as mr "
                . "inner join tt_sum as sm "
                    . "inner join tt_all as dc "
                        . "inner join tt_head as dh "
                            . "inner join \"CTable\" as ch "
                            . "on dh.value=ch.id "
                        . "on dc.docid=dh.entityid "
                    . "on sm.routepoint=dc.routepoint "
                    . "and sm.dateupdate=dc.dateupdate "
                . "on mr.itemid=sm.routepoint "
                . "order by mr.rank"; 
        $res = DataManager::dm_query($sql);
        
        
        $objs['LDATA']=array();
        $objs['PSET']=array(
            'routepoint'=>array('id'=>'routepoint','name'=>'routepoint','synonym'=>'Точка маршрута','type'=>'id', 'class'=>'active'),
            'tdate'=>array('id'=>'startdate','name'=>'startdate','synonym'=>'Дата операции','type'=>'date', 'class'=>'active'),
            'begn'=>array('id'=>'begn','name'=>'begn','synonym'=>'Поступило','type'=>'int', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Тех.потери','type'=>'int', 'class'=>'active'),
            'repl'=>array('id'=>'repl','name'=>'repl','synonym'=>'Перенос','type'=>'int', 'class'=>'active'),
            'docid'=>array('id'=>'docid','name'=>'docid','synonym'=>'Документ','type'=>'id', 'class'=>'hidden'),
            'head'=>array('id'=>'head','name'=>'head','synonym'=>'Ответственный','type'=>'cid', 'class'=>'active')
        );
        $arr_e= array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (!$show_empty)
            {
                if (($row['godn']+$row['brak']+$row['repl'])==0)
                {
                    continue;
                }    
            }    
            $rid = $row['routepoint'];
            $objs['LDATA'][$rid]=array();
            $objs['LDATA'][$rid]['begn']= array('name'=>$row['begn'], 'id'=>'');
            $objs['LDATA'][$rid]['godn']= array('name'=>$row['godn'], 'id'=>'');
            $objs['LDATA'][$rid]['brak']= array('name'=>$row['brak'], 'id'=>'');
            $objs['LDATA'][$rid]['repl']= array('name'=>$row['repl'], 'id'=>'');
            if (!in_array($rid, $arr_e)) $arr_e[]=$rid;
            $objs['LDATA'][$rid]['routepoint'] = array('name'=>$rid, 'id'=>$rid);
            if ($row['docid']!='')
            {
                if (!in_array($row['docid'], $arr_e)) $arr_e[]=$row['docid'];
                $objs['LDATA'][$rid]['docid']= array('name'=>$row['docid'], 'id'=>$row['docid']);
            }  
            else
            {    
                $objs['LDATA'][$rid]['docid']= array('name'=>'', 'id'=>'');
            }
            if (isset($row['tdate']))
            {    
                $objs['LDATA'][$rid]['tdate']= array('name'=>$row['tdate'], 'id'=>'');
            }
            else
            {
               $objs['LDATA'][$rid]['tdate']= array('name'=>'', 'id'=>'');
            }    
            if (isset($row['head']))
            {    
                $objs['LDATA'][$rid]['head']= array('name'=>$row['head'], 'id'=>'');
            }
            else
            {
               $objs['LDATA'][$rid]['head']= array('name'=>'', 'id'=>'');
            }    
        }
        DataManager::droptemptable($ar_tt);
        if (count($arr_e))
        {
//            $sql = "SELECT * FROM tt_head"; 
//            $res = DataManager::dm_query($sql);
//            $rows = $res->fetchAll(PDO::FETCH_ASSOC);
//            die(var_dump($arr_e));
            $arr_entities = EntitySet::getAllEntitiesToStr($arr_e);
            foreach($objs['LDATA'] as $toperid=>$row)
            {
                if (array_key_exists($toperid, $arr_entities))
                {
                    $objs['LDATA'][$toperid]['routepoint']['name']=$arr_entities[$toperid]['name'];
                }    
                if (array_key_exists($row['docid']['id'], $arr_entities))
                {
                    $objs['LDATA'][$toperid]['docid']['name']=$arr_entities[$row['docid']['id']]['name'];
                }    
                if (array_key_exists($row['head']['id'], $arr_entities))
                {
                    $objs['LDATA'][$toperid]['head']['name']=$arr_entities[$row['head']['id']]['name'];
                }    
            }
        }
       
        return $objs;	
    }
    public function getNZPbyCS($trouteid, $mindate='',$show_empty=false)
    {
        $objs = array();
        $objs['actionlist'] = array(array('id'=>'print','name'=>'print','synonym'=>'Печать','icon'=>'print'));
   
        $ar_tt = array();
        $ent= new Entity($trouteid);
        $objs['SDATA']=array();
        $objs['SDATA']['parameter']=array('id'=>$trouteid,'name'=>$ent->getname());
        
        $params=array();
        $params['trouteid']=$trouteid;
        $sql = "SELECT id, id as entityid FROM \"ETable\" as et WHERE id=:trouteid"; 
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_el00',$params);
        if ($mindate!='')
        {
            //выбрали сопроводительные листы у которых дата запуска больше или равно отбору
            $ar_tt[] = DataManager::getTT_entity('tt_el0',$this->cs_mdid,$this->propdate,$mindate,'date','>=');
            $objs['SDATA']['mindate']=array('id'=>'','name'=>$mindate);
        }    
        else 
        {
            $params=array();
            $params['mdid']=$this->cs_mdid;
            $sql = "SELECT id, id as entityid  FROM \"ETable\" WHERE mdid=:mdid"; 
            $ar_tt[] = DataManager::createtemptable($sql, 'tt_el0',$params);
            $objs['SDATA']['mindate']=array('id'=>'','name'=>'');
        }
        $objs['PLIST'] = array(
                'parameter'=>array('id'=>'parameter','name'=>'parameter','synonym'=>'Тех.маршрут','rank'=>1,'type'=>'id','valmdid'=>'def88585-c509-4200-8980-19ae0e164bd7','valmdtypename'=>'Refs','class'=>'active'),
                'mindate'=>array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'active')
                );

        //выбрали сопроводительные листы у которых техмаршрут соответствует выбранным ранее
        $ar_tt[] = DataManager::getTT_from_ttent('tt_el','tt_el0',$this->proptroute,'id','tt_el00');

        //нашли количество запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_st','tt_el',$this->mpprop_cs_start,'int');
        //нашли даты запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_dat_st','tt_el',$this->mpprop_cs_date,'date');
        
        //теперь ищем строки таб.частей документов в которых искомые сопр.листы
        $ar_tt[] = DataManager::getTT_from_ttprop('tt_sel',$this->mpprop_dv_cs,'id','tt_el0');


        //теперь ищем последние значения реквизита поступило в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_begn','tt_sel',$this->mpprop_dv_begn,'int');
        
        //теперь ищем последние значения реквизита годные в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_godn','tt_sel',$this->mpprop_dv_godn,'int');
        
        //теперь ищем последние значения реквизита брак в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_brak','tt_sel',$this->mpprop_dv_brak,'int');
        
        //теперь ищем последние значения реквизита перемещение в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_repl','tt_sel',$this->mpprop_dv_repl,'int');

        //теперь ищем последние значения реквизита точка техмаршрута в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_ptroute','tt_sel',$this->mpprop_dv_ptroute,'id');

        //теперь ищем последние значения реквизита Активность в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_proute_act','tt_sel',$this->mpprop_dv_act,'bool');
        
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
        
        //теперь ищем последние значения реквизита дата в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_date','tt_dc',$this->propdate,'date');

        //теперь ищем последние значения реквизита активность в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_act','tt_dc',$this->prop_act,'bool');
        
        //выберем все точки тех маршрута 
        // нашли значение реквизита Маршрут (ТЧ) у техмаршрута
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_set_tr','tt_el00',$this->mpprop_ref_troute_set,'id');
        
        // нашли строки ТЧ Маршрут (ТЧ) техмаршрута - все точки техмаршрута
        $sql = "select sdl.childid as entityid, sdl.childid as itemid, tr.entityid as trouteid from \"SetDepList\" as sdl
                    inner join tt_set_tr as tr
                    ON tr.value=sdl.parentid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_route');

        //собрали значения реквизита Порядок для точек маршрута
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_route_rank','tt_route',$this->mpprop_tr_rank,'int');
        
        //собрали значения реквизита Активность для точек маршрута
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_route_act','tt_route',$this->mpprop_tr_act,'bool');
        
        $sql = "select mr.trouteid, mr.itemid, rn.value as rank, COALESCE(ac.value, TRUE) as activity from tt_route as mr
                    left join tt_route_rank as rn
                    on mr.itemid=rn.entityid
                    left join tt_route_act as ac
                    on mr.itemid=ac.entityid 
                    WHERE COALESCE(ac.value, true)";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr'); 
        
        
        $sql = "select dc.docid, dc.itemid, dc.csid, mr.trouteid, tp.value as routepoint, COALESCE(da.value,true) as activity, dt.value as date, gd.value as godn, rp.value as repl, br.value as brak, mr.rank from tt_doc as dc 
                left join tt_act as da
                on dc.docid = da.entityid
                left join tt_date as dt
                on dc.docid = dt.entityid
                left join tt_kol_repl as rp
                on dc.itemid = rp.entityid
                left join tt_proute_act as rt_act
                on dc.itemid = rt_act.entityid
                left join tt_kol_godn as gd
                on dc.itemid = gd.entityid
                left join tt_kol_brak as br
                on dc.itemid = br.entityid
                inner join tt_ptroute as tp
                    left join tt_mr as mr
                    on tp.value = mr.itemid
                on dc.itemid = tp.entityid 
                where COALESCE(da.value,true) AND COALESCE(rt_act.value,true)";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtmm');
        //$res = DataManager::dm_query($sql);
        //$rows = $res->fetchAll(PDO::FETCH_ASSOC);
        //die(var_dump($rows));
        $sql = "select dc.csid, dc.routepoint, sum(dc.godn) as godn, sum(dc.repl) as repl, sum(dc.brak) as brak from tt_dtmm as dc group by dc.csid, dc.routepoint";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtmt');
        
        $sql = "select dc.csid, max(mm.rank) as rank from tt_dtmt as dc "
                . "inner join tt_dtmm as mm "
                . "on dc.csid=mm.csid "
                . "and dc.routepoint=mm.routepoint "
                . "group by dc.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_rank');
        

        $sql = "select dc.csid, sum(mm.repl) as repl, sum(mm.brak) as brak from tt_dtmt as dc "
                . "inner join tt_dtmm as mm "
                . "on dc.csid=mm.csid "
                . "and dc.routepoint=mm.routepoint "
                . "group by dc.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_brak');

        $sql = "select dc.csid, mm.rank, dc.godn, mm.routepoint from tt_dtmt as dc "
                . "inner join tt_dtmm as mm "
                    . "inner join tt_cs_rank as cs "
                    . "on mm.csid=cs.csid "
                    . "and mm.rank=cs.rank "
                . "on dc.csid = mm.csid "
                . "and dc.routepoint = mm.routepoint";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_godn');
        
        $sql = "select tp.entityid as csid, tp.value as trouteid, gd.routepoint, gd.godn, br.repl, br.brak, st.value as startkol, to_char(dt.value,'DD-MM-YYYY') as startdate from tt_el as tp
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
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Тех.потери','type'=>'int', 'class'=>'active'),
            'repl'=>array('id'=>'repl','name'=>'repl','synonym'=>'Перенос','type'=>'int', 'class'=>'active'),
            'routepoint'=>array('id'=>'routepoint','name'=>'routepoint','synonym'=>'Точка маршрута','type'=>'id', 'class'=>'active')
        );
        $arr_e= array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (($row['godn']+$row['brak']+$row['repl'])==0)
            {
                continue;
            }    
            $objs['LDATA'][$row['csid']]=array();
            $objs['LDATA'][$row['csid']]['csid']= array('name'=>$row['tprocid'], 'id'=>$row['tprocid']);
            $objs['LDATA'][$row['csid']]['godn']= array('name'=>$row['godn'], 'id'=>'');
            $objs['LDATA'][$row['csid']]['repl']= array('name'=>$row['repl'], 'id'=>'');
            $objs['LDATA'][$row['csid']]['brak']= array('name'=>$row['brak'], 'id'=>'');
            $objs['LDATA'][$row['csid']]['startkol']= array('name'=>$row['startkol'], 'id'=>'');
            $objs['LDATA'][$row['csid']]['startdate']= array('name'=>$row['startdate'], 'id'=>'');
            $objs['LDATA'][$row['csid']]['routepoint']= array('name'=>$row['routepoint'], 'id'=>$row['routepoint']);
            if ($row['csid'])
            {
                if (!in_array($row['csid'], $arr_e)) $arr_e[]=$row['csid'];
            }
            if ($row['routepoint'])
            {
                if (!in_array($row['routepoint'], $arr_e)) $arr_e[]=$row['routepoint'];
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
                if (array_key_exists($row['routepoint']['id'], $arr_entities))
                {
                    $objs['LDATA'][$csid]['routepoint']['name']=$arr_entities[$row['routepoint']['id']]['name'];
                }    
            }
        }
       
        return $objs;	
    }

    public function getNZPbyTProc($divid='', $mindate='',$show_empty=false)
    {
        $objs = array();
        $objs['actionlist'] = array(array('id'=>'print','name'=>'print','synonym'=>'Печать','icon'=>'print'));
        
        $objs['PLIST'] = array(
                'parameter'=>array('id'=>'parameter','name'=>'parameter','synonym'=>'Подразделение','rank'=>1,'type'=>'id','valmdid'=>'50643d39-aec2-485e-9c30-bf29b04db75c','valmdtypename'=>'Refs','class'=>'active'),
                'mindate'=>array('id'=>'mindate','name'=>'mindate','synonym'=>'Cопр.листы с','rank'=>2,'type'=>'date','valmdid'=>TZ_TYPE_EMPTY,'valmdtypename'=>'','class'=>'active')
                );
        $objs['PSET']=array(
            'trouteid'=>array('id'=>'trouteid','name'=>'trouteid','synonym'=>'Техмаршрут','type'=>'id', 'class'=>'active'),
            'startkol'=>array('id'=>'startkol','name'=>'startkol','synonym'=>'Запуск','type'=>'int', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Годные','type'=>'int', 'class'=>'active'),
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Тех.потери','type'=>'int', 'class'=>'active'),
            'repl'=>array('id'=>'repl','name'=>'repl','synonym'=>'Перенос','type'=>'int', 'class'=>'active')
        );
        $objs['LDATA']=array();
        $objs['SDATA']=array();
        
        $ar_tt = array();
        if ($divid=='')
        {
            $settings = DataManager::getSettings();
            $key = array_search($this->prop_div, array_column($settings, 'propid'));
            if ($key!==false)
            {
                $divid = $settings[$key]['value'];
            }
        }
        if ($divid=='')
        {
            return $objs;
        }    
        
        //выбрали техмаршруты у которых подразделение равно отбору
         $ent= new Entity($divid);
         $ar_tt[] = DataManager::getTT_entity('tt_el00',$this->troute_mdid,$this->prop_div,$divid,'id','=');
         $objs['SDATA']['parameter']=array('id'=>$divid,'name'=>$ent->getname());
        if ($mindate!='')
        {
            //выбрали сопроводительные листы у которых дата запуска больше или равно отбору
            $ar_tt[] = DataManager::getTT_entity('tt_el0',$this->cs_mdid,$this->propdate,$mindate,'date','>=');
            $objs['SDATA']['mindate']=array('id'=>'','name'=>$mindate);
        }    
        else 
        {
            $params=array();
            $params['mdid']=$this->cs_mdid;
            $sql = "SELECT id, id as entityid  FROM \"ETable\" WHERE mdid=:mdid"; 
            $ar_tt[] = DataManager::createtemptable($sql, 'tt_el0',$params);
            $objs['SDATA']['mindate']=array('id'=>'','name'=>'');
        }
        //выбрали сопроводительные листы у которых техмаршрут соответствует выбранным ранее
        $ar_tt[] = DataManager::getTT_from_ttent('tt_el','tt_el0',$this->proptroute,'id','tt_el00');

        //нашли количество запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_st','tt_el',$this->mpprop_cs_start,'int');
        //нашли даты запуска сопроводительных листов
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_dat_st','tt_el',$this->mpprop_cs_date,'date');
        
        
        //теперь ищем строки таб.частей документов в которых искомые сопр.листы
        $ar_tt[] = DataManager::getTT_from_ttprop('tt_sel',$this->mpprop_dv_cs,'id','tt_el0');


        //теперь ищем последние значения реквизита годные в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_godn','tt_sel',$this->mpprop_dv_godn,'int');
        
        //теперь ищем последние значения реквизита брак в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_brak','tt_sel',$this->mpprop_dv_brak,'int');
        
        //теперь ищем последние значения реквизита перемещение в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_kol_repl','tt_sel',$this->mpprop_dv_repl,'int');

        //теперь ищем последние значения реквизита точка техмаршрута в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_ptroute','tt_sel',$this->mpprop_dv_ptroute,'id');

        //теперь ищем последние значения реквизита Активность в найденных строках
        $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_proute_act','tt_sel',$this->mpprop_dv_act,'bool');
        
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
        
        //теперь ищем последние значения реквизита дата в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_date','tt_dc',$this->propdate,'date');
        
        //теперь ищем последние значения реквизита активность в найденных доках
        $ar_tt[] = DataManager::getTT_from_ttent('tt_act','tt_dc',$this->prop_act,'bool','',FALSE);

        
        $sql = "select distinct value as id FROM tt_ptroute";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_routepoint');
        
        $sql = "select sdl.childid as routepoint, sdl.parentid as setid from \"SetDepList\" as sdl
                    inner join tt_routepoint as tr
                    ON tr.id=sdl.childid
                    AND sdl.rank>0";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_routeset');
        
        $sql = "select it.entityid as trouteid, rs.routepoint as routepoint from \"PropValue_id\" as pv
                    inner join \"IDTable\" as it
                    ON pv.id = it.id
                    inner join tt_routeset as rs
                    on pv.value=rs.setid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_troute');

        //теперь ищем последние значения реквизита ранг в найденных точках маршрута
        $ar_tt[] = DataManager::getTT_from_ttent('tt_route_rank','tt_routepoint',$this->prop_rank,'int','',TRUE);
        
        $sql = "select mr.trouteid, mr.routepoint, rn.value as rank from tt_troute as mr
                    left join tt_route_rank as rn
                    on mr.routepoint=rn.entityid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_mr'); 
        
        
        
        $sql = "select dc.docid, dc.itemid, mr.trouteid, dc.csid, tp.value as routepoint, dt.value as date, COALESCE(da.value,true) as activity, gd.value as godn, br.value as brak, rp.value as repl, mr.rank from tt_doc as dc 
                left join tt_act as da
                on dc.docid = da.entityid
                left join tt_date as dt
                on dc.docid = dt.entityid
                left join tt_kol_godn as gd
                on dc.itemid = gd.entityid
                left join tt_kol_repl as rp
                on dc.itemid = rp.entityid
                left join tt_proute_act as rt_act
                on dc.itemid = rt_act.entityid
                left join tt_kol_brak as br
                on dc.itemid = br.entityid
                left join tt_ptroute as tp
                    left join tt_mr as mr
                    on tp.value = mr.routepoint
                on dc.itemid = tp.entityid 
                where COALESCE(da.value,true) AND COALESCE(rt_act.value,true)";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtmm');
        
        $sql = "select dc.csid, dc.routepoint, dc.trouteid, max(dc.date) as date, dc.rank, sum(dc.godn) as godn, sum(dc.brak) as brak, sum(dc.repl) as repl from tt_dtmm as dc group by dc.csid, dc.routepoint, dc.trouteid, dc.rank";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtms');
//        $sqlp = "select * from tt_dtms";
//        $res = DataManager::dm_query($sqlp);
//        die(var_dump($res->fetchAll(PDO::FETCH_ASSOC)));    
        
        $sql = "select dc.csid, dc.trouteid, max(dc.rank) as rank, sum(dc.brak) as brak, sum(dc.repl) as repl from tt_dtms as dc group by dc.csid, dc.trouteid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_dtmt');
        
        $sql = "select dc.csid, max(dc.rank) as rank from tt_dtmt as dc group by dc.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_rank');

        $sql = "select dc.csid, sum(mm.brak) as brak from tt_dtmt as dc inner join tt_dtms as mm on dc.csid=mm.csid and dc.trouteid=mm.trouteid group by dc.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_brak');
        
        $sql = "select dc.csid, sum(mm.repl) as repl from tt_dtmt as dc inner join tt_dtms as mm on dc.csid=mm.csid and dc.trouteid=mm.trouteid group by dc.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_repl');

        $sql = "select dc.csid, dc.rank, dc.trouteid, dt.godn, st.value as startkol from tt_dtmt as dc "
                . "inner join tt_cs_rank as cs "
                . "on dc.csid=cs.csid "
                . "and dc.rank=cs.rank "
                . "inner join tt_dtms as dt "
                    . "left join tt_kol_st as st "
                    . "on dt.csid = st.entityid "
                . "on dc.csid=dt.csid "
                . "and dc.rank=dt.rank ";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs_godn');
        
        
        $sql = "select tp.entityid as csid, gd.trouteid, gd.godn, br.brak, rp.repl, gd.startkol from tt_el as tp
                left join tt_cs_godn as gd
                on tp.entityid = gd.csid
                left join tt_cs_brak as br
                on tp.entityid = br.csid
                left join tt_cs_repl as rp
                on tp.entityid = rp.csid";
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_cs');

        
        $sql = "select tp.trouteid, sum(tp.godn) as godn, sum(tp.brak) as brak, sum(tp.repl) as repl, sum(tp.startkol) as startkol from tt_cs as tp group by tp.trouteid";
        $res = DataManager::dm_query($sql);
        $arr_e= array();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            if (($row['godn']+$row['brak']+$row['repl'])==0)
            {
                continue;
            }    
            if ($row['trouteid'])
            {  
                $objs['LDATA'][$row['trouteid']]=array();
                $objs['LDATA'][$row['trouteid']]['trouteid']= array('name'=>$row['trouteid'], 'id'=>$row['trouteid']);
                $objs['LDATA'][$row['trouteid']]['godn']= array('name'=>$row['godn'], 'id'=>'');
                $objs['LDATA'][$row['trouteid']]['brak']= array('name'=>$row['brak'], 'id'=>'');
                $objs['LDATA'][$row['trouteid']]['repl']= array('name'=>$row['repl'], 'id'=>'');
                $objs['LDATA'][$row['trouteid']]['startkol']= array('name'=>$row['startkol'], 'id'=>'');
                if (!in_array($row['trouteid'], $arr_e)) $arr_e[]=$row['trouteid'];
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
                    $objs['LDATA'][$tprocid]['trouteid']['name']=$arr_entities[$tprocid]['name'];
                }    
            }
        }
       
        return $objs;	
    }
}    