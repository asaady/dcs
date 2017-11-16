<?php
namespace tzVendor;

use PDO;
use PDOStatement;
use tzVendor\Entity;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");

class UploadSet extends Model 
{
    protected $entity;
    protected $target_mdid;
    protected $setpropid;
    protected $target_id;
    protected $filename;
    protected $setid;

    public function __construct($id) 
    {
        
        if ($id=='')
        {
            die("class.UploadSet constructor: id is empty");
        }
        $this->entity = new CollectionItem($id);
        
        if ($this->entity->getname()!='UploadSet')
        {
            die("class.UploadSet constructor: bad id");
        }    
        $this->id = $id;
        $this->name = $this->entity->getsynonym();
        $this->version = time();        
    }
    public function get_data($data)
    {
        $sdata = array();
        $plist = array(
                array('id'=>'target_mdid','name'=>'target_mdid','synonym'=>'Тип объекта','rank'=>1,'type'=>'mdid','valmdid'=>'','valmdtypename'=>'','class'=>'active'),
                array('id'=>'setpropid','name'=>'setpropid','synonym'=>'табличная часть','rank'=>2,'type'=>'propid','valmdid'=>'','valmdtypename'=>'','class'=>'active'),
                array('id'=>'target_id','name'=>'target_id','synonym'=>'Объект','rank'=>3,'type'=>'id','valmdid'=>'','valmdtypename'=>'','class'=>'active'),
                array('id'=>'filename','name'=>'filename','synonym'=>'Файл импорта','rank'=>5,'type'=>'text','valmdid'=>'','valmdtypename'=>'','class'=>'active')
        );
        $pset=array();

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
    public function import($target_id,$target_mdid,$setpropid)
    {
        $this->target_id = $target_id;
        $this->target_mdid = $target_mdid;
        $this->setpropid = $setpropid;
        $curm = date("Ym");
        $this->filename = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/upload/import/".$curm."/".$target_id."_".$setpropid.".csv";
        
        //реквизит ТЧ в которую грузим по setpropid 
        $prop = new Mdproperty($setpropid);
        $set_mdid = $prop->getpropstemplate()->getvalmdentity()->getid();
        
        //найдем значение реквизита ТЧ в которую грузим по setpropid
        $sql = "select pv.value from \"PropValue_id\" as pv inner join \"IDTable\" as it on pv.id=it.id where it.propid = :propid and it.entityid = :entityid";
	$res = DataManager::dm_query($sql, array('propid'=>$setpropid,'entityid'=>$target_id));	
        $ar_set = $res->fetchAll(PDO::FETCH_ASSOC);
        if (count($ar_set))
        {
            $this->setid = $ar_set[0]['value'];
            Common_data::import_log('setid = '.$this->setid);
        }    
        else 
        {
            //создадим табличную часть
            $sql = "insert into \"ETable\" (name,mdid) values (:name,:mdid) returning id";
            $res = DataManager::dm_query($sql, array('name'=>$prop->getsynonym(),'mdid'=>$set_mdid));	
            $ar_set = $res->fetchAll(PDO::FETCH_ASSOC);
            $this->setid = $ar_set[0]['id'];
            //запишем ее в реквизит
            $sql = "insert into \"IDTable\" (entityid,propid,userid) values (:setid,:propid,:userid) returning id";
            $params=array();
            $params['setid']=$this->target_id;
            $params['propid']=$setpropid;
            $params['userid']=$_SESSION['user_id'];
            $res = DataManager::dm_query($sql, $params);	
            $ar_trans = $res->fetchAll(PDO::FETCH_ASSOC);
            $trans_id = $ar_trans[0]['id'];
            $sql = "insert into \"PropValue_id\" (id,value) values (:trans_id,:setid)";
            $params=array();
            $params['setid']=$this->setid;
            $params['trans_id']=$trans_id;
            $res = DataManager::dm_query($sql, $params);	
            Common_data::import_log('created setid = '.$this->setid);
        }
        //найдем id реквизита строка ТЧ в ТЧ в которую грузим по setid
        $sql = DataManager::get_select_properties(" WHERE mp.mdid = :mdid AND mp.rank>0 ");
	$res = DataManager::dm_query($sql,array('mdid'=>$set_mdid));
        $set_plist = $res->fetchAll(PDO::FETCH_ASSOC);
        $item_propid = '';
        $item_mdid = '';
        foreach ($set_plist as $prop)
        {
            if ($prop['valmdtypename']=='Items')
            {
                $item_propid = $prop['id'];
                $item_mdid = $prop['valmdid'];
                break;
            }    
        }    
        if ($item_mdid == '')
        {
            $emessage = 'in set '.$prop['name'].' item property not found';
            Common_data::import_log($emessage);
            return array('status'=>'error','message'=>$emessage);
        }    
        //получим структура реквизитов строки ТЧ
        $sql = DataManager::get_select_properties(" WHERE mp.mdid = :mdid AND mp.rank>0 ");
	$res = DataManager::dm_query($sql,array('mdid'=>$item_mdid));
        $item_plist = $res->fetchAll(PDO::FETCH_ASSOC);
        //создадим временную таблицу для импорта структуру возьмем из $item_plist
        $sql = "CREATE TEMP TABLE tt_imp (";
        $fields='';
        $act_prop='';
        $rank_prop='';
        foreach ($item_plist as $prop)
        {
            if (strtolower($prop['name'])=='activity')
            {
                $act_prop=$prop['id'];
                Common_data::import_log('activity propid:'.$act_prop);
                continue;
            }    
            if (strtolower($prop['name'])=='rank')
            {
                $rank_prop=$prop['id'];
                Common_data::import_log('rank propid:'.$rank_prop);
                continue;
            }    
            $propname = str_replace(' ','',$prop['name']);
            $propname = str_replace('/','',$propname);
            $propname = str_replace('.','',$propname);
            $type = Common_data::type_to_db($prop['type']);
                
            $fields .=', '.$propname.' '.$type;
        } 
        $fields = substr($fields,1);
        $sql .= $fields.");";
        Common_data::import_log('create temp table sql:'.$sql);
        $res = DataManager::dm_query($sql);
        $sql = "COPY tt_imp FROM '".$this->filename."' DELIMITER ',' CSV;";
        $res = DataManager::dm_query($sql);
        Common_data::import_log('import sql:'.$sql);
        $sql = "select * from tt_imp";
        $res = DataManager::dm_query($sql);
        $tt_imp = $res->fetchAll(PDO::FETCH_ASSOC);
        $sql = "drop table tt_imp";        
        $res = DataManager::dm_query($sql);
        if (!count($tt_imp))
        {
            $emessage = 'import table is empty';
            Common_data::import_log($emessage);
            return array('status'=>'error','message'=>$emessage);
        }    
        $ar_tt=array();
        if ($act_prop!='')
        {    
            //найдем все существующие строки в ТЧ
            $sql = "create temp table tt_item as (select sdl.childid as entityid, sdl.childid as id from \"SetDepList\" as sdl where sdl.parentid=:setid)";
            $res = DataManager::dm_query($sql,array('setid'=>$this->setid));
            $ar_tt[] = 'tt_item';
            
            //теперь ищем последние значения реквизита activity в найденных строках
            $ar_tt[] = DataManager::getTT_from_ttent_prop('tt_act','tt_item',$act_prop,'bool');
            
            //выберем только не удаленные в найденных строках
            $sql = "select tt.entityid, coalesce(act.value,TRUE) as value from tt_item as tt left join tt_act as act on tt.entityid = act.entityid where coalesce(act.value,TRUE)=TRUE";
            $res = DataManager::dm_query($sql);
            $ar_item = $res->fetchAll(PDO::FETCH_ASSOC);
            //пометим на удаление все существующие строки в ТЧ
            foreach ($ar_item as $arr) 
            {
                $sql = "insert into \"IDTable\" (entityid,propid,userid) values (:itemid,:propid,:userid) returning id";
                $params=array();
                $params['itemid']=$arr['entityid'];
                $params['propid']=$act_prop;
                $params['userid']=$_SESSION['user_id'];
                DataManager::dm_beginTransaction();
                try 
                {
                    $res = DataManager::dm_query($sql, $params);	
                } catch (Exception $ex) {
                    Common_data::import_log('sql = '.$sql." ERROR: ".$ex->getMessage());
                    DataManager::dm_rollback();
                    continue;
                }
                $ar_trans = $res->fetchAll(PDO::FETCH_ASSOC);
                $trans_id = $ar_trans[0]['id'];
                $sql = "insert into \"PropValue_bool\" (id,value) values (:trans_id,:val)";
                $params=array();
                $params['val']='false';
                $params['trans_id']=$trans_id;
                try {
                    $res = DataManager::dm_query($sql, $params);	
                } 
                catch (Exception $ex) 
                {
                    Common_data::import_log('sql = '.$sql." ERROR: ".$ex->getMessage());
                    DataManager::dm_rollback();
                    continue;
                }
                //Common_data::import_log('deleted itemid = '.$arr['entityid']);
                DataManager::dm_commit();
            }
        }
        $irank=0;
        foreach ($tt_imp as $arr) 
        {
            $irank++;
            //создадим строку табличной части
            $sql = "insert into \"ETable\" (name,mdid) values (:name,:mdid) returning id";
            DataManager::dm_beginTransaction();
            try 
            {
                $res = DataManager::dm_query($sql, array('name'=>$irank,'mdid'=>$item_mdid));	
            } catch (Exception $ex) {
                Common_data::import_log('sql = '.$sql." ERROR: ".$ex->getMessage());
                DataManager::dm_rollback();
                continue;
            }
            $ar_item = $res->fetchAll(PDO::FETCH_ASSOC);
            $itemid = $ar_item[0]['id'];
            $sql = "insert into \"SetDepList\" (parentid,childid,rank) values (:setid,:itemid,:rank)";
            $params=array();
            $params['itemid']=$itemid;
            $params['setid']=$this->setid;
            $params['rank']=$irank;
            try 
            {
                $res = DataManager::dm_query($sql, $params);	
            } catch (Exception $ex) {
                Common_data::import_log('sql = '.$sql." ERROR: ".$ex->getMessage());
                DataManager::dm_rollback();
                continue;
            }

            //Common_data::import_log('rank = '.$irank.' insert item :'.$itemid);
            $err = '';
            foreach ($item_plist as $prop)
            {
                $sql = "insert into \"IDTable\" (entityid,propid,userid) values (:itemid,:propid,:userid) returning id";
                $params=array();
                $params['itemid']=$itemid;
                $params['propid']=$prop['id'];
                $params['userid']=$_SESSION['user_id'];
                try {
                    $res = DataManager::dm_query($sql, $params);	
                } 
                catch (Exception $ex) 
                {
                    Common_data::import_log('sql = '.$sql." ERROR: ".$ex->getMessage());
                    DataManager::dm_rollback();
                    $err = 'error';
                    break;
                }
                $ar_trans = $res->fetchAll(PDO::FETCH_ASSOC);
                $trans_id = $ar_trans[0]['id'];
                if (strtolower($prop['name'])=='activity')
                {
                    $val = 'true';
                }
                elseif (strtolower($prop['name'])=='rank')    
                {
                    $val = $irank;
                }    
                else 
                {
                    $propname = str_replace(' ','',$prop['name']);
                    $propname = str_replace('/','',$propname);
                    $propname = str_replace('.','',$propname);
                    $val = $arr[$propname];
                }
                $sql = "insert into \"PropValue_$prop[type]\" (id,value) values (:trans_id,:val)";
                $params=array();
                $params['val']=$val;
                $params['trans_id']=$trans_id;
                try 
                {
                    $res = DataManager::dm_query($sql, $params);	
                } 
                catch (Exception $ex) 
                {
                    Common_data::import_log('sql = '.$sql." ERROR: ".$ex->getMessage());
                    DataManager::dm_rollback();
                    $err = 'error';
                    break;
                }
                //Common_data::import_log('rank = '.$irank.' insert prop :'.$prop['name']." value = ".$val);
            }
            if ($err=='')
            {    
                DataManager::dm_commit();
            }    
        }
//        ob_start();
//        var_dump($tt_imp);
//        $dump = ob_get_contents();
//        ob_end_clean();        
//        Common_data::import_log($dump);
        DataManager::droptemptable($ar_tt);
    }  
}    