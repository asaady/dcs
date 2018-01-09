<?php
namespace dcs\app\components\prnforms\coversheets;

use PDO;
use PDOStatement;
use DateTime;
use dcs\vendor\core\Entity;
use dcs\vendor\core\Model;
use dcs\vendor\core\DataManager;

class PrnCoverSheets extends Model 
{
     protected $entity;
    protected $tproc_mdid='def88585-c509-4200-8980-19ae0e164bd7';  //тех.процесс справочник
    protected $mdid='be0d47b9-2972-496c-a11b-0f3d38874aab';  //сопр лист справочник
    protected $prop_div='08d45b18-7207-4ad9-a4fa-a76bdb880c01';  //реквизит подразделение справочник
    protected $proptroute='9c26942a-7aa2-4082-ae9a-ef8daf030ee2'; //реквизит техмаршрут шаблон
    protected $prop_to = '79ea3c05-c94b-4161-b24b-f7667ab41e6a'; //реквизит техоперация шаблон
    protected $prop_act = '11cc9d05-d63e-4943-bb95-87149b4e9eff'; //реквизит активность шаблон
    protected $prop_mr = 'f06b5b81-aa70-42de-8d4e-1718d2033952'; //реквизит тех.маршрут шаблон
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
    
    

    public function __construct($id) 
    {
        $this->id = $id;
        $this->entity = new Entity($id);
        $this->name = $this->entity->getattr($this->mpprop_cs_name); 
        $this->version = time();        
    }
    public function get_data($data)
    {
        $sdata = array();
        $ldata = array();
        $plist = array();
        $pset=array(
            'nom' => array('id'=>'nom','name'=>'nom','synonym'=>'№ п/п','type'=>'nom', 'class'=>'active'),
            'tname'=>array('id'=>'tname','name'=>'tname','synonym'=>'Тех.операция','type'=>'str', 'class'=>'active'),
            'tdate'=>array('id'=>'startdate','name'=>'startdate','synonym'=>'Дата','type'=>'int', 'class'=>'active'),
            'ttime_st'=>array('id'=>'starttime','name'=>'starttime','synonym'=>'Начало','type'=>'int', 'class'=>'active'),
            'ttime_en'=>array('id'=>'endtime','name'=>'endtime','synonym'=>'Оконч.','type'=>'int', 'class'=>'active'),
            'tdate'=>array('id'=>'startdate','name'=>'startdate','synonym'=>'Дата','type'=>'int', 'class'=>'active'),
            'kols'=>array('id'=>'kols','name'=>'kols','synonym'=>'Поступило, шт','type'=>'int', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Сдано на след. опер.','type'=>'int', 'class'=>'active'),
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Тех. потери','type'=>'int', 'class'=>'active'),
            'repl'=>array('id'=>'repl','name'=>'repl','synonym'=>'Перенос','type'=>'int', 'class'=>'active'),
            'thead'=>array('id'=>'thead','name'=>'thead','synonym'=>'Подпись','type'=>'cid', 'class'=>'active')
        );
        $date = new DateTime($this->entity->getattr($this->mpprop_cs_date));
        return array(
          'id'=>$this->id,
          'name'=>$this->name,
          'date'=>$date->format('d.m.Y'),
          'troute'=>$this->entity->getattr($this->mpprop_cs_troute),
          'tprod'=>$this->entity->getattr($this->mpprop_cs_prod),
          'depart'=>$this->entity->getattr($this->mpprop_cs_depart),
          'start'=>$this->entity->getattr($this->mpprop_cs_start),
          'version'=>$this->version,
          'PLIST' => $plist,   
          'PSET' => $pset,   
          'navlist' => array()
          );
    }        
    public function getCSdata_byTO()
    {
        
        $objs = array();
        $objs['actionlist'] = array(array('id'=>'print','name'=>'print','synonym'=>'Печать','icon'=>'print'));
        
        $csid = $this->id;
        
        $ar_tt = array();
        $ent= new Entity($csid);
        $objs['SDATA']=array();
        $objs['SDATA']['parameter']=array('id'=>$csid,'name'=>$ent->getattr($this->mpprop_cs_name));
        
        
        $params=array();
        $params['csid']=$csid;
        $sql = "SELECT id, id as entityid  FROM \"ETable\" as et WHERE id=:csid"; 
        $ar_tt[] = DataManager::createtemptable($sql, 'tt_el0',$params);
        $objs['PLIST'] = array();

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
                    on mr.itemid=ac.entityid WHERE COALESCE(ac.value, true)=true ORDER BY rn.value";
        $res = DataManager::dm_query($sql);
        
        
        $objs['LDATA']=array();
        $objs['PSET']=array(
            'nom' => array('id'=>'nom','name'=>'nom','synonym'=>'№ п/п','type'=>'nom', 'class'=>'active'),
            'tname'=>array('id'=>'tname','name'=>'tname','synonym'=>'Тех.операция','type'=>'str', 'class'=>'active'),
            'tdate'=>array('id'=>'tdate','name'=>'tdate','synonym'=>'Дата','type'=>'int', 'class'=>'active'),
            'ttime_st'=>array('id'=>'starttime','name'=>'starttime','synonym'=>'Начало','type'=>'int', 'class'=>'active'),
            'ttime_en'=>array('id'=>'endtime','name'=>'endtime','synonym'=>'Оконч.','type'=>'int', 'class'=>'active'),
            'kols'=>array('id'=>'kols','name'=>'kols','synonym'=>'Поступило, шт','type'=>'int', 'class'=>'active'),
            'godn'=>array('id'=>'godn','name'=>'godn','synonym'=>'Сдано на след. опер.','type'=>'int', 'class'=>'active'),
            'brak'=>array('id'=>'brak','name'=>'brak','synonym'=>'Тех. потери','type'=>'int', 'class'=>'active'),
            'repl'=>array('id'=>'repl','name'=>'repl','synonym'=>'Перенос','type'=>'int', 'class'=>'active'),
            'thead'=>array('id'=>'thead','name'=>'thead','synonym'=>'Подпись','type'=>'cid', 'class'=>'active')
        );
        $arr_e= array();
        $nom=0;
        while($row = $res->fetch(PDO::FETCH_ASSOC)) 
        {
            $nom++;
            $objs['LDATA'][$row['itemid']]=array();
            $objs['LDATA'][$row['itemid']]['nom']= array('name'=>$nom, 'id'=>'');
            $objs['LDATA'][$row['itemid']]['godn']= array('name'=>'', 'id'=>'');
            $objs['LDATA'][$row['itemid']]['brak']= array('name'=>'', 'id'=>'');
            $objs['LDATA'][$row['itemid']]['repl']= array('name'=>'', 'id'=>'');
            $objs['LDATA'][$row['itemid']]['tname']= array('name'=>$row['tname'], 'id'=>'');
            $objs['LDATA'][$row['itemid']]['thead']= array('name'=>'', 'id'=>'');
            $objs['LDATA'][$row['itemid']]['tdate']= array('name'=>'', 'id'=>'');
        }
        DataManager::droptemptable($ar_tt);
        return $objs;	
    }
}    