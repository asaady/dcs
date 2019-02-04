<?php
namespace Dcs\App\Components\Utils\Uploadobject;

use PDO;
use PDOStatement;
use Dcs\Vendor\Core\Models\Entity;
use Dcs\Vendor\Core\Models\EntitySet;
use Dcs\Vendor\Core\Models\Sheet;
use Dcs\Vendor\Core\Models\CollectionItem;
use Dcs\Vendor\Core\Models\DataManager;
use Dcs\Vendor\Core\Models\Common_data;
use Dcs\Vendor\Core\Models\DcsException;
use Dcs\Vendor\Core\Models\DcsContext;

require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");

class UploadObject 
{
    protected $id;
    protected $head;     
    protected $entity;
    protected $target_mdid;
    protected $filename;

    public function __construct() 
    {
        $context = DcsContext::getcontext();
        $id = $context->getattr('ITEMID');
        if (!$id)
        {
            throw new DcsException("class.UploadObject constructor: id is empty");
        }
        $this->entity = new CollectionItem($id);
        if ($this->entity->getname() !== 'Uploadobject')
        {
            die('id = '.$id.' name = '.var_dump($this->entity->getname()));
            throw new DcsException("class.Uploadobject constructor: bad id");
        }    
        $this->id = $id;
        $this->head = $this->entity->head();
    }
    public function head() 
    {
        return $this->entity->head();
    }
    public function dbtablename()
    {
        return "";
    }
    public function getid() 
    {
        return $this->id;
    }
    public function item() 
    {
        return NULL;
    }
    public function item_classname()
    {
        return "";
    }        
    public function getprop_classname()
    {
        return NULL;
    }
    public function get_data()
    {
        $objs = array(
          'id'=>$this->id,
          'name'=>$this->entity->getname(),
          'synonym'=>$this->entity->getsynonym(),
          'version'=>$this->entity->getversion()
        );
        $objs['PLIST'] = $this->getplist();
        $objs['PSET'] = array();
        return $objs;
    }
    public function getplist()
    {        
        $plist = array(
            '0'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'readonly','field'=>1),
            '1'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'readonly','field'=>1),
            '2'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
                        'rank'=>2,'ranktoset'=>3,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'readonly','field'=>1),
            '3'=>array('id'=>'target_mdid','name'=>'target_mdid','synonym'=>'Тип объекта',
                        'rank'=>3,'ranktoset'=>4,'ranktostring'=>0,
                        'name_type'=>'mdid','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
            '4'=>array('id'=>'filename','name'=>'filename','synonym'=>'Файл импорта',
                        'rank'=>5,'ranktoset'=>5,'ranktostring'=>0,
                        'name_type'=>'file','name_valmdid'=>'','id_valmdid'=>'',
                        'name_valmditem'=>'','class'=>'active','field'=>1),
             );
        $this->plist = $plist;
        return $plist;
    }
    public function txtsql_forDetails() 
    {
        return "";
    }
    public function txtsql_property($parname)
    {
        return NULL;
    }        
    public function txtsql_properties($parname)
    {
        return NULL;
    }        
    public function add_navlist(&$navlist) 
    {
        if ($this->head) {
            $phead = $this->head;
            $phead->add_navlist($navlist); 
            $strval = $phead->getNameFromData()['synonym'];
            $navlist[] = array('id'=>$this->head->getid(),'name'=>sprintf("%s",$strval));
        }
    }
    public function get_navlist()
    {
        $navlist = array();
        $strkey = $this->id;
        $strval = $this->entity->getsynonym();
        $this->add_navlist($navlist);
        $navlist[] = array('id' => $strkey,'name' => sprintf("%s",$strval));
        return $navlist;
    }        
    public function load_data($data='')
    {
        $data = array();
        $data['id'] = array('id'=>'','name'=>$this->id);
        $data['name'] = array('id'=>'','name'=>$this->entity->getname());
        $data['synonym'] = array('id'=>'','name'=>$this->entity->getsynonym());
        return $data;
    }            
    public function getItemsByName($name) 
    {
        $sql = "select md.id, md.name, md.synonym from \"MDTable\" as md "
                . "where md.name ilike :name or md.synonym ilike :name";
        $params = array();
        $params['name'] = '%'.$name.'%';
        $res = DataManager::dm_query($sql, $params);
        $objs = array();
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $objs[$row['id']] = array('id'=>$row['id'],'name'=>$row['synonym']);
        }
        return $objs;
    }        
//        return array(
//          'id'=>$this->id,
//          'name'=>$this->name,
//          'version'=>$this->version,
//          'PLIST' => $plist,   
//          'PSET' => $pset,   
//          'SDATA' => $sdata,   
//          'LDATA' => array(),   
//          'navlist' => array(
//              $this->entity->getcollectionset()->getmditem()->getid()=>$this->entity->getcollectionset()->getmditem()->getsynonym(),
//              $this->entity->getcollectionset()->getid()=>$this->entity->getcollectionset()->getsynonym(),
//              $this->id=>$this->name
//            )
//          );
//    }   
    public function exec()
    {
        return array('status'=>'ok');
    }        
    
    public function create_entity($curname, $plist, $arr)
    {        
        //создадим элемент
        $sql = "insert into \"ETable\" (name,mdid) values (:name,:mdid) returning id";
        DataManager::dm_beginTransaction();
        try 
        {
            $res = DataManager::dm_query($sql, array('name'=>$curname,'mdid'=> $this->target_mdid));	
        } catch (DcsException $ex) {
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