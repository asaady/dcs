<?php
namespace Dcs\Vendor\Core\Models;
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_STRING)."/app/dcs_const.php");
use PDO;
//use dcs\vendor\core\Mditem;

class MdentitySet extends Sheet implements I_Sheet
{
    use T_Sheet;
    use T_Property;
    
    public function txtsql_forDetails() 
    {
        return "SELECT ct.id, ct.name, ct.synonym, 
                NULL as mdid, '' as mdname, '' as mdsynonym,
                NULL as mditem, '' as mdtypename, '' as mdtypedescription
                FROM \"CTable\" as ct 
                LEFT JOIN \"MDTable\" as md
                ON ct.mdid=md.id AND md.name='MDitems' WHERE ct.id=:id";
    }        
    public function loadProperties() 
    {
        return array(
            'id'=>array('id'=>'id','name'=>'id','synonym'=>'ID','rank'=>0,'ranktoset'=>1,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'hidden','field'=>1),
            'name'=>array('id'=>'name','name'=>'name','synonym'=>'NAME','rank'=>1,'ranktoset'=>2,'ranktostring'=>1,'type'=>'str','valmdtypename'=>'','class'=>'active','field'=>1),
            'synonym'=>array('id'=>'synonym','name'=>'synonym','synonym'=>'SYNONYM','rank'=>3,'ranktoset'=>3,'ranktostring'=>0,'type'=>'str','valmdtypename'=>'','class'=>'active','field'=>1),
            'mditem'=>array('id'=>'mditem','name'=>'mditem','synonym'=>'MDITEM','rank'=>4,'ranktoset'=>0,'ranktostring'=>0,'type'=>'cid','valmdtypename'=>'Cols','class'=>'hidden','field'=>1)
            );        
    }        
    function head()
    {
        return NULL;
    }
    function item()
    {
        return new Mdentity($this->id);
    }
    function load_data() {
        return NULL;
    }
    public function getItems($context) 
    {
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
        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
        {
            $objs[$row['id']] = array();
            $objs[$row['id']]['id'] = array('id'=>'','name'=>$row['id']);
            $objs[$row['id']]['name'] = array('id'=>'','name'=>$row['name']);
            $objs[$row['id']]['synonym'] = array('id'=>'','name'=>$row['synonym']);
            $objs[$row['id']]['class'] = 'active';
        }
        return $objs;
    }
//    public function getItemsByFilter($context) 
//    {
//        $prefix = $context['PREFIX'];
//        $action = $context['ACTION'];
//	$sql = "SELECT md.id, md.name, md.synonym, md.mditem FROM \"MDTable\" AS md WHERE md.mditem= :mditem";
//        $params = array('mditem'=>$this->id);
//        $dop = DataManager::get_md_access_text($action);
//        if ($dop != '')
//        {    
//            $params['userid'] = $_SESSION['user_id'];
//            $sql .= " AND ".$dop;
//        }    
//        $sth = DataManager::dm_query($sql,$params);        
//        $objs = array();
//        $objs['PLIST'] = array();
//        $objs['PSET'] = $this->getProperties(TRUE,'toset');
//        $objs['SDATA'] = array();
//        $objs['SDATA'][$this->id] = array();
//        $objs['SDATA'][$this->id]['id'] = array('id'=>$this->id,'name'=>'');
//        $objs['SDATA'][$this->id]['name'] = array('id'=>'','name'=>$this->name);
//        $objs['SDATA'][$this->id]['synonym'] = array('id'=>'','name'=>$this->synonym);
//        $objs['actionlist'] = DataManager::getActionsbyItem('EntitySet',$prefix,$action);          
//        $objs['navlist'] = $this->get_navlist($context);
//        $objs['LDATA'] = array();
//        while($row = $sth->fetch(PDO::FETCH_ASSOC)) 
//        {
//            $objs['LDATA'][$row['id']] = array();
//            $objs['LDATA'][$row['id']]['id'] = array('id'=>'','name'=>$row['id']);
//            $objs['LDATA'][$row['id']]['name'] = array('id'=>'','name'=>$row['name']);
//            $objs['LDATA'][$row['id']]['synonym'] = array('id'=>'','name'=>$row['synonym']);
//            $objs['LDATA'][$row['id']]['class'] = 'active';
//        }
//        return $objs;
//    }
    public function getItemsByName($name)
    {
        
    }
}

