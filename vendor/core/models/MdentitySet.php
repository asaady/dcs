<?php
namespace Dcs\Vendor\Core\Models;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");
use PDO;
//use dcs\vendor\core\Mditem;

class MdentitySet extends Head implements I_Head
{
    use T_Head;
    
//    public function __construct($id = '') 
//    {
//	if ($id == '') 
//        {
//            throw new Exception("empty mditem");
//	}
//        $res = self::getMDitem($id);
//        $this->id = $id; 
//        $this->name = $res['name']; 
//        $this->synonym = $res['synonym']; 
//        $this->version = time();
//        $this->loadProperties();
//    }
    public function loadProperties() 
    {
        $this->properties = array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'ranktoset'=>1,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'hidden','field'=>1),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'ranktoset'=>2,'ranktostring'=>1,'type'=>'str','valmdtypename'=>'','class'=>'active','field'=>1),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'ranktoset'=>3,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'active','field'=>1),
            'mditem'=>array('id'=>'mditem','name'=>'mditem','synonym'=>'MDITEM','rank'=>4,'ranktoset'=>0,'ranktostring'=>0,'type'=>'cid','valmdtypename'=>'Cols','class'=>'hidden','field'=>1)
            );        
    }        
    function item()
    {
        return new Mdentity($this->id);
    }
    function load_data() {
        return NULL;
    }
//    function getProperties($mode = '') 
//    {
//        if ($mode == 'CONFIG')
//        {
//            $this->properties['id']['class'] = 'readonly';
//        }    
//        return $this->properties;
//    }
    
    public function getItemsByFilter($context, $filter) 
    {
        $prefix = $context['PREFIX'];
        $action = $context['ACTION'];
	$sql = "SELECT md.id, md.name, md.synonym, md.mditem FROM \"MDTable\" AS md WHERE md.mditem= :mditem";
        $params = array('mditem'=>$this->id);
        $dop = DataManager::get_md_access_text($action);
        if ($dop != '')
        {    
            $params['userid'] = $_SESSION['user_id'];
            $sql .= " AND ".$dop;
        }    
        $sth = DataManager::dm_query($sql,$params);        
        $objs = array();
        $objs['PLIST'] = array();
        $objs['PSET'] = $this->getProperties(TRUE,'toset');
        $objs['SDATA'] = array();
        $objs['SDATA'][$this->id] = array();
        $objs['SDATA'][$this->id]['id'] = array('id'=>$this->id,'name'=>'');
        $objs['SDATA'][$this->id]['name'] = array('id'=>'','name'=>$this->name);
        $objs['SDATA'][$this->id]['synonym'] = array('id'=>'','name'=>$this->synonym);
        $objs['actionlist'] = DataManager::getActionsbyItem('EntitySet',$prefix,$action);          
        $objs['navlist'] = $this->get_navlist($context);
        $objs['LDATA'] = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs['LDATA'][$row['id']] = array();
            $objs['LDATA'][$row['id']]['id'] = array('id'=>'','name'=>$row['id']);
            $objs['LDATA'][$row['id']]['name'] = array('id'=>'','name'=>$row['name']);
            $objs['LDATA'][$row['id']]['synonym'] = array('id'=>'','name'=>$row['synonym']);
            $objs['LDATA'][$row['id']]['class'] = 'active';
        }
        return $objs;
    }
//    public function create($data)
//    {
//        $objs = array();
//        $params = array();
//        $props= array('name','synonym');
//        $sql='';
//        $fname ='';
//        $fval = '';
//        foreach ($props as $prop)
//        {    
//            if (array_key_exists($prop, $data))
//            {
//                $fname .=", $prop";
//                $fval .=", :$prop";
//                $params[$prop]=$data[$prop]['name'];
//            }    
//        }
//        $fname = substr($fname,1);
//        $fval  = substr($fval,1);
//        $objs['status']='NONE';
//        if ($fname!='')
//        {
//            $objs['status']='OK';
//            $sql ="INSERT INTO \"MDTable\" ($fname, mditem) VALUES ($fval,:mditem) RETURNING \"id\"";
//            $params['mditem']=$this->id;
//            $res = DataManager::dm_query($sql,$params);
//            if(!$res) 
//            {
//                $objs['status']='ERROR';
//                $objs['msg']=$sql;
//            }
//            else 
//            {
//                $row = $res->fetch(PDO::FETCH_ASSOC); 
//                $objs['id'] = $row['id'];
//            }
//        }
//        if ($objs['status']=='OK')
//        {
//            Mdproperty::CreateMustBeProperty($this->id,$objs['id']);
//        }
//            
//        return $objs;
//    }       
    public function getItemsByName($name)
    {
        
    }
}

