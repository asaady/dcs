<?php
namespace tzVendor;

use PDO;
use PDOStatement;
use tzVendor\Entity;
use tzVendor\EntitySet;
use tzVendor\PropsTemplate;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/tz_const.php");

class UploadObject extends Model 
{
    protected $entity;
    protected $target_mdid;
    protected $filename;

    public function __construct($id) 
    {
        
        if ($id=='')
        {
            die("class.UploadObject constructor: id is empty");
        }
        $this->entity = new CollectionItem($id);
        
        if ($this->entity->getname()!='UploadObject')
        {
            die("class.UploadObject constructor: bad id");
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
    
    public function create_entity($curname, $plist, $arr)
    {        
        //создадим элемент
        $sql = "insert into \"ETable\" (name,mdid) values (:name,:mdid) returning id";
        DataManager::dm_beginTransaction();
        try 
        {
            $res = DataManager::dm_query($sql, array('name'=>$curname,'mdid'=> $this->target_mdid));	
        } catch (Exception $ex) {
            Common_data::import_log('sql = '.$sql." ERROR: ".$ex->getMessage());
            DataManager::dm_rollback();
            return;
        }
        $ar_item = $res->fetchAll(PDO::FETCH_ASSOC);
        $itemid = $ar_item[0]['id'];
        $err = '';
        foreach ($plist as $prop)
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
            else 
            {
                $propname = str_replace(' ','',$prop['name']);
                $propname = str_replace('/','',$propname);
                $propname = strtolower(str_replace('.','',$propname));
                if (($prop['type']=='id')||($prop['type']=='cid')||($prop['type']=='mdid')||($prop['type']=='propid'))
                {
                    $valname = $arr[$propname];
                    //надо поискать по представлению    
                    $md = new Mdproperty($prop['id']);
                    $type = $prop['type'];
                    $objs = EntitySet::search_by_name($md->getvalmdid(),$type,$arr[$propname]);
                    $cur_id='';
                    if (count($objs))
                    {
                        foreach ($objs as $ent)
                        {
                            if ($ent['name']==$arr[$propname])
                            {
                                $cur_id=$ent['id'];
                                break;
                            }    
                        }    
                        if ($cur_id=='')
                        {
                            //точного соответствия не нашли
                            //возьмем первый элемент
                            $val=$objs[0]['id'];    
                        }
                        else
                        {
                            $val=$cur_id;    
                        }    
                    }    
                }   
                else 
                {
                    $val = $arr[$propname];
                }
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

    public function _import()
    {
        //найдем реквизиты по mdid
        $sql = DataManager::get_select_properties(" WHERE mp.mdid = :mdid AND mp.ranktoset>0 ");
	$res = DataManager::dm_query($sql,array('mdid'=>$this->target_mdid));
        $plist = $res->fetchAll(PDO::FETCH_ASSOC);
        //создадим временную таблицу для импорта структуру возьмем из $plist
        $sql = "CREATE TEMP TABLE tt_imp (";
        $fields='';
        $act_prop='';
        $arr_tostring=array();
        foreach ($plist as $prop)
        {
            if (strtolower($prop['name'])=='activity')
            {
                $act_prop=$prop['id'];
                Common_data::import_log('activity propid:'.$act_prop);
                continue;
            }    
            $propname = str_replace(' ','',$prop['name']);
            $propname = str_replace('/','',$propname);
            $propname = str_replace('.','',$propname);
            $type = Common_data::type_to_db($prop['type']);
            $fields .=', '.$propname.' '.$type;
            if ($prop['ranktostring']>0)
            {
                $arr_tostring[]=$prop;
            }    
        } 
        if (!count($arr_tostring))
        {
            $emessage = 'object metadata mdid='.$this->target_mdid. ' has not <tostring> fields';
            Common_data::import_log($emessage);
            return array('status'=>'error','message'=>$emessage);
        }    
        $fields = substr($fields,1);
        $sql .= $fields.");";
        Common_data::import_log('create temp table sql:'.$sql);
        $res = DataManager::dm_query($sql);
        $sql = "COPY tt_imp FROM '".$this->filename."' DELIMITER ';' CSV;";
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
        else
        {
//            ob_start();
//            var_dump($tt_imp);
//            $dump = ob_get_contents();
//            ob_end_clean();        
            Common_data::import_log('count rows to import = '.count($tt_imp));
        }    
        $ar_tt=array();
        $irank=0;
        foreach ($tt_imp as $arr) 
        {
            $irank++;
            $curname='';
            foreach ($arr_tostring as $prop)
            {
                $propname = str_replace(' ','',$prop['name']);
                $propname = str_replace('/','',$propname);
                $propname = strtolower(str_replace('.','',$propname));
                $curname .= ' '.$arr[$propname];
            }    
            if ($curname!='')
            {
                $curname= substr($curname, 1);
            }    
            //поищем элемент с таким именем = $arr_tostring[0] - первый в списке tostring;
            $propname = str_replace(' ','',$arr_tostring[0]['name']);
            $propname = str_replace('/','',$propname);
            $propname = strtolower(str_replace('.','',$propname));
            $objs = EntitySet::search_by_name($this->target_mdid,'id',$arr[$propname]);
            $cur_id='';
            if (count($objs))
            {
                //нашли массив по имени - переберем найденное на полное совпадение.
                foreach ($objs as $ent)
                {
                    if ($ent['name']==$arr[$propname])
                    {
                        $cur_id=$ent['id'];
                        break;
                    }    
                }    
            }   
            if ($cur_id!='')
            {
                $emessage = 'item '.$irank.' : '.$arr[$propname].' is present id = '.$cur_id;
                Common_data::import_log($emessage);
                continue;
            }    
            self::create_entity($curname, $plist, $arr);
        }
        DataManager::droptemptable($ar_tt);
    }        
    public function import($target_mdid)
    {
        $this->target_mdid = $target_mdid;
        $curm = date("Ym");
        $this->filename = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/upload/import/".$curm."/".$target_mdid.".csv";
        
        $mdentity = new EntitySet($target_mdid);
        $method_name = 'self::import_'.strtolower($mdentity->name);
        if (is_callable($method_name))
        {
            call_user_func($method_name);
        }    
        else 
        {
            self::_import();
        }
    }  
}    