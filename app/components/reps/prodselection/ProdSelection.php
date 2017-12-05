<?php
namespace tzVendor;

use PDO;
use PDOStatement;
use tzVendor\Entity;
use tzVendor\Hungarian;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");

class ProdSelection extends Model 
{
    protected $entity;
    protected $doc_mdid = '1a41838f-ab73-4da8-a2aa-7ed23f5cd06d';  //документ Измерения
    protected $ref_mdid = '71cbbcc2-a5f5-4862-9834-9aa953bfa00f';  //справочник таблица пар
    protected $item_doc_mdid = '652f697b-717d-42e8-8a9b-2b5f169449b9';  //строка ТЧ документ Измерения
    protected $item_ref_mdid = '94af0e54-d1ef-44d5-a445-6e5f2725d7cd';  //строка ТЧ справочника таблиц подбора
    protected $set_ref_mdid = '87701a86-f11c-498d-9c65-6c859dac2828'; //реквизит - ТЧ [Значения подбора] справочника таблицы подбора
    protected $set_doc_mdid = '39cac10a-f0c2-436e-8586-5485674143ec'; //реквизит - ТЧ [Измерения] документа Измерения
    protected $ref_value1_propid = '34d4b29b-72d4-4c92-96f1-eb8e4d6abbb5';
    protected $ref_value2_propid = '80032873-5abb-475e-8c66-0213e18ab9bd';
    protected $ref_activ_propid = 'fc3b49de-8307-4c6b-b839-47fd79a0a32a'; //реквизит строки ТЧ [Значения подбора] справочника таблицы подбора - активность
    
    protected $doc_par1_propid = 'bfde0a7b-ba46-495e-a652-ec213b18ee4c'; //реквизит строки ТЧ [Измерения] документа Измерения - параметр 1
    protected $doc_par2_propid = 'c6192618-f4cb-429e-9954-416898024aa2'; //реквизит строки ТЧ [Измерения] документа Измерения - параметр 2
    protected $doc_par3_propid = '940622ae-4c8b-41e2-8efa-068bd38e6a39'; //реквизит строки ТЧ [Измерения] документа Измерения - параметр 3
    protected $doc_rank_propid = '69918705-5cfe-4ec3-88f0-10f3ae077be4'; //реквизит строки ТЧ [Измерения] документа Измерения - ранг
    protected $doc_activ_propid = 'd3496cd7-d4b8-4b43-aa0d-a5fa16e076f3'; //реквизит строки ТЧ [Измерения] документа Измерения - активность
    
    protected $doc_param1_propid = '18544ce5-39bf-453d-acd5-d74f3d4484a4'; //реквизит параметр1 документа Измерение
    protected $doc_param2_propid = '1599ead6-a2a5-4577-b608-2b522494e1e4'; //реквизит параметр2 документа Измерение
    protected $doc_param3_propid = '27184f54-7912-42b2-bb5a-408ff81bb745'; //реквизит параметр3 документа Измерение
    protected $step_propid = '45b4c797-e059-4223-b15a-0265725d78bd'; //реквизит step справочника Параметры измерений






    public function __construct($id) 
    {
        
        if ($id=='')
        {
            die("class.ProdSelection constructor: id is empty");
        }
        $this->entity = new CollectionItem($id);
        
        if ($this->entity->getname()!='ProdSelection')
        {
            die("class.ProdSelection constructor: bad id");
        }    
        $this->id = $id;
        $this->name = $this->entity->getsynonym();
        $this->version = time();        
    }
    public function get_data($data)
    {
        $sdata = array();
        $plist = array(
                array('id'=>'doc1','name'=>'doc1','synonym'=>'Документ по изд.1','rank'=>3,'type'=>'id','valmdid'=>$this->doc_mdid,'valmdtypename'=>'Docs','class'=>'active'),
                array('id'=>'doc2','name'=>'doc2','synonym'=>'Документ по изд.2','rank'=>4,'type'=>'id','valmdid'=>$this->doc_mdid,'valmdtypename'=>'Docs','class'=>'active'),
//                array('id'=>'ref1','name'=>'ref1','synonym'=>'Таблица параметра 1','rank'=>5,'type'=>'id','valmdid'=>$this->ref_mdid,'valmdtypename'=>'Refs','class'=>'active'),
//                array('id'=>'ref2','name'=>'ref2','synonym'=>'Таблица параметра 2','rank'=>7,'type'=>'id','valmdid'=>$this->ref_mdid,'valmdtypename'=>'Refs','class'=>'active'),
//                array('id'=>'ref3','name'=>'ref3','synonym'=>'Таблица параметра 3','rank'=>9,'type'=>'id','valmdid'=>$this->ref_mdid,'valmdtypename'=>'Refs','class'=>'active'),
        );
        $pset=array(
            'num'=>array('id'=>'num','name'=>'num','synonym'=>'Номер п/п','type'=>'int', 'class'=>'active'),
            'nom1'=>array('id'=>'nom1','name'=>'nom1','synonym'=>'Изделие 1','type'=>'int', 'class'=>'active'),
            'nom2'=>array('id'=>'nom2','name'=>'nom2','synonym'=>'Изделие 2','type'=>'int', 'class'=>'active'),
            'izm11'=>array('id'=>'izm11','name'=>'izm11','synonym'=>'Изд1: Изм.1','type'=>'float', 'class'=>'active'),
            'izm21'=>array('id'=>'izm21','name'=>'izm21','synonym'=>'Изд2: Изм.1','type'=>'float', 'class'=>'active'),
            'izm12'=>array('id'=>'izm12','name'=>'izm12','synonym'=>'Изд1: Изм.2','type'=>'float', 'class'=>'active'),
            'izm22'=>array('id'=>'izm22','name'=>'izm22','synonym'=>'Изд2: Изм.2','type'=>'float', 'class'=>'active'),
            'izm13'=>array('id'=>'izm13','name'=>'izm13','synonym'=>'Изд1: Изм.3','type'=>'float', 'class'=>'active'),
            'izm23'=>array('id'=>'izm23','name'=>'izm23','synonym'=>'Изд2: Изм.3','type'=>'float', 'class'=>'active'),
            //'error'=>array('id'=>'error','name'=>'error','synonym'=>'Ошибка','type'=>'int', 'class'=>'active')
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
    function get_selectiontable($ttname,$ref1_id,$ref2_id,$ref3_id, &$ar_tt)        
    {
        $artt = array();
        
        $sql = "select it.entityid as refid, pv.value as setid from \"PropValue_id\" as pv inner join \"IDTable\" as it on pv.id=it.id where it.propid = :propid and it.entityid in (:ref1,:ref2,:ref3)";
	$artt[] = DataManager::createtemptable($sql, 'tt_el',array('propid'=>$this->set_ref_mdid,'ref1'=>$ref1_id,'ref2'=>$ref2_id,'ref3'=>$ref3_id));
        
        //найдем все строки ТЧ справочников
        $sql = "select tm.refid, tm.setid, sdl.childid as entityid, sdl.childid as itemid FROM tt_el as tm
                    INNER JOIN \"SetDepList\" as sdl
                    ON tm.setid=sdl.parentid
                    AND sdl.rank>0";
        $artt[] = DataManager::createtemptable($sql, 'tt_tri');
        
       //нашли значения изд1 в таблицах подбора
        $artt[] = DataManager::getTT_from_ttent_prop('tt_value1','tt_tri',$this->ref_value1_propid,'float');
       //нашли значения изд2 в таблицах подбора
        $artt[] = DataManager::getTT_from_ttent_prop('tt_value2','tt_tri',$this->ref_value2_propid,'float');
       //нашли значения activity в таблицах подбора
        $artt[] = DataManager::getTT_from_ttent_prop('tt_activ','tt_tri',$this->ref_activ_propid,'bool');

        $sql = "select tr.refid, tr.itemid, v1.value as value1, v2.value as value2, COALESCE(ac.value,TRUE) as activity from tt_tri as tr 
                    inner join tt_value1 as v1 
                        inner join tt_value2 as v2 
                        on v1.entityid = v2.entityid 
                    on tr.itemid = v1.entityid
                    inner join tt_activ as ac
                    on tr.itemid = ac.entityid";
	$ar_tt[] = DataManager::createtemptable($sql, $ttname);
        DataManager::droptemptable($artt);
        return 0;
    }
    function get_doctable($ttname,$doc_id, &$ar_tt)        
    {
        $artt = array();
        //найдем значение реквизитов ТЧ документа подбора
        $sql = "select it.entityid as docid, pv.value as setid from \"PropValue_id\" as pv inner join \"IDTable\" as it on pv.id=it.id where it.propid = :propid and it.entityid = :entityid";
	$artt[] = DataManager::createtemptable($sql, 'tt_el',array('propid'=>$this->set_doc_mdid,'entityid'=>$doc_id));
        
        //найдем все строки ТЧ документов
        $sql = "select tm.docid, tm.setid, sdl.childid as entityid, sdl.childid as itemid FROM tt_el as tm
                    INNER JOIN \"SetDepList\" as sdl
                    ON tm.setid=sdl.parentid
                    AND sdl.rank>0";
        $artt[] = DataManager::createtemptable($sql, 'tt_tdi');
        
       //нашли значения парам1 в таблицах измерений
        $artt[] = DataManager::getTT_from_ttent_prop('tt_par1','tt_tdi',$this->doc_par1_propid,'float');
       //нашли значения парам2 в таблицах измерений
        $artt[] = DataManager::getTT_from_ttent_prop('tt_par2','tt_tdi',$this->doc_par2_propid,'float');
       //нашли значения парам3 в таблицах измерений
        $artt[] = DataManager::getTT_from_ttent_prop('tt_par3','tt_tdi',$this->doc_par3_propid,'float');
       //нашли значения activity в таблицах измерений
        $artt[] = DataManager::getTT_from_ttent_prop('tt_activ','tt_tdi',$this->doc_activ_propid,'bool');
       //нашли значения rank в таблицах измерений
        $artt[] = DataManager::getTT_from_ttent_prop('tt_rank','tt_tdi',$this->doc_rank_propid,'int');

        $sql = "select tr.docid, tr.itemid, rn.value as rank, v1.value as value1, v2.value as value2, v3.value as value3, COALESCE(ac.value,TRUE) as activity from tt_tdi as tr 
                    left join tt_par1 as v1
                    on tr.itemid = v1.entityid
                    left join tt_par2 as v2 
                    on tr.itemid = v2.entityid 
                    left join tt_par3 as v3 
                    on tr.itemid = v3.entityid 
                    left join tt_activ as ac
                    on tr.itemid = ac.entityid
                    inner join tt_rank as rn
                    on tr.itemid = rn.entityid
                    ";
	$ar_tt[] = DataManager::createtemptable($sql, $ttname);
        DataManager::droptemptable($artt);
        return 0;
    }
    
    function get_docparameter($doc_id, $prop_id)        
    {
        $artt = array();
        //найдем значение реквизитов ТЧ документа подбора
        $sql = "select it.entityid as docid, pv.value as param_id, it.dateupdate from \"PropValue_id\" as pv inner join \"IDTable\" as it on pv.id=it.id where it.propid = :propid and it.entityid = :entityid";
	$artt[] = DataManager::createtemptable($sql, 'tt_pp',array('propid'=>$prop_id,'entityid'=>$doc_id));
        
        //найдем последние значения свойства документов
        $sql = "select el.param_id from tt_pp as el where el.dateupdate in (select max(el.dateupdate) as dateupdate from tt_pp as el)";
        $res = DataManager::dm_query($sql);	
        $tt_pv = $res->fetchAll(PDO::FETCH_ASSOC);
        
        DataManager::droptemptable($artt);
        
        foreach($tt_pv as $pv)
        {
            return new Entity($pv['param_id']);
        }    
    }

    public function get_selection($doc1_id,$doc2_id,$ref1_id,$ref2_id,$ref3_id)
    {
        $objs = array();
        $objs['actionlist'] = array(array('id'=>'print','name'=>'print','synonym'=>'Печать','icon'=>'print'));
        
        $ar_tt = array();
//        $doc1= new Entity($doc1_id);
//        $doc2= new Entity($doc2_id);
//        $ref1= new Entity($ref1_id);
//        $ref2= new Entity($ref2_id);
//        $ref3= new Entity($ref3_id);
        
//        if ($this->get_selectiontable('tt_ref',$ref1_id,$ref2_id,$ref3_id,$ar_tt))
//        {
//            die('ref table error');
//        }    
        
        if ($this->get_doctable('tt_doc1',$doc1_id,$ar_tt))
        {
            die('doc1 table error');
        }    
        if ($this->get_doctable('tt_doc2',$doc2_id,$ar_tt))
        {
            die('doc2 table error');
        }    
        $par1 = $this->get_docparameter($doc1_id, $this->doc_param1_propid);
        $step1 = $par1->getattr($this->step_propid);
        Common_data::_log('/log','Parametr1 step: '.$step1);
        $par2 = $this->get_docparameter($doc1_id, $this->doc_param2_propid);
        $step2 = $par2->getattr($this->step_propid);
        Common_data::_log('/log','Parametr2 step: '.$step2);
        $par3 = $this->get_docparameter($doc1_id, $this->doc_param3_propid);
        $step3 = $par3->getattr($this->step_propid);
        Common_data::_log('/log','Parametr3 step: '.$step3);
//        $sql = "select * from tt_ref limit 100";
//        
//        $res = DataManager::dm_query($sql);	
//        $tt_par = $res->fetchAll(PDO::FETCH_ASSOC);
//        ob_start();
//        var_dump($tt_par);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::_log('/log','REF: '.$dump);
        
        $sql = "select count(*) from tt_doc1";
        $res = DataManager::dm_query($sql);	
        $tt_par = $res->fetchAll(PDO::FETCH_ASSOC);
        $count_1 = $tt_par[0]['count'];
        Common_data::_log('/log','DOC1 item count: '.$count_1);
        
        $sql = "select * from tt_doc1";
        $res = DataManager::dm_query($sql);	
        $tt_doc1 = $res->fetchAll(PDO::FETCH_ASSOC);
        
        
        $sql = "select count(*) from tt_doc2";
        $res = DataManager::dm_query($sql);	
        $tt_par = $res->fetchAll(PDO::FETCH_ASSOC);
        $count_2 = $tt_par[0]['count'];
        Common_data::_log('/log','DOC2 item count: '.$count_2);
        
        $sql = "select * from tt_doc2";
        $res = DataManager::dm_query($sql);	
        $tt_doc2 = $res->fetchAll(PDO::FETCH_ASSOC);
        
        $sql = "select dc1.rank as rank1, dc2.rank as rank2, dc1.itemid as itemid1, dc2.itemid as itemid2, dc1.value1 as value11, dc2.value1 as value21 from tt_doc1 as dc1, tt_doc2 as dc2 where abs(dc1.value1-dc2.value1)<=:step";
        $artt[] = DataManager::createtemptable($sql, 'tt_par1',array('step'=>$step1));        
        $sql = "select dc1.rank as rank1, dc2.rank as rank2, dc1.itemid as itemid1, dc2.itemid as itemid2, dc1.value2 as value12, dc2.value2 as value22 from tt_doc1 as dc1, tt_doc2 as dc2 where abs(dc1.value2-dc2.value2)<=:step";
        $artt[] = DataManager::createtemptable($sql, 'tt_par2',array('step'=>$step2));        
        $sql = "select dc1.rank as rank1, dc2.rank as rank2, dc1.itemid as itemid1, dc2.itemid as itemid2, dc1.value3 as value13, dc2.value3 as value23 from tt_doc1 as dc1, tt_doc2 as dc2 where abs(dc1.value3-dc2.value3)<=:step";
        $artt[] = DataManager::createtemptable($sql, 'tt_par3',array('step'=>$step3));        
//        $res = DataManager::dm_query($sql);	
//        $tt_par = $res->fetchAll(PDO::FETCH_ASSOC);
//        
//        ob_start();
//        var_dump($tt_par);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::_log('/log','PAR1 : '.$dump);
        
//        $res = DataManager::dm_query($sql);	
//        $tt_par = $res->fetchAll(PDO::FETCH_ASSOC);
//        
//        ob_start();
//        var_dump($tt_par);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::_log('/log','PAR2 : '.$dump);
        
//        $sql = "select dc1.rank as rank1, dc2.rank as rank2, dc1.itemid as itemid1, dc2.itemid as itemid2, dc1.value3 as value13, dc2.value3 as value23 from tt_doc1 as dc1                     
//                    inner join tt_ref as rf3 
//                        inner join tt_doc2 as dc2
//                        on rf3.value2=dc2.value3
//                    on dc1.value3=rf3.value1
//                    ";
//        $artt[] = DataManager::createtemptable($sql, 'tt_par3');        
//        $res = DataManager::dm_query($sql);	
//        $tt_par = $res->fetchAll(PDO::FETCH_ASSOC);
//        
//        ob_start();
//        var_dump($tt_par);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::_log('/log','PAR3 : '.$dump);
        
        $sql = "select ins.rank1, ins.rank2, sum(ins.value11), sum(ins.value21), sum(ins.value12), sum(ins.value22), sum(ins.value13), sum(ins.value23), sum(ins.isum) as isum from 
                (select pr1.rank1, pr1.rank2, pr1.value11, pr1.value21,0 as value12, 0 as value22, 0 as value13, 0 as value23, 1 as isum from tt_par1 as pr1 
                union 
                select pr2.rank1, pr2.rank2, 0, 0, pr2.value12, pr2.value22, 0, 0, 1 from tt_par2 as pr2 
                union 
                select pr3.rank1, pr3.rank2, 0, 0, 0, 0, pr3.value13, pr3.value23, 1 from tt_par3 as pr3) as ins group by ins.rank1, ins.rank2";

        $res = DataManager::dm_query($sql);	
        $tt_par = $res->fetchAll(PDO::FETCH_ASSOC);
//        ob_start();
//        var_dump($tt_par);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::_log('/log','RES : '.$dump);
        
        $acosts=array();
        $signatures=array();
        $sums=array();
        for($i=0; $i<$count_1; $i++)
        {
            $acosts[$i]=array();
            $signatures[$i]='';
            $sums[$i]=0;
            for($j=0; $j<$count_2; $j++)
            {
                $acosts[$i][$j]=3;
                $signatures[$i] .= '0';
            }
        }
        foreach ($tt_par as $par)
        {
            $i = $par['rank1']-1;
            $j = $par['rank2']-1;
            $acosts[$i][$j]=3-$par['isum'];
            $signatures[$i][$j] = '1';
            $sums[$i] += 1;
        }
        $objd = array();
        
//        $arr_0 = array_filter($sums,function($var){ return $var==0;});
//        ob_start();
//        var_dump($arr_0);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::_log('/log','строки 0 : '.$dump);
//
//        $arr_1 = array_filter($sums,function($var){ return $var==1;});
//        ob_start();
//        var_dump($arr_1);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::_log('/log','строки 1 : '.$dump);
//        foreach($arr_1 as $key=>$row) 
//        {
//            $pos = strpos($row, '1');
//            for($i=0; $i<$count_1; $i++)
//            {
//                if ($key==$i)
//                {
//                    continue;
//                }    
//                if ($signatures[$i][$pos]=='1')
//                {
//                    $signatures[$i][$pos] = '0';
//                    $sums[$i] -= 1;
//                }    
//            }    
//            $i = $par['rank1']-1;
//            $j = $par['rank2']-1;
//            $objd[$i] = array();
//            $objd[$i]['nom1']=array('name'=>$par['rank1'],'id'=>'');
//            $objd[$i]['nom2']=array('name'=>$par['rank2'],'id'=>'');
//            $objd[$i]['izm11']=array('name'=>$tt_doc1[$pos]['value1'],'id'=>'');
//            $objd[$i]['izm12']=array('name'=>$tt_doc1[$pos]['value2'],'id'=>'');
//            $objd[$i]['izm13']=array('name'=>$tt_doc1[$pos]['value3'],'id'=>'');
//            $objd[$i]['izm21']=array('name'=>$tt_doc2[$pos]['value1'],'id'=>'');
//            $objd[$i]['izm22']=array('name'=>$tt_doc2[$pos]['value2'],'id'=>'');
//            $objd[$i]['izm23']=array('name'=>$tt_doc2[$pos]['value3'],'id'=>'');
//            $objd[$i]['error']=array('name'=>$error,'id'=>'');
//        }    
        
//        for($i=0; $i<$count_1; $i++)
//        {
//            Common_data::_log('/log','signature '.$i.' = '.$signatures[$i].' sum = '.$sums[$i]);
//        }
        
        //удаляем строки по которым нет возможных пар
        $costs=array();
        $addr_row=array();
        for($i=0; $i<$count_1; $i++)
        {
            $sum = 0;
            for($j=0; $j<$count_2; $j++)
            {
                $sum += $acosts[$i][$j];
            }
            if ((3*$count_2 - $sum)>0)
            {
                $costs[] = $acosts[$i];
                $addr_row[] = $i;
                $msg = 'COSTS : '.($i+1).' :';
                for($j=0; $j<$count_2; $j++)
                {
                    if ($acosts[$i][$j]==0)
                    {    
                        $msg .= ', '.($j+1);
                    }    
                }
                //Common_data::_log('/log',$msg);
            }    
            else 
            {
                Common_data::_log('/log','without row: '.($i+1));
            }
        }
        
//        ob_start();
//        var_dump($costs);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::_log('/log','COSTS : '.$dump);
        
        //$hung = new Hungarian($costs);
        $hung = new tzHung($costs);
        $res = $hung->execute();
//        ob_start();
//        var_dump($res);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::_log('/log','RESULT : '.$dump);
        Common_data::_log('/log','HUNGARY FINISH');
        DataManager::droptemptable($ar_tt);
        $objs['SDATA'] = array();
        $objs['PLIST'] = array();
        $objs['PSET'] = array(
            'num'=>array('id'=>'num','name'=>'num','synonym'=>'Номер п/п','type'=>'int', 'class'=>'active'),
            'nom1'=>array('id'=>'nom1','name'=>'nom1','synonym'=>'Изделие 1','type'=>'int', 'class'=>'active'),
            'nom2'=>array('id'=>'nom2','name'=>'nom2','synonym'=>'Изделие 2','type'=>'int', 'class'=>'active'),
            'izm11'=>array('id'=>'izm11','name'=>'izm11','synonym'=>'Изд1: Изм.1','type'=>'float', 'class'=>'active'),
            'izm21'=>array('id'=>'izm21','name'=>'izm21','synonym'=>'Изд2: Изм.1','type'=>'float', 'class'=>'active'),
            'izm12'=>array('id'=>'izm12','name'=>'izm12','synonym'=>'Изд1: Изм.2','type'=>'float', 'class'=>'active'),
            'izm22'=>array('id'=>'izm22','name'=>'izm22','synonym'=>'Изд2: Изм.2','type'=>'float', 'class'=>'active'),
            'izm13'=>array('id'=>'izm13','name'=>'izm13','synonym'=>'Изд1: Изм.3','type'=>'float', 'class'=>'active'),
            'izm23'=>array('id'=>'izm23','name'=>'izm23','synonym'=>'Изд2: Изм.3','type'=>'float', 'class'=>'active'),
            //'error'=>array('id'=>'error','name'=>'error','synonym'=>'Ошибка','type'=>'int', 'class'=>'active')
        );
        foreach($res as $par)
        {
            $i = $par[0];
            $j = $par[1];
            
            $pos1 = array_search($addr_row[$i]+1, array_column($tt_doc1,'rank'));
            if ($pos1===false)
            {
                continue;
            }    
            $pos2 = array_search($j+1, array_column($tt_doc2,'rank'));
            if ($pos2===false)
            {
                continue;
            }    
            $error = '0';
            if (abs($tt_doc1[$pos1]['value1']-$tt_doc2[$pos2]['value1'])>$step1)
            {
                $error = '1';
                continue;
            }    
            if (abs($tt_doc1[$pos1]['value2']-$tt_doc2[$pos2]['value2'])>$step2)
            {
                $error .= '2';
                continue;
            }    
            if (abs($tt_doc1[$pos1]['value3']-$tt_doc2[$pos2]['value3'])>$step3)
            {
                $error .= '3';
                continue;
            }    
            $objd[$i] = array();
            $objd[$i]['nom1']=array('name'=>$addr_row[$i]+1,'id'=>'');
            $objd[$i]['nom2']=array('name'=>$j+1,'id'=>'');
            $objd[$i]['izm11']=array('name'=>$tt_doc1[$pos1]['value1'],'id'=>'');
            $objd[$i]['izm12']=array('name'=>$tt_doc1[$pos1]['value2'],'id'=>'');
            $objd[$i]['izm13']=array('name'=>$tt_doc1[$pos1]['value3'],'id'=>'');
            $objd[$i]['izm21']=array('name'=>$tt_doc2[$pos2]['value1'],'id'=>'');
            $objd[$i]['izm22']=array('name'=>$tt_doc2[$pos2]['value2'],'id'=>'');
            $objd[$i]['izm23']=array('name'=>$tt_doc2[$pos2]['value3'],'id'=>'');
            //$objd[$i]['error']=array('name'=>$error,'id'=>'');
        }    
        $objs['LDATA'] = $objd;
        return $objs;
    }    
}    