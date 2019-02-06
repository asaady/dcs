<?php
namespace Dcs\Vendor\Core\Models;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");
use PDO;
//use dcs\vendor\core\Mditem;

class MdentitySet extends Sheet implements I_Sheet
{
    use T_Sheet;
    
    public function __construct($id='',$hd='')
    {
        $this->id = $id;
        $this->isnew = false;
        if ($id) {
            $arData = $this->getDetails($id);

            if (!$arData) {
                throw new DcsException("Class ".get_called_class().
                    " constructor: id is wrong",DCS_ERROR_WRONG_PARAMETER);
            }
            $this->name = $arData['name']; 
            $this->synonym = $arData['synonym']; 
        }
        $this->plist = array();
        $this->properties = array();
        $this->data = array();
        $this->version = time();
    }
    public function dbtablename()
    {
        return 'CTable';
    }
    public function txtsql_forDetails() 
    {
        return "SELECT ct.id, ct.name, ct.synonym, 
                NULL as mdid, '' as mdname, '' as mdsynonym,
                NULL as mditem, '' as mdtypename, '' as mdtypedescription
                FROM \"CTable\" as ct 
                LEFT JOIN \"MDTable\" as md
                ON ct.mdid=md.id AND md.name='MDitems' WHERE ct.id=:id";
    }        
    function head()
    {
        return NULL;
    }
    function item($id)
    {
        return new Mdentity($id,$this);
    }
    public function getprop_classname()
    {
        return NULL;
    }
    public function item_classname()
    {
        return 'Mdentity';
    }        
    function load_data($data='') 
    {
        if (!$data) {
            return array('id'=>array('id'=>$this->id, 'name'=>$this->id),
                          'name'=>array('id'=>'','name'=>$this->name),
                          'synonym'=>array('id'=>'', 'name'=>$this->synonym)
                );
        }
        return array('id'=>array('id'=>$data['id'], 'name'=>$data['id']),
                      'name'=>array('id'=>$data['name'],'name'=>$data['name']),
                      'synonym'=>array('id'=>$data['synonym'], 'name'=>$data['synonym'])
            );
    }
    public function get_items() 
    {
        $params = array();
	$sql = "SELECT md.id, md.name, md.synonym, "
                . "md.mditem as id_mditem, ct.name as name_mditem, 'mditem' as propid_mditem "
                . "FROM \"MDTable\" AS md "
                . "INNER JOIN \"CTable\" as ct "
                . "ON md.mditem = ct.id "
                . "WHERE md.mditem= :mditem";
        if ($this->id) {
            $params['mditem'] = $this->id;
        } else {
            $sql = str_replace('WHERE md.mditem= :mditem', '', $sql);
        }
        $dop = DataManager::get_md_access_text();
        if ($dop != '')
        {    
            $params['userid'] = $_SESSION['user_id'];
            $sql .= " AND ".$dop;
        }    
        return DataManager::dm_query($sql,$params);        
    }        
    public function getItemsByName($name) 
    {
        $params = array();
	$sql = "SELECT md.id, md.name, md.synonym, "
                . "md.mditem as id_mditem, ct.name as name_mditem, 'mditem' as propid_mditem "
                . "FROM \"MDTable\" AS md "
                . "INNER JOIN \"CTable\" as ct "
                . "ON md.mditem = ct.id "
                . "WHERE (md.name ILIKE :name OR md.synonym ILIKE :name) "
                . "AND md.mditem= :mditem";
        if ((!$this->id)||($this->id == DCS_EMPTY_ENTITY)) {
            $sql = str_replace('AND md.mditem= :mditem', '', $sql);
        } else {
            $params['mditem'] = $this->id;
        }
        $dop = DataManager::get_md_access_text();
        if ($dop != '')
        {    
            $params['userid'] = $_SESSION['user_id'];
            $sql .= " AND ".$dop;
        }    
        $sql .= " LIMIT 5";
        $params['name'] = "%$name%";
	$res = DataManager::dm_query($sql,$params);
	$rows = $res->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }
//    public function getItems($filter=array()) 
//    {
//	$sql = "SELECT md.id, md.name, md.synonym, md.mditem FROM \"MDTable\" AS md WHERE md.mditem= :mditem";
//        $params = array('mditem'=>$this->id);
//        $dop = DataManager::get_md_access_text();
//        if ($dop != '')
//        {    
//            $params['userid'] = $_SESSION['user_id'];
//            $sql .= " AND ".$dop;
//        }    
//        $sth = DataManager::dm_query($sql,$params);        
//        $objs = array();
//        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
//        {
//            $objs[$row['id']] = array();
//            $objs[$row['id']]['id'] = array('id'=>'','name'=>$row['id']);
//            $objs[$row['id']]['name'] = array('id'=>'','name'=>$row['name']);
//            $objs[$row['id']]['synonym'] = array('id'=>'','name'=>$row['synonym']);
//            $objs[$row['id']]['class'] = 'active';
//        }
//        return $objs;
//    }
    public function setcontext_action($param, $prefix) 
    {
        if ($prefix == 'CONFIG')
        {
            return 'EDIT';
        }  else {
            return 'VIEW';
        }
        
    }        
    public function getplist() 
    {
        $context = DcsContext::getcontext();
        if ($context->getattr('PREFIX') !== 'CONFIG') {
            return array();
        }    
        return array(
            '0'=>array('id'=>'id','name'=>'id','synonym'=>'ID',
                        'rank'=>0,'ranktoset'=>1,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'','name_valmditem'=>'','class'=>'readonly','field'=>1),
            '1'=>array('id'=>'name','name'=>'name','synonym'=>'NAME',
                        'rank'=>1,'ranktoset'=>2,'ranktostring'=>1,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'','name_valmditem'=>'','class'=>'active','field'=>1),
            '2'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
                        'rank'=>2,'ranktoset'=>3,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'','name_valmditem'=>'','class'=>'active','field'=>1),
            '3'=>array('id'=>'version','name'=>'version','synonym'=>'VERSION',
                        'rank'=>4,'ranktoset'=>0,'ranktostring'=>0,
                        'name_type'=>'str','name_valmdid'=>'','id_valmdid'=>'','name_valmditem'=>'','class'=>'hidden','field'=>0),
             );
    }
    public function create_object($name,$synonym='')
    {
        return NULL;
    }        
    public function txtsql_property($parname)
    {
        return NULL;    
    }        
    public function txtsql_properties($parname)
    {
        return NULL;    
    }        
    public function loadProperties() 
    {
        $properties = array(
            'id' => array(
                    'id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,
                    'ranktoset'=>1,'ranktostring'=>0,'type'=>'str',
                    'name_type'=>'str','valmdid'=>'','valmdtypename'=>'',
                    'class'=>'hidden','field'=>1),
            'name' => array(
                    'id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,
                    'ranktoset'=>2,'ranktostring'=>1,'type'=>'str',
                    'name_type'=>'str','valmdid'=>'','valmdtypename'=>'',
                    'class'=>'active','field'=>1),
            'synonym'=>array(
                    'id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM',
                    'rank'=>3,'ranktoset'=>3,'ranktostring'=>0,'type'=>'str',
                    'name_type'=>'str','valmdid'=>'','valmdtypename'=>'',
                    'class'=>'active','field'=>1),
            'mditem'=>array(
                    'id'=>'mditem','name'=>'mditem','synonym'=>'MDITEM',
                    'rank'=>4,'ranktoset'=>4,'ranktostring'=>0,'type'=>'cid',
                    'name_type'=>'cid','valmdid'=>'','valmdtypename'=>'Cols',
                    'class'=>'hidden','field'=>1)
            );        
        $this->properties = $properties;
        return $properties;
    }        
}

